<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require "mongo.php";

/*
|---------------------------------------
| Status filtresi
|---------------------------------------
| ?status=active   → status = true
| ?status=resolved → status = false
| default: active
*/
$status = $_GET['status'] ?? 'active';

if (!in_array($status, ['active', 'resolved'])) {
    $status = 'active';
}

// STRING → BOOLEAN MAP
$statusBool = ($status === 'active');

$query = new MongoDB\Driver\Query(
    ['status' => $statusBool],
    ['sort' => ['created_at' => -1]]
);

$tickets = $manager->executeQuery("$dbName.$collection", $query);
?>

<h1>Admin Panel – <?= strtoupper($status) ?> Tickets</h1>

<!-- STATUS BUTTONS -->
<div style="margin-bottom:15px;">
    <a href="index.php?status=active">
        <button <?= ($status === 'active') ? 'disabled' : '' ?>>
            Active Tickets
        </button>
    </a>

    <a href="index.php?status=resolved">
        <button <?= ($status === 'resolved') ? 'disabled' : '' ?>>
            Inactive Tickets
        </button>
    </a>
</div>

<?php
$found = false;
foreach ($tickets as $ticket):
    $found = true;
?>
    <div style="border:1px solid black; padding:10px; margin:10px;">
        <b>User:</b> <?= htmlspecialchars($ticket->username) ?><br>
        <b>Date:</b> <?= htmlspecialchars($ticket->created_at) ?><br>
        <b>Status:</b>
        <?= $ticket->status ? 'ACTIVE' : 'RESOLVED' ?><br>

        <a href="ticket_details.php?id=<?= (string)$ticket->_id ?>">
            View Details
        </a>
    </div>
<?php endforeach; ?>

<?php if (!$found): ?>
    <p>No <?= htmlspecialchars($status) ?> tickets.</p>
<?php endif; ?>

<br>
<a href="../user/index.php">⬅ Back to User Home</a>
