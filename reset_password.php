<?php
session_start();
require "db.php";

// must be logged in
if (empty($_SESSION["username"])) {
    header("Location: employee_login.php");
    exit;
}

$username = $_SESSION["username"];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $newPassword     = $_POST["new_password"]     ?? '';
    $confirmPassword = $_POST["confirm_password"] ?? '';

    if (empty($newPassword)) {
        $error = "New password cannot be blank.";
    } elseif ($newPassword !== $confirmPassword) {
        $error = "Passwords do not match.";
    } else {
        try {
            $dbh = connectDB();
            $hashed = password_hash($newPassword, PASSWORD_DEFAULT);

            $stmt = $dbh->prepare(
              "UPDATE employees
               SET password    = :p,
                   first_login = 0
               WHERE username = :u"
            );
            $stmt->execute([
              ':p' => $hashed,
              ':u' => $username
            ]);

            // Done — send them to the main page
            header("Location: employee_main.php");
            exit;

        } catch (PDOException $e) {
            $error = "Database error: " . htmlspecialchars($e->getMessage());
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Reset Password – eStore</title>
</head>
<body>
  <h1>Welcome to eStore!</h1>
  <form method="post">
    <p>You must reset your password before continuing.</p>

    <label for="new_password">Enter new password:</label>
    <input id="new_password" name="new_password" type="password" required>
    <br>
    <label for="confirm_password">Enter new password again:</label>
    <input id="confirm_password" name="confirm_password" type="password" required>
    <br>
    <button type="submit">Reset Password</button>
  </form>

  <?php if (!empty($error)): ?>
    <p style="color:red;"><?= htmlspecialchars($error) ?></p>
  <?php endif; ?>
</body>
</html>
