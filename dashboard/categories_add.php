<?php
require 'inc/header.php';
require '../inc/db.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

$message = '';

if (isset($_POST['submit'])) {
    $name = trim($_POST['name']);
    $status = isset($_POST['status']) ? 1 : 0;

    if ($name) {
        $stmt = $conn->prepare("INSERT INTO categories(name,status) VALUES(?,?)");
        $stmt->bind_param("si", $name, $status);
        $stmt->execute();
        $stmt->close();

        header("Location: categories.php");
        exit;
    } else {
        $message = "Category name is required!";
    }
}
?>

<div class="container mt-4">
    <h3>Add Category</h3>

    <?php if ($message): ?>
        <div class="alert alert-danger"><?= $message ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="mb-3">
            <label class="form-label">Category Name</label>
            <input type="text" class="form-control" name="name" required>
        </div>

        <div class="form-check mb-3">
            <input class="form-check-input" type="checkbox" name="status" checked>
            <label class="form-check-label">Active</label>
        </div>

        <button class="btn btn-success" name="submit">Save</button>
        <a href="categories.php" class="btn btn-secondary">Back</a>
    </form>
</div>