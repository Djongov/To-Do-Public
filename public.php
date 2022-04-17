<?php

include_once $_SERVER['DOCUMENT_ROOT'] . '/functions/db.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/pages/html-start.php';

// If only list param is specified, (meaning that the list is probably shared + public)
if (isset($_GET["list"])) {
    $list = htmlspecialchars($_GET["list"]);
    // If there is a name parameter, then the list must not be public
    if (isset($_GET["name"])) {  
        $name = htmlspecialchars($_GET["name"]);
    }
    // Let's do some checks if this user exists in the DB
    if (isset($name)) {
        $user_check = $link->prepare("SELECT * FROM `users` WHERE `username` = ?");
        $user_check->bind_param("s", $name);
        if ($user_check->execute()) {
        $user_check->store_result();
            if ($user_check->num_rows === 0) {
                echo '<p class="center red bold">Unsuccessful</p>';
                // No such list in database
                die("");
            }
        } else {
            // Unsuccessful query
            die("Unsuccessful");
        }
    }
    // Let's do some checks if this list exists in the DB
        if (isset($name)) {
            $list_public_check = $link->prepare("SELECT * FROM lists WHERE public = 1 AND `name` = ?");
            $list_public_check->bind_param("s", $list);
        } else {
            $list_public_check = $link->prepare("SELECT * FROM lists WHERE public = 1");
        }
        if ($list_public_check->execute()) {
        $list_public_check->store_result();
            if ($list_public_check->num_rows === 0) {
                // Just say Unsuccessful, do not reveal 
                echo '<p class="center red bold">Unsuccessful</p>';
                // No such list in database
                die();
            }
        } else {
            // Unsuccessful query
            die("Unsuccessful");
        }
        echo '<main>';
        echo '<h1>' . $list . '\'s List</h1>';
        echo '<table class="to-do">';
        echo '<thead>';
        echo '<tr>';
        echo '<th>Name</th>';
        echo '<th>Link</th>';
        echo '<th>Created</th>';
        echo '</tr>';
        echo '</thead>';
        echo '<tbody>';
        if (isset($name)) {
            $stmt = $link->prepare("SELECT * FROM $list WHERE `created_by` = ?");
            $stmt->bind_param("s", $name);
        } else {
            $stmt = $link->prepare("SELECT * FROM $list");
        }
        $stmt->execute();
        if ($stmt->num_rows !== 0) {
            echo '<p class="center red bold">Unsuccessful</p>';
            die();
        } else {
            $result = $stmt->get_result();
                while ($row = mysqli_fetch_array($result)) {
                    echo '<tr>';
                    echo '<td class="task">' . $row['task'] . '</td>';
                    echo '<td class="task"><a href="'. urldecode($row['link']) . '" target="_blank">Link</a></td>';
                    echo '<td class="task">' . date('d.m H:i', strtotime($row["created_at"])) . '</td>';
                    echo '</tr>';
                }
        }
        echo '</tbody>';
        echo '</table>';
        echo '</main>';
} else {
    echo '<p class="center red bold">Invalid parameter</p>';
}
?>