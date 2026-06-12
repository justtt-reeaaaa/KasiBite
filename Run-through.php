<?php
include("config.php");

$sql = "SELECT * FROM Products";
$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>KasiBite - Home</title>
    <style>
        body {
            font-family: Arial;
        }
        .product {
            border: 1px solid #ccc;
            padding: 10px;
            margin: 10px;
            width: 200px;
            display: inline-block;
        }
    </style>
</head>
<body>

<h1>Welcome to KasiBite 🍔</h1>

<?php
if (mysqli_num_rows($result) > 0) {
    while($row = mysqli_fetch_assoc($result)) {
        echo "<div class='product'>";
        echo "<h3>" . $row['name'] . "</h3>";
        echo "<p>" . $row['description'] . "</p>";
        echo "<p>Price: R" . $row['price'] . "</p>";
        echo "</div>";
    }
} else {
    echo "No products available";
}
?>

</body>
</html>