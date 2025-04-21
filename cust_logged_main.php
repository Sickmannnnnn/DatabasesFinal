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
        .boxed{
            border: 2px solid black;
            padding: 10px;
            margin: 10px;
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
              <?php
            if(isset($_GET["shopping_cart"])){
                if(isset($_SESSION["id"])){
                    $id = $_SESSION["id"];
                    echo "<h3>Your Shopping Cart</h3>";
                    echo "<form method='POST'>";
                    echo "<table border='1' cellpadding='8' cellspacing='0'>
                        <tr>
                            <td>Product ID</td>
                            <td>Product Name</td>
                            <td>Price</td>
                            <td>Quantity</td>
                            <td></td>
                            <td></td>
                        </tr>";
                    $statement = $dbh->prepare("SELECT product_id, Prod_Name, price, quantity FROM Cart JOIN Product ON Cart.product_id = Product.id WHERE customer_id = :id");
                    $statement->bindParam(":id", $id);
                    $statement->execute();
                    $products = $statement->fetchAll(PDO::FETCH_ASSOC);
                    foreach ($products as $product){
                        echo "  <tr> 
                                    <td>" . $product['product_id'] . "</td>
                                    <td>" . $product['Prod_Name'] . "</td>
                                    <td>" . $product["price"] . "</td>
                                    <td><input type='number' name='quantity[" . $product['product_id'] . "]' value='" . $product['quantity'] . "' min='0'></td>
                                    <td><button type='submit' name='update' value='" . $product['product_id'] . "'>Update</button></td>
                                    <td><button type='submit' name='remove' value='" . $product['product_id'] . "'>Remove</button></td>
                                </tr>";
                    }
                    echo "</table>";
                    echo "<br><button type='submit' name='checkout'>Check Out</button>";
                    echo "</form>";
                }else{
                    echo "<p style='color:red'>You must login to see your cart</p>";
                }
            }
            if($_SERVER["REQUEST_METHOD"] == "POST"){
                $customer_id = $_SESSION["id"];
                //Handle update quantity
                if (isset($_POST['update'])){
                    $product_id = $_POST['update'];
                    $new_quantity = $_POST['quantity'][$product_id];
                    try{
                        $statement = $dbh->prepare("UPDATE Cart SET quantity = :quantity WHERE product_id = :product_id AND customer_id=:customer_id");
                        $statement->bindParam(":quantity", $new_quantity);
                        $statement->bindParam(":product_id", $product_id);
                        $statement->bindParam(":customer_id", $customer_id);
                        $statement->execute();
                        $_SESSION["quantity_updated"] = true;
                        header("Location: ?shopping_cart=1");
                        exit;
                    }
                    catch (Exception $e){
                        echo "<p style='color: red;'>Error updating quantity: " . htmlspecialchars($e->getMessage()) . "</p>";
                    }
                }
                if (isset($_POST['remove'])){
                    $product_id = $_POST['remove'];
                    try{
                        $statement = $dbh->prepare("DELETE FROM Cart where product_id = :product_id AND customer_id = :customer_id");
                        $statement->bindParam(":product_id", $product_id);
                        $statement->bindParam(":customer_id", $customer_id);
                        $statement->execute();
                        $_SESSION["product_removed"] = true;
                        header("Location: ?shopping_cart=1");
                        exit;
                    }
                    catch (Exception $e){
                        echo "<p style='color: red;'>Error removing product: " . htmlspecialchars($e->getMessage()) . "</p>";
                    }
                }
                if (isset($_POST['checkout'])){
                    try {
                        // Step 1: Set the output variables
                        $dbh->prepare("SET @order_id = NULL;")->execute();
                        $dbh->prepare("SET @out_stock = NULL;")->execute();
                    
                        // Step 2: Prepare the CALL statement
                        $stmt = $dbh->prepare("CALL checkout(:cust_id, @order_id, @out_stock)");
                        $stmt->bindParam(":cust_id", $customer_id, PDO::PARAM_INT);
                        $stmt->execute();

                        $stmt->closeCursor();
                    
                        // Step 3: Retrieve output values
                        $result = $dbh->query("SELECT @order_id AS order_id, @out_stock AS out_stock");
                        $output = $result->fetch(PDO::FETCH_ASSOC);
                    
                        if ($output) {
                            echo "Order ID: " . $output['order_id'] . "<br>";
                            if(isset($output['out_stock']) && $output['out_stock'] != -1){
                                echo "Insufficient quantity of product with ID: " . $output['out_stock'];
                                echo "<br>Please remove this product from your cart to continue";
                            }
                            else if(isset($output['out_stock'])){
                                echo "Your cart is empty";
                            }
                            else{
                                echo "Checkout Successful";
                            }
                        } else {
                            echo "No output returned.";
                        }
                    } catch (PDOException $e) {
                        echo "Error: " . htmlspecialchars($e->getMessage());
                    }
                }
            }
            if(isset($_SESSION["quantity_updated"])){
                echo "<p style='color: green;'>Quantity successfully updated </p>";
                $_SESSION['quantity_updated'] = false;
            }
            if(isset($_SESSION["product_removed"])){
                echo "<p style='color: green;'>Product successfully removed </p>";
                $_SESSION['product_removed'] = false;
            }
            if($_SERVER["REQUEST_METHOD"] == "POST"){
                $customer_id = $_SESSION["id"];
                if(isset($_POST['add_to_cart']) && $customer_id){
                    $prodName = $_POST["add_to_cart"];
                    $quantity = $_POST['quantity'][$prodName];
                    try{
                        $statement = $dbh->prepare("SELECT id FROM Product WHERE Prod_Name = :prod_name");
                        $statement->bindParam(":prod_name", $prodName);
                        $statement->execute();
                        $product = $statement->fetch(PDO::FETCH_ASSOC);
                        if($product){
                            $product_id = $product['id'];

                            //check if the item is already in the cart
                            $cart_statement = $dbh->prepare("SELECT quantity FROM Cart WHERE product_id = :prod_id AND customer_id = :cust_id");
                            $cart_statement->bindParam(":prod_id", $product_id);
                            $cart_statement->bindParam(":cust_id", $customer_id);
                            $cart_statement->execute();
                            $cart_product = $cart_statement->fetch(PDO::FETCH_ASSOC);
                            //If it is already in the cart just increment it
                            if($cart_product){
                                $new_quantity = $cart_product['quantity'] + $quantity;
                                $update_statement = $dbh->prepare("UPDATE Cart SET quantity = :quantity WHERE product_id = :product_id AND customer_id = :customer_id");
                                $update_statement->bindParam(":quantity", $new_quantity);
                                $update_statement->bindParam(":product_id", $product_id);
                                $update_statement->bindParam(":customer_id", $customer_id);
                                $update_statement->execute();
                                echo "Added to the cart via update";
                            }
                            //If it's not in the cart, add it
                            else{
                                echo "PRODUCT_ID ".$product_id;
                                $insert_statement = $dbh->prepare("INSERT INTO Cart values(:product_id, :customer_id, :quantity)");
                                $insert_statement->bindParam(":product_id", $product_id);
                                $insert_statement->bindParam(":customer_id", $customer_id);
                                $insert_statement->bindParam(":quantity", $quantity);
                                $insert_statement->execute();
                                echo "Added to the cart via insert";
                            }
                            $_SESSION["added_to_cart"] = true;
                            header("Location: ?category=" . urlencode($_GET["category"]));
                            exit;
                        } else {
                            echo "<p style='color:red;'>Product not found.</p>";
                        }
                        
                    }
                    catch(Exception $e){
                        echo "ERROR adding to cart: " . htmlspecialchars($e->getMessage());
                    }
                }
            }
            if(isset($_GET["change_password"])){
                echo "<div class='boxed'>";
                echo "<h3>Change Password</h3>";
                echo "<form method='POST'>";
                echo "<label>Old Password: </label><input name='old_password'><br>";
                echo "<label>New Password: </label><input name='new_password'><br>";
                echo "<label>New Password: </label><input name='new_password_verify'><br>";
                echo "<button type='submit'>Updated Password</button>";
                echo "</form>";
                echo "</div>";
                if($_SERVER["REQUEST_METHOD"] == "POST"){
                    $old_password = $_POST["old_password"];
                    $new_password = $_POST["new_password"];
                    $new_password_verify = $_POST["new_password_verify"];
                    $hashed_password = hash('sha256', $old_password);
                    $statement = $dbh->prepare("SELECT passwd FROM Customer WHERE id = :id");
                    $statement->bindParam(":id", $_SESSION["id"]);
                    $statement->execute();
                    $actual_password = $statement->fetch(PDO::FETCH_ASSOC);
                    if($hashed_password == $actual_password["passwd"]){
                        if($new_password == $new_password_verify && $new_password != ""){
                            $statement = $dbh->prepare("UPDATE Customer SET passwd = :passwd WHERE id = :id");
                            $hashed_new_password = hash('sha256', $new_password);
                            $statement->bindParam(":passwd", $hashed_new_password);
                            $statement->bindParam(":id", $_SESSION["id"]);
                            $statement->execute();
                            echo "<p style='color:green'>Password Successfully Changed</p>";
                        }
                        else{
                            if($new_password == ""){
                                echo "<p style='color: red'>Password must contain letters or numbers</p>";
                            }
                            else{
                                echo "<p style='color: red'>New Passwords must match</p>";
                            }
                        }
                    }
                    else{
                        echo "<p style='color: red'>Incorrect Password</p>";
                    }
                }
            }
            
            if(isset($_GET["logout"])){
                echo "<div class='boxed'>";
                echo "<h3>Logout</h3>";
                echo "<form method='POST'>";
                echo "You are currently logged in. Would you like to logout?";
                echo "<br><button type='submit' name='logout'>logout</button>";
                echo "</form>";
                echo "</div>";
                if($_SERVER["REQUEST_METHOD"] == "POST"){
                    if(isset($_POST["logout"])){
                        session_destroy();
                        header("Location: login.php");
                        exit();
                    }
                }
            }
            
        ?>
        <form method="GET">
            <br>
            <button type="submit" name="view_order">View Orders</button>
            <button type="submit" name="shopping_cart">Shopping Cart</button>
            <button type="submit" name="change_password">Change Password</button>
            <button type="submit" name="logout">Logout</button>
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
                try{
                    $dbh = connectDB();
                    $statement = $dbh->prepare("SELECT id FROM Customer WHERE username = :username");
                    $statement->bindParam(':username', $_SESSION['username']);
                    $statement->execute();
                    $result = $statement->fetch(PDO::FETCH_ASSOC);
                    $id;
                    if(isset($result["id"])){
                        $id = $result["id"];
                        $_SESSION["id"] = $id;
                    }
                }
                catch(Exception $e){
                    echo "<p>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
                }
                //View Order Button
                if(isset($_GET["view_order"])){
                    try{
                        if(isset($_SESSION["id"])){
                            $id = $_SESSION["id"];
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
                            }else{
                                echo "No Orders Placed";
                            }
                        }
                        else{
                            echo "<p style='color:red'>You must login to see your cart</p>";
                        }
                    }
                    catch(Exception $e){
                        echo "<p>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
                    }
                }
            ?>
            </div>
        </form>
        <?php
            if (isset($_GET['category']) && !isset($_GET["view_order"]) && !isset($_GET["change_password"])) {
                $selectedCategory = $_GET['category'];

                try {
                    $dbh = connectDB();

                    // Fetch images based on selected category
                    $statement = $dbh->prepare("SELECT Prod_Name, Price, image_url FROM Product WHERE Prod_cat = :category");
                    $statement->bindParam(':category', $selectedCategory);
                    $statement->execute();

                    $images = $statement->fetchAll(PDO::FETCH_ASSOC);

                    if (count($images) > 0) {
                        echo "<h2>Category: " . htmlspecialchars($selectedCategory) . "</h2>";
                        echo "<form method='POST'>";
                        echo "<div class='image-gallery'>";
                        foreach ($images as $img) {
                            $prodName = $img["Prod_Name"];
                            $price = $img["Price"];
                            echo "<div style='margin: 10px; text-align: center; width: 200px;'>";
                            echo "<img src='" . htmlspecialchars($img['image_url']) . "' alt='Image' style='width:200px;height:auto;margin:10px;'>";
                            echo "<strong>$prodName</strong><br>";
                            echo "<span>\$" . number_format($price, 2) . "</span>";
                            echo "<input type='number' name='quantity[".$prodName."]' value=0 min=0>";
                            echo "<button type='submit' name='add_to_cart' value='$prodName'>Add to Cart</button>";
                            echo "</div>";
                        }
                        echo "</div>";
                        echo "</form>";
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
</body>
</html>