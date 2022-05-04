<?php

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if ((isset($_SERVER['HTTP_REFERER']) && !empty($_SERVER['HTTP_REFERER']))) {
        // Because some implementations are localhost:portnumber, let's see if there is a port first
        if ((parse_url($_SERVER['HTTP_REFERER'], PHP_URL_PORT))) {
            // Compare the REFERER HOST:PORT part of the URL to the HTTP Host header, if they are not the same, exit script
            if (strtolower(parse_url($_SERVER['HTTP_REFERER'], PHP_URL_HOST)) . ':' . parse_url($_SERVER['HTTP_REFERER'], PHP_URL_PORT) !== strtolower($_SERVER['HTTP_HOST'])) {
                echo "Invalid source of request. Request coming from " . strtolower(parse_url($_SERVER['HTTP_REFERER'], PHP_URL_HOST)) . ':' . parse_url($_SERVER['HTTP_REFERER'], PHP_URL_PORT) . " which is not the same as " . strtolower($_SERVER['HTTP_HOST']);
                die();
            }
        // If there is no port
        } else {
            // If REFERER host is not the same as HTTP Host
            if (strtolower(parse_url($_SERVER['HTTP_REFERER'], PHP_URL_HOST)) !== strtolower($_SERVER['HTTP_HOST'])) {
                echo "Invalid source of request. Request coming from " . strtolower(parse_url($_SERVER['HTTP_REFERER'], PHP_URL_HOST)) . " which is not the same as " . strtolower($_SERVER['HTTP_HOST']);
                die();
            }
        }
    }
    // Final check for the secret header we've sent from the Javascript fetch (secretheader : badass). Not a big security feature but still something
    if (isset($_SERVER['HTTP_SECRETHEADER']) and $_SERVER['HTTP_SECRETHEADER'] === 'badass') {
        // Proceed only if all arguments that should be received in this POST are present
        if (isset($_POST['id'], $_POST['table'], $_POST['action'])) {
        include_once $_SERVER['DOCUMENT_ROOT'] . '/functions/db.php';
        (!is_int($_POST['id'])) ? $id = $_POST['id'] : die("ID passed not as integer");
        (isset($_POST['table'])) ? $table = htmlspecialchars($_POST["table"]) : '';
        if ($_POST['action'] === 'mark-complete') {
            $action = 1;
        } elseif ($_POST['action'] === 'undo') {
            $action = 0;
        }
        $sql = "UPDATE `$table` SET `completed` = ? WHERE `$table`.`id` = ?";
        $stmt = $link->prepare($sql);
        $stmt->bind_param("ii", $action, $id);
        if ($stmt->execute()) {
            echo "OK";
        } else {
            echo "Error: $mysqli->error";
        }
        $stmt->close();
    } else {
            echo 'Invalid arguments';
        }
    } else {
        echo 'Oops, something is missing from the request';
    }
}
?>