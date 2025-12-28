<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

$conn = new mysqli("localhost", "root", "", "cs306");
if ($conn->connect_error) {
    die("DB connection failed");
}

$orders = $conn->query("SELECT order_id FROM `order`");

$products = $conn->query("SELECT product_id, product_name, price FROM product");

$message = "";
$before = null;
$after = null;

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $order_id = $_POST["order_id"];
    $product_id = $_POST["product_id"];
    $quantity = $_POST["quantity"];

    $res = $conn->query("SELECT total_amount FROM `order` WHERE order_id=$order_id");
    $before = $res->fetch_assoc()["total_amount"];

    $res = $conn->query("SELECT price FROM product WHERE product_id=$product_id");
    $price = $res->fetch_assoc()["price"];
    $subtotal = $price * $quantity;

    $conn->query("CALL sp_add_order_item($order_id, $product_id, $quantity, $subtotal)");

    $res = $conn->query("SELECT total_amount FROM `order` WHERE order_id=$order_id");
    $after = $res->fetch_assoc()["total_amount"];

    $message = "Successfull!!!!!";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Stored Procedure Demo</title>
</head>
<body>

<h1>Stored Procedure Demo</h1>
<h3>sp_add_order_item</h3>

<p>
This stored procedure streamlines the order management process by allowing the direct insertion of products into an active order. It handles the data entry logic and ensures the total order amount is recalculated immediately upon execution.
</p>

<form method="post">
    <label>Order ID</label><br>
    <select name="order_id" required>
        <?php while($o = $orders->fetch_assoc()): ?>
            <option value="<?= $o["order_id"] ?>">
                Order #<?= $o["order_id"] ?>
            </option>
        <?php endwhile; ?>
    </select>

    <br><br>

    <label>Product</label><br>
    <select name="product_id" required>
        <?php while($p = $products->fetch_assoc()): ?>
            <option value="<?= $p["product_id"] ?>">
                <?= $p["product_name"] ?> (<?= $p["price"] ?>)
            </option>
        <?php endwhile; ?>
    </select>

    <br><br>

    <label>Quantity</label><br>
    <select name="quantity">
        <?php for($i=1;$i<=5;$i++): ?>
            <option value="<?= $i ?>"><?= $i ?></option>
        <?php endfor; ?>
    </select>

    <br><br>

    <button type="submit">Add Order Item</button>
</form>

<?php if ($message): ?>
    <p><b><?= $message ?></b></p>
    <p>Before: <?= $before ?></p>
    <p>After: <?= $after ?></p>
<?php endif; ?>

<br>
<a href="../index.php"> Back to User Home</a>

</body>
</html>
