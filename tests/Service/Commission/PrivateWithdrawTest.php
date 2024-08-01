<?php

namespace Adam314\CommissionTask\Tests\Service\Commission;

use Adam314\CommissionTask\DependencyInjection\DiContainer;
use Adam314\CommissionTask\DependencyInjection\DiContainerInterface;
use Adam314\CommissionTask\Entity\Client;
use Adam314\CommissionTask\Entity\Transaction;
use Adam314\CommissionTask\Service\Commission\BusinessWithdraw;
use Adam314\CommissionTask\Service\Commission\PrivateWithdraw;
use Adam314\CommissionTask\Service\ExchangeRatesApi;
use Adam314\CommissionTask\Type\ClientType;
use Adam314\CommissionTask\Type\TransactionType;
use Brick\Math\RoundingMode;
use Brick\Money\Money;
use MongoDB\BSON\Type;
use PHPUnit\Framework\TestCase;

class PrivateWithdrawTest extends TestCase
{
    private const EXCHANGE_RATE = 1.23;

    private DiContainerInterface $diMock;

    public static function getCommissionForTransactionDataProvider(): array
    {
        // let's prepare some variables
        $today = new \DateTime();
        $client = new Client(1, ClientType::PRIVATE);

        return [
            // case 0, there are already 3 withdrawals and some deposits, should give 0.3% commission
            [
                [
                    new Transaction($today, $client, Money::of(200, 'EUR'), TransactionType::WITHDRAW),
                    new Transaction($today, $client, Money::of(200, 'EUR'), TransactionType::WITHDRAW),
                    new Transaction($today, $client, Money::of(200, 'EUR'), TransactionType::WITHDRAW),
                ],
                new Transaction($today, $client, Money::of(100, 'EUR'), TransactionType::WITHDRAW),
                0.30,
            ],
            // case 1, there are 2 transactions  but their total value exceeds 1000 euro, expected 0.3% comission
            [
                [
                    new Transaction($today, $client, Money::of(600, 'EUR'), TransactionType::WITHDRAW),
                    new Transaction($today, $client, Money::of(500, 'EUR'), TransactionType::WITHDRAW),
                ],
                new Transaction($today, $client, Money::of(100, 'EUR'), TransactionType::WITHDRAW),
                0.30,
            ],
            // case 2, same as above but transactions are in JPY and USD
            [
                [
                    new Transaction($today, $client, Money::of(600, 'JPY'), TransactionType::WITHDRAW),
                    new Transaction($today, $client, Money::of(500, 'USD'), TransactionType::WITHDRAW),
                ],
                new Transaction($today, $client, Money::of(100, 'EUR'), TransactionType::WITHDRAW),
                0.30,
            ],
            // case 3, the sum of transactions is 0 (no transactions), new one is 200 so should be free
            [
                [],
                new Transaction($today, $client, Money::of(100, 'EUR'), TransactionType::WITHDRAW),
                0,
            ],
            // case 4, the sum of transactions is 800 , new one is 200 so should be free
            [
                [
                    new Transaction($today, $client, Money::of(400, 'EUR'), TransactionType::WITHDRAW),
                    new Transaction($today, $client, Money::of(400, 'EUR'), TransactionType::WITHDRAW),
                ],
                new Transaction($today, $client, Money::of(200, 'EUR'), TransactionType::WITHDRAW),
                0,
            ],
            // case 5, the sum is 800 but the new one is 400 so should be 0.3% from 200 eoro
            [
                [
                    new Transaction($today, $client, Money::of(800, 'EUR'), TransactionType::WITHDRAW),
                ],
                new Transaction($today, $client, Money::of(400, 'EUR'), TransactionType::WITHDRAW),
                0.60,
            ],
            // case 6 - no transactions but the foreign currency one far exceeded the limit
            [
                [
                ],
                new Transaction($today, $client, Money::of(31415926, 'JPY'), TransactionType::WITHDRAW),
                94245,
            ],

        ];
    }

    /**
     * @dataProvider getCommissionForTransactionDataProvider
     */
    public function testGetCommissionForTransaction(
        array $pastTransactions,
        Transaction $transaction,
        float $expectedValue
    ): void {
        $this->prepareDiMock();
        $this->prepareTransactionRepository($pastTransactions);
        $this->prepareExchangeRates();

        $service = new PrivateWithdraw($this->diMock);
        $result = $service->getCommissionForTransaction($transaction);

        $this->assertEquals(TransactionType::COMMISSION, $result->getType());
        $this->assertEquals(
            0,
            $result->getValue()->getAmount()->compareTo($expectedValue),
            sprintf("Expected %s doesn't match actual %s", $expectedValue, $result->getValue()->getAmount())
        );
        $this->assertEquals($transaction->getValue()->getCurrency(), $result->getValue()->getCurrency());

        /** @var \Adam314\CommissionTask\Repository\Transaction $transactionRepo */
        $transactionRepo = $this->diMock->getService('transaction_repository');
    }

    private function prepareTransactionRepository(array $transactions): void
    {
        $transactionMock = $this->getMockBuilder(\Adam314\CommissionTask\Repository\Transaction::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['findForThisWeek'])
            ->getMock();

        $transactionMock->method('findForThisWeek')
            ->willReturn($transactions);

        $this->diMock->setService('transaction_repository', $transactionMock);
    }

    private function prepareExchangeRates(): void
    {
        $exchangeRatesMock = $this->getMockBuilder(ExchangeRatesApi::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getRate'])
            ->getMock();

        // for basic tests, let's have one exchange rate
        $exchangeRatesMock->method('getRate')
            ->withAnyParameters()
            ->willReturn(self::EXCHANGE_RATE);

        $this->diMock->setService('exchange_rates_api', $exchangeRatesMock);
    }

    private function prepareDiMock(): void
    {
        $this->diMock = new class extends DiContainer {
            public function setService(string $serviceName, $serviceObject)
            {
                $this->services[$serviceName] = $serviceObject;
            }
        };
    }
}
