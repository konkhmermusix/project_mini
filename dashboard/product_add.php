<?php
require '../inc/db.php';
session_start();

// Admin Permission
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

$message = '';

// Fetch Brands & Categories
$brands = $conn->query("SELECT id,name FROM brands WHERE status=1 ORDER BY name");
$categories = $conn->query("SELECT id,name FROM categories WHERE status=1 ORDER BY name");

// Handle Form Submission
if (isset($_POST['submit'])) {
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $original_price = floatval($_POST['original_price']);
    $selling_price = floatval($_POST['selling_price']);
    $qty = intval($_POST['qty']);
    $brand_id = intval($_POST['brand_id']);
    $category_id = intval($_POST['category_id']);
    $featured = isset($_POST['featured']) ? 1 : 0;
    $trending = isset($_POST['trending']) ? 1 : 0;
    $discount_percent = floatval($_POST['discount_percent']);
    $status = isset($_POST['status']) ? 1 : 0;

    // Validation
    if ($selling_price < $original_price) {
        $message = "<div class='alert alert-danger'>Selling price must be >= original price.</div>";
    } elseif ($discount_percent < 0 || $discount_percent > 100) {
        $message = "<div class='alert alert-danger'>Discount percent must be between 0 and 100.</div>";
    }

    // Handle Image Upload
    $image = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];
        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $allowed)) {
            $message = "<div class='alert alert-danger'>Invalid image type!</div>";
        } else {
            $image = 'uploads/' . time() . '_' . basename($_FILES['image']['name']);
            move_uploaded_file($_FILES['image']['tmp_name'], '../' . $image);
        }
    }

    // Insert into DB
    if (empty($message)) {
        $stmt = $conn->prepare("INSERT INTO products (name, description, original_price, selling_price, qty, brand_id, category_id, featured, trending, discount_percent, image, status) VALUES (?,?,?,?,?,?,?,?,?,?,?,?)");
        $stmt->bind_param("ssddiiiiiids", $name, $description, $original_price, $selling_price, $qty, $brand_id, $category_id, $featured, $trending, $discount_percent, $image, $status);
        $stmt->execute();
        $stmt->close();
        header("Location: products.php");
        exit;
    }
}

require 'inc/header.php';
?>

<div class="px-2 mt-4 mb-5">
    <div class="card shadow-sm mb-3">
        <div class="card-body d-flex align-items-center">
            <h3 class="mb-0">Add Product</h3>
            <a href="products.php" class="btn btn-secondary ms-auto"><i class="bi bi-arrow-left me-2"></i>Back</a>
        </div>
    </div>

    <?php if ($message) echo $message; ?>

    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form method="POST" enctype="multipart/form-data">

                <div class="mb-3">
                    <label class="form-label">Product Name</label>
                    <input type="text" name="name" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control" rows="4"></textarea>
                </div>

                <div class="row mb-3">
                    <div class="col-md-4">
                        <label class="form-label">Original Price ($)</label>
                        <input type="number" name="original_price" step="0.01" class="form-control" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Selling Price ($)</label>
                        <input type="number" name="selling_price" step="0.01" class="form-control" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Stock Qty</label>
                        <input type="number" name="qty" class="form-control" required>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-4">
                        <label class="form-label">Brand</label>
                        <select name="brand_id" class="form-select" required>
                            <option value="">Select Brand</option>
                            <?php while ($b = $brands->fetch_assoc()): ?>
                                <option value="<?= $b['id'] ?>"><?= htmlspecialchars($b['name']) ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Category</label>
                        <select name="category_id" class="form-select" required>
                            <option value="">Select Category</option>
                            <?php while ($c = $categories->fetch_assoc()): ?>
                                <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Discount (%)</label>
                        <input type="number" name="discount_percent" step="0.01" class="form-control">
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
                    <label class="form-label">Product Image</label>
                    <input type="file" name="image" class="form-control" accept="image/*" onchange="previewImage(event)">
                    <img id="preview" src="#" alt="Preview" class="img-fluid mt-2" style="max-height:200px; display:none;">
                </div>

                <div class="form-check mb-3">
                    <input type="checkbox" name="status" class="form-check-input" id="status" checked>
                    <label class="form-check-label" for="status">Active</label>
                </div>

                <button type="submit" name="submit" class="btn btn-success">Save Product</button>
            </form>
        </div>
    </div>
</div>

<script>
    function previewImage(event) {
        const preview = document.getElementById('preview');
        preview.src = URL.createObjectURL(event.target.files[0]);
        preview.style.display = 'block';
    }
    setTimeout(() => {
        document.querySelector('.alert')?.remove();
    }, 3000);
</script>

<?php require 'inc/footer.php'; ?>