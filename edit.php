<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$root = __DIR__. '/';
require_once $root . 'db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if ($id <= 0) {
    header('Location: users.php'); 
    exit;
}

try {
    // ✅ Use your actual column names
    $stmt = $conn->prepare("SELECT id, username, password, role FROM users WHERE id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $res = $stmt->get_result();
    $user = $res->fetch_assoc();
    $stmt->close();
} catch (Exception $ex) {
    die('Database error: ' . $ex->getMessage());
}

if (!$user) {
    echo "User not found!";
    exit;
}

// ---------------- HANDLE FORM ----------------
$errors = [];
$username = $user['username'];
$password = $user['password'];
$room = $user['role'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $room = trim($_POST['rol'] ?? '');

    if (strlen($username) < 3) $errors[] = 'Username must be at least 3 characters.';
    if (strlen($password) < 5) $errors[] = 'Password must be at least 5 characters.';

    if (empty($errors)) {
        try {
            $up = $conn->prepare("UPDATE users SET username = ?, password = ?, role = ? WHERE id = ?");
            $up->bind_param('sssi', $username, $password, $room, $id);
            $up->execute();
            $up->close();
            header('Location: users.php?updated=1'); exit;
        } catch (Exception $ex) {
            $errors[] = 'Database error: ' . $ex->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Edit User</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
<div class="container mt-5" style="max-width:600px;">
  <h2>Edit User</h2>

  <?php if (!empty($errors)): ?>
    <div class="alert alert-danger">
      <?php foreach ($errors as $er): ?>
        <div><?php echo htmlspecialchars($er); ?></div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

  <form method="post" action="">
    <div class="mb-3">
      <label class="form-label">Username</label>
      <input name="username" class="form-control" value="<?php echo htmlspecialchars($username); ?>" required>
    </div>

    <div class="mb-3">
      <label class="form-label">Password</label>
      <input name="password" type="text" class="form-control" value="<?php echo htmlspecialchars($password); ?>" required>
    </div>

    <div class="mb-3">
      <label class="form-label">role</label>
      <input name="role" class="form-control" value="<?php echo htmlspecialchars($room); ?>">
    </div>

    <button class="btn btn-primary">Update User</button>
    <a href="users.php" class="btn btn-secondary">Cancel</a>
  </form>
</div>
</body>
</html>