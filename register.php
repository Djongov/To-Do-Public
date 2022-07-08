<?php 
//include_once $_SERVER['DOCUMENT_ROOT'] . '/functions/firewall.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/functions/db.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/functions/session.php';
// Define variables and initialize with empty values
$username = $password = $confirm_password = $output = "";
// Set limits for the length of the username and password
$username_min_length = 3;
$username_max_length = 50;

$password_min_length = 4;
$password_max_length = 255;

// Password comlexity pattern in regex. If you want to disable - change it to $password_complexity_pattern = false;
$password_complexity_pattern = '/(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{4,}/';
$password_complexity_pattern = false;



// Processing form data when form is submitted
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Let's validate that the correct post arguments are passed
    if (isset($_POST['username'], $_POST['password'], $_POST['confirm_password'])) {
        // Let's validate if any of the post arguments are empty
        $empty_post = false;
        foreach($_POST as $key => $value) {
            if (empty($_POST[$key])) {
                $empty_post = true;
                $output .= '<p class="red bold">' . $key . ' is empty</p>';
            }
        }
        // if they are not empty proceed
        if (!$empty_post) {
            // Let's check if the username is within allowed limits
            if (strlen(trim($_POST['username'])) >= $username_min_length && strlen(trim($_POST['username'])) <= $username_max_length) {
                // Let's check if the password and the confirm password are within allowed limits
                if (strlen(trim($_POST['password'])) >= $password_min_length && strlen(trim($_POST['password'])) <= $password_max_length) {
                    // Let's check if the password is the same as confirm password
                    if ($_POST['password'] === $_POST['confirm_password']) {
                        // Let's define this variable and we will use to do some checks around complexity
                        $pass_complexity_check = false;
                        // if $password_complexity_pattern is defined as string
                        if ($password_complexity_pattern) {
                            // Do a regex match on the password
                            if (preg_match($password_complexity_pattern, $_POST['password'])) {
                                // if match is found, make complexity check true
                                $pass_complexity_check = true;
                            } else {
                                $output .= '<span class="red bold">Password does not meet complexity requirements</span>';
                            }
                        // if it's false it means the complexity is turned off
                        } else {
                            // So we make the complexity check true so we can continue below
                            $pass_complexity_check = true;
                        }
                        // Check if the complexity check pass is true and continue if it is
                        if ($pass_complexity_check) {
                            // OK ALL CHECKS ARE DONE, LET'S BOTHER THE DATABASE WITH A QUERY
                            
                            // Save the post username as a variable but apply htmlspecialchars and trim
                            $username = htmlspecialchars(trim($_POST['username']));
                            // Save the post password as a variable but only apply trim, no need for escaping or encoding as they can alter the password itself and the user will not login with the expected password, we are not saving the password itself to the database but its hash so it's safe
                            $password = trim($_POST["password"]);
                            // Let's hash the password and not store it like that in the database
                            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                            // Escape special characters in username. No need for the password to be escaped.
                            $username = mysqli_real_escape_string($link, $username);
                            // Prepare the SQL statement
                            $stmt = $link->prepare("SELECT * FROM `users` WHERE `username` = ?");
                            // Bind the parameters
                            $stmt->bind_param("s", $username);
                            // Try to execute
                            if ($stmt->execute()) {
                                // Store the result
                                $result = $stmt->get_result();
                                // If there is no such username in the db, the resulting rows will be 0, which is what we want
                                if ($result->num_rows == 0) {
                                    // Let's free result and close the statement so we can start a new one
                                    $result->free_result();
                                    $stmt->close();
                                    // The INSERT query will be this, prepare it
                                    $stmt = $link->prepare("INSERT INTO `users` (username, password, admin) VALUES (?, ?, 0)");
                                    // Bind the parameters
                                    $stmt->bind_param("ss", $username, $hashed_password);
                                    // Try to execute
                                    if ($stmt->execute()) {
                                        $output .= '<span class="green bold">Successful registration</span>';
                                        // Redirect to login page
                                        //header("location: /admin");
                                    } else {
                                        $output .= '<span class="red bold">Unsuccessful</span>';
                                    }
                                } else {
                                    $output .= '<span class="red bold">Username already exists</span>';
                                }
                            } else {
                                $output .= '<span class="red bold">Unsuccessful</span>';
                            }
                        } else {
                            $output .= '<span class="red bold">Password does not meet complexity requirements</span>';
                        }
                    } else {
                        $output .= '<span class="red bold">Passwords are not the same</span>';
                    }
                } else {
                    $output .= '<span class="red bold">Password not within allowed limits (Must be at least ' . $password_min_length . ' and not more than ' . $password_max_length . ')</span>';
                }
            } else {
                $output .= '<span class="red bold">Username ' . trim($_POST['username']) . ' not within allowed limits (Must be at least ' . $username_min_length . ' and not more than ' . $username_max_length . ')</span>';
            }
        } else {
            $output .= '<span class="red bold">Empty argument/s</span>';
        }
    } else {
        $output .= '<span class="red bold">Incorrect arguments</span>';
    }
}    

include_once $_SERVER['DOCUMENT_ROOT'] . '/pages/html-start.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/pages/header.php';
?>
<div class="center">
    <p>Please fill this form to create an account.</p>
    <form method="post" autocomplete="off">
    <div>
        <label>Username</label>
        <input class="task-input" type="text" placeholder="Username" name="username" minlength="3" maxlength="20" value="<?php echo $username; ?>" autocomplete="off" />
    </div>    
    <div>
        <label>Password </label>
        <input class="task-input" type="password" placeholder="Password" name="password" minlength="4" maxlength="30" title="Must contain at least one  number and one uppercase and lowercase letter, and at least 4 or more characters" value="<?php echo $password; ?>" autocomplete="off" />
    </div>
    <div>
        <label>Confirm Password</label>
        <input class="task-input" type="password" placeholder="Confirm Password" name="confirm_password" minlength="4" maxlength="30" value="<?php echo htmlspecialchars($confirm_password); ?>" autocomplete="off" />
    </div>
    <div>
        <button class="edit" type="submit">Register</button>
        <button class="edit" type="reset" value="Reset">Reset</button>
    </div>
    </form>
    <p><?=$output;?></p>
</div>
<?php include_once $_SERVER['DOCUMENT_ROOT'] . '/pages/footer.php';?>