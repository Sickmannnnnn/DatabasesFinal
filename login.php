<?php
require "db.php";
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Check login credentials using authenticate function
    if (authenticate($_POST["username"], $_POST["password"]) == 1) {
        // Set session variable and redirect to the main page
        $_SESSION["username"] = $_POST["username"];
        header("Location: cust_logged_main.php");
        exit;
    } else {
        $error = "Incorrect username and password";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Login</title>
</head>
<body>
<form method="post" action="login.php">
    <label for="username"><b>Username: </b></label>
    <input type="text" placeholder="Enter Username" name="username" required>
    <br><br>
    <label for="password"><b>Password: </b></label>
    <input type="password" placeholder="Enter Password" name="password" required>
    <br><br>
    <button type="submit">Login</button> &nbsp;&nbsp;&nbsp;
    <button type="button" onclick="location.href='register.php'">Register</button>
    <br><br>
    <button type="button" onclick="location.href='employee_login.php'">Employee Login</button>
</form>
<?php
if (isset($error)) {
    echo '<p style="color:red">' . $error . '</p>';
}
?>
</body>
</html>