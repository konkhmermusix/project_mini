<?php
require '../inc/db.php';
session_start();

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

require 'inc/header.php';

?>


<?php if (!empty($message)) echo $message; ?>

<div class="px-2 mt-4 mb-5">
    <div class="card shadow-sm mb-3">
        <div class="card-body p-4 d-flex align-items-center">
            <h3 class="mb-0">Add Blog Post</h3>
            <a href="products.php" class="btn btn-secondary ms-auto"><i class="bi bi-arrow-left me-2"></i>Back</a>
        </div>
    </div>
    <div class="card shadow-sm mb-4">
        <div class="card-body p-4">
            <form method="POST">

                <div class="form-outline mb-3">
                    <input class="form-control" type="text" name="title" placeholder=" " required>
                    <label>Title</label>
                </div>

                <div class="form-outline mb-3">
                    <textarea class="form-control" name="content" rows="4" placeholder=" "></textarea>
                    <label>Content</label>
                </div>

                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" name="status" checked>
                    <label class="form-check-label">Active</label>
                </div>

                <button class="btn btn-success" name="submit">Save</button>
            </form>
        </div>
    </div>
</div>
<?php require 'inc/footer.php'; ?>