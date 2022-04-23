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
    $json_data = file_get_contents('php://input'); // Read the data from the POST request
    $array = json_decode($json_data, true);
    if (count($array) > 0) {
        include_once $_SERVER['DOCUMENT_ROOT'] . '/functions/db.php';
        (!is_int($array['id'])) ? $id = $array['id'] : die("ID passed not as integer");
        (isset($array['table'])) ? $table = htmlspecialchars($array["table"]) : '';
        if ($array['action'] === 'mark-complete') {
            $action = 1;
        } elseif ($array['action'] === 'undo') {
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
    }
}
?>