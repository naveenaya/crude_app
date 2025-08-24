<?php
include 'db.php';

$id = $_GET['id'] ?? null;
if (!$id) {
    echo "Post not found!";
    exit;
}

// fetch post from posts table
$result = mysqli_query($conn, "SELECT * FROM posts WHERE id = $id");
$post = mysqli_fetch_assoc($result);

if (!$post) {
    echo "Post not found!";
    exit;
}

// handle update
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = $_POST['title'];
    $content = $_POST['content'];

    $sql = "UPDATE posts SET title='$title', content='$content' WHERE id=$id";
    mysqli_query($conn, $sql);

    header("Location: posts.php"); // redirect to list of posts
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
    <input type="text" name="title" value="<?= $post['title'] ?>" required><br><br>

    <label>Content:</label><br>
    <textarea name="content" required><?= $post['content'] ?></textarea><br><br>

    <button type="submit">Update Post</button>
    <a href="posts.php">Cancel</a>
</form>
</body>
</html>