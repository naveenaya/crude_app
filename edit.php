<?php
// include database connection
include("config.php");

// fetch all posts from database
$sql = "SELECT * FROM posts ORDER BY id DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>View Posts</title>
    <style>
        table {
            width: 80%;
            border-collapse: collapse;
            margin: 20px auto;
        }
        th, td {
            padding: 10px;
            border: 1px solid #333;
            text-align: left;
        }
        th {
            background: #f2f2f2;
        }
        a {
            text-decoration: none;
            color: blue;
            margin: 0 5px;
        }
        a.delete {
            color: red;
        }
    </style>
</head>
<body>
    <h2 style="text-align:center;">All Posts</h2>
    <table>
        <tr>
            <th>ID</th>
            <th>Title</th>
            <th>Content</th>
            <th>Actions</th>
        </tr>
        <?php
        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                echo "<tr>";
                echo "<td>".$row['id']."</td>";
                echo "<td>".$row['title']."</td>";
                echo "<td>".$row['content']."</td>";
                echo "<td>
                        <a href='edit.php?id=".$row['id']."'>Edit</a> | 
                        <a href='delete.php?id=".$row['id']."' class='delete' onclick='return confirm(\"Are you sure you want to delete this post?\");'>Delete</a>
                      </td>";
                echo "</tr>";
            }
        } else {
            echo "<tr><td colspan='4' style='text-align:center;'>No posts found</td></tr>";
        }
        ?>
    </table>
    <div style="text-align:center;">
        <a href="create.php">+ Add New Post</a> | 
        <a href="dashboard.php">⬅ Back to Dashboard</a>
    </div>
</body>
</html>