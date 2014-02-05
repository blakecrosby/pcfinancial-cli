<?php
#!/usr/bin/php


# Set some stuff up
$posturl = "https://m.pcfinancial.ca/middleware/MWServlet";

#set the default timezone to avoid php Date warnings
date_default_timezone_set('EST');

# Command line options
# c: card number
# p: password
# a: display specific account details
# h: display account history
# v: list personal verification questions

$options = getopt("c:p:a:h:v");

if (!($options['c'] && $options['p'])) {
    print "pcfinancial-cli: Command Line Options\n";
    print "Required:\n";
    print "\t-c Card Number\n\t-p Web Banking Password\n\n";
    print "By default your account balances are shown, however you can alter this behaviour with the following:\n";
    print "\t -a <account number> Display details for specific account number\n";
    print "\t -h Date range to display account activity in YYYYMMDD-YYYYMMDD format, must be used with -a above.\n";
    print "\t -v Display your personal verification questions\n\n";
    die;
}


# Get session cookie and login
get('checkAppNContentUpgrade',array('contentTimestamp' => time()*1000));
$signon = get('signOn',array('password'=>$options['p'],'cardNumber'=>$options['c'],'packageName'=>'SystemAccess','contentTimestamp' => time()*1000));

#Error Handling
if ($signon->status == "fail") {
    print "Error: $signon->errorMsg\n\n";
    die;
}

# If the system is going to ask for a PVQ question, we need to get it to continue.
# Problem is, we don't know which one it's going to ask us, so we can't really automate this :/
if ($signon->next == "PROMPT_PVQ") {

    $pvq = get('pvqIdSignOn1',array('channelId' => 'mobile','cacheid' => '','packageName'=>'SystemAccess','timestamp' => date("Ynj".intval(date('G').intval(date('i').intval(date('s')))))));

    print "Please answer the following question to continue:\n";
    $pvqanswer = readline("Question: $pvq->question: ");

    #submit the answer and lets carry on
    $pvqsuccess = get('pvqIdSignOnM',array('method'=>'process','answer'=>"$pvqanswer",'channelId' => 'mobile','cacheid' => '','packageName'=>'SystemAccess','timestamp' => date("Ynj".intval(date('G').intval(date('i').intval(date('s')))))));

}


# If -v was used let's list the PVQs and exit.
if (array_key_exists('v',$options)) {
    $pvqs = get('getPvqM',array('cacheid' => '','timestamp' => date("Ynj".intval(date('G').intval(date('i').intval(date('s')))))));
    foreach ($pvqs->personalVerificationList as $question) {
        print "$question->id. $question->question\n";
    }

    get('signOff');
    die;
}


# Get account information
$accountinfo = get('accountSummary',array('channelId' => 'mobile','cacheid' => '','packageName'=>'Accounts','timestamp' => date("Ynj".intval(date('G').intval(date('i').intval(date('s')))))));


# Main logic
foreach ($accountinfo->accountsList as $account) {

    # If is a single account (-a) was chosen, only display it.
    if (isset($options['a'])) {
        if ($account->accountNumber == $options['a']) {
            print "$account->productName ($account->accountNumber) - Available: $account->availableBalance Current: $account->currentBalance\n";
            # If we're getting account transaction history (-h and -a options specified)
            if (isset($options['h'])) {

                # parse the date range
                # silly PCF web app expects months to start at 0 instead of 1, silly Java.
                $date = str_split($options['h'],8);
                $start_year = substr($date[0],0,4);
                $start_month = (substr($date[0],4,2)*1)-1;
                $start_day = (substr($date[0],6,2))*1;
                $end_year = substr($date[1],0,4);
                $end_month = (substr($date[1],4,2)*1)-1;
                $end_day = (substr($date[1],6,2))*1;
                print "$start_year $start_month $start_day ... $end_year $end_month $end_day\n";
                get('signOff');
                die;

                $history = get('transactionHistoryM2',array('fromAccount'=>"$account->accountSlot;$account->transitNumber;$account->accountNumber",'startDate__YEAR'=>$start_year,'startDate__MONTH'=>$start_month,'startDate__DAY' => $start_day,'endDate__YEAR'=>$end_year,'endDate__MONTH'=>$end_month,'endDate__DAY'=>$end_day,'transactionType'=>'','txnMarkerList'=>'','nextFitId'=>'','previousFitId'=>'','lowLimit'=>'','highLimit'=>'','channelId' => 'mobile','cacheid' => '','packageName'=>'Accounts','timestamp' => date("Ynj".intval(date('G').intval(date('i').intval(date('s')))))));

                print_r($history);

                echo sprintf("%-10s %-50.50s %-12s %-12s %-12s\n","Date","Description","Credit","Debit","Balance");
                foreach ($history->transactionList as $txn){
                    echo sprintf("%-10s %-50.50s %-12s %-12s %-12s\n",date("Y-m-d",strtotime($txn->transactionDate)),$txn->transactionDesc,$txn->amountIn,$txn->amountOut,$txn->balance);
                }
            }
            else{
                break;
            }
        }
    }
    # Default behaviour, show accounts and balances and exit.
    else {
        print "$account->productName ($account->accountNumber) - Available: $account->availableBalance Current: $account->currentBalance\n";
    }
}

#Log out
get('signOff');


function get($service,$parameters = Array()) {
    global $posturl;

    $fields = array(
        'platform' => 'android',
        'platformId' => 'android',
        'appID' => 'PCFMobile',
        'appver' => '1.3',
        'rcid' => 'Nexus 5',
        'channel' => 'rc',
        'serviceID' => $service,
        'platformver' => '4.1.GA'
    );
    $fields = array_merge($fields,$parameters);

    $ch = curl_init($posturl);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
    curl_setopt($ch,CURLOPT_POSTFIELDS, http_build_query($fields));
    curl_setopt($ch, CURLOPT_COOKIEJAR, "/tmp/cookieFileName");
    curl_setopt($ch, CURLOPT_COOKIEFILE, "/tmp/cookieFileName");
    curl_setopt($ch,CURLOPT_USERAGENT,'Apache-HttpClient/android/Nexus 5');
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Cookie2: $Version=1'));
    $output = curl_exec($ch);

    return json_decode($output);

}