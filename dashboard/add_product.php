<?php
require 'inc/header.php';
require '../inc/db.php';

// Admin guard
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

$message = '';

// Fetch brands and categories
$brands = $conn->query("SELECT id, name FROM brands WHERE status=1 ORDER BY name");
$categories = $conn->query("SELECT id, name FROM categories WHERE status=1 ORDER BY name");

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

    $image = '';
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
        $stmt = $conn->prepare("INSERT INTO products (name, description, price, cost_price, qty, brand_id, category_id, featured, trending, discount_percent, image) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssddiiiiids", $name, $description, $price, $cost_price, $qty, $brand_id, $category_id, $featured, $trending, $discount, $image);
        if ($stmt->execute()) {
            $message = "<div class='alert alert-success alert-right'>Product added successfully!</div>";
        } else {
            $message = "<div class='alert alert-danger alert-right'>Error: " . $stmt->error . "</div>";
        }
        $stmt->close();
    }
}
?>

<style>
    .form-outline {
        position: relative;
    }

    .form-outline input,
    .form-outline textarea {
        height: 45px;
        border-radius: 5px;
        padding: 16px 12px;
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
        transition: 0.2s;
        pointer-events: none;
    }

    .form-outline input:focus+label,
    .form-outline input:not(:placeholder-shown)+label,
    .form-outline textarea:focus+label,
    .form-outline textarea:not(:placeholder-shown)+label {
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
</head>

<body>
    <?php if (!empty($message)) echo $message; ?>

    <div class="container mt-4">
        <div class="card shadow-sm mt-2 mb-3">
            <div class="card-body p-4 d-flex align-items-center">
                <h3 class="mb-0">Add New Product</h3>
                <a href="products.php" class="btn btn-secondary ms-auto">&larr; Back</a>
            </div>
        </div>

        <div class="card shadow-sm ">
            <div class="card-body p-4">
                <form method="POST" enctype="multipart/form-data">

                    <div class="form-outline mb-3">
                        <input id="name" class="form-control" name="name" placeholder=" " required>
                        <label for="name">Product Name</label>
                    </div>

                    <div class="form-outline mb-3">
                        <textarea id="description" class="form-control" name="description" placeholder=" " rows="5"></textarea>
                        <label for="description">Description</label>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3 form-outline">
                            <input id="price" class="form-control" type="number" step="0.01" name="price" placeholder=" " required>
                            <label for="price">Price ($)</label>
                        </div>
                        <div class="col-md-4 mb-3 form-outline">
                            <input id="cost_price" class="form-control" type="number" step="0.01" name="cost_price" placeholder=" ">
                            <label for="cost_price">Cost Price ($)</label>
                        </div>
                        <div class="col-md-4 mb-3 form-outline">
                            <input id="qty" class="form-control" type="number" name="qty" placeholder=" " value="1" required>
                            <label for="qty">Stock Quantity</label>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3 form-outline">
                            <select name="brand_id" class="form-select" required>
                                <option value="">Select Brand</option>
                                <?php while ($b = $brands->fetch_assoc()): ?>
                                    <option value="<?= $b['id'] ?>"><?= htmlspecialchars($b['name']) ?></option>
                                <?php endwhile; ?>
                            </select>
                            <label>Brand</label>
                        </div>
                        <div class="col-md-4 mb-3 form-outline">
                            <select name="category_id" class="form-select" required>
                                <option value="">Select Category</option>
                                <?php while ($c = $categories->fetch_assoc()): ?>
                                    <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
                                <?php endwhile; ?>
                            </select>
                            <label>Category</label>
                        </div>
                        <div class="col-md-4 mb-3 form-outline">
                            <input type="number" class="form-control" name="discount" step="0.01" placeholder=" " value="0">
                            <label>Discount (%)</label>
                        </div>
                    </div>

                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" name="featured" id="featured">
                        <label class="form-check-label" for="featured">Featured</label>
                    </div>
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" name="trending" id="trending">
                        <label class="form-check-label" for="trending">Trending</label>
                    </div>

                    <div class="mb-3">
                        <input id="image" class="form-control" type="file" name="image" accept="image/*" onchange="previewImage(event)">
                        <img id="preview" src="#" alt="Image Preview" class="img-fluid">
                    </div>

                    <button type="submit" class="btn btn-success" name="submit">Add Product</button>
                    <a href="products.php" class="btn btn-secondary ms-2">Back to Products</a>
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

        // Auto-hide alert after 3 seconds
        setTimeout(() => {
            document.querySelector('.alert-right')?.remove();
        }, 3000);
    </script>
    <?php include 'inc/footer.php'; ?>