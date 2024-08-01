# Commission task

## Commission calculation

The purpose of this code is to calculate commission for transactions provided in a CSV file. Internally the code creates a repository of transactions and adds a commission transaction to each one.

## Installation

To install this code follow these steps:

1. run `composer install`
2. Go to https://exchangeratesapi.io/ and obtain API key
3. Create a file `.env.local` and put your API key in it. An example `.env.example` file is provided

## Execution

There are two commands available:

### Calculating commission
To calculate commission for each line from a CSV file run:

`php index.php commision <path-to-file>`

Output is a value of the commission in the transaction's currency. For example, if input value is:

`2016-01-10,2,business,deposit,10000.00,EUR`

Then on the output there will be `3.00` - 3 euro commission.

To understand how a commission is calculated a verbose output can be used:

`php index.php commision <path-to-file>`

Then, instead of raw value a message will be provided on the output:

`0.03% of EUR 10000.00 is EUR 3.00`

An example data can be found in `tests/Data/input.csv`.

### Testing exchange rates API

To check if exchange rates are configured properly a `rates` command is available

`php index.php rates`

This pulls current rates from the https://exchangeratesapi.io/ API and prints them in the output.

Note: The free API plan has a limit of requests. Please run the commands carefully to not exceed it.

## Commission calculation rules

There are several types of transactions and clients, For each of them the rules are different.

1. A deposit transaction always has 0.3% commission
2. A withdrawal transaction:
   1. For business clients commission is always 0.5%
   2. For private clients a sum of all transactions for this week is calculated in Euro and:
      1. First 3 transactions this week have no commission if they don't exceed 1000 total this week
      2. If the new transaction exceeds 1000 euro for this week, then the 0.3% commission is calculated for the value, that is above 1000 Euro for this week
      3. If sum exceeds 1000 Euro, a commission of 0.3% is calculated
      4. If there are already 3 transactions this week, a commission of 0.3% is charged regardless of transaction value
      
The totals for the week are calculated in Euro, if a transaction is in different currency then an exchange rate is applied. However, the commission is provided in the currency of the original transaction

## Testing

Run `composer run test` for PHPUnit tests and php code style tests. 


