?php
// db.php
if (session_status() === PHP_SESSION_NONE) { session_start(); }

$host = 'localhost';
$user = 'root';
$pass = '';
$db   = 'blog';      // change if your DB name is different

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
  die('Database connection failed: ' . $conn->connect_error);
}
$conn->set_charset('utf8mb4');