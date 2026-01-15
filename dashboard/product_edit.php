<?php

require '../inc/db.php';
session_start();
// Admin guard
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

// Fetch brands and categories
$brands = $conn->query("SELECT id,name FROM brands WHERE status=1 ORDER BY name");
$categories = $conn->query("SELECT id,name FROM categories WHERE status=1 ORDER BY name");

// Fetch product data
$stmt = $conn->prepare("SELECT * FROM products WHERE id=?");
$stmt->bind_param("i", $id);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$product) {
    header("Location: products.php");
    exit;
}

// Handle form submission
if (isset($_POST['submit'])) {
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $price = floatval($_POST['price']);
    $cost_price = floatval($_POST['cost_price']);
    $qty = intval($_POST['qty']);
    $brand_id = intval($_POST['brand_id']);
    $category_id = intval($_POST['category_id']);
    $featured = isset($_POST['featured']) ? 1 : 0;
    $trending = isset($_POST['trending']) ? 1 : 0;
    $discount = floatval($_POST['discount']);

    $image = $product['image']; // Keep old image by default
    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];
        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $allowed)) {
            $message = "<div class='alert alert-danger alert-right'>Invalid image type!</div>";
        } else {
            $image = 'uploads/' . time() . '_' . basename($_FILES['image']['name']);
            move_uploaded_file($_FILES['image']['tmp_name'], '../' . $image);
        }
    }

    if (empty($message)) {
        $stmt = $conn->prepare("UPDATE products SET name=?, description=?, price=?, cost_price=?, qty=?, brand_id=?, category_id=?, featured=?, trending=?, discount_percent=?, image=? WHERE id=?");
        $stmt->bind_param("ssddiiiiidsi", $name, $description, $price, $cost_price, $qty, $brand_id, $category_id, $featured, $trending, $discount, $image, $id);
        if ($stmt->execute()) {
            $message = "<div class='alert alert-success alert-right'>Product updated successfully!</div>";
        } else {
            $message = "<div class='alert alert-danger alert-right'>Error: " . $stmt->error . "</div>";
        }
        $stmt->close();
    }
}

require 'inc/header.php';

?>

<style>
    .form-outline {
        position: relative;
    }

    .form-outline input,
    .form-outline textarea,
    .form-outline select {
        height: 45px;
        padding: 16px 12px;
        border-radius: 5px;
    }

    .form-outline label {
        position: absolute;
        top: 50%;
        left: 12px;
        transform: translateY(-50%);
        background: #fff;
        padding: 0 6px;
        color: #6c757d;
        font-size: 14px;
        pointer-events: none;
        transition: .2s;
    }

    .form-outline input:focus+label,
    .form-outline input:not(:placeholder-shown)+label,
    .form-outline textarea:focus+label,
    .form-outline textarea:not(:placeholder-shown)+label,
    .form-outline select:focus+label,
    .form-outline select:not(:placeholder-shown)+label {
        top: 0;
        font-size: 12px;
        color: #0d6efd;
    }

    .alert-right {
        position: fixed;
        top: 20px;
        right: 20px;
        min-width: 300px;
        z-index: 1055;
        border-radius: 8px;
    }

    #preview {
        display: block;
        max-height: 200px;
        margin-top: 10px;
    }
</style>


<?php if (!empty($message)) echo $message; ?>

<div class="container mt-4">
    <div class="card shadow-sm mb-3">
        <div class="card-body p-4 d-flex align-items-center">
            <h3 class="mb-0">Edit Product</h3>
            <a href="products.php" class="btn btn-secondary ms-auto">&larr; Back</a>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body p-4">
            <form method="POST" enctype="multipart/form-data">

                <div class="form-outline mb-3">
                    <input class="form-control" type="text" name="name" placeholder=" " value="<?= htmlspecialchars($product['name']) ?>" required>
                    <label>Product Name</label>
                </div>

                <div class="form-outline mb-3">
                    <textarea class="form-control" name="description" rows="4" placeholder=" "><?= htmlspecialchars($product['description']) ?></textarea>
                    <label>Description</label>
                </div>

                <div class="row">
                    <div class="col-md-4 mb-3 form-outline">
                        <input class="form-control" type="number" name="price" step="0.01" placeholder=" " value="<?= $product['price'] ?>" required>
                        <label>Price ($)</label>
                    </div>
                    <div class="col-md-4 mb-3 form-outline">
                        <input class="form-control" type="number" name="cost_price" step="0.01" placeholder=" " value="<?= $product['cost_price'] ?>">
                        <label>Cost Price ($)</label>
                    </div>
                    <div class="col-md-4 mb-3 form-outline">
                        <input class="form-control" type="number" name="qty" placeholder=" " value="<?= $product['qty'] ?>" required>
                        <label>Stock Qty</label>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4 mb-3 form-outline">
                        <select class="form-select" name="brand_id" required>
                            <?php $brands->data_seek(0);
                            while ($b = $brands->fetch_assoc()): ?>
                                <option value="<?= $b['id'] ?>" <?= $product['brand_id'] == $b['id'] ? 'selected' : '' ?>><?= htmlspecialchars($b['name']) ?></option>
                            <?php endwhile; ?>
                        </select>
                        <label>Brand</label>
                    </div>
                    <div class="col-md-4 mb-3 form-outline">
                        <select class="form-select" name="category_id" required>
                            <?php $categories->data_seek(0);
                            while ($c = $categories->fetch_assoc()): ?>
                                <option value="<?= $c['id'] ?>" <?= $product['category_id'] == $c['id'] ? 'selected' : '' ?>><?= htmlspecialchars($c['name']) ?></option>
                            <?php endwhile; ?>
                        </select>
                        <label>Category</label>
                    </div>
                    <div class="col-md-4 mb-3 form-outline">
                        <input class="form-control" type="number" name="discount" step="0.01" placeholder=" " value="<?= $product['discount_percent'] ?>">
                        <label>Discount (%)</label>
                    </div>
                </div>

                <div class="form-check mb-3">
                    <input type="checkbox" name="featured" class="form-check-input" id="featured" <?= $product['featured'] ? 'checked' : '' ?>>
                    <label class="form-check-label" for="featured">Featured</label>
                </div>
                <div class="form-check mb-3">
                    <input type="checkbox" name="trending" class="form-check-input" id="trending" <?= $product['trending'] ? 'checked' : '' ?>>
                    <label class="form-check-label" for="trending">Trending</label>
                </div>

                <div class="mb-3">
                    <input class="form-control" type="file" name="image" accept="image/*" class="form-control" onchange="previewImage(event)">
                    <?php if ($product['image']): ?>
                        <img id="preview" src="../<?= htmlspecialchars($product['image']) ?>" class="img-fluid" alt="Preview">
                    <?php else: ?>
                        <img id="preview" src="#" class="img-fluid" alt="Preview">
                    <?php endif; ?>
                </div>

                <button type="submit" class="btn btn-primary" name="submit">Update Product</button>
                <a href="products.php" class="btn btn-secondary ms-2">Back</a>
            </form>
        </div>
    </div>
</div>

<script>
    function previewImage(event) {
        var preview = document.getElementById('preview');
        preview.src = URL.createObjectURL(event.target.files[0]);
        preview.style.display = 'block';
    }
    setTimeout(() => {
        document.querySelector('.alert-right')?.remove();
    }, 3000);
</script>

<?php include 'inc/footer.php'; ?>