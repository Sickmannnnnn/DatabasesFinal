<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Category Dropdown and Search</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
        }
        .header {
            display: flex;
            justify-content: flex-start;
            align-items: center;
            background-color: #f4f4f4;
            padding: 10px;
        }
        .header button {
            padding: 10px 20px;
            background-color: #007BFF;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .header button:hover {
            background-color: #0056b3;
        }
        .content {
            padding: 20px;
        }
        .search {
            margin-top: 20px;
        }
        .search button {
            padding: 10px 20px;
            background-color: #28a745;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .search button:hover {
            background-color: #218838;
        }
    </style>
</head>
<body>
    <div class="header">
        <button onclick="location.href='login.php'">Login</button>
    </div>
    <div class="content">
        <?php
            session_start();
            // Include the database connection file
            include 'db.php';

            // Fetch categories from the database
            try {
                $dbh = connectDB();
                $statement = $dbh->prepare("SELECT firstname FROM Customer WHERE username = :username");
                $statement -> bindParam(":username", $_SESSION["username"]);
                //Issue the welcome statement
                $statement->execute();
                $result = $statement->fetch(PDO::FETCH_ASSOC);
                if ($result && isset($result["firstname"])) {
                    echo "<div> Welcome " . htmlspecialchars($result["firstname"]) . "</div>";
                }
                else{
                    echo "<div>Please log in to see your name.</div>";
                }
                
            }
            catch (PDOException $e) {
                echo "<option value=\"\">Error loading categories</option>";
            }
        ?>
        
        <form method="GET">
            <br>
            <button type="submit" name="view_order">View Orders</button>
            <button type="button" name="shopping_cart">Shopping Cart</button>
            <button type="button" name="change_password">Change Password</button>
            <button type="button" name="logout">Logout</button>
            <br><br>
            <label for="category">Select Category:</label>
            <select name="category" id="category">
                <?php
                try{
                    $statement = $dbh->prepare("SELECT Cat_name FROM Category");
                    $statement->execute();
                    $categories = $statement->fetchAll(PDO::FETCH_ASSOC);
                    $selectedCategory = $_GET['category'] ?? '';

                    foreach ($categories as $category) {
                        $catName = htmlspecialchars($category['Cat_name']);
                        $isSelected = ($catName === $selectedCategory) ? 'selected' : '';
                        echo "<option value=\"$catName\" $isSelected>$catName</option>";
                    }
                } catch (PDOException $e) {
                    echo "<option value=\"\">Error loading categories</option>";
                }
                ?>
            </select>
            <div class="search">
                <button type="submit" name="search">Search</button>
                <?php
                if(isset($_GET["view_order"])){
                    try{
                        $dbh = connectDB();
                        $statement = $dbh->prepare("SELECT id FROM Customer WHERE username = :username");
                        $statement->bindParam(':username', $_SESSION['username']);
                        $statement->execute();
                        $result = $statement->fetch(PDO::FETCH_ASSOC);
                        $id = $result["id"];

                        $statement = $dbh->prepare("SELECT Order_ID, order_date, total FROM C_Order WHERE C_id = :id");
                        $statement->bindParam(':id', $id);
                        $statement->execute();
                        $result = $statement->fetchAll(PDO::FETCH_ASSOC);
                        if($result){
                            echo "<div> Here are your (id: $id) orders:";
                            $count = 1;
                            foreach ($result as $order){
                                //Outside of table logic
                                echo "<br>$count)
                                    &nbsp;&nbsp;&nbsp; ID: " . $order['Order_ID'] . 
                                    "<br> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Date: " . $order['order_date'] . 
                                    "<br> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Total: " . $order["total"];
                                $count = $count+1;

                                //Inside of table logic
                                $statement = $dbh->prepare("SELECT product_id, Prod_Name, Price, quantity FROM Order_Item JOIN Product ON Order_Item.product_id=Product.id WHERE Order_ID = :order");
                                $statement->bindParam(":order", $order['Order_ID']);
                                $statement->execute();
                                $products = $statement->fetchAll(PDO::FETCH_ASSOC);
                                echo "<table border='1' cellpadding='8' cellspacing='0'>
                                            <tr>
                                                <td> Product ID </td>
                                                <td> Product Name </td>
                                                <td> Price </td>
                                                <td> Quantity </td>
                                            </tr>";
                                foreach ($products as $product){
                                    echo "  <tr> 
                                                <td>" . $product['product_id'] . "</td>
                                                <td>" . $product['Prod_Name'] . "</td>
                                                <td>" . $product["Price"] . "</td>
                                                <td>" . $product['quantity'] . "</td>
                                            </tr>";
                                }
                                echo "</table>";
                            }
                            echo "</div>";
                        }
                        else{
                            echo "No Orders Placed";
                        }
                    }
                    catch(Exception $e){
                        echo "<p>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
                    }
                }
                else if (isset($_GET['category'])) {
                    $selectedCategory = $_GET['category'];

                    try {
                        $dbh = connectDB();

                        // Fetch images based on selected category
                        $statement = $dbh->prepare("SELECT Prod_Name, Price, image_url FROM Product WHERE Prod_cat = :category");
                        $statement->bindParam(':category', $selectedCategory);
                        $statement->execute();

                        $images = $statement->fetchAll(PDO::FETCH_ASSOC);

                        if (count($images) > 0) {
                            echo "<h2>Images in category: " . htmlspecialchars($selectedCategory) . "</h2>";
                            echo "<div class='image-gallery'>";
                            foreach ($images as $img) {
                                $prodName = $img["Prod_Name"];
                                $price = $img["Price"];
                                echo "<div style='margin: 10px; text-align: center; width: 200px;'>";
                                echo "<img src='" . htmlspecialchars($img['image_url']) . "' alt='Image' style='width:200px;height:auto;margin:10px;'>";
                                echo "<strong>$prodName</strong><br>";
                                echo "<span>\$" . number_format($price, 2) . "</span>";
                                echo "</div>";
                            }
                            echo "</div>";
                        } else {
                            echo "<p>No images found for this category.</p>";
                        }

                    } catch (PDOException $e) {
                        echo "<p>Error fetching images: " . htmlspecialchars($e->getMessage()) . "</p>";
                    }
                } else {
                    echo "<p>Please select a category.</p>";
                }
            ?>
            </div>
        </form>
    </div>
</body>
</html>