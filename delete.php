<?php
include("config.php");

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    $sql = "DELETE FROM posts WHERE id=$id";

    if ($conn->query($sql) === TRUE) {
        header("Location: view.php");
        exit;
    } else {
        echo "Error deleting record: " . $conn->error;
    }
} else {
    echo "Invalid request!";
}
?>
