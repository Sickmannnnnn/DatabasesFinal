<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        .boxed{
            border: 2px solid black;
            padding: 10px;
            margin: 10px;
        }
        
    </style>
</head>
<h1>Employee Home</h1>
<?php
    // Include the database connection file
    include 'db.php';
    if(isset($_GET["restock"])){
        echo "<div class='boxed'>";
        echo"<form method='POST'>
            <label for='category'>Select Product:</label>
            <select name='category' id='category'>";
        // Fetch categories from the database
        try {
            $dbh = connectDB();
            $statement = $dbh->prepare("SELECT Prod_Name FROM Product");
            $statement->execute();
            $categories = $statement->fetchAll(PDO::FETCH_ASSOC);
            $selectedCategory = $_POST['category'] ?? '';

            foreach ($categories as $category) {
                $prodName = htmlspecialchars($category['Prod_Name']);
                $isSelected = ($prodName === $selectedCategory) ? 'selected' : '';
                echo "<option value=\"$prodName\" $isSelected>$prodName</option>";
            
            }
        } catch (PDOException $e) {
            echo "<option value=\"\">Error loading categories</option>";
        }
        echo "</select>";
        echo "<br><br><label>Restock Amount  </label><input type='number' name='restock_quantity' value=0 min=0>";
        echo "<br><br><button type='submit' name='submit'>Submit</button>";
        echo "</form>";
        echo "</div>";
        if(isset($_POST['submit'])){
            $statement = $dbh->prepare("UPDATE Product SET Stock_NUM = :new_stock WHERE Prod_Name = :prod_name");
            $stock_statement = $dbh->prepare("SELECT Stock_NUM FROM Product WHERE Prod_Name = :prod_name");
            $prod_name = $_POST['category'];
            $stock_statement->bindParam(":prod_name", $prod_name);
            $stock_statement->execute();
            $stock_result = $stock_statement->fetch(PDO::FETCH_ASSOC);
            $new_stock = $stock_result['Stock_NUM'] + $_POST['restock_quantity'];
            $statement->bindParam(":new_stock", $new_stock);
            $statement->bindParam(":prod_name", $prod_name);
            $statement->execute();
            echo "<p style='color: green'>$prod_name restocked to $new_stock</p>";
        }
    }
    else if(isset($_GET['change_price'])){
        echo "<div class='boxed'>";
        echo"<form method='POST'>
            <label for='category'>Select Product:</label>
            <select name='category' id='category'>";
        // Fetch categories from the database
        try {
            $dbh = connectDB();
            $statement = $dbh->prepare("SELECT Prod_Name FROM Product");
            $statement->execute();
            $categories = $statement->fetchAll(PDO::FETCH_ASSOC);
            $selectedCategory = $_POST['category'] ?? '';

            foreach ($categories as $category) {
                $prodName = htmlspecialchars($category['Prod_Name']);
                $isSelected = ($prodName === $selectedCategory) ? 'selected' : '';
                echo "<option value=\"$prodName\" $isSelected>$prodName</option>";
            
            }
        } catch (PDOException $e) {
            echo "<option value=\"\">Error loading categories</option>";
        }
        echo "</select>";
        echo "<br><br><button type='submit' name='go'>Go</button>";
        if(isset($_POST['category'])){
            $prod_name = $_POST['category'];
            $price_statement = $dbh->prepare("SELECT Price FROM Product WHERE Prod_Name = :prod_name");
            $price_statement->bindParam(":prod_name", $prod_name);
            $price_statement->execute();
            $old_price = $price_statement->fetch(PDO::FETCH_ASSOC);
            if(!isset($_POST['submit'])){
                echo "<br><br><label>New Price  </label><input type='decimal' name='new_price' value=".$old_price['Price']." min=0>";
                echo "<br><br><button type='submit' name='submit'>Change Price</button>";
            }
            
            if(isset($_POST['submit'])){
                $prod_name = $_POST['category'];
                $statement = $dbh->prepare("UPDATE Product SET Price = :new_price WHERE Prod_Name = :prod_name");
                $new_price = $_POST['new_price'];
                $statement->bindParam(":new_price", $new_price);
                $statement->bindParam(":prod_name", $prod_name);
                $statement->execute();
                echo "<p style='color: green'>$prod_name price to $new_price</p>";
                
            }
            echo "</form>";
        }
        echo "</div>";
    }
    else if(isset($_GET['stock_history'])){
        try{
            $dbh = connectDB();
            $statement = $dbh->prepare("SELECT * FROM Product_History");
            $statement->execute();
            $records = $statement->fetchAll(PDO::FETCH_ASSOC);
            echo "<h3>Stock Change History</h3>";
            echo "<table border='1' cellpadding='8' cellspacing='0'>
                    <tr>
                        <td>Product ID</td>
                        <td>Date Time</td>
                        <td>Action Type</td>
                        <td>Old Stock</td>
                        <td>New Stock</td>
                        <td>Updated By</td>
                        <td>Updated Role</td>
                        <td>Order ID</td>
                    </tr>";
            foreach($records as $record){
                if($record['old_stock'] != $record['new_stock'])
                echo 
                    "<tr>
                        <td>" . $record['product_id'] . "</td>
                        <td>" . $record['date_time'] . "</td>
                        <td>" . $record['action_type'] . "</td>
                        <td>" . $record['old_stock'] . "</td>
                        <td>" . $record['new_stock'] . "</td>
                        <td>" . $record['updated_by'] . "</td>
                        <td>" . $record['updated_role'] . "</td>
                        <td>" . $record['order_id'] . "</td>
                    </tr>";
            }
            echo "</table>";
        }
        catch(Exception $e){
            echo $e;
        }
    }
    else if(isset($_GET['price_history'])){
        try{
            $dbh = connectDB();
            $statement = $dbh->prepare("SELECT * FROM Product_History");
            $statement->execute();
            $records = $statement->fetchAll(PDO::FETCH_ASSOC);
            echo "<h3>Price Change History</h3>";
            echo "<table border='1' cellpadding='8' cellspacing='0'>
                    <tr>
                        <td>Product ID</td>
                        <td>Date Time</td>
                        <td>Action Type</td>
                        <td>Old Price</td>
                        <td>New Price</td>
                        <td>Updated By</td>
                        <td>Updated Role</td>
                        <td>Order ID</td>
                    </tr>";
            foreach($records as $record){
                if($record['old_price'] != $record['new_price'])
                echo 
                    "<tr>
                        <td>" . $record['product_id'] . "</td>
                        <td>" . $record['date_time'] . "</td>
                        <td>" . $record['action_type'] . "</td>
                        <td>" . $record['old_price'] . "</td>
                        <td>" . $record['new_price'] . "</td>
                        <td>" . $record['updated_by'] . "</td>
                        <td>" . $record['updated_role'] . "</td>
                        <td>" . $record['order_id'] . "</td>
                    </tr>";
            }
            echo "</table>";
        }
        catch(Exception $e){
            echo $e;
        }
    }
?>
<form method="GET">
    
    <br><br>
    <button type="submit" name="restock">Restock Product</button>
    <button type="submit" name="change_price">Change Product Price</button>
    <button type="submit" name="stock_history">Stock History</button>
    <button type="submit" name="price_history">Price History</button>
    <button type="button" onclick="window.location.href='employee_login.php'">Return to Login</button>
    <br><br>

</form>
