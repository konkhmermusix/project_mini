<?php
require 'inc/header.php';
require '../inc/db.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

$id = (int)$_GET['id'];
$category = $conn->query("SELECT * FROM categories WHERE id=$id")->fetch_assoc();

if (!$category) die("Category not found");

if (isset($_POST['submit'])) {
    $name = trim($_POST['name']);
    $status = isset($_POST['status']) ? 1 : 0;

    $stmt = $conn->prepare("UPDATE categories SET name=?, status=? WHERE id=?");
    $stmt->bind_param("sii", $name, $status, $id);
    $stmt->execute();
    $stmt->close();

    header("Location: categories.php");
    exit;
}
?>

<div class="container mt-4">
    <h3>Edit Category</h3>

    <form method="POST">
        <div class="mb-3">
            <label class="form-label">Category Name</label>
            <input type="text" class="form-control" name="name" value="<?= htmlspecialchars($category['name']) ?>" required>
        </div>

        <div class="form-check mb-3">
            <input class="form-check-input" type="checkbox" name="status" <?= $category['status'] ? 'checked' : '' ?>>
            <label class="form-check-label">Active</label>
        </div>

        <button class="btn btn-primary" name="submit">Update</button>
        <a href="categories.php" class="btn btn-secondary">Back</a>
    </form>
</div>