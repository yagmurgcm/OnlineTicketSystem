<?php
// Vendor yolu güncellendi
require '../../vendor/autoload.php'; 

try {
    $manager = new MongoDB\Driver\Manager("mongodb://localhost:27017");
} catch (MongoDB\Driver\Exception\Exception $e) {
    die("MongoDB Bağlantı Hatası: " . $e->getMessage());
}

$message_status = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $body = $_POST['body'];
    $created_at = date("Y-m-d H:i:s");

    $bulk = new MongoDB\Driver\BulkWrite;
    $document = [
        'username'   => $username,
        'body'       => $body,
        'status'     => true,
        'created_at' => $created_at,
        'comments'   => []
    ];

    $bulk->insert($document);

    try {
        $manager->executeBulkWrite('cs306.tickets', $bulk);
        // Başarılı olunca linki göster
        $message_status = "<p style='color: green;'>✅ Ticket created! <a href='tickets.php'>Go back to list</a></p>";
    } catch (MongoDB\Driver\Exception\Exception $e) {
        $message_status = "<p style='color: red;'>🛑 Error: " . $e->getMessage() . "</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Create a Ticket</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; max-width: 600px; }
        .nav-links a { margin-right: 15px; color: blue; }
        label { display: block; margin-top: 15px; font-weight: bold; }
        input, textarea { width: 100%; padding: 8px; margin-top: 5px; }
        button { margin-top: 15px; padding: 10px 20px; cursor: pointer; }
    </style>
</head>
<body>

    <div class="nav-links">
        <a href="tickets.php">View Tickets</a>
        <a href="../index.php">Home</a> </div>

    <h3>Create a Ticket</h3>
    
    <?php echo $message_status; ?>

    <form method="POST">
        <label>Username</label>
        <input type="text" name="username" required placeholder="celebrimbor_fan">

        <label>Body</label>
        <textarea name="body" rows="5" required placeholder="Please help!"></textarea>

        <button type="submit">Create Ticket</button>
    </form>

</body>
</html>