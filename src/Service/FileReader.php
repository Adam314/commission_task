<?php

namespace Adam314\CommissionTask\Service;

use Adam314\CommissionTask\Entity\Transaction;
use Adam314\CommissionTask\Repository\Client;
use Adam314\CommissionTask\Type\ClientType;
use Adam314\CommissionTask\Type\TransactionType;
use Brick\Math\Exception\NumberFormatException;
use Brick\Math\Exception\RoundingNecessaryException;
use Brick\Money\Exception\UnknownCurrencyException;
use Brick\Money\Money;

class FileReader
{
    public function __construct(private string $filename)
    {
    }

    /**
     * @return \Generator<Transaction>
     *
     * @throws NumberFormatException
     * @throws RoundingNecessaryException
     * @throws UnknownCurrencyException
     */
    public function read(): \Generator
    {
        $clientRepository = new Client();

        $file_handle = fopen($this->filename, "r") or throw new \Exception('File not found');
        while (($line = fgetcsv($file_handle)) !== false) {
            // take user and find it in repo
            if (($user = $clientRepository->findOneById($line[1])) === false) {
                $user = new \Adam314\CommissionTask\Entity\Client($line[1], ClientType::from($line[2]));
                $clientRepository->store($user);
            }

            $date = new \DateTime($line[0]);

            yield new Transaction($date, $user, Money::of($line[4], $line[5]), TransactionType::from($line[3]));
        }
    }
}
