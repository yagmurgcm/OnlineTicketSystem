<?php
// ================= DB CONNECTION =================
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "cs306"; 

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// ================= VARIABLES =================
$beforeTotal = null;
$afterTotal = null;
$successMsg = "";
$selectedOrderId = $_POST['order_id'] ?? null;

// ================= PROCESS FORM =================
if (isset($_POST['add_product'])) {
    $order_id   = $_POST['order_id'];
    $product_id = $_POST['product_id'];
    $quantity   = $_POST['quantity'];

    // 1. İşlemden Önceki Tutarı Çek
    // 'order' kelimesi rezerve olduğu için backtick (`) kullanıyoruz
    $res = $conn->query("SELECT total_amount FROM `order` WHERE order_id = $order_id");
    $row = $res->fetch_assoc();
    $beforeTotal = $row['total_amount'];

    // 2. Seçilen Ürünün Fiyatını Bul
    $p = $conn->query("SELECT price FROM product WHERE product_id = $product_id");
    $p_row = $p->fetch_assoc();
    $price = $p_row['price'];

    // Subtotal Hesapla
    $subtotal = $price * $quantity;

    // 3. Ekleme Yap (Burada TRIGGER devreye girecek)
    $stmt = $conn->prepare("INSERT INTO orderdetail (order_id, product_id, quantity, subtotal) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("iiid", $order_id, $product_id, $quantity, $subtotal);
    
    if ($stmt->execute()) {
        // 4. İşlemden Sonraki Tutarı Çek (Trigger çalıştı mı kontrolü)
        $res2 = $conn->query("SELECT total_amount FROM `order` WHERE order_id = $order_id");
        $row2 = $res2->fetch_assoc();
        $afterTotal = $row2['total_amount'];

        $successMsg = " Product added successfully. Trigger executed!";
    } else {
        $successMsg = "Error: " . $conn->error;
    }
}

// ================= DATA FOR DROPDOWNS =================
$orderResult = $conn->query("SELECT order_id, total_amount FROM `order` ORDER BY order_id");
$productResult = $conn->query("SELECT product_id, product_name, price FROM product");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Trigger Demo</title>
</head>
<body>

    <h3>Trigger Name: after_orderdetail_update_total</h3>
    <p>
        This page demonstrates the automation of data consistency.
        When a new item is INSERTED into the orderdetail table, 
        the trigger automatically recalculates and updates the total_amount field in the order table.
    </p>
    
    <a href="../index.php">Go to Homepage</a>
    <hr>

    <form method="POST">
        
        <label><b>Select Order to Test:</b></label><br>
        <select name="order_id" required>
            <option value="">-- Choose an Order --</option>
            <?php 
            if ($orderResult->num_rows > 0) {
                while($o = $orderResult->fetch_assoc()) {
                    $selected = ($selectedOrderId == $o['order_id']) ? "selected" : "";
                    echo "<option value='" . $o['order_id'] . "' $selected>Order #" . $o['order_id'] . " (Current Total: " . $o['total_amount'] . ")</option>";
                }
            }
            ?>
        </select>
        <br><br>

        <label><b>Select Product:</b></label><br>
        <select name="product_id" required>
            <option value="">-- Choose a Product --</option>
            <?php 
            if ($productResult->num_rows > 0) {
                while($p = $productResult->fetch_assoc()) {
                    echo "<option value='" . $p['product_id'] . "'>" . $p['product_name'] . " - $" . $p['price'] . "</option>";
                }
            }
            ?>
        </select>
        <br><br>

        <label><b>Quantity:</b></label><br>
        <input type="number" name="quantity" value="1" min="1" max="10" required>
        <br><br>

        <button type="submit" name="add_product">Add Product & Test Trigger</button>

    </form>

    <br>
    <hr>

    <?php if ($beforeTotal !== null && $afterTotal !== null): ?>
        <h3>Test Results:</h3>
        <p>
            <b>Order Total (Before):</b> <?php echo $beforeTotal; ?> <br>
            <b>Order Total (After Trigger):</b> <?php echo $afterTotal; ?>
        </p>
        <p style="color: green; font-weight: bold;">
            <?php echo $successMsg; ?>
        </p>
    <?php endif; ?>

</body>
</html>