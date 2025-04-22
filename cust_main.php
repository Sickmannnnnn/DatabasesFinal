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
        <button onclick="location.href='employee_login.php'" style="margin-left: 10px;">Employee Login</button>
    </div>
    <div class="content">
        <form method="GET">
            <label for="category">Select Category:</label>
            <select name="category" id="category">
                <?php
                // Include the database connection file
                include 'db.php';

                // Fetch categories from the database
                try {
                    $dbh = connectDB();
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
                if (isset($_GET['category'])) {
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
