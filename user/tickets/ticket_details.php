<?php
// user/tickets/ticket_details.php
require '../../vendor/autoload.php';

// MongoDB Bağlantısı
try {
    $manager = new MongoDB\Driver\Manager("mongodb://localhost:27017");
} catch (MongoDB\Driver\Exception\Exception $e) {
    die("MongoDB Bağlantı Hatası: " . $e->getMessage());
}

$id = isset($_GET['id']) ? $_GET['id'] : null;
$message_status = "";

// ID kontrolü
if (!$id) {
    die("Error: Ticket ID is missing.");
}

// ---------------------------------------------------------
// 1. YENİ YORUM EKLEME İŞLEMİ (Form gönderildiyse çalışır)
// ---------------------------------------------------------
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $commentUser = $_POST['comment_user'];
    $commentText = $_POST['comment_text'];
    $createdAt = date("Y-m-d H:i:s");

    // Yorum Objesi (PDF Figure 10 yapısına uygun)
    $newComment = [
        'username'   => $commentUser,
        'comment'    => $commentText,
        'created_at' => $createdAt
    ];

    // MongoDB: Belirli bir dökümanın içine ($push) ekleme yap
    $bulk = new MongoDB\Driver\BulkWrite;
    $bulk->update(
        ['_id' => new MongoDB\BSON\ObjectId($id)], // Hangi bilet?
        ['$push' => ['comments' => $newComment]]   // Neyi ekle?
    );

    try {
        $manager->executeBulkWrite('cs306.tickets', $bulk);
        $message_status = "<p style='color: green;'>✅ Comment added!</p>";
    } catch (MongoDB\Driver\Exception\Exception $e) {
        $message_status = "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
    }
}

// ---------------------------------------------------------
// 2. BİLET BİLGİLERİNİ ÇEKME
// ---------------------------------------------------------
try {
    $filter = ['_id' => new MongoDB\BSON\ObjectId($id)];
    $query = new MongoDB\Driver\Query($filter);
    $cursor = $manager->executeQuery('cs306.tickets', $query);
    $ticket = current($cursor->toArray());

    if (!$ticket) {
        die("Ticket not found.");
    }
} catch (Exception $e) {
    die("Invalid ID format or Database Error.");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Ticket Details</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; max-width: 800px; }
        h2 { border-bottom: 2px solid #ccc; padding-bottom: 10px; }
        .details p { margin: 5px 0; }
        .label { font-weight: bold; }
        
        /* Yorum Kutuları */
        .comments-section { margin-top: 30px; border-top: 1px solid #000; padding-top: 10px; }
        .comment-box { 
            border: 1px solid #999; 
            padding: 10px; 
            margin-bottom: 10px; 
            background-color: #f9f9f9; 
        }
        .comment-meta { font-size: 0.85em; color: #555; margin-bottom: 5px; }

        /* Form */
        input, textarea { width: 100%; padding: 8px; margin-top: 5px; margin-bottom: 10px; border: 1px solid #ccc; }
        button { padding: 10px 15px; cursor: pointer; background: #ddd; border: 1px solid #999; }
        
        a { color: blue; text-decoration: underline; }
    </style>
</head>
<body>

    <h2>Ticket Details</h2>

    <div class="details">
        <p><span class="label">Username:</span> <?php echo htmlspecialchars($ticket->username); ?></p>
        <p><span class="label">Body:</span> <?php echo htmlspecialchars($ticket->body); ?></p>
        <p><span class="label">Status:</span> <?php echo $ticket->status ? 'Active' : 'Resolved'; ?></p>
        <p><span class="label">Created At:</span> <?php echo htmlspecialchars($ticket->created_at); ?></p>
    </div>

    <div class="comments-section">
        <h3>Comments:</h3>

        <?php 
        // Eğer comments dizisi boşsa veya yoksa hata vermesin diye kontrol
        $comments = isset($ticket->comments) ? $ticket->comments : []; 
        
        if (count($comments) == 0): ?>
            <p>No comments yet.</p>
        <?php else: ?>
            <?php foreach ($comments as $c): ?>
                <div class="comment-box">
                    <div class="comment-meta">
                        Created At: <?php echo is_object($c) ? $c->created_at : $c['created_at']; ?> <br>
                        Username: <?php echo is_object($c) ? $c->username : $c['username']; ?>
                    </div>
                    <div>
                        <b>Comment:</b> <?php echo is_object($c) ? $c->comment : $c['comment']; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <div style="margin-top: 20px;">
        <?php echo $message_status; ?>
        
        <form method="POST">
            <input type="text" name="comment_text" placeholder="Add a comment" required>
            <input type="text" name="comment_user" placeholder="Your Username" required>
            <button type="submit">Add Comment</button>
        </form>
    </div>

    <br>
    <a href="index.php">Back to Tickets</a>

</body>
</html>