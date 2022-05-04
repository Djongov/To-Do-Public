<?php
include_once $_SERVER['DOCUMENT_ROOT'] . '/functions/firewall.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/functions/db.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/functions/session.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/pages/html-start.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/pages/header.php';
$post_output = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['name'], $_POST['link'], $_POST['price'], $_POST['shared'], $_POST['public'], $_POST['show_created_by'], $_POST['show_created_at'])) {
        if (checkAdmin($_SESSION['username'], $link)) {
            $name = trim(htmlspecialchars($_POST['name']));
            $name = mysqli_real_escape_string($link, $name);
            $link_form = intval(htmlspecialchars($_POST['link']));
            $price = intval(htmlspecialchars($_POST['price']));
            $shared = intval(htmlspecialchars($_POST['shared']));
            $public = intval(htmlspecialchars($_POST['public']));
            $created_by = intval(htmlspecialchars($_POST['show_created_by']));
            $created_at = intval(htmlspecialchars($_POST['show_created_at']));
            $stmt = $link->prepare("UPDATE `lists` SET `name`=?,`link`=?,`price`=?,`shared`=?,`public`=?,`show_created_by`=?,`show_created_at`=? WHERE `name`=?");
            $stmt->bind_param("siiiiiis", $name, $link_form, $price, $shared, $public, $created_by, $created_at, $name);
            if ($stmt->execute()) {
                $post_output .= '<div class="center">';
                $post_output .= '<p class="green bold">Success</p>';
                $post_output .= '<p class="bold"><a href="/admin">Back to Admin</a></p>';
                $post_output .= '</div>';
                $stmt->close();
            } else {
                echo "Error: $stmt->error";
            }
        } else {
            echo '<p class="red bold">Only Admins can edit users</p>';
        }
    } else {
        echo '<p class="red bold">Invalid arguments</p>';
    }
}


if (isset($_GET['list'], $_GET['id'])) {
    if (checkAdmin($_SESSION['username'], $link)) {
        $name = htmlspecialchars($_GET['list']);
        $name = mysqli_real_escape_string($link, $name);
        $id = intval(htmlspecialchars($_GET['id']));
        $stmt = $link->prepare("SELECT * FROM `lists` WHERE `id` = '$id' AND `name` = '$name'");
        if ($stmt->execute()) {
            $result = $stmt->get_result();
            $list_array = $result->fetch_all(MYSQLI_ASSOC);
            $result->free_result();
            $stmt->close();
        } else {
            echo "Error: $stmt->error";
        }
        if (count($list_array) > 0) {
        echo '<h1>Edit List</h1>';
        echo '<div class="center">';
            echo '<form action="" method="post">';
                echo '<table class="to-do responsive">';
                    echo '<tbody>';
                        foreach ($list_array[0] as $column=>$value) {
                        echo '<tr>';
                        $disabled = '';
                        if ($column === 'id' || $column === 'created_by' || $column === 'created_at') {
                            $disabled = 'disabled';
                        }
                            if (is_int($value)) {
                                echo '<td><label for="' . $column . '">' . $column . '</label></td>';
                                echo '<td><input class="center" type="number" min="0" max="1" name="' . $column . '" value="' . $value . '"' .$disabled . ' /></td>';
                            } else {
                                echo '<td><label for="' . $column . '">' . $column . '</label></td>';
                                echo '<td><input class="center" type="text" name="' . $column . '" value="' . $value . '" ' .$disabled . ' /></td>';
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
            echo '<p class="red bold">No Result</p>';
        }
    } else {
        echo '<p class="red bold">Only Admins can edit users</p>';
    }
} else {
    echo '<p class="red bold">Invalid arguments</p>';
}

echo $post_output;

include_once $_SERVER['DOCUMENT_ROOT'] . '/pages/footer.php';
?>