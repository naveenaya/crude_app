?php
// protect.php
if (session_status() === PHP_SESSION_NONE) { session_start(); }

function requireLogin(): void {
  if (empty($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
  }
}

// $roles example: ['admin','editor']
function requireRole(array $roles): void {
  requireLogin();
  $userRole = $_SESSION['role'] ?? 'user';
  if (!in_array($userRole, $roles, true)) {
    http_response_code(403);
    echo '<div style="padding:16px;font-family:system-ui">Access denied (need role: '
         . htmlspecialchars(implode(' or ', $roles)) . ').</div>';
    exit;
  }
}