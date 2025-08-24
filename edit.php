<?php
// edit.php — robust version with clear checks & helpful debug info
// ---------------------- DEBUG (temporary) ----------------------
// Remove or comment these out after you finish testing:
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// ---------------------- INCLUDES & SESSION ----------------------
/*
  Expects:
    - db.php defines $conn (mysqli) and optionally an e() helper.
    - protect.php defines require_login() and require_role().
*/
$root = __DIR__. '/';

if (!file_exists($root . 'db.php')) {
    http_response_code(500);
    die('Fatal: missing db.php in project root. Please ensure db.php exists.');
}
require_once $root . 'db.php';

// ensure session started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// include protect.php if present, otherwise define safe fallbacks
if (file_exists($root . 'protect.php')) {
    require_once $root . 'protect.php';
} else {
    // fallback implementations so script doesn't fatally error
    function require_login() {
        if (empty($_SESSION['user_id'])) {
            header('Location: login.php'); exit;
        }
    }
    function require_role($roles) {
        if (empty($_SESSION['role'])) {
            header('Location: login.php'); exit;
        }
        $allowed = is_array($roles) ? $roles : [$roles];
        if (!in_array($_SESSION['role'], $allowed, true)) {
            http_response_code(403); echo "<h3>403 - Forbidden</h3><p>Access denied.</p>"; exit;
        }
    }
}

// small safe output helper (if not provided by db.php)
if (!function_exists('e')) {
    function e($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
}

// ---------------------- ACCESS CONTROL ----------------------
/* ensure user is logged in and has admin/editor role */
if (function_exists('require_login')) {
    require_login();
} else {
    if (empty($_SESSION['user_id'])) { header('Location: login.php'); exit; }
}

if (function_exists('require_role')) {
    require_role(['admin','editor']);
} else {
    if (empty($_SESSION['role']) || !in_array($_SESSION['role'], ['admin','editor'], true)) {
        http_response_code(403); echo "<h3>403 - Forbidden</h3><p>Access denied.</p>"; exit;
    }
}

// ---------------------- VALIDATE & FETCH POST ----------------------
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if ($id <= 0) {
    // no valid id provided — redirect to posts list
    header('Location: posts.php'); exit;
}

try {
    $stmt = $conn->prepare("SELECT id, title, content, user_id FROM posts WHERE id = ?");
    if (!$stmt) throw new Exception('Prepare failed: ' . $conn->error);
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $res = $stmt->get_result();
    $post = $res->fetch_assoc();
    $stmt->close();
} catch (Exception $ex) {
    http_response_code(500);
    die('Database error: ' . e($ex->getMessage()));
}

if (!$post) {
    http_response_code(404);
    echo "<h3>404 - Post not found</h3><p>No post exists with id=" . e($id) . ".</p>";
    exit;
}

// If current user is editor, ensure they own this post
$currentRole = $_SESSION['role'] ?? 'user';
$currentUserId = (int)($_SESSION['user_id'] ?? 0);

if ($currentRole === 'editor' && $currentUserId !== (int)$post['user_id']) {
    http_response_code(403);
    echo "<h3>403 - Forbidden</h3><p>Editors can only edit their own posts.</p>";
    exit;
}

// ---------------------- HANDLE FORM SUBMIT ----------------------
$errors = [];
$title = $post['title'];
$content = $post['content'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');

    if (strlen($title) < 3) $errors[] = 'Title must be at least 3 characters.';
    if (strlen($content) < 10) $errors[] = 'Content must be at least 10 characters.';

    if (empty($errors)) {
        try {
            $up = $conn->prepare("UPDATE posts SET title = ?, content = ? WHERE id = ?");
            if (!$up) throw new Exception('Prepare failed: ' . $conn->error);
            $up->bind_param('ssi', $title, $content, $id);
            $up->execute();
            $up->close();
            // success -> redirect back to posts (you can change to view.php?id=...)
            header('Location: posts.php?updated=1'); exit;
        } catch (Exception $ex) {
            $errors[] = 'Database error: ' . $ex->getMessage();
        }
    }
}

// ---------------------- RENDER FORM ----------------------
include $root . 'header.php';
?>
<div class="container mt-5" style="max-width:800px;">
  <h2 class="mb-3">Edit Post</h2>

  <?php if (!empty($errors)): ?>
    <div class="alert alert-danger">
      <?php foreach ($errors as $er): ?>
        <div><?php echo e($er); ?></div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

  <form method="post" action="">
    <div class="mb-3">
      <label class="form-label">Title</label>
      <input name="title" class="form-control" required minlength="3" value="<?php echo e($title); ?>">
    </div>

    <div class="mb-3">
      <label class="form-label">Content</label>
      <textarea name="content" rows="8" class="form-control" required minlength="10"><?php echo e($content); ?></textarea>
    </div>

    <button class="btn btn-primary">Update Post</button>
    <a href="posts.php" class="btn btn-secondary">Cancel</a>
  </form>
</div>

<?php include $root . 'footer.php';