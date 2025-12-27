<?php
require "mongo.php";

if (!isset($_GET['id'])) {
    echo "Ticket ID not provided.";
    exit;
}

$id = new MongoDB\BSON\ObjectId($_GET['id']);

/* Ticket fetch */
$query = new MongoDB\Driver\Query(['_id' => $id]);
$ticket = current(
    $manager->executeQuery("$dbName.$collection", $query)->toArray()
);

/* Add admin comment */
if (isset($_POST['add_comment'])) {
    $bulk = new MongoDB\Driver\BulkWrite;

    $bulk->update(
        ['_id' => $id],
        ['$push' => ['comments' => "admin: " . $_POST['comment']]]
    );

    $manager->executeBulkWrite("$dbName.$collection", $bulk);
    header("Refresh:0");
}

/* Mark as resolved */
if (isset($_POST['resolve'])) {
    $bulk = new MongoDB\Driver\BulkWrite;

    $bulk->update(
        ['_id' => $id],
        ['$set' => ['status' => false]]
    );

    $manager->executeBulkWrite("$dbName.$collection", $bulk);
    header("Location: index.php");
}
?>

<h2>Ticket Details</h2>

<b>User:</b> <?= $ticket->username ?><br>
<b>Created At:</b> <?= $ticket->created_at ?><br>
<b>Status:</b> <?= $ticket->status ? "Active" : "Resolved" ?><br>

<p><b>Message:</b><br><?= $ticket->message ?></p>

<h3>Comments</h3>

<?php
if (count($ticket->comments) === 0) {
    echo "<p>No comments yet.</p>";
} else {
    foreach ($ticket->comments as $c) {
        echo "<div>- $c</div>";
    }
}
?>

<form method="post">
    <textarea name="comment" rows="4" cols="40" required></textarea><br><br>
    <button type="submit" name="add_comment">Add Comment</button>
</form>

<br>

<form method="post">
    <button type="submit" name="resolve">Mark as Resolved</button>
</form>

<br>
<a href="index.php">← Back to Admin Panel</a>
