<?php
require "mongo.php";

$query = new MongoDB\Driver\Query(
    ['status' => true],
    ['sort' => ['created_at' => -1]]
);

$tickets = $manager->executeQuery("$dbName.$collection", $query);
?>

<h1>Admin Panel – Active Tickets</h1>

<?php
$found = false;
foreach ($tickets as $ticket):
    $found = true;
?>
    <div style="border:1px solid black; padding:10px; margin:10px;">
        <b>User:</b> <?= $ticket->username ?><br>
        <b>Date:</b> <?= $ticket->created_at ?><br>
        <a href="ticket_details.php?id=<?= $ticket->_id ?>">View Details</a>
    </div>
<?php endforeach; ?>

<?php if (!$found): ?>
    <p>No active tickets.</p>
<?php endif; ?>

<br>
<a href="../user/index.php">Back to User Home</a>
