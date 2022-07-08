<?php
//include_once $_SERVER['DOCUMENT_ROOT'] . '/functions/firewall.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/functions/db.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/functions/session.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/pages/html-start.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/pages/header.php';
$post_output = '';

// Handle the POST request, which will be after the Edit is submitted. We start with the POST first, because the GET request after displays the user data, and we want the POST first to do the changes and get the latest data in the user details rendering later
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check if username and admin are passed as arguments
    if (isset($_POST['username'], $_POST['admin'])) {
        // Save to variables and perform escaping and trimming and encoding
        $username = trim(htmlspecialchars($_POST['username']));
        $username = mysqli_real_escape_string($link, $username);
        $admin = intval(htmlspecialchars($_POST['admin']));
        // If a password argument is passed, them we need to update the new password too
        if (isset($_POST['password'])) {
            // We will do the same as when we register a user, hash the password
            $password = trim($_POST['password']);
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            // With the password, the SQL statement will be this
            $stmt = $link->prepare("UPDATE `users` SET `username`= ?,`password`= ?,`admin`= ? WHERE `username`= ?");
            // Bind the parameters
            $stmt->bind_param("ssis", $username, $hashed_password, $admin, $username);
        } else {
            // Without the password, the SQL statement will be this
            $stmt = $link->prepare("UPDATE `users` SET `username`= ?,`admin`= ? WHERE `username`= ?");
            // Bind the parameters
            $stmt->bind_param("sis", $username, $admin, $username);
        }
        // Try to execute
        if ($stmt->execute()) {
            $post_output .= '<div class="center">';
            $post_output .= '<p class="green bold">Success</p>';
            $post_output .= '<p class="bold"><a href="/admin">Back to Admin</a></p>';
            $post_output .= '</div>';
            $stmt->close();
        } else {
            $post_output .= "Error: $stmt->error";
        }
    } else {
        echo '<p class="red bold">Invalid arguments</p>';
    }
}

// We expect the page to start with these GET parameters, check if they are passed
if (isset($_GET['user'], $_GET['id'])) {
    if (checkAdmin($_SESSION['username'], $link)) {
    // Save to variables and perform escaping and trimming and encoding
    $user = htmlspecialchars($_GET['user']);
    $user = mysqli_real_escape_string($link, $user);
    $id = intval(htmlspecialchars($_GET['id']));
    // Prepare the SQL statement, no bind parameters here because we only pull from DB and not update or insert
    $stmt = $link->prepare("SELECT * FROM `users` WHERE `id` = '$id' AND `username` = '$user'");
    // try to execute
    if ($stmt->execute()) {
        // Save the result
        $result = $stmt->get_result();
        // Create an associative array from the result
        $user_array = $result->fetch_all(MYSQLI_ASSOC);
        // Free the result, we are done with the queries
        $result->free_result();
        // Close the statement
        $stmt->close();
    } else {
        echo "Error: $stmt->error";
    }
    // If the array length is more than 0, it means that we have a user that exists and can be edited
    if (count($user_array) > 0) {
    // Start HTML output
    echo '<h1>Edit User</h1>';
    echo '<div class="center">';
        echo '<form action="" method="post">';
            echo '<table class="to-do responsive">';
                echo '<tbody>';
                    // Loot through the user array result and display as rows in the table
                    foreach ($user_array[0] as $column=>$value) {
                    echo '<tr>';
                    $disabled = '';
                    // We want some of them not to be editable
                    if ($column === 'id' || $column === 'password' || $column === 'created_at') {
                        $disabled = 'disabled';
                    }
                        // If the result is an integer, it is probably a boolean with 1 or 0
                        if (is_int($value)) {
                            echo '<td><label for="' . $column . '">' . $column . '</label></td>';
                            echo '<td><input class="center" type="number" min="0" max="1" name="' . $column . '" value="' . $value . '"' . $disabled . ' /></td>';
                        // Otherwise a text input
                        } else {
                            if ($column === 'password') {
                                echo '<td><label for="' . $column . '">' . $column . '</label></td>';
                                echo '<td><input class="center" type="text" name="' . $column . '" value="' . $value . '" ' . $disabled . ' /><p><label for="turn-password-on">Change password?</label><input type="checkbox" id="turn-password-on" /></p></td>';
                                continue;
                            }
                            echo '<td><label for="' . $column . '">' . $column . '</label></td>';
                            echo '<td><input class="center" type="text" name="' . $column . '" value="' . $value . '" ' . $disabled . ' /></td>';
                        }
                    echo '</tr>';
                    }
                echo '</tbody>';
            echo '</table>';
            echo '<button type="submit">Save changes</button>';
        echo '</form>';
        echo '<a href="./admin"><button>Discard</button></a>';
    echo '</div>';
    } else {
        echo '<p class="red bold">No Result, user does not exist</p>';
    }
} else {
    echo '<p class="red bold">Only Admins can edit users</p>';
}
} else {
    echo '<p class="red bold">Invalid arguments</p>';
}

echo $post_output;
echo '<script src="./edit-user.js"></script>';
include_once $_SERVER['DOCUMENT_ROOT'] . '/pages/footer.php';
?>