<?php

include_once $_SERVER['DOCUMENT_ROOT'] . '/site-config.php';

$maxlifetime = 60000;
$secure = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? true : false; // if you only want to receive the cookie over HTTPS but only set it if the current connection is secure
if ($secure === true) {
    if (session_name() !== '__Secure-SSID') {
        session_name("__Secure-SSID");
    }
} else {
    if (session_name() !== 'SSID') {
        session_name("SSID");
    }
}
$httponly = true; // prevent JavaScript access to session cookie
$samesite = 'Strict';

if(PHP_VERSION_ID < 70300) {
    session_set_cookie_params($maxlifetime, '/; samesite='.$samesite, $_SERVER['HTTP_HOST'], $secure, $httponly);
} else {
    session_set_cookie_params([
        'lifetime' => $maxlifetime,
        'path' => '/',
        'domain' => $_SERVER['HTTP_HOST'],
        'secure' => $secure,
        'httponly' => $httponly,
        'samesite' => $samesite,
    ]);
}

if (session_status() !== 2) {
    session_start();
}

// Check if the user is logged in, if not then redirect him to login page
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: /login");
    exit;
}

// Check if mysqli extension is available, otherwise the app won't work
if (!extension_loaded('mysqli')) {
    die("mysqli extension is not loaded, exiting app");
}
?>