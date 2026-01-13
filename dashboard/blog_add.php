<?php
require 'inc/header.php';
require '../inc/db.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

$message = '';

if (isset($_POST['submit'])) {
    $title = trim($_POST['title']);
    $content = trim($_POST['content']);
    $user_id = $_SESSION['user_id'];

    if ($title && $content) {
        $stmt = $conn->prepare(
            "INSERT INTO posts (user_id, title, content) VALUES (?, ?, ?)"
        );
        $stmt->bind_param("iss", $user_id, $title, $content);
        $stmt->execute();
        $stmt->close();

        header("Location: blog.php");
        exit;
    } else {
        $message = "All fields are required!";
    }
}

?>

<div class="container mt-4">
    <h3>Add Blog Post</h3>

    <?php if ($message): ?>
        <div class="alert alert-danger"><?= $message ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="mb-3">
            <label class="form-label">Title</label>
            <input class="form-control" name="title" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Content</label>
            <textarea class="form-control" name="content" rows="6" required></textarea>
        </div>

        <button class="btn btn-success" name="submit">Save</button>
        <a href="blog.php" class="btn btn-secondary">Back</a>
    </form>
</div>

<?php include 'inc/footer.php'; ?>