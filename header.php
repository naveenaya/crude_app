<?php // header.php ?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Blog App</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <!-- Bootstrap CSS (CDN) -->
  <link
    href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
    rel="stylesheet"
  >
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
  <div class="container">
    <a class="navbar-brand" href="posts.php">Blog</a>
    <div>
      <a class="btn btn-outline-light btn-sm" href="add_post.php">Add Post</a>
      <a class="btn btn-warning btn-sm ms-2" href="logout.php">Logout</a>
    </div>
  </div>
</nav>
<div class="container">