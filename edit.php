<?php
session_start();
include 'db.php';

// check if user is logged in
if (!isset($_SESSION['username'])) {
    echo "You must login first!";
    exit;
}

// only allow Navinaya (or others you choose)
$allowed_users = ["Naveenaya", "AdminUser"];
if (!in_array($_SESSION['username'], $allowed_users)) {
    echo "You are not allowed to edit posts.";
    exit;
}