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
    $conn = connectDB();
    $stmt = $conn->prepare("UPDATE Employee SET password = ? WHERE username = ?");
    $hashed = password_hash($newPassword, PASSWORD_DEFAULT);
    $stmt->bind_param("ss", $hashed, $username);
    return $stmt->execute();
}


$error = "";
$showResetFields = false;

// If form submitted
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    //$username = $_POST["username"] ?? '';
    //$password = $_POST["password"] ?? '';

    if (Employee_authenticate($_POST["username"], $_POST["password"]) == 1) {
        // Set session variable and redirect to the main page
        $_SESSION["username"] = $_POST["username"];

        if (isDefaultPassword($username, $password)) {
            $showResetFields = true;

            if (!empty($_POST["new_password"]) && !empty($_POST["confirm_password"])) {
                $newPassword = $_POST["new_password"];
                $confirmPassword = $_POST["confirm_password"];

                if ($newPassword !== $confirmPassword) {
                    $error = "New passwords do not match.";
                } else {
                    // Update password in database (you need to implement updatePassword in db.php)
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
  <script>
    function toggleResetFields(show) {
      document.getElementById('reset_fields').style.display = show ? 'block' : 'none';
    }
  </script>
</head>
<body>
  <h1>Welcome to eStore!</h1>
  <form method="post">
    <label for="username">Enter username:</label>
    <input id="username" name="username" type="text" required value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
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
  </form>

  <?php if (!empty($error)): ?>
    <p style="color:red;"><?= htmlspecialchars($error) ?></p>
  <?php endif; ?>
</body>
</html>
