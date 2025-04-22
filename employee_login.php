<?php
require "db.php"; // must define connectDB() and authenticate()
session_start();

function Employee_authenticate($user, $passwd) {
    try {
        $dbh = connectDB();
        $statement = $dbh->prepare("SELECT count(*) FROM Employee WHERE username = :username AND passwd = sha2(:passwd,256)");
        $statement->bindParam(":username", $user);
        $statement->bindParam(":passwd", $passwd);
        $statement->execute();
        $row = $statement->fetch();
        $dbh = null;
        return $row[0];
    } catch (PDOException $e) {
        print "Error! " . $e->getMessage() . "<br/>";
        die();
    }
}

function isDefaultPassword($username, $password) {
    $firstInitial = strtoupper($username[0]);
    $lastInitial = strtoupper($username[1]);
    $defaultPassword = $firstInitial . $lastInitial . '1234!';
    return $password === $defaultPassword;
}

function updatePassword($username, $newPassword) {
    try {
        $dbh = connectDB();
        $hashed = hash("sha256", $newPassword); // Keep consistent with authenticate()
        $stmt = $dbh->prepare("UPDATE Employee SET passwd = :hashed WHERE username = :username");
        $stmt->bindParam(":hashed", $hashed);
        $stmt->bindParam(":username", $username);
        return $stmt->execute();
    } catch (PDOException $e) {
        error_log("Password update failed: " . $e->getMessage());
        return false;
    }
}


$error = "";
$showResetFields = false;

// If form submitted
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = $_POST["username"];
    $password = $_POST["password"];

    if (Employee_authenticate($username, $password) == 1) {
        $_SESSION["username"] = $username;

        if (isDefaultPassword($username, $password)) {
            $showResetFields = true;

            if (!empty($_POST["new_password"]) && !empty($_POST["confirm_password"])) {
                $newPassword = $_POST["new_password"];
                $confirmPassword = $_POST["confirm_password"];

                if ($newPassword !== $confirmPassword) {
                    $error = "New passwords do not match.";
                } else {
                    if (updatePassword($username, $newPassword)) {
                        header("Location: employee_main.php");
                        exit;
                    } else {
                        $error = "Password update failed.";
                    }
                }
            }
        } else {
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
  <title>Employee Login – eStore</title>
</head>
<body>
  <h1>Welcome to eStore!</h1>

  <form method="post">
    <label for="username">Enter username:</label>
    <input id="username" name="username" type="text" required value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
    <br>
    <br>

    <label for="password">Enter password:</label>
    <input id="password" name="password" type="password" required>
    <br>

    <?php if ($showResetFields): ?>
      <div id="reset_fields">
        <p>You must reset your password before continuing.</p>

        <label for="new_password">Enter new password:</label>
        <input id="new_password" name="new_password" type="password" required>
        <br>

        <label for="confirm_password">Enter new password again:</label>
        <input id="confirm_password" name="confirm_password" type="password" required>
        <br>
      </div>
    <?php endif; ?>

    <button type="submit"><?= $showResetFields ? 'Reset Password' : 'Login' ?></button>
    <br><br>
  </form>

  <button type="button" onclick="window.location.href='cust_main.php'">Go to Customer Main</button>
  <br><br>

  <?php if (!empty($error)): ?>
    <p style="color:red;"><?= htmlspecialchars($error) ?></p>
  <?php endif; ?>
</body>
</html>
