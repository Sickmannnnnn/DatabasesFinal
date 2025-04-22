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
    session_start();
    if(isset($_GET["restock"])){
        echo "<div class='boxed'>";
        echo"<form method='GET'>
            <input type='hidden' name='restock' value='1'>
            <label for='category'>Select Product:</label>
            <select name='category' id='category'>";
        // Fetch categories from the database
        try {
            $dbh = connectDB();
            $statement = $dbh->prepare("SELECT Prod_Name FROM Product");
            $statement->execute();
            $categories = $statement->fetchAll(PDO::FETCH_ASSOC);
            $selectedCategory = $_GET['category'] ?? '';

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
        echo "<br><br><button type='submit' name='restock_product_submit'>Submit</button>";
        echo "</form>";
        echo "</div>";
        if(isset($_GET['restock_product_submit'])){
            $statement = $dbh->prepare("UPDATE Product SET Stock_NUM = :new_stock WHERE Prod_Name = :prod_name");
            $stock_statement = $dbh->prepare("SELECT Stock_NUM FROM Product WHERE Prod_Name = :prod_name");
            $prod_name = $_GET['category'];
            $stock_statement->bindParam(":prod_name", $prod_name);
            $stock_statement->execute();
            $stock_result = $stock_statement->fetch(PDO::FETCH_ASSOC);
            $new_stock = $stock_result['Stock_NUM'] + $_GET['restock_quantity'];
            $statement->bindParam(":new_stock", $new_stock);
            $statement->bindParam(":prod_name", $prod_name);
            $statement->execute();
            //Insert Stock change into Product History table
                    //Find the product id
                    $prod_statement = $dbh->prepare("SELECT id, Stock_NUM FROM Product WHERE Prod_Name = :prod_name");
                    $prod_statement->bindParam(":prod_name", $prod_name);
                    $prod_statement->execute();
                    $prod_result = $prod_statement->fetch(PDO::FETCH_ASSOC);
                    $product_id = $prod_result['id'];
                    //find current datetime
                    $currentDateTime = date("Y-m-d H:i:s");
                    //Find Old Stock
                    $old_stock = $stock_result["Stock_NUM"];
                    //Find stock amount
                    $stock = $prod_result['Stock_NUM'];
                    //find price
                    $price_statement = $dbh->prepare("SELECT Price FROM Product WHERE Prod_Name = :prod_name");
                    $price_statement->bindParam(":prod_name", $prod_name);
                    $price_statement->execute();
                    $price_result = $price_statement->fetch(PDO::FETCH_ASSOC);
                    $price = $price_result['Price'];
                    //Find Employee ID
                    //Find Employee ID
                    $id_statement = $dbh->prepare("SELECT id FROM Employee WHERE username=:username");
                    $id_statement->bindParam(":username", $_SESSION['username']);
                    $id_statement->execute();
                    $id_result = $id_statement->fetch(PDO::FETCH_ASSOC);
                    $emp_id = $id_result['id'];
                $statement = $dbh->prepare("INSERT INTO Product_History VALUES (:product_id, :date_time, 'Update', :price, :price, :old_stock, :new_stock, :emp_id, 'Employee', NULL)");
                $statement->bindParam("product_id", $product_id);
                $statement->bindParam(":date_time", $currentDateTime);
                $statement->bindParam(":old_stock", $old_stock);
                $statement->bindParam(":new_stock", $new_stock);
                $statement->bindParam(":price", $stock);
                $statement->bindParam(":emp_id", $emp_id);
                $statement->execute();
            echo "<p style='color: green'>$prod_name restocked to $new_stock</p>";
        }
    }
    else if(isset($_GET['change_price'])){
        echo "<div class='boxed'>";
        echo"<form method='GET'>
            <input type='hidden' name='change_price' value='1'>
            <label for='category'>Select Product:</label>
            <select name='category' id='category'>";
        // Fetch categories from the database
        try {
            $dbh = connectDB();
            $statement = $dbh->prepare("SELECT Prod_Name FROM Product");
            $statement->execute();
            $categories = $statement->fetchAll(PDO::FETCH_ASSOC);
            $selectedCategory = $_GET['category'] ?? '';

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
        if(isset($_GET['category'])){
            $prod_name = $_GET['category'];
            $price_statement = $dbh->prepare("SELECT Price FROM Product WHERE Prod_Name = :prod_name");
            $price_statement->bindParam(":prod_name", $prod_name);
            $price_statement->execute();
            $old_price = $price_statement->fetch(PDO::FETCH_ASSOC);
            if(isset($_GET['go'])){
                echo "<br><br><label>New Price  </label><input type='decimal' name='new_price' value=".$old_price['Price']." min=0>";
                echo "<br><br><button type='submit' name='change_price_submit'>Change Price</button>";
            }
            
            if(isset($_GET['change_price_submit'])){
                //Update the product table
                $prod_name = $_GET['category'];
                $statement = $dbh->prepare("UPDATE Product SET Price = :new_price WHERE Prod_Name = :prod_name");
                $new_price = $_GET['new_price'];
                $statement->bindParam(":new_price", $new_price);
                $statement->bindParam(":prod_name", $prod_name);
                $statement->execute();

                //Insert Price change into Product History table
                    //Find the product id
                    $prod_statement = $dbh->prepare("SELECT id, Stock_NUM FROM Product WHERE Prod_Name = :prod_name");
                    $prod_statement->bindParam(":prod_name", $prod_name);
                    $prod_statement->execute();
                    $prod_result = $prod_statement->fetch(PDO::FETCH_ASSOC);
                    $product_id = $prod_result['id'];
                    //find current datetime
                    $currentDateTime = date("Y-m-d H:i:s");
                    //Find Old Price
                    $oldPrice = $old_price['Price'];
                    //Find stock amount
                    $stock = $prod_result['Stock_NUM'];
                    //Find Employee ID
                    $id_statement = $dbh->prepare("SELECT id FROM Employee WHERE username=:username");
                    $id_statement->bindParam(":username", $_SESSION['username']);
                    $id_statement->execute();
                    $id_result = $id_statement->fetch(PDO::FETCH_ASSOC);
                    $emp_id = $id_result['id'];
                $statement = $dbh->prepare("INSERT INTO Product_History VALUES (:product_id, :date_time, 'Update', :old_price, :new_price, :stock, :stock, :emp_id, 'Employee', NULL)");
                $statement->bindParam("product_id", $product_id);
                $statement->bindParam(":date_time", $currentDateTime);
                $statement->bindParam(":old_price", $oldPrice);
                $statement->bindParam(":new_price", $new_price);
                $statement->bindParam(":stock", $stock);
                $statement->bindParam(":emp_id", $emp_id);
                $statement->execute();
                //Success Message
                echo "<p style='color: green'>$prod_name price to $new_price</p>";
                
            }
            echo "</form>";
        }
        echo "</div>";
    }
    else if(isset($_GET['stock_history'])){
        echo "<form method='GET'>
                <input type='hidden' name='stock_history' value='1'>
                <label>Product ID  </label>
                <select name='category' id='category'>";
                // Fetch categories from the database
                try {
                    $dbh = connectDB();
                    $statement = $dbh->prepare("SELECT id FROM Product");
                    $statement->execute();
                    $categories = $statement->fetchAll(PDO::FETCH_ASSOC);
                    $selectedCategory = $_GET['category'] ?? '';

                    foreach ($categories as $category) {
                        $prodID = htmlspecialchars($category['id']);
                        $isSelected = ($prodID === $selectedCategory) ? 'selected' : '';
                        echo "<option value=\"$prodID\" $isSelected>$prodID</option>";
                        
                    }
                    
                } catch (PDOException $e) {
                    echo "<option value=\"\">Error loading categories".$e."</option>";
                }
                echo "</select>";
                echo "<br><br>
                <button type='submit' name='stock_history_view'>Search</button>
            </form>";
        if(isset($_GET['category'])){
            try{
                $dbh = connectDB();
                $statement = $dbh->prepare("SELECT * FROM Product_History WHERE product_id = :prod_id");
                
                $statement->bindParam(":prod_id", $_GET['category']);
                $statement->execute();
                $records = $statement->fetchAll(PDO::FETCH_ASSOC);
                echo "<h3>Stock Change History for " . $_GET['category'] . "</h3>";
                echo "<table border='1' cellpadding='8' cellspacing='0'>
                        <tr>
                            <td>Date Time</td>
                            <td>Old Stock</td>
                            <td>New Stock</td>
                            <td>Change</td>
                        </tr>";
                foreach($records as $record){
                    if($record['old_stock'] != $record['new_stock']){
                        $change = $record['new_stock'] - $record['old_stock'];
                        echo 
                            "<tr>
                                <td>" . $record['date_time'] . "</td>
                                <td>" . $record['old_stock'] . "</td>
                                <td>" . $record['new_stock'] . "</td>
                                <td>" . $change . "</td>
                            </tr>";
                    }
                }
                echo "</table>";
            }
            catch(Exception $e){
                echo $e;
            }
        }
    }
    else if(isset($_GET['price_history'])){
        echo "<form method='GET'>
                <input type='hidden' name='price_history' value='1'>
                <label>Product ID  </label>
                <select name='category' id='category'>";
                // Fetch categories from the database
                try {
                    $dbh = connectDB();
                    $statement = $dbh->prepare("SELECT id FROM Product");
                    $statement->execute();
                    $categories = $statement->fetchAll(PDO::FETCH_ASSOC);
                    $selectedCategory = $_GET['category'] ?? '';

                    foreach ($categories as $category) {
                        $prodID = htmlspecialchars($category['id']);
                        $isSelected = ($prodID === $selectedCategory) ? 'selected' : '';
                        echo "<option value=\"$prodID\" $isSelected>$prodID</option>";
                        
                    }
                    
                } catch (PDOException $e) {
                    echo "<option value=\"\">Error loading categories".$e."</option>";
                }
                echo "</select>
                <br><br>
                <button type='submit' name='price_history_view'>Search</button>
            </form>";
            
        if(isset($_GET['category'])){
            try{
                $dbh = connectDB();
                $statement = $dbh->prepare("SELECT * FROM Product_History WHERE product_id = :prod_id");
                $statement->bindParam(":prod_id", $_GET['category']);
                $statement->execute();
                $records = $statement->fetchAll(PDO::FETCH_ASSOC);
                echo "<h3>Price Change History</h3>";
                echo "<table border='1' cellpadding='8' cellspacing='0'>
                        <tr>
                            <td>Date Time</td>
                            <td>Old Price</td>
                            <td>New Price</td>
                            <td>Percent Change</td>
                        </tr>";
                foreach($records as $record){
                    if($record['old_price'] != $record['new_price']){
                        $percentage = round($record['new_price'] / $record['old_price'] * 100 - 100, 2);
                        echo 
                            "<tr>
                                <td>" . $record['date_time'] . "</td>
                                <td>" . $record['old_price'] . "</td>
                                <td>" . $record['new_price'] . "</td>
                                <td>" . $percentage . "%</td>
                            </tr>";
                    }
                }
                echo "</table>";
            }
            catch(Exception $e){
                echo $e;
            }
        }
    }
    else if(isset($_GET['logout'])){
        header('Location: employee_login.php');
        session_destroy();
        exit();
    }
?>
<form method="GET">
    
    <br><br>
    <button type="submit" name="restock">Restock Product</button>
    <button type="submit" name="change_price">Change Product Price</button>
    <button type="submit" name="stock_history">Stock History</button>
    <button type="submit" name="price_history">Price History</button>
    <button type="submit" name="logout">Logout</button>
    <br><br>

</form>
