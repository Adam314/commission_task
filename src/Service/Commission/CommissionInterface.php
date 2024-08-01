<?php

namespace Adam314\CommissionTask\Service\Commission;

use Adam314\CommissionTask\Entity\Transaction;
use Brick\Money\Money;

interface CommissionInterface
{
    public function getCommissionForTransaction(Transaction $transaction): Transaction;
}
