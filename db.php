<?php
// db.php
$host = 'localhost';
$user = 'root';     // change if you set a MySQL password/user
$pass = '';
$db   = 'blog';

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die('DB connection failed: ' . $conn->connect_error);
}
$conn->set_charset('utf8mb4');

if (session_status() === PHP_SESSION_NONE) {
    session_start(); // so we can use $_SESSION for login checks
}