<?php
require 'inc/header.php';
require '../inc/db.php';

// Only admin can access
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

// pagination settings
$limit = 5;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? $_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// total records
$totalResult = $conn->query("SELECT COUNT(*) AS total FROM categories WHERE status = 1");
$totalRow = $totalResult->fetch_assoc();
$totalRecords = $totalRow['total'];
$totalPages = ceil($totalRecords / $limit);
$totalPages = ceil($totalRecords / $limit);

// Fetch categories with pagination
$sql = "SELECT * 
        FROM categories
        WHERE status = 1
        ORDER BY id DESC
        LIMIT $limit OFFSET $offset";

$result = $conn->query($sql);
?>

<div class="px-2 mt-4 mb-5">
    <div class="card shadow-sm mb-3">
        <div class="card-body p-4 d-flex align-items-center">
            <h3 class="mb-0">Category</h3>
            <a class="btn btn-success ms-auto" href="categories_add.php"><i class="bi bi-plus-circle me-2"></i>Add</a>
        </div>
    </div>

    <div class="card shadow-sm mb-3">
        <div class="table-responsive">
            <table class="table table-bordered table-hover table-active align-middle">
                <thead class="">
                    <tr>
                        <th class="text-center" width="80">ID</th>
                        <th>Name</th>
                        <th width=120">Status</th>
                        <th class="text-center" width="180">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td class="text-center"><?= $row['id'] ?></td>
                                <td><?= htmlspecialchars($row['name']) ?></td>
                                <td>
                                    <?php if ($row['status'] == 1): ?>
                                        <span class="badge bg-success">Active</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Inactive</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <a class="btn btn-primary btn-sm" href="categories_edit.php?id=<?= $row['id'] ?>"><i class="bi bi-pencil-square"></i></a>
                                    <a class="btn btn-danger btn-sm" href="categories_delete.php?id=<?= $row['id'] ?>" onclick="return confirm('Are you sure you want to delete this product?')"><i class="bi bi-trash"></i></a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="text-center">No categories found.</td>
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
                        <a class="page-link" href="?page=<?= $page - 1  ?>"> Previous</a>
                    </li>

                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <li class="page-item <?= ($page == $i) ? 'active' : '' ?>">
                            <a class="page-link" href="?page=<? $i ?>"><?= $i ?></a>
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

<?php include 'inc/footer.php'; ?>