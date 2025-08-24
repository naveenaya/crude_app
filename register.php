
?php
require 'db.php';   // database connection
$errors = [];       // always initialize error array

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $confirm  = $_POST['confirm'];

    // 🔹 Validation
    if (strlen($username) < 3) {
        $errors[] = "Username must be at least 3 characters long.";
    }
    if (strlen($password) < 6) {
        $errors[] = "Password must be at least 6 characters long.";
    }
    if ($password !== $confirm) {
        $errors[] = "Passwords do not match.";
    }

    // 🔹 Check if username already exists
    if (empty($errors)) {
        $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $errors[] = "Username is already taken. Please choose another.";
        }
        $stmt->close();
    }

    // 🔹 If no errors → insert user
    if (empty($errors)) {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $role = "user"; // default role for new registrations

        $stmt = $conn->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $username, $hash, $role);
        $stmt->execute();
        $stmt->close();

        // redirect to login after success
        header("Location: login.php?registered=1");
        exit;
    }
}
?>

<?php include 'header.php'; ?>
<div class="container mt-5">
    <h2>Create Account</h2>

    <!-- 🔹 Display errors -->
    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <?php foreach ($errors as $error): ?>
                <p><?php echo $error; ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <!-- 🔹 Registration form -->
    <form method="post" action="">
        <div class="mb-3">
            <label class="form-label">Username</label>
            <input type="text" name="username" class="form-control" required>
            <small class="form-text text-muted">Enter username (min 3 chars)</small>
        </div>

        <div class="mb-3">
            <label class="form-label">Password</label>
            <input type="password" name="password" class="form-control" required>
            <small class="form-text text-muted">Password (min 6 chars)</small>
        </div>

        <div class="mb-3">
            <label class="form-label">Confirm Password</label>
            <input type="password" name="confirm" class="form-control" required>
            <small class="form-text text-muted">Re-enter the same password</small>
        </div>

        <button type="submit" class="btn btn-primary">Register</button>
        <a href="login.php" class="btn btn-link">Login</a>
    </form>
</div>
<?php include 'footer.php'; ?>