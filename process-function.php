<?php

include_once $_SERVER['DOCUMENT_ROOT']. '/functions/session.php';

function processTask($category, $link_to_do, $task, $price) {
    // Inlcude the MySQL connection and $link
    include_once $_SERVER['DOCUMENT_ROOT'] . '/functions/db.php';
    $username = $_SESSION['username'];
    // Escape quotes in case there are
    $task = mysqli_real_escape_string($link, $task);
    // If link is provided it will not be a 'No' so one type of SQL statement
    if (!empty($link_to_do) and !empty($price)) {
        $link_to_do = mysqli_real_escape_string($link, urlencode($_POST['link']));
        $price = floatval(mysqli_real_escape_string($link, urlencode($_POST['price'])));
        $sql = "INSERT INTO `$category` (task, created_by, link, price) VALUES (?, ?, ?, ?)";
        $stmt = $link->prepare($sql);
        $stmt->bind_param("ssss", $task, $username, $link_to_do, $price);
        echo ($stmt->execute()) ? '' : $link->error;
        $link->close();
        header("Location: /list?list=" . $category, true, 301);
        exit();
    // And another if link is not defined
    } elseif (!empty($link_to_do) and empty($price)) {
        $link_to_do = mysqli_real_escape_string($link, urlencode($_POST['link']));
        $sql = "INSERT INTO `$category` (task, created_by, link) VALUES (?, ?, ?)";
        $stmt = $link->prepare($sql);
        $stmt->bind_param("sss", $task, $username, $link_to_do);
        if ($stmt->execute()) {
        } else {
            echo $link->error;
        }
        $link->close();
        header("Location: /list?list=" . $category, true, 301);
        exit();
    // And if link is empty but price is defined
    }  elseif (empty($link_to_do) and !empty($price)) {
        $price = floatval(mysqli_real_escape_string($link, urlencode($_POST['price'])));
        $sql = "INSERT INTO `$category` (task, created_by, price) VALUES (?, ?, ?)";
        $stmt = $link->prepare($sql);
        $stmt->bind_param("sss", $task, $username, $price);
        if ($stmt->execute()) {
        } else {
            echo $link->error;
        }
        $link->close();
        header("Location: /list?list=" . $category, true, 301);
        exit();
    } else {
        $sql = "INSERT INTO `$category` (task, created_by) VALUES (?, ?)";
        $stmt = $link->prepare($sql);
        $stmt->bind_param("ss", $task, $username);
        if ($stmt->execute()) {
        } else {
            echo $link->error;
        }
        $link->close();
        header("Location: /list?list=" . $category, true, 301);
        exit();
    }
}


// Only process if request method is POST
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Only continue if POST argument 'category' and 'task' are set which comes from a hidden input field in each list and from the normal input field for task name
    if (isset($_POST['category']) && isset($_POST['task'])) {
        // Save the task name to a variable
        $task = strip_tags($_POST['task']);
        $category = strip_tags($_POST['category']);
        $link_to_do = (isset($_POST['link'])) ? htmlspecialchars($_POST['link']) : '';
        $price = (isset($_POST['price'])) ? htmlspecialchars($_POST['price']) : '';
        processTask($category, $link_to_do, $task, $price);
    } else {
        echo '<h4>Category or task argument missing</h4>';
        exit();
    }
// If not POST method
} else {
    echo '<h4>Wrong HTTP method</h4>';
    exit();
}
?>