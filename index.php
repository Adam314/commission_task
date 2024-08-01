<?php
/**
 * Main application
 */

require __DIR__ . '/vendor/autoload.php';

use Adam314\CommissionTask\Service\Commission;
use Adam314\CommissionTask\Service\FileReader;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Dotenv\Dotenv;

$dotenv = new Dotenv();
$dotenv->load(__DIR__.'/.env.local');

$application = new Application();

$application->register('commision')
    ->setDescription('Parse CSV file and return commision')
    ->addArgument('file', InputArgument::REQUIRED)
//    ->addOption('verbose','v',)
    ->setCode(function (InputInterface $input, OutputInterface $output): int {
        $diContainer = new \Adam314\CommissionTask\DependencyInjection\DiContainer();

        /** @var \Adam314\CommissionTask\Repository\Transaction $transactionRepository */
        $transactionRepository = $diContainer->getService('transaction_repository');

        $fileName = $input->getArgument('file');
        $fileReader = new FileReader($fileName);
        $commissionService = new Commission($diContainer);

        foreach ($fileReader->read() as $transaction) {
            $commission = $commissionService->getCommissionForTransaction($transaction);
            if ($output->isVerbose()) {
                $output->writeln($commission->getDescription());
            } else {
                $output->writeln($commission->getValue()->getAmount());
            }
            $transactionRepository->save($transaction);
            $transactionRepository->save($commission); // we don't really need to save for this task but it looks better for the future
        }

        return Command::SUCCESS;
    });

$application->register('rates')
    ->setDescription('Pull exchange rates from external service')
    ->setCode(function (InputInterface $input, OutputInterface $output): int {
        try {
            $ratesApi = new \Adam314\CommissionTask\Service\ExchangeRatesApi($_ENV['EXCHANGE_RATES_KEY']);
            $exchangeRates = $ratesApi->getRates();
            foreach ($exchangeRates as $base => $rates) {
                foreach ($rates as $currency => $rate) {
                    $output->writeln(sprintf('%s:%s = %.4f', $base, $currency, $rate));
                }
            }
            return Command::SUCCESS;
        } catch (\Throwable $exception) {
            $output->writeln($exception->getMessage());
            return Command::FAILURE;
        }
    });

$application->run();