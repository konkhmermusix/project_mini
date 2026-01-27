<?php
require 'inc/header.php';
require '../inc/db.php';

// Admin Permission
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

// Pagination
$limit = 5;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Total records
$totalResult = $conn->query("SELECT COUNT(*) AS total FROM products WHERE status=1");
$totalRecords = $totalResult->fetch_assoc()['total'];
$totalPages = ceil($totalRecords / $limit);

// Fetch products with brand & category
$sql = "SELECT p.*, b.name AS brand_name, c.name AS category_name
        FROM products p
        LEFT JOIN brands b ON p.brand_id = b.id
        LEFT JOIN categories c ON p.category_id = c.id
        WHERE p.status=1
        ORDER BY p.id DESC
        LIMIT ? OFFSET ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $limit, $offset);
$stmt->execute();
$result = $stmt->get_result();
?>

<div class="px-2 mt-4 mb-5">
    <div class="card shadow-sm mb-3">
        <div class="card-body p-4 d-flex align-items-center">
            <h3 class="mb-0">Products</h3>
            <a class="btn btn-secondary ms-auto" href="product_add.php">
                <i class="bi bi-plus-circle me-2"></i>Add Product
            </a>
        </div>
    </div>

    <div class="card shadow-sm mb-3">
        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle">
                <thead>
                    <tr>
                        <th class="text-center">ID</th>
                        <th>Image</th>
                        <th>Name</th>
                        <th>Brand</th>
                        <th>Category</th>
                        <th>Price</th>
                        <th>Discount</th>
                        <th>Status</th>
                        <th class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td class="text-center"><?= $row['id'] ?></td>
                                <td>
                                    <?php if (!empty($row['image'])): ?>
                                        <img src="../<?= htmlspecialchars($row['image']) ?>" alt="<?= htmlspecialchars($row['name']) ?>"
                                            style="width:70px; height:auto; object-fit:cover;">
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($row['name']) ?></td>
                                <td><?= htmlspecialchars($row['brand_name'] ?? '-') ?></td>
                                <td><?= htmlspecialchars($row['category_name'] ?? '-') ?></td>
                                <td>
                                    $<?= number_format($row['selling_price'], 2) ?>
                                    <small class="text-muted">(<s>$<?= number_format($row['original_price'], 2) ?></s>)</small>
                                </td>
                                <td>
                                    <?= $row['discount_percent'] > 0 ? $row['discount_percent'] . '%' : '-' ?>
                                </td>
                                <td>
                                    <?= $row['status'] ? '<span class="badge bg-success">Active</span>'
                                        : '<span class="badge bg-secondary">Inactive</span>' ?>
                                </td>
                                <td class="text-center">
                                    <a class="btn btn-primary btn-sm" href="product_edit.php?id=<?= $row['id'] ?>">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>
                                    <a class="btn btn-danger btn-sm" href="product_delete.php?id=<?= $row['id'] ?>"
                                        onclick="return confirm('Are you sure you want to delete this product?')">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="9" class="text-center">No products found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
            <nav>
                <ul class="pagination justify-content-center">
                    <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
                        <a class="page-link" href="?page=<?= $page - 1 ?>">Previous</a>
                    </li>
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <li class="page-item <?= ($page == $i) ? 'active' : '' ?>">
                            <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                        </li>
                    <?php endfor; ?>
                    <li class="page-item <?= ($page >= $totalPages) ? 'disabled' : '' ?>">
                        <a class="page-link" href="?page=<?= $page + 1 ?>">Next</a>
                    </li>
                </ul>
            </nav>
        <?php endif; ?>
    </div>
</div>

<?php require 'inc/footer.php'; ?>