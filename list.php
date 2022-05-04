<?php
include_once $_SERVER['DOCUMENT_ROOT']. '/functions/session.php';
$username = $_SESSION['username'];
if (isset($_GET['list'])) {
    $list_name = trim(htmlentities($_GET['list']));
    include_once $_SERVER['DOCUMENT_ROOT'] . '/functions/db.php';
    $sql = "SELECT * FROM `lists` WHERE name='$list_name';";
    $stmt = $link->prepare($sql);
    if ($stmt->execute()) {
        $result = $stmt->get_result();
        $list_array = $result->fetch_all(MYSQLI_ASSOC);
        $result->free_result();
        $stmt->close();
    } else {
        echo $stmt->error;
    }
    if (count($list_array) > 0) {
        include_once $_SERVER['DOCUMENT_ROOT']. '/pages/html-start.php';
        include_once $_SERVER['DOCUMENT_ROOT'] . '/functions/delete-entry.php';
        $list = $list_array[0]['name'];
        $link_form = ($list_array[0]['link'] === 1) ? true : false;
        $price = ($list_array[0]['price'] === 1) ? true : false;
        $shared = ($list_array[0]['shared'] === 1) ? true : false;
        $public = ($list_array[0]['public'] === 1) ? true : false;
        $created_by = ($list_array[0]['show_created_by'] === 1) ? true : false;
        $created_at = ($list_array[0]['show_created_at'] === 1) ? true : false;
        echo '<header>';
        echo '<h1>' . ucfirst($list) . ' List</h1>';
        echo '<form method="post" action="/process-function">';
            echo '<label for="task">';
            echo '<input type="hidden" name="category" value="' . $list . '" />';
                echo '<input class="task-input" type="text" name="task" min="3" max="65" required placeholder="Task Name...." required /><input type="submit" class="add-task" value="Add" />';
            echo '</label>';
            // Show this input field only if $link_form is true
            if (isset($link_form)) {
                if ($link_form) {
                    echo '<label for="link">
                    <input class="task-input" type="text" name="link" placeholder="Link" />
                </label>';
                }
            }
            // Show this input field only if $price is true
            if (isset($price)) {
                if ($price) {
                    echo '<label for="link">
                    <input class="task-input" type="number" name="price" placeholder="Price" step=".01" />
                </label>';
                }
            } 
        echo '</form>';
        echo '</header>';
        echo '<main>';
        //echo '<h1>' . ucfirst($list) . ' Tasks</h1>';
        $sql = ($shared) ? "SELECT * FROM `$list`" : "SELECT * FROM `$list` WHERE `created_by`='$username';";
        $stmt = $link->prepare($sql);
        if ($stmt->execute()) {
            $result = $stmt->get_result();
            $list_tasks = $result->fetch_all(MYSQLI_ASSOC);
            //var_dump($list_tasks);
            $result->free_result();
            if (count($list_tasks) > 0) {
                echo '<table class="to-do responsive">';
                    echo '<thead>';
                        echo '<tr>';
                            echo '<th>Name</th>';
                            echo ($link_form) ? '<th>Link</th>' : null;
                            echo ($price) ? '<th>Price</th>' : null;
                            echo ($created_at) ? '<th>Created</th>' : null;
                            echo (($shared and $created_by) or $created_by) ? '<th>Created by</th>' : null;
                            echo '<th>Action</th>';
                        echo '</tr>';
                    echo '</thead>';
                    echo '<tbody>';
                    foreach ($list_tasks as $task) {
                        echo ($task['completed'] === 1) ? '<tr tabindex="0" class="completed">' : '<tr tabindex="0">';
                            echo '<td class="task contenteditable" data-id="' . $task['id'] . '" data-table="' . $list . '" data-type="task">' . $task['task'] . '</td>';
                            if ($link_form) {
                                if ($task['link'] !== NULl) {
                                    echo '<td class="task"><a href="' . urldecode($task['link']) . '" target="_blank">Link</a></td>';
                                } else {
                                    echo '<td class="task">No Link</td>';
                                }
                            }
                            //echo ($link_form) and !empty($link_form)) ? '<td class="task"><a href="' . urldecode($task['link']) . '" target="_blank">Link</a></td>' : '<td class="task"></td>';
                            echo ($price) ? '<td class="task contenteditable" data-id="' . $task['id'] . '" data-table="' . $list . '" data-type="price">' . $task['price'] . '</td>' : null;
                            echo ($created_at) ? '<td class="task">' . date('d.m.y', strtotime($task["created_at"])) . '</td>' : null;
                            echo (($shared and $created_by) or $created_by) ? '<td class="task">' . $task['created_by'] . '</td>' : null;
                            //echo '<td class="delete"><a href="./list?list=' . $list . '&del_task=' . $task['id'] . '"><button class="delete" data-id="' . $task['id'] . '" data-table="' . $list . '">Delete</button></a></td>';
                            echo ($task['completed'] === 1) ? '<td><button class="undo-complete" data-id="' . $task['id'] . '" data-table="' . $list . '" title="Undo Complete">&#9100;</button><button class="delete-small" data-id="' . $task['id'] . '" data-table="' . $list . '" title="Delete">X</button></td>' : '<td><button class="mark-complete" data-id="' . $task['id'] . '" data-table="' . $list . '" title="Mark Complete">&#10003;</button><button class="delete-small" data-id="' . $task['id'] . '" data-table="' . $list . '" title="Delete">X</button></td>';
                        echo '</tr>';
                }
            }
            echo '</tbody>';
        echo '</table>';
        if ($public) {
            if (isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] === 'on' || $_SERVER['HTTPS'] == 1) || isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https') {
                $protocol = 'https://';
            } else {
                $protocol = 'http://';
            }
            $port = '';
            if (parse_url($_SERVER['HTTP_HOST'], PHP_URL_PORT)) {
                $port = ':' . parse_url($_SERVER['HTTP_HOST'], PHP_URL_PORT);
            }
            echo '<h4>Your link to share to the public:</h4>';
            if ($shared) {
                echo '<p><a href="' . $protocol . $_SERVER['SERVER_NAME'] . $port . '/public?list=' . $list . '" target="_blank">' . $protocol . $_SERVER['SERVER_NAME'] . $port . '/public?list=' . $list . '</a><p>';
            } else {
                echo '<p><a href="' . $protocol . $_SERVER['SERVER_NAME'] . $port . '/public?name=' . $username . '&list=' . $list . '" target="_blank">' . $protocol . $_SERVER['SERVER_NAME'] . $port . '/public?name=' . $username . '&list=' . $list . '</a><p>';
            }
        }
    }
    echo '</main>';
    echo '<script src="./tables.js"></script>';
    include_once $_SERVER['DOCUMENT_ROOT'] . '/pages/footer.php';
    }
}
?>