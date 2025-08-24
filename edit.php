<?php
require 'db.php';

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $username = trim($_POST['username'] ?? '');
  $password = trim($_POST['password'] ?? '');
  $confirm  = trim($_POST['confirm']  ?? '');

  // server-side validation
  if ($username === '' || $password === '' || $confirm === '') {
    $errors[] = 'All fields are required.';
  } elseif (strlen($username) < 3) {
    $errors[] = 'Username must be at least 3 characters.';
  } elseif ($password !== $confirm) {
    $errors[] = 'Passwords do not match.';
  }

  // unique username?
  if (!$errors) {
    $stmt = $conn->prepare('SELECT id FROM users WHERE username = ?');
    $stmt->bind_param('s', $username);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
      $errors[] = 'Username is already taken.';
    }
    $stmt->close();
  }

  // create user
  if (!$errors) {
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $role = 'user'; // default role
    $stmt = $conn->prepare('INSERT INTO users (username, password, role) VALUES (?, ?, ?)');
    $stmt->bind_param('sss', $username, $hash, $role);
    $stmt->execute();
    $stmt->close();

    header('Location: login.php?registered=1');
    exit;
  }
}
include 'header.php';
?>
<div class="container" style="max-width:540px">
  <h3 class="mb-3">Create Account</h3>

  <?php if ($errors): ?>
    <div class="alert alert-danger"><?= implode('<br>', array_map('htmlspecialchars', $errors)) ?></div>
  <?php endif; ?>

  <form method="post" class="needs-validation" novalidate>
    <div class="mb-3">
      <label class="form-label">Username</label>
      <input name="username" class="form-control" required minlength="3">
      <div class="invalid-feedback">Enter a username (min 3 chars)</div>
    </div>
    <div class="mb-3">
      <label class="form-label">Password</label>
      <input name="password" type="password" class="form-control" required minlength="6">
      <div class="invalid-feedback">Enter a password (min 6 chars)</div>
    </div>
    <div class="mb-3">
      <label class="form-label">Confirm Password</label>
      <input name="confirm" type="password" class="form-control" required minlength="6">
      <div class="invalid-feedback">Re-enter the same password</div>
    </div>
    <button class="btn btn-primary">Register</button>
    <a class="btn btn-link" href="login.php">Login</a>
  </form>
</div>
<script>
(() => {
  const forms = document.querySelectorAll('.needs-validation');
  Array.from(forms).forEach(form => form.addEventListener('submit', e => {
    if (!form.checkValidity()) { e.preventDefault(); e.stopPropagation(); }
    form.classList.add('was-validated');
  }));
})();
</script>
<?php include 'footer.php'; ?>