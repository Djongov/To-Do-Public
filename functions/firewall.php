<?php
// Check if using Cloudflare
if (isset($_SERVER['HTTP_CF_CONNECTING_IP'])) {
    $connecting_ip = $_SERVER['HTTP_CF_CONNECTING_IP'];
// Or a Proxy address
} elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
    $connecting_ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
} elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
// Or the Azure client IP header but it has :port number, so remove it
    $connecting_ip = str_replace(strstr($_SERVER['HTTP_CLIENT_IP'], ':'), '', $_SERVER['HTTP_CLIENT_IP']);
} else {
    // or just use the normal remote addr
    $connecting_ip = $_SERVER['REMOTE_ADDR'];
}

// Here is an array with the allowed IPs in CIDR
$allow_list = [
    // Localhost
    '127.0.0.1/32',
    // Allow Private Networks
    '10.0.0.0/8',
    '172.16.0.0/12',
    '192.168.0.0/16',
];

// Function to match the CIDR, taken from https://ajagwe.wordpress.com/2013/06/25/block-or-allow-access-to-php-script-based-on-remote-ip-and-cidr-list/
function cidr_match($ip, $range) {
    list ($subnet, $bits) = explode('/', $range);
    if ($bits === null) {
        $bits = 32;
    }
    $ip = ip2long($ip);
    $subnet = ip2long($subnet);
    $mask = -1 << (32 - $bits);
    $subnet &= $mask; # nb: in case the supplied subnet wasn't correctly aligned
    return ($ip & $mask) == $subnet;
}

// Initiate validator variable
$validaddr = false;
// Loop through the allow list
foreach ($allow_list as $addr) {
    // If there is a match
    if (cidr_match($connecting_ip, $addr)) {
        // Set the validator to true
        $validaddr = true;
        // and break the loop
        break;
    }
}

// Allow only requests that pass the validation to continue
if (!$validaddr) {
    header($_SERVER['SERVER_PROTOCOL'] . " 401 Unauthorized");
    echo "$connecting_ip: Unauthorized";
    exit ();
}