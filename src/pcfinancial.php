<?php
#!/usr/bin/php



# Set some stuff up
$posturl = "https://m.pcfinancial.ca/middleware/MWServlet";

#set the default timezone to avoid php Date warnings
date_default_timezone_set('EST');

# Command line options
# c: card number
# p: password
$options = getopt("c:p:a:");


if (!$options) {
    print "pcfinancial-cli: Command Line Options\n";
    print "Required:\n";
    print "\t-c Card Number\n\t-p Web Banking Password\n\n";
    print "By default your account balances are shown, however you can alter this behaviour with the following:\n";
    print "\t -a <account number> Display details for specific account number\n\n";
    die;
}

# Get session cookie and login
get('checkAppNContentUpgrade',array('contentTimestamp' => time()*1000));
get('signOn',array('password'=>$options['p'],'cardNumber'=>$options['c'],'packageName'=>'SystemAccess','contentTimestamp' => time()*1000));

# Get account information
$accountinfo = get('accountSummary',array('channelId' => 'mobile','cacheid' => '','packageName'=>'Accounts','timestamp' => date("Ynj".intval(date('G').intval(date('i').intval(date('s')))))));
$accountinfo = json_decode($accountinfo);

# Default behaviour. Show accounts and balances.
foreach ($accountinfo->accountsList as $account) {

    # If is a single account (-a) was chosen, only display it.
    if (isset($options['a'])) {
        if ($account->accountNumber == $options['a']) {
            print "$account->productName ($account->accountNumber) - Available: $account->availableBalance Current: $account->currentBalance\n";
            break;
        }
    }
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

    return $output;

}