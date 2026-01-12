<?php
require 'inc/header.php'; // session_start() inside header
require '../inc/db.php';

// Only admin can access
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

// Fetch products with brand and category
$sql = "SELECT p.*, b.name AS brand_name, c.name AS category_name 
        FROM products p
        LEFT JOIN brands b ON p.brand_id = b.id
        LEFT JOIN categories c ON p.category_id = c.id
        ORDER BY p.id DESC";
$result = $conn->query($sql);
?>

<div class="container mt-4">
    <div class="d-flex align-items-center mb-3">
        <h2 class="me-auto">Products</h2>
        <a class="btn btn-success" href="add_product.php">Add Product</a>
    </div>

    <div class="table-responsive">
        <table class="table table-bordered table-hover align-middle">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Image</th>
                    <th>Name</th>
                    <th>Brand</th>
                    <th>Category</th>
                    <th>Price</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= $row['id'] ?></td>
                            <td>
                                <?php if (!empty($row['image'])): ?>
                                    <img src="../<?= htmlspecialchars($row['image']) ?>" style="width:70px; height:auto;" alt="<?= htmlspecialchars($row['name']) ?>">
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($row['name']) ?></td>
                            <td><?= htmlspecialchars($row['brand_name'] ?? '-') ?></td>
                            <td><?= htmlspecialchars($row['category_name'] ?? '-') ?></td>
                            <td>$<?= number_format($row['price'], 2) ?></td>
                            <td>
                                <?php if ($row['status'] == 1): ?>
                                    <span class="badge bg-success">Active</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Inactive</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a class="btn btn-primary btn-sm" href="edit_product.php?id=<?= $row['id'] ?>">Edit</a>
                                <a class="btn btn-danger btn-sm" href="delete_product.php?id=<?= $row['id'] ?>" onclick="return confirm('Are you sure you want to delete this product?')">Delete</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8" class="text-center">No products found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include 'inc/footer.php'; ?>