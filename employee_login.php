<?php
session_start();
require "db.php"; // Include the database connection file

// Function to check if the password is the default one
function isDefaultPassword($username, $password) {
    $firstInitial = strtoupper($username[0]);
    $lastInitial = strtoupper($username[1]);
    $defaultPassword = $firstInitial . $lastInitial . '1234!';
    return $password === $defaultPassword;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST["username"];
    $password = $_POST["password"];

    // Use the authenticate function from db.php
    if (authenticate($username, $password) == 1) {
        if (isDefaultPassword($username, $password)) {
            // Redirect to password reset page if default password is detected
            $_SESSION["username"] = $username;
            header("Location: reset_password.php");
            exit;
        } else {
            // Set session variable and redirect to the main page
            $_SESSION["username"] = $username;
            header("Location: employee_main.php");
            exit;
        }
    } else {
        $error = "Incorrect username or password.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Login</title>
</head>
<body>
    <h1>Welcome to eStore!</h1>
    <form method="POST" action="">
        <label for="username">Enter username:</label>
        <input type="text" id="username" name="username" required>
        <br>
        <label for="password">Enter password:</label>
        <input type="password" id="password" name="password" required>
        <br>
        <button type="submit">Login</button>
    </form>
    <?php
    if (isset($error)) {
        echo '<p style="color:red">' . htmlspecialchars($error) . '</p>';
    }
    ?>
</body>
</html>