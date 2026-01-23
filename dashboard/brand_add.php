<?php
require '../inc/db.php';
session_start();

// Admin guard
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit;
}

$message = '';

if (isset($_POST['submit'])) {
    $name = trim($_POST['name']);
    $status = isset($_POST['status']) ? 1 : 0;

    if ($name) {
        $stmt = $conn->prepare("INSERT INTO brands(name,status) VALUES(?,?)");
        $stmt->bind_param("si", $name, $status);
        $stmt->execute();
        $stmt->close();

        header("Location: brands.php");
        exit;
    } else {
        $message = "Brand name is required!";
    }
}

require 'inc/header.php';
?>

<?php if ($message): ?>
    <div class="alert alert-danger"><?= $message ?></div>
<?php endif; ?>

<div class="px-2 mt-4 mb-5">
    <div class="card shadow-sm mb-3">
        <div class="card-body p-4 d-flex align-items-center">
            <h3 class="mb-0">Add Brand</h3>
            <a href="brands.php" class="btn btn-secondary ms-auto"><i class="bi bi-arrow-left me-2"></i>Back</a>
        </div>
    </div>
    <div class="card shadow-sm mb-4">
        <div class="card-body p-4">
            <form method="POST">

                <div class="form-outline mb-3">
                    <input class="form-control" type="text" name="name" placeholder=" " required>
                    <label>Brand Name</label>
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

<?php require 'inc/footer.php' ?>