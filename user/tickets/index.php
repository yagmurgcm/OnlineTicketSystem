<?php
require '../../vendor/autoload.php';

// DÖKÜMAN KURALI (Sayfa 12): MongoDB Driver Manager kullanılmalı 
try {
    $manager = new MongoDB\Driver\Manager("mongodb://localhost:27017");
} catch (MongoDB\Driver\Exception\Exception $e) {
    die("MongoDB Bağlantı Hatası: " . $e->getMessage());
}

// 1. Adım: Dropdown için Kullanıcıları Çek
// Döküman Kuralı: Sadece aktif bileti olan kullanıcılar listelenmeli [cite: 80]
$command = new MongoDB\Driver\Command([
    'distinct' => 'tickets',
    'key' => 'username',     // Döküman: username [cite: 201]
    'query' => ['status' => true]
]);

try {
    $cursor = $manager->executeCommand('cs306', $command); 
    $usernames = current($cursor->toArray())->values;
} catch(Exception $e) {
    $usernames = []; 
}

$selectedUser = isset($_GET['username']) ? $_GET['username'] : '';
$tickets = [];

if ($selectedUser) {
    $filter = ['username' => $selectedUser, 'status' => true];
    $query = new MongoDB\Driver\Query($filter);
    

    $cursorResult = $manager->executeQuery('cs306.tickets', $query);
    $tickets = $cursorResult->toArray(); 
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Support Tickets</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; max-width: 800px; }
        .top-links { margin-bottom: 20px; }
        a { color: blue; text-decoration: underline; }
        .ticket-box { border: 1px solid #000; padding: 10px; margin-bottom: 10px; }
        select { padding: 5px; }
        button { padding: 5px 10px; cursor: pointer; }
    </style>
</head>
<body>

    <div class="top-links">
        <a href="../index.php">⬅️ Back to Home</a>
    </div>

    <h3>Support Tickets (Active Only)</h3>

    <form method="GET" action="">
        <select name="username">
            <option value="">Select User</option>
            <?php foreach ($usernames as $u): ?>
                <option value="<?php echo htmlspecialchars($u); ?>" <?php if($selectedUser == $u) echo 'selected'; ?>>
                    <?php echo htmlspecialchars($u); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <button type="submit">Select</button>
    </form>
    
    <br>

    <a href="create_ticket.php"><b>+ Create a New Ticket</b></a>

    <hr>

    <h3>Results:</h3>

    <?php if ($selectedUser && count($tickets) == 0): ?>
        <p>No active tickets found for this user.</p>
    <?php endif; ?>

    <?php foreach ($tickets as $ticket): ?>
        <div class="ticket-box">
            <div><b>Status:</b> <?php echo $ticket->status ? 'Active' : 'Resolved'; ?></div>
            <div><b>Body:</b> <?php echo htmlspecialchars($ticket->body); ?></div>
            <div><b>Created At:</b> <?php echo htmlspecialchars($ticket->created_at); ?></div>
            <div><b>Username:</b> <?php echo htmlspecialchars($ticket->username); ?></div>
            
            <a href="ticket_details.php?id=<?php echo $ticket->_id; ?>">View Details</a>
        </div>
    <?php endforeach; ?>

</body>
</html>