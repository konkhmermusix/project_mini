<?php
require 'inc/header.php'; // session_start() inside header
require '../inc/db.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}
// Fetch products
$result = $conn->query("SELECT * FROM products");
?>
<div class="container">
    <div class="d-flex">
        <h2>Products</h2>
        <a class="btn btn-success mb-3 ms-auto" href="add_product.php">Add Product</a>
    </div>

    <div class="col-md-12">
        <div class="mb-3">
            <table class="table table-bordered table-responsive">
                <tr>
                    <th>ID</th>
                    <th>Image</th>
                    <th>Name</th>
                    <th>Price</th>
                    <th>Action</th>
                </tr>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= $row['id'] ?></td>
                        <td>
                            <?php if ($row['image'] != ''): ?>
                                <img src="../<?= $row['image'] ?>" style="width:70px; height:auto;">
                            <?php endif; ?>
                        </td>
                        <td><?= $row['name'] ?></td>
                        <td><?= $row['price'] ?></td>
                        <td>
                            <a class="btn btn-primary btn-sm" href="edit_product.php?id=<?= $row['id'] ?>">Edit</a>
                            <a class="btn btn-danger btn-sm" href="delete_product.php?id=<?= $row['id'] ?>" onclick="return confirm('Are you sure?')">Delete</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </table>
        </div>
    </div>
</div>

<?php include 'inc/footer.php'; ?>