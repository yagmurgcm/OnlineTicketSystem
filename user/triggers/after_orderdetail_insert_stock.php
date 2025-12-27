<?php
// Veritabanı Bağlantısı
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "cs306"; // Veritabanı ismin

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$message = "";

// POST İŞLEMİ (Butonlara basılınca)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Formdan gelen verileri al
    $selected_product_id = (int)$_POST['product_id'];
    
    $qty_to_buy = 0;
    if (isset($_POST['case1'])) {
        $qty_to_buy = 1; // Case 1
    } elseif (isset($_POST['case2'])) {
        $qty_to_buy = 10; // Case 2
    }

    if ($qty_to_buy > 0 && $selected_product_id > 0) {
        
        // Önce seçilen ürünün eski stoğunu öğren (Raporlamak için)
        $sql_old = "SELECT stock FROM product WHERE product_id = $selected_product_id";
        $res_old = $conn->query($sql_old);
        $row_old = $res_old->fetch_assoc();
        $old_stock = $row_old['stock'];

        try {
            // INSERT işlemi (Trigger burada devreye girer)
            // subtotal sütunu senin tablonda olduğu için ekledim
            $sql = "INSERT INTO orderdetail (order_id, product_id, quantity, subtotal) 
                    VALUES (1, $selected_product_id, $qty_to_buy, 500)";
            
            if ($conn->query($sql) === TRUE) {
                // İşlem başarılı, yeni stoğu çek
                $sql_new = "SELECT stock FROM product WHERE product_id = $selected_product_id";
                $res_new = $conn->query($sql_new);
                $row_new = $res_new->fetch_assoc();
                $new_stock = $row_new['stock'];

                // Basit metin mesajı
                $message = "<p><strong>SUCCESS:</strong> Product ID $selected_product_id purchased.<br>" .
                           "Ordered: $qty_to_buy items.<br>" .
                           "Old Stock: $old_stock <br>" .
                           "New Stock: $new_stock (Trigger Worked!)</p><hr>";
            }
        } catch (Exception $e) {
            $message = "<p><strong>ERROR:</strong> " . $e->getMessage() . "</p><hr>";
        }
    }
}

// DROPDOWN İÇİN ÜRÜNLERİ ÇEK
// Tüm ürünleri listeliyoruz ki istediğini seçebilesin
$product_list = [];
$sql_products = "SELECT product_id, product_name, stock FROM product"; 
// NOT: Eğer tablonun adı 'name' ise 'product_name' kısmını 'name' yap.
// Genelde product tablosunda 'name', 'title' veya 'product_name' olur.
// Hata alırsan burayı kontrol et.

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
    <p>Select a product and simulate a purchase to test the trigger.</p>
    <hr>

    <?php echo $message; ?>

    <form method="POST">
        <label><b>Select Product:</b></label><br>
        <select name="product_id">
            <?php 
            if ($result && $result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    // Eğer tablonuzda isim sütunu 'name' ise aşağıyı $row['name'] yapın
                    // Ben garanti olsun diye ID ve Stock yazdırıyorum.
                    $p_name = isset($row['product_name']) ? $row['product_name'] : "Product " . $row['product_id'];
                    
                    echo "<option value='" . $row['product_id'] . "'>" . 
                         $p_name . " (Stock: " . $row['stock'] . ")" . 
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