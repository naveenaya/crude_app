<?php
session_start();
require 'db.php';   // database connection
$errors = [];       // initialize error array

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    // Validation
    if (empty($username) || empty($password)) {
        $errors[] = "Please enter both username and password.";
    } else {
        // 🔹 Check if user exists
        $stmt = $conn->prepare("SELECT id, username, password, role FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows == 1) {
            $stmt->bind_result($id, $uname, $hash, $role);
            $stmt->fetch();

            // 🔹 Verify password
            if (password_verify($password, $hash)) {
                // Correct login → set session
                $_SESSION['user_id']  = $id;
                $_SESSION['username'] = $uname;
                $_SESSION['role']     = $role;

                header("Location: posts.php");
                exit;
            } else {
                $errors[] = "Invalid password.";
            }
        } else {
            $errors[] = "No account found with that username.";
        }
        $stmt->close();
    }
}
?>

<?php include 'header.php'; ?>
<div class="container mt-5">
    <h2>Login</h2>

    <!-- 🔹 Show success message if redirected from register -->
    <?php if (isset($_GET['registered']) && $_GET['registered'] == 1): ?>
        <div class="alert alert-success">Registration successful. Please log in.</div>
    <?php endif; ?>

    <!-- 🔹 Display errors -->
    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <?php foreach ($errors as $error): ?>
                <p><?php echo $error; ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <!-- 🔹 Login form -->
    <form method="post" action="">
        <div class="mb-3">
            <label class="form-label">Username</label>
            <input type="text" name="username" class="form-control" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Password</label>
            <input type="password" name="password" class="form-control" required>
        </div>

        <button type="submit" class="btn btn-primary">Login</button>
        <a href="register.php" class="btn btn-link">Register</a>
    </form>
</div>
<?php include 'footer.php'; ?>