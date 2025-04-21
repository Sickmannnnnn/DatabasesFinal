<?php
session_start();
require "db.php"; // Include the database connection file

if (!isset($_SESSION["username"])) {
    header("Location: employee_login.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $newPassword = $_POST["new_password"];
    $confirmPassword = $_POST["confirm_password"];

    if ($newPassword === $confirmPassword && !empty($newPassword)) {
        try {
            $dbh = connectDB(); // Connect to the database

            // Update the password in the database
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            $stmt = $dbh->prepare("UPDATE Employee SET passwd = :newPassword WHERE username = :username");
            $stmt->bindParam(":newPassword", $hashedPassword);
            $stmt->bindParam(":username", $_SESSION["username"]);
            $stmt->execute();

            // Redirect to the main page after successful password reset
            header("Location: employee_main.php");
            exit;
        } catch (PDOException $e) {
            $error = "Error updating password: " . $e->getMessage();
        }
    } else {
        $error = "Passwords do not match or are empty.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
</head>
<body>
    <h1>Reset Your Password</h1>
    <form method="POST" action="">
        <label for="new_password">Enter new password:</label>
        <input type="password" id="new_password" name="new_password" required>
        <br>
        <label for="confirm_password">Confirm new password:</label>
        <input type="password" id="confirm_password" name="confirm_password" required>
        <br>
        <button type="submit">Reset Password</button>
    </form>
    <?php
    if (isset($error)) {
        echo '<p style="color:red">' . htmlspecialchars($error) . '</p>';
    }
    ?>
</body>
</html>