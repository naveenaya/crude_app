?php
/************ EDIT.PHP — FINAL VERSION ************/
require 'protect.php';
require_login();                          // must be logged in
require_role(['admin', 'editor']);        // only admin/editor allowed

// Ensure we have a valid post id
$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    header("Location: posts.php");
    exit;
}

// Fetch the post
$stmt = $conn->prepare("SELECT id, title, content, user_id FROM posts WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$post = $result->fetch_assoc();
$stmt->close();

if (!$post) {
    http_response_code(404);
    echo "<h3>404 - Post not found</h3>";
    exit;
}

// If editor, only allow editing their own posts
if ($_SESSION['role'] === 'editor' && $_SESSION['user_id'] != $post['user_id']) {
    http_response_code(403);
    echo "<h3>403 - Forbidden</h3><p>Editors can only edit their own posts.</p>";
    exit;
}

$errors = [];
$title = $post['title'];
$content = $post['content'];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');

    if (strlen($title) < 3) {
        $errors[] = "Title must be at least 3 characters.";
    }
    if (strlen($content) < 10) {
        $errors[] = "Content must be at least 10 characters.";
    }

    if (empty($errors)) {
        $stmt = $conn->prepare("UPDATE posts SET title=?, content=? WHERE id=?");
        $stmt->bind_param("ssi", $title, $content, $id);
        $stmt->execute();
        $stmt->close();

        header("Location: posts.php?updated=1");
        exit;
    }
}
?>

<?php include 'header.php'; ?>
<div class="container mt-5" style="max-width:720px;">
    <h2 class="mb-4">Edit Post</h2>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <?php foreach ($errors as $er): ?>
                <p class="mb-0"><?php echo e($er); ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <form method="post" action="">
        <div class="mb-3">
            <label class="form-label">Title</label>
            <input name="title" class="form-control" value="<?php echo e($title); ?>" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Content</label>
            <textarea name="content" rows="6" class="form-control" required><?php echo e($content); ?></textarea>
        </div>

        <button class="btn btn-success">Update</button>
        <a href="posts.php" class="btn btn-secondary">Cancel</a>
    </form>
</div>
<?php include 'footer.php'; ?>