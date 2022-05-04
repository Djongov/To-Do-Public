<?php
include_once $_SERVER['DOCUMENT_ROOT']. '/functions/session.php';
include_once $_SERVER['DOCUMENT_ROOT']. '/pages/html-start.php';
include_once $_SERVER['DOCUMENT_ROOT']. '/pages/header.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/functions/db.php';
echo '<div class="flex">';
$sql = "SELECT * FROM `lists`";
$stmt = $link->prepare($sql);
if ($stmt->execute()) {
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
                foreach($result as $row) {
                    $link_from = ($row["link"] === 1) ? true : false;
                    $price = ($row["price"] === 1) ? true : false;
                    $shared = ($row["shared"] === 1) ? true : false;
                    echo ($shared) ? '<div class="holder"><a class="special-link" href="./list?list=' . $row["name"] . '">' . ucfirst($row["name"]) . ' list (shared)</a></div>' : '<div class="holder"><a class="special-link" href="./list?list=' . $row["name"] . '">' . ucfirst($row["name"]) . ' list</a></div>';
                }
        }
    } else {
        echo '<p class="red bold">You do not have any lists. Go to <a href="./admin">Admin Page</a> to create</p>';
    }
} else {
    echo $stmt->error;
}
echo '</div>';

// After installation, rename the install file
if (file_exists('./install.php')) {
    rename('./install.php', './install-passed.php');
}
include_once $_SERVER['DOCUMENT_ROOT'] . '/pages/footer.php';
?>