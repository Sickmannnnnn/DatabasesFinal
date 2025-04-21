<?php
function connectDB()
{
    // Use the absolute path to your db.ini file
    $config = parse_ini_file("/local/my_web_files/zrsickma/db.ini");
    $dbh = new PDO($config['dsn'], $config['username'], $config['password']);
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    return $dbh;
}

// Return number of rows matching the given user and password.
function authenticate($user, $passwd) {
    try {
        $dbh = connectDB();
        $statement = $dbh->prepare("SELECT count(*) FROM Customer WHERE username = :username AND passwd = sha2(:passwd,256)");
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

// Retrieve accounts for the given user.
function get_accounts($user)
{
    try {
        $dbh = connectDB();
        // Corrected SQL: removed the mistaken "name" after balance
        $statement = $dbh->prepare("SELECT account_no, balance FROM lab8_accounts WHERE username = :username");
        $statement->bindParam(":username", $user);
        $statement->execute();
        $accounts = $statement->fetchAll();
        $dbh = null;
        return $accounts;
    } catch (PDOException $e) {
        print "Error! " . $e->getMessage() . "<br/>";
        die();
    }
}

// Perform a money transfer between accounts.
function transfer($from, $to, $amount, $user)
{
    try {
        $dbh = connectDB();
        $dbh->beginTransaction();

        // Check if there is enough balance in the from account.
        $statement = $dbh->prepare("SELECT balance FROM lab8_accounts WHERE account_no = :from");
        $statement->bindParam(":from", $from);
        $statement->execute();
        $row = $statement->fetch();
        if ($row) {
            $currentBalance = $row[0];
            if ($currentBalance < $amount) {
                $dbh->rollBack();
                $dbh = null;
                return "Not enough balance in $from.";
            }
        } else {
            $dbh->rollBack();
            $dbh = null;
            return "Account $from does not exist.";
        }

        // Deduct amount from the source account.
        $statement = $dbh->prepare("UPDATE lab8_accounts SET balance = balance - :amount WHERE account_no = :from");
        $statement->bindParam(":amount", $amount);
        $statement->bindParam(":from", $from);
        $statement->execute();
        if ($statement->rowCount() != 1) {
            $dbh->rollBack();
            return "Error: Unexpected number of rows affected when deducting amount.";
        }

        // Add amount to the destination account.
        $statement = $dbh->prepare("UPDATE lab8_accounts SET balance = balance + :amount WHERE account_no = :to");
        $statement->bindParam(":amount", $amount);
        $statement->bindParam(":to", $to);
        $statement->execute();
        if ($statement->rowCount() != 1) {
            $dbh->rollBack();
            return "Error: Unexpected number of rows affected when crediting amount.";
        }

        $dbh->commit();
        return "Money has been transferred successfully.";
    } catch (Exception $e) {
        if ($dbh) {
            $dbh->rollBack();
        }
        return "Failed: " . $e->getMessage();
    }
}

/* return all the orders as an array for a customer 
Function getOrders ($cust_id) {
}

/* return the details as array for a order 
Function getOrderDetails($order_id) {
}

*/
?>
