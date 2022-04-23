<?php
include_once $_SERVER['DOCUMENT_ROOT']. '/pages/header.php';
include_once $_SERVER['DOCUMENT_ROOT']. '/pages/html-start.php';
$output = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['database-server'], $_POST['database-name'], $_POST['database-user'], $_POST['database-password'], $_POST['admin-user'], $_POST['admin-password'], $_POST['admin-password-confirm'])) {
        /* Attempt to connect to MySQL database */
        ini_set('display_errors', 'On');
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
        $link = mysqli_connect($_POST['database-server'], $_POST['database-user'], $_POST['database-password']);
        // Check connection
        if ($link !== false) {
            $output .= '<p class="green bold">Database Connection established</p>';
            $database = $_POST['database-name'];
            $stmt = $link->prepare("CREATE DATABASE IF NOT EXISTS `$database`");
            if ($stmt->execute()) {
                $output .= '<p class="green bold">Database ' . $database . ' successfully created</p>';
                $stmt->close();
                $link->select_db($database);
                $sql = "CREATE TABLE `users` (`id` int(11) NOT NULL AUTO_INCREMENT,`username` varchar(50) NOT NULL,`password` varchar(255) NOT NULL,`admin` tinyint(1) NOT NULL,`created_at` datetime DEFAULT CURRENT_TIMESTAMP,PRIMARY KEY (`id`),UNIQUE KEY `username` (`username`)) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci";
                $stmt = $link->prepare($sql);
                if ($stmt->execute()) {
                    $output .= '<p class="green bold">Users table Created</p>';
                    $stmt->close();
                    $username = htmlspecialchars(trim($_POST['admin-user']));
                    // Escape special characters in username. No need for the password to be escaped.
                    $username = mysqli_real_escape_string($link, $username);
                    if ($_POST['admin-password'] === $_POST['admin-password-confirm']) {
                        $password = trim($_POST["admin-password"]);
                        // Let's hash the password and not store it like that in the database
                        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                        $sql = "INSERT INTO `users` (username, password, admin) VALUES (?, ?, 1)";
                        $stmt = $link->prepare($sql);
                        $stmt->bind_param("ss", $username, $hashed_password);
                        if ($stmt->execute()) {
                            $output .= '<p class="green bold">Admin User Created</p>';
                            $stmt->close();
                            $sql = "CREATE TABLE `lists` (`id` int(11) NOT NULL AUTO_INCREMENT,`name` varchar(25) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,`link` tinyint(1) NOT NULL,`price` tinyint(1) DEFAULT NULL,`shared` tinyint(1) NOT NULL,`public` tinyint(1) NOT NULL,`created_by` varchar(25) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,`created_at` datetime DEFAULT CURRENT_TIMESTAMP,PRIMARY KEY (`id`)) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci";
                            $stmt = $link->prepare($sql);
                            if ($stmt->execute()) {
                                $output .= '<p class="green bold">Lists table created</p>';
                                $output .= '<h1 class="green bold>Installation succeeded</h1>';
                                // Attempt to delete install.php file
                                if (!file_exists('./site-config.php')) {
                                    $database_server = $_POST['database-server'];
                                    $database_user = $_POST['database-user'];
                                    $database_password = $_POST['database-password'];
                                    $database_name = $_POST['database-name'];
                                    touch('./site-config.php');
                                    $site_config = <<<EOL
                                    <?php
                                    define('DB_SERVER', '$database_server');
                                    define('DB_USERNAME', '$database_user');
                                    define('DB_PASSWORD', '$database_password');
                                    define('DB_NAME', '$database_name');
                                    ?>
                                    EOL;
                                    if (file_put_contents('./site-config.php', $site_config)) {
                                        $output .= '<h1 class="green bold>Configuration set successfully</h1>';
                                        $output .= '<a href="/login"><button>Login</button></a>';
                                    } else {
                                        $output .= '<h1 class="red bold>Configuration could not be set in site-config.php</h1>';
                                    }
                                }
                            } else {
                                $output .= '<p class="red bold">Lists table creation failed: ' . $stmt->error . '</p>';
                            }
                        } else {
                            $output .= '<p class="red bold">Admin user creation failed: ' . $stmt->error . '</p>';
                        }
                    } else {
                        $output .= '<p class="red bold">Admin user passwords do not match</p>';
                    }
                } else {
                    $output .= '<p class="red bold">Users table creation failed: ' . $stmt->error . '</p>';
                }
            } else {
                $output .= '<p class="red bold">Database creation failed: ' . $stmt->error . '</p>';
            }
        } else {
            $output .= '<p class="red bold">ERROR: Could not connect. ' . mysqli_connect_error() . '</p>';
        }
    } else {
        $output .= '<p class="red bold">Invalid arguments</p>';
    }
}

?>
<h1>Installation</h1>
<div class="flex">
    <p>You are about to install the To Do list system.</p>
    <h2>Prerequisites</h2>
    <p>We will be creating a database in a MySQL server</p>
    <?php
        // Check if mysqli extension is available, otherwise the app won't work
        $extension_check = false;
        if (!extension_loaded('mysqli')) {
            $output .= '<p class="red bold">mysqli extension is not available</p>';
        } else {
            echo '<p class="green bold">mysqli extension available</p>';
            $extension_check = true;
        }
    if ($extension_check) {

    ?>
    <h2>Installation Details</h2>
    <form method="post" action="">
    <p>These are the database connection settings. </p>
        <div>
            <label for="database">Database Server</label>
            <input type="text" name="database-server" placeholder="mysql" value="<?=(isset($_POST['database-server'])) ? htmlspecialchars($_POST['database-server']): null;?>" required />
        </div>
        <div>
            <label for="database">Database Name</label>
            <input type="text" name="database-name" placeholder="'to-do' for example" value="<?=(isset($_POST['database-name'])) ? htmlspecialchars($_POST['database-name']): null;?>" required />
        </div>
        <div>
            <label for="database">Database User</label>
            <input type="text" name="database-user" placeholder="root" value="<?=(isset($_POST['database-user'])) ? htmlspecialchars($_POST['database-user']): null;?>" required />
        </div>
        <div>
            <label for="database">Database User password</label>
            <input type="password" name="database-password" value="<?=(isset($_POST['database-password'])) ? htmlspecialchars($_POST['database-password']): null;?>" required />
        </div>
        <hr />
        <p>These will be the To-Do account details. This account you will use to login after the installation is complete.</p>
        <div>
            <label for="database">Admin username</label>
            <input type="text" name="admin-user" value="<?=(isset($_POST['admin-user'])) ? htmlspecialchars($_POST['admin-user']): null;?>" required />
        </div>
        <div>
            <label for="database">Admin password</label>
            <input type="password" name="admin-password" value="<?=(isset($_POST['admin-password'])) ? htmlspecialchars($_POST['admin-password']): null;?>" required />
        </div>
        <div>
            <label for="database">Confirm Admin password</label>
            <input type="password" name="admin-password-confirm" value="<?=(isset($_POST['admin-password-confirm'])) ? htmlspecialchars($_POST['admin-password-confirm']): null;?>" required />
        </div>
        <div>
            <button type="submit">Install</button>
        </div>
    </form>
</div>
<div class="flex">
    <?=$output?>
</div>
<?php
// Extension check fail goes here
} else {
    
}
include_once $_SERVER['DOCUMENT_ROOT'] . '/pages/footer.php';
?>