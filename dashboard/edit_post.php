<?php
require '../inc/db.php';
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') die("Access denied.");

include 'inc/header.php';

$id = $_GET['id'];
$result = $conn->query("SELECT * FROM posts WHERE id=$id");
$post = $result->fetch_assoc();

if (isset($_POST['submit'])) {
    $title = $_POST['title'];
    $content = $_POST['content'];

    $sql = "UPDATE posts SET title='$title', content='$content' WHERE id=$id";
    if ($conn->query($sql)) {
        echo "<div class='alert alert-success'>Post updated successfully!</div>";
    } else {
        echo "<div class='alert alert-danger'>Error: " . $conn->error . "</div>";
    }
}
?>

<h2>Edit Blog Post</h2>
<form method="POST">
    <div class="mb-3"><input class="form-control" name="title" value="<?= $post['title'] ?>" required></div>
    <div class="mb-3"><textarea class="form-control" name="content" rows="5"><?= $post['content'] ?></textarea></div>
    <button class="btn btn-primary" name="submit">Update Post</button>
</form>

<?php include 'inc/footer.php'; ?>