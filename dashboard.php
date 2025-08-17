<?php
// Always protect the page first
include("protect.php");
include("config.php");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Dashboard</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        a { margin: 0 10px; text-decoration: none; }
        a:hover { text-decoration: underline; }
        nav { margin-bottom: 20px; }
        iframe { width: 100%; height: 500px; border: 1px solid #ccc; }
    </style>
</head>
<body>
    <h2>Welcome, <?php echo $_SESSION['username']; ?>!</h2>
    <nav>
        <a href="create.php" target="contentFrame">➕ Create Post</a> |
        <a href="view.php" target="contentFrame">👀 View Posts</a> |
        <a href="post.php" target="contentFrame">📝 Add Post</a> |
        <a href="edit.php" target="contentFrame">✏ Edit Post</a> |
        <a href="delete.php" target="contentFrame">❌ Delete Post</a> |
        <a href="logout.php">🚪 Logout</a>
    </nav>

    <!-- This will load other pages inside dashboard -->
    <iframe name="contentFrame"></iframe>
</body>
</html>