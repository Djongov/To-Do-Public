<?php
// Only proceed if the method is POST
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Make an attempt to stop requests not coming from the same host. Not very secure as HTTP REFERER header can be spoofed with any value but .. still
    if ((isset($_SERVER['HTTP_REFERER']) && !empty($_SERVER['HTTP_REFERER']))) {
        // Because some implementations are localhost:portnumber, let's see if there is a port first
        if ((parse_url($_SERVER['HTTP_REFERER'], PHP_URL_PORT))) {
            // Compare the REFERER HOST:PORT part of the URL to the HTTP Host header, if they are not the same, exit script
            if (strtolower(parse_url($_SERVER['HTTP_REFERER'], PHP_URL_HOST)) . ':' . parse_url($_SERVER['HTTP_REFERER'], PHP_URL_PORT) !== strtolower($_SERVER['HTTP_HOST'])) {
                die("Invalid source of request");
            }
        // If there is no port
        } else {
            // If REFERER host is not the same as HTTP Host
            if (strtolower(parse_url($_SERVER['HTTP_REFERER'], PHP_URL_HOST)) !== strtolower($_SERVER['HTTP_HOST'])) {
                die("Invalid source of request");
            }
        }
    }
    // Final check for the secret header we've sent from the Javascript fetch (secretheader : badass). Not a big security feature but still something
    if (isset($_SERVER['HTTP_SECRETHEADER']) and $_SERVER['HTTP_SECRETHEADER'] === 'badass') {
        // Proceed only if all arguments that should be received in this POST are present
        if (isset($_POST['id'], $_POST['entry'], $_POST['table'], $_POST['type'])) {
            // include the db file
            include_once $_SERVER['DOCUMENT_ROOT'] . '/functions/db.php';
            // assign the post arguments to variables
            // id should be an integer, else kill script
            (!is_int($_POST['id'])) ? $id = $_POST['id'] : die("ID passed not as integer");
            // sanitize the other post arguments of any html tags
            $entry = strip_tags($_POST["entry"]);
            $table = strip_tags($_POST["table"]);
            $type = strip_tags($_POST["type"]);
            // If type is a task
            if ($type === 'task') {
                // this sql statement
                $sql = "UPDATE `$table` SET `task`=? WHERE `id`=?";
            } elseif ($type === 'price') {
                // if type is a price, it should be a value so intval it, this will convert it to integer even if the input is a string
                $entry = floatval($entry);
                $sql = "UPDATE `$table` SET `price`=? WHERE `id`=?";
            } else {
                // there is no other choice so kill script if the 2 conditions above are not met
                die("Invalid task or price");
            }
            // If you have an existing price and you just delete the entry and pass "", let it be null because the db field accepts null
            if ($type === 'price' and $entry === '') {
                $entry = null;
            }
            // initiate query
            $stmt = $link->prepare($sql);
            // bind the params
            $stmt->bind_param("si", $entry, $id);
            if ($stmt->execute()) {
                // return string "OK" if it executes fine, so the javascript that initiated all this would know that it's all good
                echo "OK";
            } else {
                // return an error
                echo "Error: $mysqli->error";
            }
            // close statement, we are all done by now
            $stmt->close();
        } else {
            // throw invalid arguments if POST arguments are not all present
            echo "Invalid arguments";
        }
    }
}
?>