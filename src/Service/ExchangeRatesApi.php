<?php

namespace Adam314\CommissionTask\Service;

class ExchangeRatesApi
{
    private $url;

    private $rates = [];

    public function __construct(string $access_key)
    {
        $this->url = sprintf('http://api.exchangeratesapi.io/v1/latest?access_key=%s', $access_key);
    }

    public function getRates()
    {
        $json = $this->curlRequest($this->url);
        $result = json_decode($json, true);
        if (empty($result['success'])) {
            throw new \Exception('Failed to pull rates');
        }

        $this->rates[$result['base']] = $result['rates'];

        return $this->rates;
    }

    protected function curlRequest(string $url): string
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $json = curl_exec($ch);
        curl_close($ch);
        return $json;
    }

    public function getRate(string $from, string $to): float
    {
        if (empty($this->rates)) {
            $this->getRates();
        }

        if (isset($this->rates[$from][$to])) {
            return $this->rates[$from][$to];
        }
        if (isset($this->rates[$to][$from])) {
            return 1 / $this->rates[$to][$from];
        }
        return 0;
    }
}
