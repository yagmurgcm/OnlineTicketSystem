<?php
$servername = "localhost";
$username = "root";
$password = ""; 
$dbname = "cs306"; 

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $surname = $_POST['surname'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $address = $_POST['address'];

    try {
        $sql = "CALL sp_add_customer_safe('$name', '$surname', '$email', '$phone', '$address')";
        
        if ($conn->query($sql) === TRUE) {
            $message = "<p style='color: green; font-weight: bold;'>Success: Customer added successfully.</p>";
        }
    } catch (mysqli_sql_exception $e) {
        $message = "<p style='color: red; font-weight: bold;'>Error: " . $e->getMessage() . "</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Stored Procedure Integration</title>
    <style>
        body {
            font-family: Arial, sans-serif; 
            margin: 40px;
            max-width: 600px;
        }
        h3 {
            border-bottom: 2px solid #ccc; 
            padding-bottom: 10px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        input[type="text"], input[type="email"] {
            width: 100%;
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        button {
            padding: 10px 15px;
            background-color: #f0f0f0;
            border: 1px solid #999;
            cursor: pointer;
            font-weight: bold;
        }
        button:hover {
            background-color: #e0e0e0;
        }
        .home-link {
            display: inline-block;
            margin-top: 20px;
            text-decoration: underline;
            color: blue;
        }
    </style>
</head>
<body>

    <h3>Stored Procedure (by Berkay): Adds a new customer safely</h3>

    <p>This form calls the sp_add_customer_safe procedure. It checks if the email exists before inserting.</p>

    <?php echo $message; ?>

    <form method="POST">
        <div class="form-group">
            <label>Name</label>
            <input type="text" name="name" required>
        </div>

        <div class="form-group">
            <label>Surname</label>
            <input type="text" name="surname" required>
        </div>

        <div class="form-group">
            <label>Email</label>
            <input type="email" name="email" required>
        </div>

        <div class="form-group">
            <label>Phone</label>
            <input type="text" name="phone">
        </div>

        <div class="form-group">
            <label>Address</label>
            <input type="text" name="address">
        </div>

        <button type="submit">Call Procedure</button>
    </form>

    <a href="../index.php" class="home-link">Go to homepage</a>

</body>
</html>