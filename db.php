<?php
// db.php
$host = "localhost";
$user = "root";
$pass = ""; // XAMPP default. Change if you use a password.
$dbname = "mechanic_finder";

$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) {
    die("DB Connection failed: " . $conn->connect_error);
}
$conn->set_charset("utf8mb4");
?>
