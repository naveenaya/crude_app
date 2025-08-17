<?php
session_start();
include 'config.php';

// Check login
if (!isset($_SESSION["username"])) {
    header("Location: login.php");
    exit();
}

$sql = "SELECT * FROM posts ORDER BY created_at DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>All Blog Posts</title>
</head>
<body>
    <h2>All Blog Posts</h2>
    <?php
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            echo "<h3>" . $row['title'] . "</h3>";
            echo "<p>" . $row['content'] . "</p>";
            echo "<small>Posted on: " . $row['created_at'] . "</small>";
            echo "<hr>";
        }
    } else {
        echo "No posts yet!";
    }
    ?>
    <br>
    <a href="dashboard.php">Back to Dashboard</a>
</body>
</html>