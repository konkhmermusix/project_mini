<?php
require '../inc/db.php';
session_start();

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

require 'inc/header.php';

?>

<?php if (!empty($message)) echo $message; ?>

<div class="px-2 mt-4 mb-5">
    <div class="card shadow-sm mb-3">
        <div class="card-body p-4 d-flex align-items-center">
            <h3 class="mb-0">Edit Blog Post</h3>
            <a href="blog.php" class="btn btn-secondary ms-auto"><i class="bi bi-arrow-left me-2"></i>Back</a>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body p-4">
            <form method="POST">

                <div class="form-outline mb-3">
                    <input class="form-control" type="text" name="title" placeholder=" " value="<?= htmlspecialchars($post['title']) ?>" required>
                    <label>Product Name</label>
                </div>

                <div class="form-outline mb-3">
                    <textarea class="form-control" name="content" rows="4" placeholder=" "><?= htmlspecialchars($post['content']) ?></textarea>
                    <label>Description</label>
                </div>

                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" name="status" <?= $post['status'] ? 'checked' : '' ?>>
                    <label class="form-check-label">Active</label>
                </div>

                <button class="btn btn-primary" name="submit">Update</button>
            </form>
        </div>
    </div>
</div>

<?php require 'inc/footer.php'; ?>