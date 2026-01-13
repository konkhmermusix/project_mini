<?php
require '../inc/db.php';
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit;
}

$id = (int)$_GET['id'];
$brand = $conn->query("SELECT * FROM brands WHERE id=$id")->fetch_assoc();

if (!$brand) die("Brand not found");

if (isset($_POST['submit'])) {
    $name = trim($_POST['name']);
    $status = isset($_POST['status']) ? 1 : 0;

    $stmt = $conn->prepare("UPDATE brands SET name=?, status=? WHERE id=?");
    $stmt->bind_param("sii", $name, $status, $id);
    $stmt->execute();
    $stmt->close();

    header("Location: brands.php"); 
    exit;
}
require 'inc/header.php';
?>

<div class="container mt-4">
    <h3>Edit Brand</h3>

    <form method="POST">
        <div class="mb-3">
            <label class="form-label">Brand Name</label>
            <input type="text" class="form-control" name="name" value="<?= htmlspecialchars($brand['name']) ?>" required>
        </div>
        <div class="form-check mb-3">
            <input class="form-check-input" type="checkbox" name="status" <?= $brand['status'] ? 'checked' : '' ?>>
            <label class="form-check-label">Active</label>
        </div>
        <button class="btn btn-primary" name="submit">Update</button>
        <a href="brands.php" class="btn btn-secondary">Back</a>
    </form>
</div>