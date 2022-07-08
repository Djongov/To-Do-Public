<?php
include_once dirname(dirname(__FILE__)) . '/site-config.php';
 
/* Attempt to connect to MySQL database */
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$link = mysqli_init();
if (defined('DB_SSL')) {
    $link->real_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME, DB_PORT, null, MYSQLI_CLIENT_SSL | MYSQLI_CLIENT_SSL_DONT_VERIFY_SERVER_CERT);
} else {
    $link->real_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME, DB_PORT);
}

// Check connection
if($link === false) {
    die("ERROR: Could not connect. " . mysqli_connect_error());
}

function checkAdmin($username, $link) {
    $stmt = $link->prepare("SELECT * FROM `users` WHERE `username`=? AND `admin`=1");
    $stmt->bind_param("s", $username);
    if ($stmt->execute()) {
        $result = $stmt->get_result();
        $check_array = $result->fetch_all(MYSQLI_ASSOC);
        $result->free_result();
        $stmt->close();
        return (count($check_array) > 0) ? true : false;
    } else {
        die("Error: $stmt->error");
    }
}
?>