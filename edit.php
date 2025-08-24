<?php

// Show all errors (for debugging)
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
include 'db.php';

// ✅ Check if user is logged in
if (!isset($_SESSION['username'])) {
    echo "You must login first!";
    exit;
}

// ✅ Allow only Naveenaya
if ($_SESSION['username'] !== "Naveenaya") {
    echo "You don't have the permission to edit.";
    exit;
}

// ✅ Get post ID from URL
$id = $_GET['id'] ?? null;
if (!$id) {
    echo "Post not found!";
    exit;
}

// ✅ Fetch post from DB
$result = mysqli_query($conn, "SELECT * FROM posts WHERE id = $id");
$post = mysqli_fetch_assoc($result);

if (!$post) {
    echo "Post not found!";
    exit;
}

// ✅ Handle update
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = $_POST['title'];
    $content = $_POST['content'];

    $sql = "UPDATE posts SET title='$title', content='$content' WHERE id=$id";
    mysqli_query($conn, $sql);

    header("Location: posts.php");
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Edit Post</title>
</head>
<body>
<h2>Edit Post</h2>
<form method="POST">
    <label>Title:</label><br>
    <input type="text" name="title" value="<?= htmlspecialchars($post['title']) ?>" required><br><br>

    <label>Content:</label><br>
    <textarea name="content" required><?= htmlspecialchars($post['content']) ?></textarea><br><br>

    <button type="submit">Update Post</button>
    <a href="posts.php">Cancel</a>
</form>
</body>
</html>