<?php
$triggers = [
    [
        "name" => "check_price_before_insert",
        "desc" => "Blocks inserting product with negative price.",
        "file" => "check_price_before_insert.php"
    ],
    [
        "name" => "after_orderdetail_insert_stock",
        "desc" => "After inserting orderdetail, decreases product stock.",
        "file" => "after_orderdetail_insert_stock.php"
    ],
    [
        "name" => "after_orderdetail_update_total",
        "desc" => "After inserting orderdetail, updates order total_amount.",
        "file" => "after_orderdetail_update_total.php"
    ]
];
?>
<!doctype html>
<html>
<head><meta charset="utf-8"><title>Triggers</title></head>
<body>
<h2>Triggers</h2>
<ul>
<?php foreach ($triggers as $t): ?>
    <li>
        <a href="<?php echo htmlspecialchars($t["file"]); ?>"><?php echo htmlspecialchars($t["name"]); ?></a>
        — <?php echo htmlspecialchars($t["desc"]); ?>
    </li>
<?php endforeach; ?>
</ul>
<a href="../index.php">Back to User Home</a>
</body>
</html>
