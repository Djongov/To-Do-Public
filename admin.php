<?php
//include_once $_SERVER['DOCUMENT_ROOT'] . '/functions/firewall.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/functions/db.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/functions/session.php';
// delete user before any html is output
if (isset($_GET['del_user'])) {
	$id = $_GET['del_user'];
    if (checkAdmin($_SESSION['username'], $link)) {
        $stmt = $link->prepare("DELETE FROM users WHERE id=?");
        $stmt->bind_param("s", $id);
        if ($stmt->execute()) {
            $stmt->close();
            header('Location: /admin');
        } else {
            echo $stmt->error;
        }
    }
}
include_once $_SERVER['DOCUMENT_ROOT'] . '/pages/html-start.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/pages/header.php';

echo '<h1>Existing users</h1>';

echo '<table class="to-do responsive">';
    echo '<thead>';
        echo '<tr>';
            $stmt = $link->prepare("SHOW COLUMNS FROM users");
            if ($stmt->execute()) {
                $columns_result = $stmt->get_result();
                while($columns = mysqli_fetch_array($columns_result, MYSQLI_ASSOC)) {
                    echo '<th>' . $columns['Field'] . '</th>';
                }
            } else {
                echo '<th>' . $stmt->error . '</th>';
            }
            $columns_result->free_result();
            $stmt->close();
            echo '<th>Action</th>';
        echo '</tr>';
    echo '</thead>';
    echo '<tbody>';
        echo '<tr>';
            $stmt = $link->prepare("SELECT * FROM users");
            if ($stmt->execute()) {
                $result = $stmt->get_result();
                while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC))  {
                    echo '<tr tabindex="0">';
                        foreach ($row as $column=>$value) {
                            echo '<td>' . $value . '</td>';  
                        }
                    echo '<td class="delete"><a href="./user-edit?user=' . $row['username'] . '&id=' . $row['id'] . '"><button class="edit">Edit</button></a><a href="/admin?del_user=' . $row['id'] . '"><button class="delete">Delete</button></a></td>';
                    echo '</tr>';
                }
            } else {
                echo '<td>' . $stmt->error . '</td>';
            }
            $result->free_result();
            $stmt->close();
    echo '</tbody>';
echo '</table>';
?>


<h1>New User creation</h1>
<div class="center">
    <a class="special-link" href="/register">Register</a>
</div>

<h1>Existing Lists</h1>

<?php

echo '<table class="to-do responsive">';
    echo '<thead>';
        echo '<tr>';
            $stmt = $link->prepare("SHOW COLUMNS FROM `lists`");
            if ($stmt->execute()) {
                $columns_result = $stmt->get_result();
                while($columns = mysqli_fetch_array($columns_result, MYSQLI_ASSOC)) {
                    echo '<th>' . $columns['Field'] . '</th>';
                }
            } else {
                echo '<th>' . $stmt->error . '</th>';
            }
            echo '<th>Action</th>';
            $columns_result->free_result();
            $stmt->close();
        echo '</tr>';
    echo '</thead>';
    echo '<tbody>';
        echo '<tr>';
            $stmt = $link->prepare("SELECT * FROM `lists`");
            $all_lists = [];
            if ($stmt->execute()) {
                $result = $stmt->get_result();
                while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC))  {
                    array_push($all_lists, $row["name"]);
                    echo '<tr tabindex="0">';
                        foreach ($row as $column=>$value) {
                            echo '<td>' . $value . '</td>';
                        }
                        echo '<td><a href="./list-edit?list=' . $row['name'] . '&id=' . $row['id'] . '"><button class="edit">Edit</button></a></td>';
                    echo '</tr>';
                }
            } else {
                echo '<td>' . $stmt->error . '</td>';
            }
    echo '</tbody>';
echo '</table>';

echo '<h1>Delete List</h1>';
echo '<form id="form-delete-list" method="post" action="">';
echo '<div class="center">';
echo '<select class="height-50px" name="delete_list">';
    foreach ($all_lists as $lists_entries) {
        echo '<option value="' . $lists_entries . '">' . $lists_entries . '</option>';
    }
echo '</select>';
echo '<button class="edit" id="delete-list" type="submit">Delete</button>';
echo '</div>';
echo '</form>';

?>

<h1>Create new list in the database</h1>
<div class="center">
<form method="post" action="">
<table class="table-center">
    <tr>
        <td>
            <label for="link">with Link</label>
        </td>
        <td>
            <input type="checkbox" name="link" value="yes" />
        </td>
    </tr>
    <tr>
        <td>
            <label for="price">with Price</label>
        </td>
        <td>
            <input type="checkbox" name="price" value="yes" />
        </td>
    </tr>
    <tr>
        <td>
            <label for="public">Public (get special publicly accessible link)</label>
        </td>
        <td>
            <input type="checkbox" name="public" value="yes" />
        </td>
    </tr>
    <tr>
        <td>
            <label for="shared">Shared (between users)</label>
        </td>
        <td>
            <input type="checkbox" name="shared" value="yes" />
        </td>
    </tr>
    <tr>
        <td>
            <label for="show_created_by">Show the created by column</label>
        </td>
        <td>
            <input type="checkbox" name="show_created_by" value="yes" />
        </td>
    </tr>
    <tr>
        <td>
            <label for="show_created_at">Show the created date column</label>
        </td>
        <td>
            <input type="checkbox" name="show_created_at" value="yes" />
        </td>
    </tr>
</table>
<label for="list-name">
<input type="text" class="task-input" name="list-name" placeholder="list name..." required />
</label>
<button type="submit" class="edit">Create</button>
</form>

<?php
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (isset($_POST['list-name']) && !empty($_POST['list-name'])) {
        if (checkAdmin($_SESSION['username'], $link)) {
            $show_link = (isset($_POST['link']) && $_POST['link'] === "yes") ? 1 : 0;
            $price = (isset($_POST['price']) && $_POST['price'] === "yes") ? 1 : 0;
            $public = (isset($_POST['public']) && $_POST['public'] === "yes") ? 1 : 0;
            $shared = (isset($_POST['shared']) && $_POST['shared'] === "yes") ? 1 : 0;
            $show_created_by = (isset($_POST['show_created_by']) && $_POST['show_created_by'] === "yes") ? 1 : 0;
            $show_created_at = (isset($_POST['show_created_at']) && $_POST['show_created_at'] === "yes") ? 1 : 0;
            $list_name = htmlspecialchars(strtolower($_POST['list-name']));
            $list_name = mysqli_real_escape_string($link, $list_name);

            $stmt = $link->prepare("SELECT * FROM `lists` WHERE `name`= ?");
            $stmt->bind_param("s", $list_name);
            if ($stmt->execute()) {
                $result = $stmt->get_result();
                $exist_check_array = $result->fetch_all(MYSQLI_ASSOC);
                if (count($exist_check_array) === 0) {
                    $result->free_result();
                    $stmt->close();

                    $stmt = $link->prepare("CREATE TABLE IF NOT EXISTS `$list_name` ( `id` INT(11) NOT NULL AUTO_INCREMENT , `task` VARCHAR(255) NULL , `link` VARCHAR(2083) NULL , `price` DECIMAL(7,2) NULL , completed BOOLEAN DEFAULT 0 , `created_by` VARCHAR(60) NOT NULL , `created_at` DATETIME NULL DEFAULT CURRENT_TIMESTAMP , PRIMARY KEY (`id`)) ENGINE = InnoDB;");
                    if ($stmt->execute()) {
                        $stmt->close();
                        $stmt = $link->prepare("INSERT INTO `lists` (`name`, `link`, `price`, `shared`, `public`, `show_created_by`, `show_created_at`, `created_by`) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                        $stmt->bind_param("siiiiiis", $list_name, $show_link, $price, $shared, $public, $show_created_by, $show_created_at, $_SESSION['username']);
                        if ($stmt->execute()) {
                            $stmt->close();
                            echo "<meta http-equiv='refresh' content='0'>";
                        } else {
                            echo "Could not update the lists table with the new list: $stmt->error";
                        }
                    } else {
                        echo "Could not create the to do list table: $stmt->error";
                    }
                } else {
                    echo '<p>Error - List already exists</p>';
                }
            }
        } else {
            echo '<p>Only Admins can create lists/p>';
        }
    // Post Argument for deleting a list
    } elseif (isset($_POST['delete_list'])) {
        // Make sure that we are deleting an existing list
        if (in_array($_POST['delete_list'], $all_lists)) {
            if (checkAdmin($_SESSION['username'], $link)) {
                $list_name = htmlspecialchars($_POST['delete_list']);
                $list_name = mysqli_real_escape_string($link, $list_name);
                    $stmt = $link->prepare("DELETE FROM lists WHERE name=?");
                    $stmt->bind_param("s", $list_name);
                    if ($stmt->execute()) {
                        $stmt->close();
                        $stmt = $link->prepare("DROP TABLE `$list_name`");
                        if ($stmt->execute()) {
                            echo '<p>List successfully deleted</p>';
                            echo "<meta http-equiv='refresh' content='0'>";
                        } else {
                            echo "<p>Could not DROP table $list_name - $stmt->error</p>";
                        }
                    } else {
                        echo "<p>Could not delete list $list_name - $stmt->error</p>";
                    }
            } else {
                echo '<p>Only Admins can create lists/p>';
            }
        } else {
            echo '<p>You are trying to delete a non existent list - ' . $_POST['delete_list'] . '</p>';
        }
    } else {
        echo '<h4>Invalud arguments</h4>';
        echo '<p>' . var_dump($_POST) . '</p>';
        exit();
    }
}
?>

</div>
<script src="/admin-site.js"></script>
<?php
include_once $_SERVER['DOCUMENT_ROOT'] . '/pages/footer.php';
?>