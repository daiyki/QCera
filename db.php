<?php
$dbhost = "localhost";
$dbuser = "root";
$dbpass = "";
$dbname = "ecomdb";

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $conn = mysqli_connect($dbhost, $dbuser, $dbpass, $dbname);
    mysqli_set_charset($conn, "utf8mb4");
    mysqli_query($conn, "SET time_zone = '+08:00'"); // Philippine time
} catch (mysqli_sql_exception $e) {
    error_log("DB connection failed: " . $e->getMessage());
    http_response_code(500);
    die("We're having trouble connecting right now. Please try again later.");
}
