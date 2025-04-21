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
?>
<form method="GET">
    
    <br><br>
    <button type="submit" name="restock">Restock Product</button>
    <button type="submit" name="change_price">Change Product Price</button>
    <button type="submit" name="stock_history">Stock History</button>
    <button type="submit" name="price_history">Price History</button>
    <br><br>

</form>
