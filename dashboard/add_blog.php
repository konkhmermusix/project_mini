<?php
require '../inc/db.php';
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') die("Access denied.");

include 'inc/header.php';

if (isset($_POST['submit'])) {
    $title = $_POST['title'];
    $content = $_POST['content'];
    $user_id = $_SESSION['user_id'];

    $sql = "INSERT INTO posts(title, content, user_id) VALUES('$title','$content','$user_id')";
    if ($conn->query($sql)) {
        echo "<div class='alert alert-success'>Post added successfully!</div>";
    } else {
        echo "<div class='alert alert-danger'>Error: " . $conn->error . "</div>";
    }
}
?>

<h2>Add Blog Post</h2>
<form method="POST">
    <div class="mb-3"><input class="form-control" name="title" placeholder="Title" required></div>
    <div class="mb-3"><textarea class="form-control" name="content" placeholder="Content" rows="5"></textarea></div>
    <button class="btn btn-success" name="submit">Add Post</button>
</form>

<?php include 'inc/footer.php'; ?>