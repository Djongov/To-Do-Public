<?php

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if ((isset($_SERVER['HTTP_REFERER']) && !empty($_SERVER['HTTP_REFERER']))) {
        if (strtolower(parse_url($_SERVER['HTTP_REFERER'], PHP_URL_HOST)) !== strtolower($_SERVER['HTTP_HOST'])) {
            die("Invalid source of request");
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