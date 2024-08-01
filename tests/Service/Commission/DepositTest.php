<?php

namespace Adam314\CommissionTask\Tests\Service\Commission;

use Adam314\CommissionTask\Entity\Client;
use Adam314\CommissionTask\Entity\Transaction;
use Adam314\CommissionTask\Service\Commission\Deposit;
use Adam314\CommissionTask\Type\ClientType;
use Adam314\CommissionTask\Type\TransactionType;
use Brick\Math\RoundingMode;
use Brick\Money\Money;
use PHPUnit\Framework\TestCase;

class DepositTest extends TestCase
{
    public static function getCommissionForTransactionDataProvider(): array
    {
        return [
            // typical case, 0.3% in USD
            [1000, 'USD', 0.3],
            // uneven values, actually 0.339 but rounded
            [1131.3, 'USD', 0.34],
            // other currencies
            [113130, 'JPY', 34],
            [1131.30, 'EUR', 0.34],
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
            TransactionType::DEPOSIT
        );

        $service = new Deposit();
        $result = $service->getCommissionForTransaction($transaction);

        $this->assertEquals($client, $result->getUser());
        $this->assertEquals(TransactionType::COMMISSION, $result->getType());
        $this->assertEquals(
            0,
            $result->getValue()->getAmount()->compareTo($expectedValue),
            sprintf("Expected %s doesn't match actual %s", $expectedValue, $result->getValue()->getAmount())
        );
        $this->assertEquals($currency, $result->getValue()->getCurrency());
    }
}
