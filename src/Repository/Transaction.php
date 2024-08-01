<?php

namespace Adam314\CommissionTask\Repository;

use Adam314\CommissionTask\Entity\Transaction as TransactionEntity;
use Adam314\CommissionTask\Type\TransactionType;
use Adam314\CommissionTask\Util\Date;

class Transaction
{
    /**
     * @var TransactionEntity[][]
     */
    private $transactionsByClient = [];

    public function save(TransactionEntity $transaction)
    {
        if (empty($this->transactionsByClient[$transaction->getUser()->getId()])) {
            $this->transactionsByClient[$transaction->getUser()->getId()] = [];
        }
        $this->transactionsByClient[$transaction->getUser()->getId()][] = $transaction;
    }

    /**
     * @param int $clientId
     * @return TransactionEntity[]
     */
    public function findBy(int $clientId): array
    {
        return $this->transactionsByClient[$clientId] ?? [];
    }

    public function findForThisWeek(int $clientId, \DateTimeInterface $day, TransactionType $type)
    {
        [$monday, $sunday] = Date::getWeekBoundaries($day);
        $transactions = $this->findBy($clientId);

        $result = [];
        foreach ($transactions as $transaction) {
            if (
                $transaction->getDate() >= $monday &&
                $transaction->getDate() <= $sunday &&
                $transaction->getType() == $type
            ) {
                $result[] = $transaction;
            }
        }
        return $result;
    }
}
