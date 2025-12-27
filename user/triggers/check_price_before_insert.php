<?php
// Veritabanı Bağlantısı
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "306project"; // Senin veritabanı ismin

$conn = new mysqli($servername, $username, $password, $dbname);

// Bağlantı kontrolü
if ($conn->connect_error) {
    die("Bağlantı hatası: " . $conn->connect_error);
}

$message = "";
$message_type = ""; // success veya error rengi için

// Form gönderildi mi?
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Form verilerini al
    $p_name = $_POST['product_name'];
    $p_type = $_POST['product_type'];
    $p_price = $_POST['price']; // Trigger burayı kontrol edecek!
    $p_stock = $_POST['stock'];
    $m_id = $_POST['manufacturer_id']; // Foreign Key (Tablonda 1,2,3.. gibi var olan bir ID girmelisin)
    
    // Varsayılan değerler (tablonda default var ama formdan da gönderebiliriz)
    $gender = "unisex"; 
    $color = "black";
    $size = "m";

    // INSERT Sorgusu
    $sql = "INSERT INTO product (product_name, product_type, price, stock, manufacturer_id, gender_category, color, size) 
            VALUES ('$p_name', '$p_type', '$p_price', '$p_stock', '$m_id', '$gender', '$color', '$size')";

    // Sorguyu çalıştır ve Hata Kontrolü Yap
    if ($conn->query($sql) === TRUE) {
        $message = "Ürün başarıyla eklendi! (Trigger izin verdi)";
        $message_type = "success";
    } else {
        // BURASI ÇOK ÖNEMLİ: Trigger hatasını burada yakalıyoruz
        // MySQL trigger'ı bir hata mesajı döndürür, biz de onu ekrana basarız.
        $message = "Veritabanı Hatası: " . $conn->error;
        $message_type = "error";
    }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Ürün Ekle (Trigger Testi)</title>
    <style>
        body { font-family: sans-serif; padding: 20px; }
        .form-container { max-width: 500px; margin: 0 auto; padding: 20px; border: 1px solid #ccc; background: #f9f9f9; }
        input { width: 100%; padding: 8px; margin: 5px 0 15px 0; box-sizing: border-box; }
        .success { color: green; font-weight: bold; padding: 10px; border: 1px solid green; background: #eaffea; }
        .error { color: red; font-weight: bold; padding: 10px; border: 1px solid red; background: #ffeaea; }
        table { width: 100%; border-collapse: collapse; margin-top: 30px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
    </style>
</head>
<body>

<div class="form-container">
    <h2>Yeni Ürün Ekle</h2>
    <p>Trigger Testi: Fiyat alanına negatif sayı girerek hatayı gözlemleyin.</p>

    <?php if ($message != ""): ?>
        <div class="<?php echo $message_type; ?>">
            <?php echo $message; ?>
        </div>
    <?php endif; ?>

    <form method="post" action="">
        <label>Ürün Adı:</label>
        <input type="text" name="product_name" required value="Test Ürün">

        <label>Ürün Tipi (tshirt, legging vb.):</label>
        <input type="text" name="product_type" required value="tshirt">

        <label>Fiyat (Trigger Burayı Kontrol Eder):</label>
        <input type="number" name="price" required placeholder="Negatif değer girin...">

        <label>Stok Adedi:</label>
        <input type="number" name="stock" required value="10">

        <label>Üretici ID (Mevcut ID'lerden biri olmalı, örn: 1):</label>
        <input type="number" name="manufacturer_id" required value="1">

        <input type="submit" value="Ürünü Ekle" style="background-color: #4CAF50; color: white; cursor: pointer;">
    </form>
</div>

<h3>Son Eklenen 5 Ürün</h3>
<table>
    <tr>
        <th>ID</th>
        <th>Ürün Adı</th>
        <th>Fiyat</th>
        <th>Stok</th>
    </tr>
    <?php
    $result = $conn->query("SELECT * FROM product ORDER BY product_id DESC LIMIT 5");
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . $row['product_id'] . "</td>";
            echo "<td>" . $row['product_name'] . "</td>";
            echo "<td>" . $row['price'] . "</td>";
            echo "<td>" . $row['stock'] . "</td>";
            echo "</tr>";
        }
    } else {
        echo "<tr><td colspan='4'>Kayıt bulunamadı</td></tr>";
    }
    ?>
</table>

</body>
</html>