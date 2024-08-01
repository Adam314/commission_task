<?php

namespace Adam314\CommissionTask\DependencyInjection;

use Adam314\CommissionTask\Repository\Client;
use Adam314\CommissionTask\Repository\Transaction;
use Adam314\CommissionTask\Service\ExchangeRatesApi;

/**
 * A basic DI container. If this was a bigger project I'd use Symfony autowiring but for a simple task this one will do
 */
class DiContainer implements DiContainerInterface
{
    protected $services = [];
    public function getService(string $serviceName): mixed
    {
        if (empty($this->services[$serviceName])) {
            $this->services[$serviceName] = match ($serviceName) {
                'client_repository' => new Client(),
                'transaction_repository' => new Transaction(),
                'exchange_rates_api' => new ExchangeRatesApi($_ENV['EXCHANGE_RATES_KEY'])
            };
        }

        return $this->services[$serviceName];
    }
}
