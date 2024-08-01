<?php

namespace Adam314\CommissionTask\Service\Commission;

use Adam314\CommissionTask\Entity\Transaction;
use Adam314\CommissionTask\Type\TransactionType;
use Brick\Math\RoundingMode;
use Brick\Money\Money;

class Deposit implements CommissionInterface
{
    public const COMMISSION_RATE = 0.0003;

    public function getCommissionForTransaction(Transaction $transaction): Transaction
    {
        $newValue = $transaction->getValue()->multipliedBy(self::COMMISSION_RATE, RoundingMode::HALF_UP);

        return new Transaction(
            new \DateTime('now'),
            $transaction->getUser(),
            $newValue,
            TransactionType::COMMISSION,
            sprintf("%s%% of %s is %s", self::COMMISSION_RATE * 100, $transaction->getValue(), $newValue)
        );
    }
}
