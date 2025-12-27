<?php
session_start();
require_once __DIR__ . "/../db_connect.php";

$msg = "";
$conn = db_connect();

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$categories = [];
try {
    $res = $conn->query("
        SELECT DISTINCT product_type
        FROM product
        WHERE product_type IS NOT NULL AND TRIM(product_type) <> ''
        ORDER BY product_type
    ");
    while ($row = $res->fetch_assoc()) {
        $categories[] = $row["product_type"];
    }
    $res->free();
} catch (mysqli_sql_exception $e) {
    $msg = "Failed to load categories: " . $e->getMessage();
}

$selected_category = $categories[0] ?? "";
$discount_rate = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $selected_category = trim($_POST["category"] ?? "");
    $discount_rate = trim($_POST["discount_rate"] ?? "");

    if ($selected_category === "" || $discount_rate === "") {
        $msg = "Category and discount rate are required.";
    } elseif (!in_array($selected_category, $categories, true)) {
        $msg = "Invalid category selected.";
    } elseif (!is_numeric($discount_rate)) {
        $msg = "Discount rate must be a number.";
    } else {
        $rate_int = (int)$discount_rate;

        try {
            $conn->begin_transaction();

            $stmt = $conn->prepare("CALL sp_apply_discount_by_category(?, ?)");
            $stmt->bind_param("si", $selected_category, $rate_int);
            $stmt->execute();
            $stmt->close();

            $affected = $conn->affected_rows;

            $conn->commit();

            $msg = "Procedure executed successfully. Category = " . htmlspecialchars($selected_category) .
                   ", Discount = " . (int)$rate_int . "%, Rows affected = " . (int)$affected . ".";
        } catch (mysqli_sql_exception $e) {
            $conn->rollback();
            $msg = "Execution failed: " . $e->getMessage();
        }
    }
}
?>
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Stored Procedure - Apply Discount</title>
</head>
<body>

<a href="../index.php">Back to User Home</a>

<h2>Stored Procedure: sp_apply_discount_by_category</h2>

<p>
Applies a percentage discount to all products in a selected category (product_type).
If discount rate is negative, procedure throws an error.
</p>

<?php if (count($categories) === 0): ?>
<p><b>No categories found in product table.</b></p>
<?php else: ?>
<form method="post">
    <p>
        Category (product_type):
        <select name="category" required>
            <?php foreach ($categories as $cat): ?>
                <option value="<?php echo htmlspecialchars($cat); ?>"
                    <?php echo ($cat === $selected_category) ? "selected" : ""; ?>>
                    <?php echo htmlspecialchars($cat); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </p>

    <p>
        Discount Rate (%):
        <input type="number" name="discount_rate" value="<?php echo htmlspecialchars($discount_rate); ?>" placeholder="10" required>
    </p>

    <button type="submit">Apply Discount</button>
</form>
<?php endif; ?>

<?php if ($msg !== ""): ?>
<hr>
<p><b><?php echo $msg; ?></b></p>
<?php endif; ?>

</body>
</html>
<?php
$conn->close();
?>
