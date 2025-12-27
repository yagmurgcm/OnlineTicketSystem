<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require "mongo.php";


if (!isset($_GET['id'])) {
    die("Ticket ID missing.");
}

$id = $_GET['id'];

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

$currentStatus = ($ticket->status === true) ? 'active' : 'resolved';


if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $reply     = trim($_POST['reply'] ?? '');
    $newStatus = ($_POST['status'] === 'resolved') ? false : true;

    $update = [
        '$set' => [
            'status' => $newStatus
        ]
    ];

    if ($reply !== '') {
        $update['$push'] = [
            'comments' => [
                'by'   => 'admin',
                'text' => $reply,
                'date' => date('Y-m-d H:i')
            ]
        ];
    }

    $bulk = new MongoDB\Driver\BulkWrite;
    $bulk->update(['_id' => $objectId], $update);
    $manager->executeBulkWrite("$dbName.$collection", $bulk);

    header("Location: ticket_details.php?id=$id");
    exit;
}
?>

<h1>Ticket Details</h1>

<b>User:</b> <?= htmlspecialchars($ticket->username) ?><br>
<b>Date:</b> <?= htmlspecialchars($ticket->created_at) ?><br>
<b>Status:</b>
<strong><?= strtoupper($currentStatus) ?></strong>

<hr>

<b>Initial Message:</b>
<p><?= nl2br(htmlspecialchars($ticket->message)) ?></p>

<hr>

<h3>Conversation</h3>

<?php if (!empty($ticket->comments) && is_array($ticket->comments)): ?>
    <?php foreach ($ticket->comments as $c): ?>
        <p>
            <b><?= strtoupper(htmlspecialchars($c->by)) ?>:</b>
            <?= htmlspecialchars($c->text) ?><br>
            <small><?= htmlspecialchars($c->date) ?></small>
        </p>
        <hr>
    <?php endforeach; ?>
<?php else: ?>
    <p>No replies yet.</p>
<?php endif; ?>

<h3>Admin Actions</h3>

<form method="post">
    <label>Reply:</label><br>
    <textarea name="reply" rows="4" cols="60"></textarea><br><br>

    <label>Status:</label>
    <select name="status">
        <option value="active" <?= ($currentStatus === 'active') ? 'selected' : '' ?>>
            Active
        </option>
        <option value="resolved" <?= ($currentStatus === 'resolved') ? 'selected' : '' ?>>
            Resolved
        </option>
    </select>
    <br><br>

    <button type="submit">Submit Reply</button>
</form>

<br>
<a href="index.php"> Back to Admin Panel</a>
