<?php
require '../inc/db.php';
session_start();

/* =========================
   Admin Guard
========================= */
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

$message = '';
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id <= 0) {
    header("Location: products.php");
    exit;
}

/* =========================
   Fetch Brands & Categories
========================= */
$brands = $conn->query("SELECT id,name FROM brands WHERE status=1 ORDER BY name");
$categories = $conn->query("SELECT id,name FROM categories WHERE status=1 ORDER BY name");

/* =========================
   Fetch Product
========================= */
$stmt = $conn->prepare("SELECT * FROM products WHERE id=?");
$stmt->bind_param("i", $id);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$product) {
    header("Location: products.php");
    exit;
}

/* =========================
   Handle Update
========================= */
if (isset($_POST['submit'])) {

    $name          = trim($_POST['name']);
    $description   = trim($_POST['description']);
    $selling_price = floatval($_POST['selling_price']);
    $original_price = floatval($_POST['original_price']);
    $qty           = intval($_POST['qty']);
    $brand_id      = intval($_POST['brand_id']);
    $category_id   = intval($_POST['category_id']);
    $discount      = floatval($_POST['discount']);
    $featured      = isset($_POST['featured']) ? 1 : 0;
    $trending      = isset($_POST['trending']) ? 1 : 0;
    $status        = isset($_POST['status']) ? 1 : 0;

    /* =========================
       Image Upload
    ========================= */
    $image = $product['image'];

    if (!empty($_FILES['image']['name'])) {
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];
        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));

        if (!in_array($ext, $allowed)) {
            $message = "<div class='alert alert-danger alert-right'>Invalid image type!</div>";
        } else {
            $image = 'uploads/' . time() . '_' . basename($_FILES['image']['name']);
            move_uploaded_file($_FILES['image']['tmp_name'], '../' . $image);
        }
    }

    /* =========================
       Update DB
    ========================= */
    if (empty($message)) {

        $stmt = $conn->prepare("
            UPDATE products SET
                name=?,
                description=?,
                original_price=?,
                selling_price=?,
                qty=?,
                brand_id=?,
                category_id=?,
                featured=?,
                trending=?,
                discount_percent=?,
                status=?,
                image=?
            WHERE id=?
        ");

        $stmt->bind_param(
            "ssddiiiiidisi",
            $name,
            $description,
            $original_price,
            $selling_price,
            $qty,
            $brand_id,
            $category_id,
            $featured,
            $trending,
            $discount,
            $status,
            $image,
            $id
        );

        if ($stmt->execute()) {
            $message = "<div class='alert alert-success alert-right'>Product updated successfully!</div>";
        } else {
            $message = "<div class='alert alert-danger alert-right'>" . $stmt->error . "</div>";
        }

        $stmt->close();
    }
}

require 'inc/header.php';
?>

<?php if (!empty($message)) echo $message; ?>

<div class="px-2 mt-4 mb-5">
    <div class="card shadow-sm mb-3">
        <div class="card-body d-flex align-items-center">
            <h3 class="mb-0">Edit Product</h3>
            <a href="products.php" class="btn btn-secondary ms-auto"><i class="bi bi-arrow-left me-2"></i>Back</a>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <form method="POST" enctype="multipart/form-data">

                <input class="form-control mb-3" name="name" value="<?= htmlspecialchars($product['name']) ?>" required>

                <textarea class="form-control mb-3" name="description"><?= htmlspecialchars($product['description']) ?></textarea>

                <div class="row">
                    <div class="col-md-4">
                        <input class="form-control mb-3" type="number" step="0.01" name="selling_price" value="<?= $product['selling_price'] ?>" required>
                    </div>
                    <div class="col-md-4">
                        <input class="form-control mb-3" type="number" step="0.01" name="original_price" value="<?= $product['original_price'] ?>">
                    </div>
                    <div class="col-md-4">
                        <input class="form-control mb-3" type="number" name="qty" value="<?= $product['qty'] ?>">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <select class="form-select mb-3" name="brand_id">
                            <?php while ($b = $brands->fetch_assoc()): ?>
                                <option value="<?= $b['id'] ?>" <?= $b['id'] == $product['brand_id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($b['name']) ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <select class="form-select mb-3" name="category_id">
                            <?php while ($c = $categories->fetch_assoc()): ?>
                                <option value="<?= $c['id'] ?>" <?= $c['id'] == $product['category_id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($c['name']) ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <input class="form-control mb-3" type="number" step="0.01" name="discount" value="<?= $product['discount_percent'] ?>">
                    </div>
                </div>

                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="featured" <?= $product['featured'] ? 'checked' : '' ?>>
                    <label class="form-check-label">Featured</label>
                </div>

                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="trending" <?= $product['trending'] ? 'checked' : '' ?>>
                    <label class="form-check-label">Trending</label>
                </div>

                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" name="status" <?= $product['status'] ? 'checked' : '' ?>>
                    <label class="form-check-label">Active</label>
                </div>

                <input class="form-control mb-3" type="file" name="image">

                <button class="btn btn-primary" name="submit">Update Product</button>

            </form>
        </div>
    </div>
</div>

<?php require 'inc/footer.php'; ?>