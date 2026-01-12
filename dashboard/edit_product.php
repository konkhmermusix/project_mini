<?php
require '../inc/db.php';
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') die("Access denied.");

include 'inc/header.php';

$id = $_GET['id'];
$result = $conn->query("SELECT * FROM products WHERE id=$id");
$product = $result->fetch_assoc();

// Handle form submission
if (isset($_POST['submit'])) {
    $name = $_POST['name'];
    $description = $_POST['description'];
    $price = $_POST['price'];

    $image = $product['image']; // keep old image if not uploaded
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $image = 'uploads/' . time() . '_' . basename($_FILES['image']['name']);
        move_uploaded_file($_FILES['image']['tmp_name'], '../' . $image);
    }

    $sql = "UPDATE products SET name='$name', description='$description', price='$price', image='$image' WHERE id=$id";
    if ($conn->query($sql)) {
        echo "<div class='alert alert-success'>Product updated successfully!</div>";
    } else {
        echo "<div class='alert alert-danger'>Error: " . $conn->error . "</div>";
    }
}
?>

<div class="container mt-4">
    <div class="card shadow-sm">
        <div class="card-header bg-warning text-white">
            <h4 class="mb-0">Edit Product</h4>
        </div>
        <div class="card-body">
            <form method="POST" enctype="multipart/form-data">
                <div class="mb-3">
                    <label for="name" class="form-label">Product Name</label>
                    <input id="name" class="form-control" name="name" value="<?= $product['name'] ?>" required>
                </div>

                <div class="mb-3">
                    <label for="description" class="form-label">Description</label>
                    <textarea id="description" class="form-control" name="description" rows="4"><?= $product['description'] ?></textarea>
                </div>

                <div class="mb-3">
                    <label for="price" class="form-label">Price ($)</label>
                    <input id="price" class="form-control" type="number" step="0.01" name="price" value="<?= $product['price'] ?>" required>
                </div>

                <div class="mb-3">
                    <label for="image" class="form-label">Product Image</label>
                    <input id="image" class="form-control" type="file" name="image" accept="image/*" onchange="previewImage(event)">

                    <?php if ($product['image'] != ''): ?>
                        <img id="preview" src="../<?= $product['image'] ?>" alt="Image Preview" class="img-fluid mt-2" style="max-height:200px;">
                    <?php else: ?>
                        <img id="preview" src="#" alt="Image Preview" class="img-fluid mt-2" style="max-height:200px; display:none;">
                    <?php endif; ?>
                </div>

                <button type="submit" class="btn btn-primary" name="submit">Update Product</button>
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
</script>

<?php include 'inc/footer.php'; ?>