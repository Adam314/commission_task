<?php

declare(strict_types=1);

namespace Adam314\CommissionTask\Tests\Service;

use Adam314\CommissionTask\Service\ExchangeRatesApi;
use PHPUnit\Framework\TestCase;

class ExchangeRatesApiTest extends TestCase
{
    public function testGetRate()
    {
        /** @var ExchangeRatesApi $rateService */
        $rateService = $this->getMock();
        $rate = $rateService->getRate('EUR', 'USD');
        $this->assertEquals(1.23, $rate);

        $rate = $rateService->getRate('USD', 'EUR');
        $this->assertEquals(1 / 1.23, $rate);
    }

    private function getMock()
    {
        return new class ('key-not-needed') extends ExchangeRatesApi {
            protected function curlRequest(string $url): string
            {
                return '{"success":true,"timestamp":1722447917,"base":"EUR","date":"2024-07-31","rates":{"USD":1.23}}';
            }
        };
    }
}
