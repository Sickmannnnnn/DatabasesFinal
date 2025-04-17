<?php
require "db.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate form inputs
    $username = $_POST["username"];
    $password = $_POST["password"];
    $password_again = $_POST["password_again"];
    $first_name = $_POST["first_name"];
    $last_name = $_POST["last_name"];
    $email = $_POST["email"];
    $shipping_address = $_POST["shipping_address"];

    if ($password !== $password_again) {
        $error = "Passwords do not match.";
    } else {
        try {
            $dbh = connectDB();

            // Generate a unique random id
            do {
                $id = random_int(100000, 999999);
                $stmt = $dbh->prepare("SELECT COUNT(*) FROM Customer WHERE id = :id");
                $stmt->bindParam(":id", $id);
                $stmt->execute();
                $count = $stmt->fetchColumn();
            } while ($count > 0);

            // Insert user into the database
            $statement = $dbh->prepare("INSERT INTO Customer (id, username, passwd, firstname, lastname, email, shipping_address) 
                                        VALUES (:id, :username, SHA2(:password, 256), :first_name, :last_name, :email, :shipping_address)");
            $statement->bindParam(":id", $id);
            $statement->bindParam(":username", $username);
            $statement->bindParam(":password", $password);
            $statement->bindParam(":first_name", $first_name);
            $statement->bindParam(":last_name", $last_name);
            $statement->bindParam(":email", $email);
            $statement->bindParam(":shipping_address", $shipping_address);

            $statement->execute();
            $success = "Registration successful! You can now log in.";
        } catch (PDOException $e) {
            $error = "Error: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Register</title>
</head>
<body>
<h1>Register</h1>
<form method="post" action="register.php">
    <label for="username"><b>Username:</b></label>
    <input type="text" name="username" required>
    <br><br>
    <label for="password"><b>Password:</b></label>
    <input type="password" name="password" required>
    <br><br>
    <label for="password_again"><b>Password Again:</b></label>
    <input type="password" name="password_again" required>
    <br><br>
    <label for="first_name"><b>First Name:</b></label>
    <input type="text" name="first_name" required>
    <br><br>
    <label for="last_name"><b>Last Name:</b></label>
    <input type="text" name="last_name" required>
    <br><br>
    <label for="email"><b>Email:</b></label>
    <input type="email" name="email" required>
    <br><br>
    <label for="shipping_address"><b>Shipping Address:</b></label>
    <textarea name="shipping_address" required></textarea>
    <br><br>
    <button type="submit">Register</button>
</form>
<!-- Button to redirect to the login page -->
<form method="get" action="login.php">
    <button type="submit">Go to Login</button>
</form>
<?php
if (isset($error)) {
    echo '<p style="color:red">' . $error . '</p>';
}
if (isset($success)) {
    echo '<p style="color:green">' . $success . '</p>';
}
?>
</body>
</html>
