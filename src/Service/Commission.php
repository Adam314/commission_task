<?php

namespace Adam314\CommissionTask\Service;

use Adam314\CommissionTask\DependencyInjection\DiContainerInterface;
use Adam314\CommissionTask\Entity\Transaction;
use Adam314\CommissionTask\Interface\ClientInterface;
use Adam314\CommissionTask\Service\Commission\BusinessWithdraw;
use Adam314\CommissionTask\Service\Commission\CommissionInterface;
use Adam314\CommissionTask\Service\Commission\Deposit;
use Adam314\CommissionTask\Service\Commission\PrivateWithdraw;
use Adam314\CommissionTask\Type\ClientType;
use Adam314\CommissionTask\Type\TransactionType;

class Commission
{
    public function __construct(private DiContainerInterface $diContainer)
    {
    }

    public function getCommissionForTransaction(Transaction $transaction): Transaction
    {
        $commissionCalc = $this->getCalcObject($transaction);

        return $commissionCalc->getCommissionForTransaction($transaction);
    }

    protected function getCalcObject(Transaction $transaction): CommissionInterface
    {
        return match ($transaction->getType()) {
            TransactionType::DEPOSIT => new Deposit(),
            TransactionType::WITHDRAW => match ($transaction->getUser()->getType()) {
                ClientType::BUSINESS => new BusinessWithdraw(),
                ClientType::PRIVATE => new PrivateWithdraw($this->diContainer),
            },
            default => null,
        };
    }
}
