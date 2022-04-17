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