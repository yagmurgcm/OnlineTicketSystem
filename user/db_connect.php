<?php
function db_connect(): mysqli {
    $host = "localhost";
    $user = "root";
    $pass = "";
    $db   = "cs306";
    $port = 3306;

    $conn = new mysqli($host, $user, $pass, $db, $port);
    if ($conn->connect_errno) {
        http_response_code(500);
        die("MySQL connection failed: " . $conn->connect_error);
    }

    $conn->set_charset("utf8mb4");
    return $conn;
}
