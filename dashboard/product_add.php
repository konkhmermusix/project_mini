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
    $original_price = (float)$_POST['original_price'];
    $selling_price = (float)$_POST['selling_price'];
    $qty = (int)$_POST['qty'];
    $brand_id = (int)$_POST['brand_id'];
    $category_id = (int)$_POST['category_id'];
    $featured = isset($_POST['featured']) ? 1 : 0;
    $trending = isset($_POST['trending']) ? 1 : 0;
    $discount_percent = (float)$_POST['discount_percent'];
    $status = isset($_POST['status']) ? 1 : 0;

    /* Validation */
    if ($selling_price > $original_price) {
        $message = "<div class='alert alert-danger'>Selling price must be less than or equal to original price.</div>";
    } elseif ($discount_percent < 0 || $discount_percent > 100) {
        $message = "<div class='alert alert-danger'>Discount must be 0–100%</div>";
    }

    /* Image Upload */
    $image = null;
    if (!empty($_FILES['image']['name'])) {
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];
        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));

        if (!in_array($ext, $allowed)) {
            $message = "<div class='alert alert-danger'>Invalid image format</div>";
        } else {
            $newName = uniqid('product_', true) . '.' . $ext;
            $image = 'uploads/' . $newName;
            move_uploaded_file($_FILES['image']['tmp_name'], '../' . $image);
        }
    }

    /* Insert */
    if (empty($message)) {
        $stmt = $conn->prepare("
            INSERT INTO products
            (name, description, original_price, selling_price, qty, brand_id, category_id, featured, trending, discount_percent, image, status)
            VALUES (?,?,?,?,?,?,?,?,?,?,?,?)
        ");

        $stmt->bind_param(
            "ssddiiiiidsi",
            $name,
            $description,
            $original_price,
            $selling_price,
            $qty,
            $brand_id,
            $category_id,
            $featured,
            $trending,
            $discount_percent,
            $image,
            $status
        );

        if ($stmt->execute()) {
            header("Location: products.php?success=1");
            exit;
        } else {
            $message = "<div class='alert alert-danger'>DB Error: {$stmt->error}</div>";
        }
        $stmt->close();
    }
}
require 'inc/header.php';
?>

<?php if ($message) echo $message; ?>

<div class="px-2 mt-4 mb-5">
    <div class="card shadow-sm mb-3">
        <div class="card-body d-flex align-items-center">
            <h3 class="mb-0">Add Product</h3>
            <a href="products.php" class="btn btn-secondary ms-auto"><i class="bi bi-arrow-left me-2"></i>Back</a>
        </div>
    </div>

    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form method="POST" enctype="multipart/form-data">

                <div class="form-outline mb-3">
                    <input type="text" name="name" class="form-control" placeholder=" " required>
                    <label class="form-label">Product Name</label>
                </div>

                <div class="form-outline mb-3">
                    <textarea name="description" class="form-control" rows="4" placeholder=" "></textarea>
                    <label class="form-label">Description</label>
                </div>

                <div class="row mb-3">
                    <div class="form-outline col-md-4">
                        <input type="number" name="original_price" step="0.01" class="form-control" placeholder=" " required>
                        <label class="form-label">Original Price ($)</label>
                    </div>
                    <div class="form-outline col-md-4">
                        <input type="number" name="selling_price" step="0.01" class="form-control" placeholder=" " required>
                        <label class="form-label">Selling Price ($)</label>
                    </div>
                    <div class="form-outline col-md-4">
                        <input type="number" name="qty" class="form-control" placeholder=" " required>
                        <label class="form-label">Stock Qty</label>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="form-outline col-md-4">

                        <select name="brand_id" class="form-select" required>
                            <option value="">Select Brand</option>
                            <?php while ($b = $brands->fetch_assoc()): ?>
                                <option value="<?= $b['id'] ?>"><?= htmlspecialchars($b['name']) ?></option>
                            <?php endwhile; ?>
                        </select>
                        <label class="form-label">Brand</label>
                    </div>
                    <div class="form-outline col-md-4">

                        <select name="category_id" class="form-select" required>
                            <option value="">Select Category</option>
                            <?php while ($c = $categories->fetch_assoc()): ?>
                                <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
                            <?php endwhile; ?>
                        </select>
                        <label class="form-label">Category</label>
                    </div>
                    <div class="form-outline col-md-4">
                        <input type="number" name="discount_percent" step="0.01" class="form-control" placeholder=" ">
                        <label class="form-label">Discount (%)</label>
                    </div>
                </div>

                <div class="form-check mb-3">
                    <input type="checkbox" name="status" class="form-check-input" id="status" checked>
                    <label class="form-check-label" for="status">Active</label>
                </div>
                <div class="form-check mb-3">
                    <input type="checkbox" name="featured" class="form-check-input" id="featured">
                    <label class="form-check-label" for="featured">Featured</label>
                </div>
                <div class="form-check mb-3">
                    <input type="checkbox" name="trending" class="form-check-input" id="trending">
                    <label class="form-check-label" for="trending">Trending</label>
                </div>

                <div class="form-outline mb-3">
                    <input type="file" name="image" class="form-control" accept="image/*" onchange="previewImage(this)">
                    <img id="preview" class="img-fluid mt-2 rounded" style="max-height:200px;display:none;">
                </div>

                <button type="submit" name="submit" class="btn btn-success">Save Product</button>
            </form>
        </div>
    </div>
</div>

<script>
    function previewImage(input) {
        const preview = document.getElementById('preview');
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = e => {
                preview.src = e.target.result;
                preview.style.display = 'block';
            };
            reader.readAsDataURL(input.files[0]);
        }
    }
</script>


<?php require 'inc/footer.php'; ?>