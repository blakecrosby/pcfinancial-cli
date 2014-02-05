A Command Line Interface to PC Financial Web Banking
===============
A Command Line Interface to PC Financial. View account balance, pay bills, transfer money, and get transaction history and more!

## Why?
I created this script so that I could automate some functions, such as e-mailing me a daily transaction report or to warn me when my account balance drops below a threshold.

It was also a good exercise in reverse engineering. All of the API-like calls I made are the same as the ones from the PC Financial Android App. The web application shouldn't be able to tell the difference between calls made by this tool and ones by the Android app.

## Requirements
The script was written in PHP, so you'll need to have PHP installed.

## Usage & Examples

***BE SMART!*** Please don't run this script on a shared server. If anyone else can access your bash history, or crontab, or anywhere else you might have set this script up they can also access your bank account.

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

Getting a list of all accounts and balances:

``` php pcfinancial.php -c 0000000000 -p password```

Getting transaction history for an account:

```php pcfinancial.php -c 0000000000 -p password -a 20000011111111 -h 20140101-20140131```

## Limitations
The tool might ask for your Personal Verification Question (PVQ). The PVQ is usually asked when you are logging in from a new IP address. One you input your PVQ answer, subsequent requests shouldn't require it. This can be an annoyance if you want to use the script in an automated process.

You *can* get locked out of your account for a short period of time if you run the script many times in short succession. If you get the error: "We're sorry. We are unable to sign you in from this location. Please sign in again from a different location" wait and try again later.