<?php
include_once $_SERVER['DOCUMENT_ROOT'] . '/functions/db.php';
 
// Define variables and initialize with empty values
$username = $password = $output = "";
 
// Processing form data when form is submitted
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (isset($_POST['username'], $_POST['password'])) {
        // Check if username is empty
        if (htmlspecialchars(!empty(trim($_POST["username"])))) {
            $username = htmlspecialchars(trim($_POST["username"]));
            // Check if password is empty
            if (htmlspecialchars(!empty(trim($_POST["password"])))) {
                $password = htmlspecialchars(trim($_POST["password"]));
                $sql = "SELECT * FROM `users` WHERE `username` = ?";
                $stmt = $link->prepare($sql);
                $stmt->bind_param("s", $username);
                if ($stmt->execute()) {
                    $result = $stmt->get_result();
                    if ($result->num_rows > 0) {
                        $user_array = $result->fetch_assoc();
                        $hashed_password = $user_array["password"];
                            if (password_verify($password, $hashed_password)) {
                                // Password is correct, so start a new session
                                if (!isset($_SESSION['username'])) {
                                    session_start();
                                    // Store data in session variables
                                    $_SESSION["loggedin"] = true;
                                    $_SESSION["username"] = $username;
                                    // Redirect user to welcome page
                                    header("location: /lists");
                                }
                            } else {
                                $output = '<span class="red bold">Wrong Password</span>';
                            }
                    } else {
                        $output = '<span class="red bold">Username does not exist</span>';
                    }
                } else {
                    $ouput = '<span class="red bold">Unsuccessful</span>';
                }
            } else {
                $output = '<span class="red bold">Password cannot be empty</span>';
            }
        } else {
            $output = "<span class='red bold'>Username cannot be empty</span>";
        }
        $result->free_result();
        $stmt->close();
    } else {
        $result = '<span class="red bold">Incorrect Parameters</span>';
    }
}

include_once $_SERVER['DOCUMENT_ROOT']. '/pages/header.php';
include_once $_SERVER['DOCUMENT_ROOT']. '/pages/html-start.php';
?>

<div class="center">
    <form method="post">
        <div>
            <label>Username</label><br/>
            <input class="task-input" type="text" name="username" value="<?=htmlspecialchars($username)?>" required />
        </div>
        <div>
            <label>Password</label><br/>
            <input class="task-input" type="password" name="password" required />
        </div>
        <button class="edit" type="submit">Login</button>
    </form>
    <p><?=$output?></p>
</div>
<?php include_once $_SERVER['DOCUMENT_ROOT'] . '/pages/footer.php';?>