<?php

namespace Adam314\CommissionTask\Type;

enum TransactionType: string
{
    case DEPOSIT = 'deposit';
    case WITHDRAW = 'withdraw';
    case COMMISSION = 'commission';
}
