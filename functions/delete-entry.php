<?php
/*
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
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $json_data = file_get_contents('php://input'); // Read the data from the POST request
    $array = json_decode($json_data, true);
    if (count($array) > 0) {
        (!is_int($array['id'])) ? $id = $array['id'] : die("ID passed not as integer");
        (isset($array['table'])) ? $table = htmlspecialchars($array["table"]) : '';
        include_once $_SERVER['DOCUMENT_ROOT'] . '/functions/db.php';
        $stmt = $link->prepare("DELETE FROM `$table` WHERE id=?");
        $stmt->bind_param("s", $id);
        if ($stmt->execute()) {
            // return OK so that the JavaScript script will trigger the remove of the table row
            echo 'OK';
        } else {
            echo $stmt->error();
        }
    }
}
?>
