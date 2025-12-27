<?php
// ================= DB CONNECTION =================
$conn = new mysqli("localhost", "root", "", "cs306");
if ($conn->connect_error) {
    die("DB Connection failed");
}

// ================= GET ORDERS =================
$orderResult = $conn->query("
    SELECT o.order_id, o.total_amount
    FROM `order` o
    ORDER BY o.order_id
");

$selectedOrderId = $_POST['order_id'] ?? null;
$beforeTotal = null;
$afterTotal = null;
$successMsg = "";

// ================= GET PRODUCTS =================
$productResult = $conn->query("
    SELECT product_id, product_name, price
    FROM product
");

// ================= ADD PRODUCT =================
if (isset($_POST['add_product'])) {

    $order_id   = $_POST['order_id'];
    $product_id = $_POST['product_id'];
    $quantity   = $_POST['quantity'];

    // BEFORE
    $res = $conn->query("SELECT total_amount FROM `order` WHERE order_id = $order_id");
    $beforeTotal = $res->fetch_assoc()['total_amount'];

    // product price
    $p = $conn->query("SELECT price FROM product WHERE product_id = $product_id");
    $price = $p->fetch_assoc()['price'];

    $subtotal = $price * $quantity;

    // INSERT orderdetail (TRIGGER WILL FIRE)
    $conn->query("
        INSERT INTO orderdetail (order_id, product_id, quantity, subtotal)
        VALUES ($order_id, $product_id, $quantity, $subtotal)
    ");

    // AFTER
    $res = $conn->query("SELECT total_amount FROM `order` WHERE order_id = $order_id");
    $afterTotal = $res->fetch_assoc()['total_amount'];

    $successMsg = "✔ Product added (trigger executed)";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Trigger Demo</title>
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            background: #f5f6fa;
            padding: 40px;
        }
        .container {
            max-width: 700px;
            margin: auto;
        }
        .card {
            background: #fff;
            border-radius: 8px;
            padding: 25px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
            margin-bottom: 25px;
        }
        h1 { margin-top: 0; }
        h2 {
            border-bottom: 1px solid #eee;
            padding-bottom: 6px;
        }
        label {
            font-weight: bold;
            display: block;
            margin-top: 15px;
        }
        select, button {
            margin-top: 6px;
            padding: 8px;
            width: 100%;
        }
        button {
            background: #4a69bd;
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        button:hover {
            background: #3b55a0;
        }
        .result {
            background: #f1f2f6;
            border-left: 4px solid #4a69bd;
        }
        .success {
            color: #2ecc71;
            font-weight: bold;
            margin-top: 10px;
        }
        .back {
            display: inline-block;
            margin-top: 15px;
        }
    </style>
</head>

<body>
<div class="container">

    <!-- HEADER -->
    <div class="card">
        <h1>Trigger Demo</h1>
        <p><b>after_orderdetail_update_total</b></p>
        <p>
            orderdetail tablosuna <b>INSERT</b> yapıldığında,
            <b>order.total_amount</b> alanı trigger ile otomatik güncellenir.
        </p>

        <form method="post">
            <label>Order</label>
            <select name="order_id" required>
                <?php while ($o = $orderResult->fetch_assoc()): ?>
                    <option value="<?= $o['order_id'] ?>"
                        <?= ($selectedOrderId == $o['order_id']) ? "selected" : "" ?>>
                        Order #<?= $o['order_id'] ?> (<?= number_format($o['total_amount'],2) ?>)
                    </option>
                <?php endwhile; ?>
            </select>
    </div>

    <!-- ADD PRODUCT -->
    <div class="card">
        <h2>Add Product</h2>

        <label>Product</label>
        <select name="product_id" required>
            <?php while ($p = $productResult->fetch_assoc()): ?>
                <option value="<?= $p['product_id'] ?>">
                    <?= $p['product_name'] ?> (<?= number_format($p['price'],2) ?>)
                </option>
            <?php endwhile; ?>
        </select>

        <label>Quantity</label>
        <select name="quantity">
            <?php for ($i=1; $i<=5; $i++): ?>
                <option value="<?= $i ?>"><?= $i ?></option>
            <?php endfor; ?>
        </select>

        <button type="submit" name="add_product">Add Product</button>
        </form>
    </div>

    <!-- RESULT -->
    <?php if ($beforeTotal !== null): ?>
    <div class="card result">
        <p><b>Before:</b> <?= number_format($beforeTotal,2) ?></p>
        <p><b>After:</b> <?= number_format($afterTotal,2) ?></p>
        <div class="success"><?= $successMsg ?></div>
    </div>
    <?php endif; ?>

    <a href="../home.php" class="back">← Back to User Home</a>

</div>
</body>
</html>
