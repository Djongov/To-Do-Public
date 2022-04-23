<?php
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Make an attempt to stop requests not coming from the same host. Not very secure as HTTP REFERER header can be spoofed with any value but .. still
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
        $entry = htmlspecialchars($array["entry"]);
        (isset($array['table'])) ? $table = htmlspecialchars($array["table"]) : '';
        $type = htmlspecialchars($array["type"]);
        //var_dump($array);
        if ($type === 'task') {
            $sql = "UPDATE `$table` SET `task`=? WHERE `id`=?";
        } elseif ($type === 'price') {
            $sql = "UPDATE `$table` SET `price`=? WHERE `id`=?";
        } else {
            die("Invalid task or price");
        }
        // If you have an existing price and you just delete the entry and pass "", let it be null because the db field accepts null
        if ($type === 'price' and $entry === '') {
            $entry = null;
        }
        $stmt = $link->prepare($sql);
        $stmt->bind_param("si", $entry, $id);
        if ($stmt->execute()) {
            echo "OK";
        } else {
            echo "Error: $mysqli->error";
        }
        $stmt->close();
    }

}
?>