<?php
session_start();
include 'config.php';


// Check if logged in
if (!isset($_SESSION["username"])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = $_POST["title"];
    $content = $_POST["content"];

    $sql = "INSERT INTO posts (title, content) VALUES ('$title', '$content')";
    if ($conn->query($sql) === TRUE) {
        echo "✅ New post created successfully! <a href='view_posts.php'>View Posts</a>";
    } else {
        echo "Error: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Create Post</title>
</head>
<body>
    <h2>Create New Blog Post</h2>
    <form method="POST">
        Title: <input type="text" name="title" required><br><br>
        Content:<br>
        <textarea name="content" rows="5" cols="40" required></textarea><br><br>
        <button type="submit">Create Post</button>
    </form>
    <br>
    <a href="dashboard.php">Back to Dashboard</a>
</body>
</html>