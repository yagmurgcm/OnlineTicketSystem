<?php

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "cs306"; 

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    $selected_product_id = (int)$_POST['product_id'];
    
    $qty_to_buy = 0;
    if (isset($_POST['case1'])) {
        $qty_to_buy = 1; 
    } elseif (isset($_POST['case2'])) {
        $qty_to_buy = 10; 
    }

    if ($qty_to_buy > 0 && $selected_product_id > 0) {
        
        $sql_prod = "SELECT price, stock FROM product WHERE product_id = $selected_product_id";
        $res_prod = $conn->query($sql_prod);
        
        if ($res_prod && $row_prod = $res_prod->fetch_assoc()) {
            $unit_price = $row_prod['price']; 
            $old_stock  = $row_prod['stock'];

            $calculated_subtotal = $unit_price * $qty_to_buy;

            try {
          
                $sql = "INSERT INTO orderdetail (order_id, product_id, quantity, subtotal) 
                        VALUES (1, $selected_product_id, $qty_to_buy, $calculated_subtotal)";
                
                if ($conn->query($sql) === TRUE) {
                   
                    $sql_new = "SELECT stock FROM product WHERE product_id = $selected_product_id";
                    $res_new = $conn->query($sql_new);
                    $row_new = $res_new->fetch_assoc();
                    $new_stock = $row_new['stock'];

                    $message = "<p><strong>SUCCESS:</strong> Product ID $selected_product_id purchased.<br>" .
                               "Unit Price: $unit_price <br>" .
                               "Quantity: $qty_to_buy <br>" .
                               "<strong>Calculated Subtotal: $calculated_subtotal </strong> (Corrected!)<br>" .
                               "Old Stock: $old_stock <br>" .
                               "New Stock: $new_stock (Trigger Worked!)</p><hr>";
                }
            } catch (Exception $e) {
                $message = "<p><strong>ERROR:</strong> " . $e->getMessage() . "</p><hr>";
            }
        } else {
            $message = "<p>Error: Product not found or no price set.</p><hr>";
        }
    }
}

$sql_products = "SELECT product_id, product_name, stock, price FROM product"; 
$result = $conn->query($sql_products);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Trigger Test</title>
</head>
<body>

    <h3>Trigger: Auto-Update Stock</h3>
    <p>
        This page demonstrates the automated inventory management trigger. When a new order item is recorded, the database automatically deducts the purchased quantity from the product's available stock to ensure real-time data consistency.
    </p>

    <?php echo $message; ?>

    <form method="POST">
        <label><b>Select Product:</b></label><br>
        <select name="product_id">
            <?php 
            if ($result && $result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    $p_name = isset($row['product_name']) ? $row['product_name'] : "Product " . $row['product_id'];
                    $p_price = isset($row['price']) ? $row['price'] : "0";
                    
                    echo "<option value='" . $row['product_id'] . "'>" . 
                         $p_name . " (Stock: " . $row['stock'] . " - Price: " . $p_price . ")" . 
                         "</option>";
                }
            } else {
                echo "<option value='0'>No products found</option>";
            }
            ?>
        </select>
        
        <br><br>

        <button type="submit" name="case1">Case 1: Buy 1 Unit</button>
        <button type="submit" name="case2">Case 2: Buy 10 Units</button>
    </form>

    <br>
    <a href="../index.php">Go to homepage</a>

</body>
</html>