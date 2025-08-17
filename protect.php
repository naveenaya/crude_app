<?php
session_start();

// if user is not logged in, redirect to login.php
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}
?>