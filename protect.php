<?php
// protect.php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once 'db.php';

function require_login() {
    if (empty($_SESSION['user_id'])) {
        header('Location: login.php');
        exit;
    }
}

function require_role($roles) {
    if (empty($_SESSION['role'])) { 
        header('Location: login.php'); 
        exit; 
    }
    $allowed = is_array($roles) ? $roles : [$roles];
    if (!in_array($_SESSION['role'], $allowed, true)) {
        http_response_code(403);
        echo "<h3>403 - Forbidden</h3><p>You don’t have permission to access this page.</p>";
        exit;
    }
}