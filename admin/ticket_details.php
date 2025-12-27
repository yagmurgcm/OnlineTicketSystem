<?php
// admin/ticket_details.php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require "mongo.php";

if (!isset($_GET['id'])) {
    die("Ticket ID missing.");
}

$id = $_GET['id'];

// ID format kontrolü
if (!preg_match('/^[a-f\d]{24}$/i', $id)) {
    die("Invalid Ticket ID.");
}

$objectId = new MongoDB\BSON\ObjectId($id);

$query  = new MongoDB\Driver\Query(['_id' => $objectId]);
$result = $manager->executeQuery("$dbName.$collection", $query);
$ticket = current($result->toArray());

if (!$ticket) {
    die("Ticket not found.");
}

// Status true/false kontrolü
$currentStatus = ($ticket->status === true) ? 'active' : 'resolved';

// ---------------------------------------------------------
// YENİ CEVAP EKLEME (POST)
// ---------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $reply     = trim($_POST['reply'] ?? '');
    $newStatus = ($_POST['status'] === 'resolved') ? false : true;

    $update = [
        '$set' => [
            'status' => $newStatus
        ]
    ];

    if ($reply !== '') {
        // DÜZELTME: Veritabanı şemana uygun isimler kullanıyoruz
        // by -> username, text -> comment, date -> created_at
        $update['$push'] = [
            'comments' => [
                'username'   => 'ADMIN', 
                'comment'    => $reply,
                'created_at' => date('Y-m-d H:i:s')
            ]
        ];
    }

    $bulk = new MongoDB\Driver\BulkWrite;
    $bulk->update(['_id' => $objectId], $update);
    $manager->executeBulkWrite("$dbName.$collection", $bulk);

    // Sayfayı yenile
    header("Location: ticket_details.php?id=$id");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin - Ticket Details</title>
    <style>
        body { font-family: sans-serif; padding: 20px; }
        .box { border: 1px solid #ccc; padding: 10px; margin-bottom: 10px; background: #f9f9f9; }
        .admin-reply { background-color: #e3f2fd; border-color: #2196f3; }
    </style>
</head>
<body>

<h1>Ticket Details</h1>

<div>
    <b>User:</b> <?= htmlspecialchars($ticket->username) ?><br>
    <b>Date:</b> <?= htmlspecialchars($ticket->created_at) ?><br>
    <b>Status:</b> 
    <strong style="color: <?= $currentStatus == 'active' ? 'green' : 'red'; ?>">
        <?= strtoupper($currentStatus) ?>
    </strong>
</div>

<hr>

<h3>Initial Message:</h3>
<div class="box">
    <p><?= nl2br(htmlspecialchars($ticket->body)) ?></p>
</div>

<hr>

<h3>Conversation</h3>

<?php if (!empty($ticket->comments)): ?>
    <?php foreach ($ticket->comments as $c): ?>
        <?php 
            // MongoDB bazen object bazen array döndürebilir, garantiye alalım.
            // Ayrıca isimleri düzelttik: by->username, text->comment, date->created_at
            $c_user = is_object($c) ? $c->username : $c['username'];
            $c_text = is_object($c) ? $c->comment : $c['comment'];
            $c_date = is_object($c) ? $c->created_at : $c['created_at'];
            
            // Admin yorumlarını renklendirelim
            $is_admin = ($c_user === 'ADMIN' || $c_user === 'admin');
            $class = $is_admin ? 'admin-reply' : '';
        ?>
        <div class="box <?= $class ?>">
            <b><?= strtoupper(htmlspecialchars($c_user)) ?>:</b> <br>
            <?= nl2br(htmlspecialchars($c_text)) ?><br>
            <small style="color:gray"><?= htmlspecialchars($c_date) ?></small>
        </div>
    <?php endforeach; ?>
<?php else: ?>
    <p>No replies yet.</p>
<?php endif; ?>

<hr>

<h3>Admin Actions</h3>

<form method="post">
    <label><b>Reply:</b></label><br>
    <textarea name="reply" rows="4" cols="60" placeholder="Type your response here..."></textarea><br><br>

    <label><b>Set Status:</b></label>
    <select name="status">
        <option value="active" <?= ($currentStatus === 'active') ? 'selected' : '' ?>>Active (Open)</option>
        <option value="resolved" <?= ($currentStatus === 'resolved') ? 'selected' : '' ?>>Resolved (Close)</option>
    </select>
    <br><br>

    <button type="submit" style="padding:10px 20px; cursor:pointer;">Submit Update</button>
</form>

<br><br>
<a href="index.php">⬅ Back to Admin Panel</a>

</body>
</html>