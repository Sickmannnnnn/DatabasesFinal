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
        <form action="search.php" method="GET">
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

                    foreach ($categories as $category) {
                        echo "<option value=\"" . htmlspecialchars($category['category_name']) . "\">" . htmlspecialchars($category['category_name']) . "</option>";
                    }
                } catch (PDOException $e) {
                    echo "<option value=\"\">Error loading categories</option>";
                }
                ?>
            </select>
            <div class="search">
                <button type="submit">Search</button>
            </div>
        </form>
    </div>
</body>
</html>