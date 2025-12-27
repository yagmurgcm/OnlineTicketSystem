<?php
session_start();
require_once __DIR__ . "/db_connect.php";

$msg = "";
$conn = db_connect();

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$gender_options = ["man", "woman", "unisex"];
$size_options = ["s", "m", "l", "xl"];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $product_name = trim($_POST["product_name"] ?? "");
    $product_type = trim($_POST["product_type"] ?? "");
    $price_raw = $_POST["price"] ?? "";
    $stock_raw = $_POST["stock"] ?? "";

    $manufacturer_id_selected_raw = $_POST["manufacturer_id_selected"] ?? "";
    $manufacturer_name_typed = trim($_POST["manufacturer_name"] ?? "");

    $gender_category = trim($_POST["gender_category"] ?? "unisex");
    $color = trim($_POST["color"] ?? "black");
    $size = trim($_POST["size"] ?? "m");

    if ($product_name === "" || $price_raw === "" || $stock_raw === "") {
        $msg = "product_name, price and stock are required.";
    } elseif (!in_array($gender_category, $gender_options, true)) {
        $msg = "Invalid gender_category.";
    } elseif (!in_array($size, $size_options, true)) {
        $msg = "Invalid size.";
    } else {
        $price = (int)$price_raw;
        $stock = (int)$stock_raw;

        if ($product_type === "") $product_type = null;
        if ($color === "") $color = "black";

        $manufacturer_id = null;

        try {
            $conn->begin_transaction();

            $manufacturer_id_selected = null;
            if ($manufacturer_id_selected_raw !== "") {
                $manufacturer_id_selected = (int)$manufacturer_id_selected_raw;
                if ($manufacturer_id_selected <= 0) $manufacturer_id_selected = null;
            }

            if ($manufacturer_id_selected !== null) {
                $stmt = $conn->prepare("SELECT manufacturer_id FROM manufacturer WHERE manufacturer_id = ? LIMIT 1");
                $stmt->bind_param("i", $manufacturer_id_selected);
                $stmt->execute();
                $res = $stmt->get_result();
                $row = $res ? $res->fetch_assoc() : null;
                $stmt->close();

                if (!$row) {
                    throw new Exception("Selected manufacturer_id not found.");
                }

                $manufacturer_id = $manufacturer_id_selected;
            } else {
                if ($manufacturer_name_typed !== "") {
                    $stmt = $conn->prepare("SELECT manufacturer_id FROM manufacturer WHERE manufacturer = ? LIMIT 1");
                    $stmt->bind_param("s", $manufacturer_name_typed);
                    $stmt->execute();
                    $res = $stmt->get_result();
                    $row = $res ? $res->fetch_assoc() : null;
                    $stmt->close();

                    if ($row) {
                        $manufacturer_id = (int)$row["manufacturer_id"];
                    } else {
                        $stmt = $conn->prepare("INSERT INTO manufacturer (manufacturer, product_id) VALUES (?, NULL)");
                        $stmt->bind_param("s", $manufacturer_name_typed);
                        $stmt->execute();
                        $manufacturer_id = (int)$conn->insert_id;
                        $stmt->close();
                    }
                }
            }

            if ($manufacturer_id === null) {
                $stmt = $conn->prepare(
                    "INSERT INTO product
                    (product_name, product_type, price, stock, manufacturer_id, gender_category, color, size)
                    VALUES (?, ?, ?, ?, NULL, ?, ?, ?)"
                );
                $stmt->bind_param(
                    "ssiisss",
                    $product_name,
                    $product_type,
                    $price,
                    $stock,
                    $gender_category,
                    $color,
                    $size
                );
            } else {
                $stmt = $conn->prepare(
                    "INSERT INTO product
                    (product_name, product_type, price, stock, manufacturer_id, gender_category, color, size)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
                );
                $stmt->bind_param(
                    "ssiiisss",
                    $product_name,
                    $product_type,
                    $price,
                    $stock,
                    $manufacturer_id,
                    $gender_category,
                    $color,
                    $size
                );
            }

            $stmt->execute();
            $new_product_id = (int)$conn->insert_id;
            $stmt->close();

            if ($manufacturer_id !== null) {
                $stmt = $conn->prepare(
                    "UPDATE manufacturer
                     SET product_id = ?
                     WHERE manufacturer_id = ? AND (product_id IS NULL OR product_id = 0)"
                );
                $stmt->bind_param("ii", $new_product_id, $manufacturer_id);
                $stmt->execute();
                $stmt->close();
            }

            $conn->commit();
            $msg = "Product inserted successfully. product_id = " . $new_product_id;
        } catch (Throwable $e) {
            if ($conn->errno === 0) {
                $conn->rollback();
            } else {
                $conn->rollback();
            }
            $msg = "Insert failed: " . $e->getMessage();
        }
    }
}

$manufacturer_list = [];
$res = $conn->query("SELECT manufacturer_id, manufacturer FROM manufacturer ORDER BY manufacturer_id");
if ($res) {
    while ($row = $res->fetch_assoc()) {
        $manufacturer_list[] = $row;
    }
    $res->free();
}
?>
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Add Product</title>
</head>
<body>

<a href="index.php">Back to User Home</a>

<h2>Add Product</h2>

<form method="post">
    <p>Product Name: <input type="text" name="product_name" required></p>
    <p>Product Type: <input type="text" name="product_type" placeholder="tshirt"></p>
    <p>Price: <input type="number" name="price" required></p>
    <p>Stock: <input type="number" name="stock" required></p>

    <p>
        Manufacturer (existing):
        <select id="man_select" name="manufacturer_id_selected">
            <option value="">(choose)</option>
            <?php foreach ($manufacturer_list as $m): ?>
                <option value="<?php echo (int)$m["manufacturer_id"]; ?>">
                    <?php echo (int)$m["manufacturer_id"] . " - " . htmlspecialchars($m["manufacturer"]); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <button type="button" onclick="useSelected()">Use Selected</button>
    </p>

    <p>
        Manufacturer (new or existing name):
        <input type="text" id="man_input" name="manufacturer_name" placeholder="Turkey">
    </p>

    <p>
        Gender Category:
        <select name="gender_category">
            <option value="man">man</option>
            <option value="woman">woman</option>
            <option value="unisex" selected>unisex</option>
        </select>
    </p>

    <p>Color: <input type="text" name="color" value="black"></p>

    <p>
        Size:
        <select name="size">
            <option value="s">s</option>
            <option value="m" selected>m</option>
            <option value="l">l</option>
            <option value="xl">xl</option>
        </select>
    </p>

    <button type="submit">Add Product</button>
</form>

<script>
function useSelected() {
    var sel = document.getElementById("man_select");
    var inp = document.getElementById("man_input");
    inp.value = "";
}
</script>

<?php if ($msg !== ""): ?>
<hr>
<p><b><?php echo htmlspecialchars($msg); ?></b></p>
<?php endif; ?>

</body>
</html>
<?php
$conn->close();
?>
