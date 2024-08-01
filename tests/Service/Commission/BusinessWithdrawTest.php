<?php

namespace Adam314\CommissionTask\Tests\Service\Commission;

use Adam314\CommissionTask\Entity\Client;
use Adam314\CommissionTask\Entity\Transaction;
use Adam314\CommissionTask\Service\Commission\BusinessWithdraw;
use Adam314\CommissionTask\Service\Commission\Deposit;
use Adam314\CommissionTask\Type\ClientType;
use Adam314\CommissionTask\Type\TransactionType;
use Brick\Math\RoundingMode;
use Brick\Money\Money;
use PHPUnit\Framework\TestCase;

class BusinessWithdrawTest extends TestCase
{
    public static function getCommissionForTransactionDataProvider(): array
    {
        return [
            // typical case
            [100, 'USD', 0.5],
            // uneven values (actually 0.5656 but rounded)
            [113.13, 'USD', 0.57],
            // other currencies
            [11313, 'JPY', 57],
            [113.13, 'EUR', 0.57],
        ];
    }

    /**
     * @dataProvider getCommissionForTransactionDataProvider
     */
    public function testGetCommissionForTransaction(float $value, string $currency, float $expectedValue): void
    {
        $client = new Client(1, ClientType::BUSINESS);

        $transaction = new Transaction(
            new \DateTime(),
            $client,
            Money::of($value, $currency, null, RoundingMode::HALF_UP),
            TransactionType::WITHDRAW
        );

        $service = new BusinessWithdraw();
        $result = $service->getCommissionForTransaction($transaction);

        $this->assertEquals($client, $result->getUser());
        $this->assertEquals(TransactionType::COMMISSION, $result->getType());
        $this->assertEquals(0, $result->getValue()->getAmount()->compareTo($expectedValue));
        $this->assertEquals($currency, $result->getValue()->getCurrency());
    }
}
