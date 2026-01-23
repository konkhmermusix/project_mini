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

<div class="px-2 mt-4 mb-5">
    <div class="card shadow-sm mb-3">
        <div class="card-body p-4 d-flex align-items-center">
            <h3 class="mb-0">Edit Brand</h3>
            <a href="brands.php" class="btn btn-secondary ms-auto"><i class="bi bi-arrow-left me-2"></i>Back</a>
        </div>
    </div>
    <div class="card shadow-sm mb-4">
        <div class="card-body p-4">
            <form method="POST">

                <div class="form-outline mb-3">
                    <input class="form-control" type="text" name="name" placeholder=" " value="<?= htmlspecialchars($brand['name']) ?>" required>
                    <label>Brand Name</label>
                </div>

                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" name="status" <?= $brand['status'] ? 'checked' : '' ?>>
                    <label class="form-check-label">Active</label>
                </div>
                <button class="btn btn-primary" name="submit">Update</button>
            </form>
        </div>
    </div>
</div>
<?php require 'inc/footer.php'; ?>