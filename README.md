A Command Line Interface to PC Financial Web Banking
===============
A Command Line Interface to PC Financial. View account balance, pay bills, transfer money, and get transaction history and more!

## Why?
I created this script so that I could automate some functions, such as e-mailing me a daily transaction report or to warn me when my account balance drops below a threshold.

It was also a good exercising in reverse engineering and I'm using the same interface as Android app does.

## Requirements
The script was written in PHP, so you'll need to have PHP installed.

## Usage & Examples
Running the tool without any parameters will give you some details on command line switches:

```
Required:
        -c Card Number
        -p Web Banking Password

By default your account balances are shown, however you can alter this behaviour with the following:
         -a <account number> Display details for specific account number
         -h Date range to display account activity in YYYYMMDD-YYYYMMDD format, must be used with -a above.
         -v Display your personal verification questions
```

Getting a list of all account balances:
``` php pcfinancial.php -c 0000000000 -p password```
