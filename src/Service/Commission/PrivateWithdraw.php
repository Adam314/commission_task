<?php

namespace Adam314\CommissionTask\Service\Commission;

use Adam314\CommissionTask\DependencyInjection\DiContainerInterface;
use Adam314\CommissionTask\Entity\Transaction;
use Adam314\CommissionTask\Service\ExchangeRatesApi;
use Adam314\CommissionTask\Type\TransactionType;
use Adam314\CommissionTask\Util\Date;
use Brick\Math\RoundingMode;
use Brick\Money\Money;

class PrivateWithdraw implements CommissionInterface
{
    public const BASE_CURRENCY = 'EUR';
    public const COMMISSION_RATE = 0.003;
    public const LIMIT_FREE_COMMISSION = 1000;

    private \Adam314\CommissionTask\Repository\Transaction $transactionRepository;

    private ExchangeRatesApi $exchangeRatesApi;
    // construct with exchange and user repository
    public function __construct(private DiContainerInterface $diContainer)
    {
    }

    public function getCommissionForTransaction(Transaction $transaction): Transaction
    {
        $this->transactionRepository = $this->diContainer->getService('transaction_repository');
        // find all withdrawals for the week so far
        $transactions = $this->transactionRepository->findForThisWeek(
            $transaction->getUser()->getId(),
            $transaction->getDate(),
            TransactionType::WITHDRAW
        );

        if (
            (count($transactions) >= 3) || // case 1 - more than 3 withdraws
            ($this->getTotalValueIn($transactions) >= self::LIMIT_FREE_COMMISSION) // case 2 - more than 1000 euro
        ) {
            $newValue = $transaction->getValue()->multipliedBy(self::COMMISSION_RATE, RoundingMode::HALF_UP);
            $description = sprintf(
                "%s%% of %s is %s",
                self::COMMISSION_RATE * 100,
                $transaction->getValue(),
                $newValue
            );
        } elseif (
            $this->getTotalValueIn($transactions) + $this->getValueIn($transaction) > self::LIMIT_FREE_COMMISSION
        ) {
            // need to calculate value of transaction that exceeds free commission
            $totalValue =
                $this->getTotalValueIn($transactions) +
                $this->getValueIn($transaction) -
                self::LIMIT_FREE_COMMISSION;

            $valueForCommission = Money::of($totalValue, self::BASE_CURRENCY);
            if ($transaction->getValue()->getCurrency() != self::BASE_CURRENCY) {
                $rate = $this->exchangeRatesApi->getRate($transaction->getValue()->getCurrency(), self::BASE_CURRENCY);

                $valueForCommission = $valueForCommission->convertedTo(
                    $transaction->getValue()->getCurrency(),
                    1 / $rate,
                    null,
                    RoundingMode::HALF_UP
                );
            }
            $newValue = $valueForCommission->multipliedBy(self::COMMISSION_RATE, RoundingMode::HALF_UP);

            $description = sprintf("%s%% of %s is %s", self::COMMISSION_RATE * 100, $valueForCommission, $newValue);
        } else {
            $newValue = Money::of(0, $transaction->getValue()->getCurrency());
            [$monday, $sunday] = Date::getWeekBoundaries($transaction->getDate());

            $description = sprintf(
                "%d transactions, total %s for cliend %d between %s and %s = free",
                count($transactions),
                $this->getTotalValueIn($transactions),
                $transaction->getUser()->getId(),
                $monday->format('Y-m-d'),
                $sunday->format('Y-m-d'),
            );
        }

        return new Transaction(
            new \DateTime('now'),
            $transaction->getUser(),
            $newValue,
            TransactionType::COMMISSION,
            $description
        );
    }

    private function getTotalValueIn(array $transactions, string $currency = self::BASE_CURRENCY): float
    {
        $sum = 0;
        foreach ($transactions as $transaction) {
            $sum += $this->getValueIn($transaction, $currency);
        }
        return $sum;
    }

    private function getValueIn(Transaction $transaction, string $currency = self::BASE_CURRENCY): float
    {
        if ($transaction->getValue()->getCurrency() == $currency) {
            $returnValue = $transaction->getValue()->getAmount()->toFloat();
        } else {
            // lazy load the service and exchange rates
            if (empty($this->exchangeRatesApi)) {
                $this->exchangeRatesApi = $this->diContainer->getService('exchange_rates_api');
            }
            $rate = $this->exchangeRatesApi->getRate($transaction->getValue()->getCurrency(), $currency);

            $returnValue = $transaction->getValue()->convertedTo(
                $currency,
                $rate,
                null,
                RoundingMode::HALF_UP
            )->getAmount()->toFloat();
        }

        return $returnValue;
    }
}
