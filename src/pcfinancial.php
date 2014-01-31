<?php
#!/usr/bin/php

# Set some stuff up
$posturl = "https://m.pcfinancial.ca/middleware/MWServlet";

# Command line options
# c: card number
# p: password
$options = getopt("c:p:");

if (!$options) {
    print "pcfinancial-cli: Command Line Options\n";
    print "Required:\n";
    print "\t-c Card Number\n\t-p Web Banking Password\n\n";
}


    # Get session cookie and login
    get('checkAppNContentUpgrade');
    get('signOn',array('password'=>$options['p'],'cardNumber'=>$options['c'],'packageName'=>'SystemAccess'));

    #Get Account balances
    #get('')

function get($service,$parameters = Array()) {
    global $posturl;

    $fields = array(
        'platform' => 'android',
        'contentTimestamp' => time()*1000,
        'platformId' => 'android',
        'appID' => 'PCFMobile',
        'appver' => '1.3',
        'rcid' => 'Nexus 5',
        'channel' => 'rc',
        'serviceID' => $service,
        'platformver' => '4.1.GA'
    );
    $fields = array_merge($fields,$parameters);

    print http_build_query($fields);
    $ch = curl_init($posturl);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch,CURLOPT_POSTFIELDS, http_build_query($fields));
    curl_setopt($ch, CURLOPT_COOKIEJAR, "/tmp/cookieFileName");
    $output = curl_exec($ch);
    print $output;

}
