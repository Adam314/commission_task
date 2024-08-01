<?php

namespace Adam314\CommissionTask\Entity;

use Adam314\CommissionTask\Interface\ClientInterface;
use Adam314\CommissionTask\Type\TransactionType;
use Brick\Money\Money;

class Transaction
{
    public const TYPE_DEPOSIT = 1;
    public const TYPE_WITHDRAW = 2;
    public const TYPE_COMMISSION = 3;

    /**
     * @param \DateTimeInterface $date
     * @param ClientInterface $user
     * @param Money $money
     */
    public function __construct(
        private \DateTimeInterface $date,
        private ClientInterface $user,
        private Money $money,
        private TransactionType $type,
        private string $description = '',
    ) {
    }

    public function getDate(): \DateTimeInterface
    {
        return $this->date;
    }

    public function getUser(): ClientInterface
    {
        return $this->user;
    }

    public function getValue(): Money
    {
        return $this->money;
    }

    public function getType(): TransactionType
    {
        return $this->type;
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }
}
