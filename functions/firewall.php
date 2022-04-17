<?php
// Allowed IPs List
$allowed_ips = ['127.0.0.1', '92.247.57.179'];

// Check if using Cloudflare
if (isset($_SERVER['HTTP_CF_CONNECTING_IP'])) {
    $connecting_ip = $_SERVER['HTTP_CF_CONNECTING_IP'];
// Or a Proxy address
} elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
    $connecting_ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
} elseif (isset($_SERVER['REMOTE_ADDR'])) {
// Or the usual remote address picked up by PHP
    $connecting_ip = $_SERVER['REMOTE_ADDR'];
}
// If IP is not in the allowed list array - throw 401 status code and exit script
if (!in_array($connecting_ip, $allowed_ips)) {
    header($_SERVER['SERVER_PROTOCOL'] . " 401 Unauthorized");
    echo "Unauthorized";
    exit ();
}