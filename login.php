<?php
/************  LOGIN.PHP — FINAL, ROBUST VERSION  ************/
session_start();
require 'db.php'; // expects $conn (mysqli) and no output before this file

// Make mysqli throw exceptions (lets us handle SQL issues gracefully)
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$errors = [];

// Small helper for safe output
function e($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

// Detect if a stored password is a proper password_hash() value
function is_hashed($hash) {
    $info = password_get_info($hash);
    return !empty($info['algo']) && $info['algo'] !== 0;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        $errors[] = 'Please enter both username and password.';
    } else {
        try {
            // Try selecting with role (Task 4 schema)
            $stmt = $conn->prepare("SELECT id, username, password, role FROM users WHERE username = ?");
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $stmt->store_result();

            $hasRoleColumn = true;
        } catch (mysqli_sql_exception $ex) {
            // If role column is missing, fall back without role (older schema)
            $hasRoleColumn = false;
            $stmt = $conn->prepare("SELECT id, username, password FROM users WHERE username = ?");
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $stmt->store_result();
        }

        if ($stmt->num_rows === 1) {
            if ($hasRoleColumn) {
                $stmt->bind_result($id, $uname, $hash, $role);
            } else {
                $stmt->bind_result($id, $uname, $hash);
                $role = 'user'; // default if role column missing
            }
            $stmt->fetch();
            $stmt->close();

            $loginOk = false;

            // 1) Normal path: hashed passwords (Task 4)
            if (is_hashed($hash) && password_verify($password, $hash)) {
                $loginOk = true;

                // Optional: upgrade hash if algorithm changed
                if (password_needs_rehash($hash, PASSWORD_DEFAULT)) {
                    $newHash = password_hash($password, PASSWORD_DEFAULT);
                    $up = $conn->prepare("UPDATE users SET password=? WHERE id=?");
                    $up->bind_param("si", $newHash, $id);
                    $up->execute();
                    $up->close();
                }
            }
            // 2) Migration path: old plain-text password from Task 2
            elseif (!is_hashed($hash) && hash_equals($hash, $password)) {
                $loginOk = true;

                // Immediately upgrade to secure hash
                $newHash = password_hash($password, PASSWORD_DEFAULT);
                $up = $conn->prepare("UPDATE users SET password=? WHERE id=?");
                $up->bind_param("si", $newHash, $id);
                $up->execute();
                $up->close();
            }

            if ($loginOk) {
                // Set session and redirect
                $_SESSION['user_id']  = (int)$id;
                $_SESSION['username'] = $uname;
                $_SESSION['role']     = $role ?: 'user';

                // IMPORTANT: no output before this header
                header("Location: posts.php");
                exit;
            } else {
                // Generic error (don’t reveal which part failed)
                $errors[] = 'Invalid username or password.';
            }
        } else {
            $stmt->close();
            $errors[] = 'Invalid username or password.';
        }
    }
}
?>

<?php include 'header.php'; ?>
<div class="container mt-5" style="max-width:520px;">
    <h2 class="mb-4">Login</h2>

    <?php if (isset($_GET['registered']) && $_GET['registered'] == '1'): ?>
        <div class="alert alert-success">Registration successful. Please log in.</div>
    <?php endif; ?>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <?php foreach ($errors as $er): ?>
                <p class="mb-0"><?php echo e($er); ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <form method="post" action="" novalidate>
        <div class="mb-3">
            <label class="form-label">Username</label>
            <input name="username" class="form-control" required value="<?php echo e($_POST['username'] ?? ''); ?>">
        </div>

        <div class="mb-3">
            <label class="form-label">Password</label>
            <input name="password" type="password" class="form-control" required>
        </div>

        <button class="btn btn-primary w-100">Login</button>
        <div class="d-flex justify-content-between mt-3">
            <a href="register.php">Create an account</a>
            <a href="logout.php">Logout</a>
        </div>
    </form>
</div>
<?php include 'footer.php'; ?>