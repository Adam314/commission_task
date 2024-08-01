<?php

namespace Adam314\CommissionTask\Tests\Repository;

use Adam314\CommissionTask\Entity\Client;
use Adam314\CommissionTask\Entity\Transaction;
use Adam314\CommissionTask\Repository\Transaction as TransactionRepository;
use Adam314\CommissionTask\Type\ClientType;
use Adam314\CommissionTask\Type\TransactionType;
use Adam314\CommissionTask\Util\Date;
use Brick\Money\Money;
use PHPUnit\Framework\TestCase;

class TransactionTest extends TestCase
{
    public static function findForThisWeekDataProvider(): array
    {
        // prepare variables
        $client1 = new Client(1, ClientType::BUSINESS);
        $client2 = new Client(2, ClientType::PRIVATE);

        $wednesday = new \DateTime('2024-07-31');
        [$monday, $sunday] = Date::getWeekBoundaries($wednesday);
        $before = (clone $monday)->modify("-1 day");
        $after = (clone $sunday)->modify("+1 day");

        // some value, not really important as we test the filtering
        $money = Money::of('1', 'USD');

        return [
            // Finding by clientId, should find 2 out of 3 transactions
            [
                [
                    new Transaction($wednesday, $client1, $money, TransactionType::WITHDRAW),
                    new Transaction($wednesday, $client2, $money, TransactionType::WITHDRAW),
                    new Transaction($wednesday, $client1, $money, TransactionType::WITHDRAW),
                ],
                $client1->getId(), // clientId
                $wednesday,
                TransactionType::WITHDRAW,
                2 // expected number of transactions found
            ],
            // Test finding by type, should find 2 out of 4
            [
                [
                    new Transaction($wednesday, $client1, $money, TransactionType::WITHDRAW),
                    new Transaction($wednesday, $client1, $money, TransactionType::COMMISSION),
                    new Transaction($wednesday, $client1, $money, TransactionType::DEPOSIT),
                    new Transaction($wednesday, $client1, $money, TransactionType::COMMISSION),
                ],
                $client1->getId(), // clientId
                $wednesday,
                TransactionType::COMMISSION,
                2 // expected number of transactions found
            ],
            // Test finding by date, should find 3 out of 5
            [
                [
                    new Transaction($before, $client1, $money, TransactionType::WITHDRAW),
                    new Transaction($monday, $client1, $money, TransactionType::WITHDRAW),
                    new Transaction($wednesday, $client1, $money, TransactionType::WITHDRAW),
                    new Transaction($sunday, $client1, $money, TransactionType::WITHDRAW),
                    new Transaction($after, $client1, $money, TransactionType::WITHDRAW),
                ],
                $client1->getId(), // clientId
                $wednesday,
                TransactionType::WITHDRAW,
                3 // expected number of transactions found
            ]
        ];
    }

    /**
     * @dataProvider findForThisWeekDataProvider
     */
    public function testFindForThisWeek(
        array $transactions,
        int $clientId,
        \DateTimeInterface $dateTime,
        TransactionType $type,
        int $expectedFind
    ): void {
        $transactionRepository = new TransactionRepository();
        foreach ($transactions as $transaction) {
            $transactionRepository->save($transaction);
        }

        $result = $transactionRepository->findForThisWeek($clientId, $dateTime, $type);
        $this->assertIsArray($result);
        $this->assertEquals($expectedFind, count($result));
    }
}
