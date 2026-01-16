<?php

require '../inc/db.php';
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

$message = '';
$brands = $conn->query("SELECT id,name FROM brands WHERE status=1 ORDER BY name");
$categories = $conn->query("SELECT id,name FROM categories WHERE status=1 ORDER BY name");

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

    $image = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];
        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $allowed)) $message = "<div class='alert alert-danger alert-right'>Invalid image type!</div>";
        else {
            $image = 'uploads/' . time() . '_' . basename($_FILES['image']['name']);
            move_uploaded_file($_FILES['image']['tmp_name'], '../' . $image);
        }
    }

    if (empty($message)) {
        $stmt = $conn->prepare("INSERT INTO products (name, description, price, cost_price, qty, brand_id, category_id, featured, trending, discount_percent, image) VALUES (?,?,?,?,?,?,?,?,?,?,?)");
        $stmt->bind_param("ssddiiiiids", $name, $description, $price, $cost_price, $qty, $brand_id, $category_id, $featured, $trending, $discount, $image);
        $stmt->execute();
        $stmt->close();
        header("Location: products.php");
        exit;
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
        display: none;
        max-height: 200px;
        margin-top: 10px;
    }
</style>

<?php if (!empty($message)) echo $message; ?>

<div class="container mt-4">
    <div class="card shadow-sm mb-3">
        <div class="card-body p-4 d-flex align-items-center">
            <h3 class="mb-0">Add Product</h3>
            <a href="products.php" class="btn btn-secondary ms-auto">&larr; Back</a>
        </div>
    </div>
    <div class="card shadow-sm mb-4">
        <div class="card-body p-4">
            <form method="POST" enctype="multipart/form-data">

                <div class="form-outline mb-3">
                    <input class="form-control" type="text" name="name" placeholder=" " required>
                    <label>Product Name</label>
                </div>

                <div class="form-outline mb-3">
                    <textarea class="form-control" name="description" rows="4" placeholder=" "></textarea>
                    <label>Description</label>
                </div>

                <div class="row">
                    <div class="col-md-4 mb-3 form-outline">
                        <input class="form-control" type="number" name="price" step="0.01" placeholder=" " required>
                        <label>Price ($)</label>
                    </div>
                    <div class="col-md-4 mb-3 form-outline">
                        <input class="form-control" type="number" name="cost_price" step="0.01" placeholder=" ">
                        <label>Cost Price ($)</label>
                    </div>
                    <div class="col-md-4 mb-3 form-outline">
                        <input class="form-control" type="number" name="qty" placeholder=" " required>
                        <label>Stock Qty</label>
                    </div>
                </div>

                <div class="row">

                    <!-- Brand -->
                    <div class="col-md-4 mb-3 form-outline">
                        <select class="form-select" name="brand_id" required>
                            <option value="">Select Brand</option>
                            <?php
                            $brands->data_seek(0); // Reset pointer
                            while ($b = $brands->fetch_assoc()): ?>
                                <option value="<?= $b['id'] ?>" <?= ($product['brand_id'] ?? '') == $b['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($b['name']) ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                        <label class="form-label">Brand</label>
                    </div>

                    <!-- Category -->
                    <div class="col-md-4 mb-3 form-outline">
                        <select class="form-select" name="category_id" required>
                            <option value="">Select Category</option>
                            <?php
                            $categories->data_seek(0); // Reset pointer
                            while ($c = $categories->fetch_assoc()): ?>
                                <option value="<?= $c['id'] ?>" <?= ($product['category_id'] ?? '') == $c['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($c['name']) ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                        <label class="form-label">Category</label>
                    </div>

                    <!-- Discount -->
                    <div class="col-md-4 mb-3 form-outline">
                        <input class="form-control" type="number" name="discount" step="0.01"
                            value="<?= htmlspecialchars($product['discount_percent'] ?? '') ?>" placeholder=" ">
                        <label class="form-label">Discount (%)</label>
                    </div>

                </div>


                <div class="form-check mb-3">
                    <input type="checkbox" name="featured" class="form-check-input" id="featured">
                    <label class="form-check-label" for="featured">Featured</label>
                </div>
                <div class="form-check mb-3">
                    <input type="checkbox" name="trending" class="form-check-input" id="trending">
                    <label class="form-check-label" for="trending">Trending</label>
                </div>

                <div class="mb-3">
                    <input class="form-control" type="file" name="image" accept="image/*" class="form-control" onchange="previewImage(event)">
                    <img id="preview" src="#" alt="Image Preview" class="img-fluid">
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