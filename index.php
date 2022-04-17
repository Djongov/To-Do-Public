<?php
// Check if installation is pending
clearstatcache();
if (file_exists($_SERVER['DOCUMENT_ROOT'] . '/install.php')) {
    header('Location: /install');
    exit;
}

include_once $_SERVER['DOCUMENT_ROOT']. '/functions/session.php';
// Check if the user is logged in, if not then redirect him to login page
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
  header("location: /login");
  exit;
} else {
  header("location: /lists");
}

?>