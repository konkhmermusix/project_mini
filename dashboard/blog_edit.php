<?php
require 'inc/header.php';
require '../inc/db.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$post = $conn->query("SELECT * FROM posts WHERE id = $id")->fetch_assoc();

if (!$post) {
    die("Post not found");
}

if (isset($_POST['submit'])) {
    $title = trim($_POST['title']);
    $content = trim($_POST['content']);

    $stmt = $conn->prepare(
        "UPDATE posts SET title = ?, content = ? WHERE id = ?"
    );
    $stmt->bind_param("ssi", $title, $content, $id);
    $stmt->execute();
    $stmt->close();

    header("Location: blog.php");
    exit;
}
?>

<div class="container mt-4">
    <h3>Edit Blog Post</h3>

    <form method="POST">
        <div class="mb-3">
            <label class="form-label">Title</label>
            <input class="form-control" name="title"
                value="<?= htmlspecialchars($post['title']) ?>" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Content</label>
            <textarea class="form-control" name="content" rows="6" required><?= htmlspecialchars($post['content']) ?></textarea>
        </div>

        <button class="btn btn-primary" name="submit">Update</button>
        <a href="blog.php" class="btn btn-secondary">Cancel</a>
    </form>
</div>

<?php include 'inc/footer.php'; ?>