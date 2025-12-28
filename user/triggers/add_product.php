<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "cs306";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$message = "";

function getTurkeyManufacturerId($conn) {
    $name = "Turkey";

    $stmt = $conn->prepare(
        "SELECT manufacturer_id FROM manufacturer WHERE manufacturer = ? LIMIT 1"
    );
    $stmt->bind_param("s", $name);
    $stmt->execute();
    $res = $stmt->get_result();
    $row = $res ? $res->fetch_assoc() : null;
    $stmt->close();

    if ($row) {
        return (int)$row["manufacturer_id"];
    }

    $stmt = $conn->prepare(
        "INSERT INTO manufacturer (manufacturer) VALUES (?)"
    );
    $stmt->bind_param("s", $name);
    $stmt->execute();
    $id = (int)$conn->insert_id;
    $stmt->close();

    return $id;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $case = $_POST["case"] ?? "";

    $product_name = "Trigger Test Product";
    $product_type = "tshirt";
    $stock = 10;

    $gender = "unisex";
    $color = "black";
    $size = "m";

    if ($case === "positive") {
        $product_name = "Case 1 - Positive Price";
        $price = 150;
    } elseif ($case === "negative") {
        $product_name = "Case 2 - Negative Price";
        $price = -150;
    } else {
        $message = "Invalid case selection.";
        $price = null;
    }

    if ($price !== null) {
        try {
            $manufacturer_id = getTurkeyManufacturerId($conn);

            $stmt = $conn->prepare(
                "INSERT INTO product
                (product_name, product_type, price, stock, manufacturer_id, gender_category, color, size)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
            );
            $stmt->bind_param(
                "ssiiisss",
                $product_name,
                $product_type,
                $price,
                $stock,
                $manufacturer_id,
                $gender,
                $color,
                $size
            );
            $stmt->execute();
            $new_id = (int)$conn->insert_id;
            $stmt->close();

            $message = "Product inserted successfully. product_id = " . $new_id ;
        } catch (Throwable $e) {
            $message = "Insert failed : " . $e->getMessage();
        }
    }
}

$last_products = [];
$res = $conn->query(
    "SELECT p.product_id, p.product_name, p.price, p.stock, m.manufacturer
     FROM product p
     JOIN manufacturer m ON p.manufacturer_id = m.manufacturer_id
     ORDER BY p.product_id DESC
     LIMIT 5"
);
if ($res) {
    while ($row = $res->fetch_assoc()) {
        $last_products[] = $row;
    }
    $res->free();
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Trigger 3 Test</title>
</head>
<body>

<div style="border:1px solid #000; padding:10px; width:900px; max-width:95%;">
    <b>Trigger 3 (by Sinan Altıntuğ):</b> Prevents inserting products with negative prices and displays an error message.
    <br><br>
    Click a case to test quickly:
    <br><br>

    <form method="post">
        <button type="submit" name="case" value="positive">Case 1 - positive priced product</button>
        <button type="submit" name="case" value="negative">Case 2 - negative priced product</button>
    </form>

    <?php if ($message !== ""): ?>
        <br><br>
        <span style="color:black; font-weight:bold;"><?php echo htmlspecialchars($message); ?></span>
    <?php endif; ?>
</div>

<br>
<a href="../index.php">Go to homepage</a>

<h2>Last 5 Products</h2>

<table border="1" cellpadding="10" cellspacing="0" style="width:900px; max-width:95%;">
    <tr>
        <th>ID</th>
        <th>Product Name</th>
        <th>Price</th>
        <th>Stock</th>
        <th>Manufacturer</th>
    </tr>
    <?php if (count($last_products) > 0): ?>
        <?php foreach ($last_products as $p): ?>
            <tr>
                <td><?php echo (int)$p["product_id"]; ?></td>
                <td><?php echo htmlspecialchars($p["product_name"]); ?></td>
                <td><?php echo htmlspecialchars($p["price"]); ?></td>
                <td><?php echo (int)$p["stock"]; ?></td>
                <td><?php echo htmlspecialchars($p["manufacturer"]); ?></td>
            </tr>
        <?php endforeach; ?>
    <?php else: ?>
        <tr>
            <td colspan="5">No records found.</td>
        </tr>
    <?php endif; ?>
</table>

</body>
</html>
<?php
$conn->close();
?>