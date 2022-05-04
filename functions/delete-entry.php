<?php
/*
This was used back when we didn't delete tasks with javascript but with pure PHP


// Removing the unnecessary portion of the URL so we can redirect to the proper page
function removeqsvar($url, $varname) {
    return preg_replace('/([?&])'.$varname.'=[^&]+(&|$)/','$1',$url);
}
// Removing the entry from the list via GET request
if (isset($_GET['del_task'], $_GET['list'])) {
	$id = htmlspecialchars($_GET['del_task']);
    $list_delete = htmlspecialchars($_GET['list']);
    include_once $_SERVER['DOCUMENT_ROOT'] . '/functions/db.php';
    $del_record = $link->prepare("DELETE FROM $list_delete WHERE id=?");
    $del_record->bind_param("s", $id);
	if ($del_record->execute()) {

    } else {
        echo $del_record->error();
    }
    if (!headers_sent()) {
        header('Location: /list?list=' . $list_delete, true, 301);
    } else {
        echo('<meta http-equiv="refresh" content="0; url=' . str_replace("&", "", removeqsvar($_SERVER['REQUEST_URI'], 'del_task')) . '">');
    }
}
*/

// Only proceed if the method is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Final check for the secret header we've sent from the Javascript fetch (secretheader : badass). Not a big security feature but still something
    if (isset($_SERVER['HTTP_SECRETHEADER']) and $_SERVER['HTTP_SECRETHEADER'] === 'badass') {
        // Proceed only if all arguments that should be received in this POST are present
        if (isset($_POST['id'], $_POST['table'])) {
            // if id is not an integer kill script
            (!is_int($_POST['id'])) ? $id = $_POST['id'] : die("ID passed not as integer");
            // if table is not present or is empty kill script
            (isset($_POST['table']) and !empty($_POST['table'])) ? $table = htmlspecialchars($_POST["table"]) : die("table must be passed and not be empty");
            // include db file
            include_once $_SERVER['DOCUMENT_ROOT'] . '/functions/db.php';
            // prepare the statement
            $stmt = $link->prepare("DELETE FROM `$table` WHERE id=?");
            // bind params
            $stmt->bind_param("s", $id);
            // attempt to execute
            if ($stmt->execute()) {
                // return OK so that the JavaScript script will trigger the remove of the table row
                echo 'OK';
            } else {
                // or return the error
                echo $stmt->error;
            }
        } else {
            echo 'Invalid arguments';
        }
    } else {
        echo 'Oops, something is missing from the request';
    }
}
?>
