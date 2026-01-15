<?php
require 'inc/header.php';
require '../inc/db.php';

// Admin guard
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit;
}

// Pagination
$limit = 5;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Total records
$totalResult = $conn->query("SELECT COUNT(*) AS total FROM slideshow");
$totalRecords = $totalResult->fetch_assoc()['total'];
$totalPages = ceil($totalRecords / $limit);

// Fetch slideshow
$sql = "SELECT * FROM slideshow ORDER BY position ASC LIMIT $limit OFFSET $offset";
$result = $conn->query($sql);

?>

<div class="px-2 mt-4 mb-5">
    <div class="d-flex align-items-center mb-3">
        <h2 class="me-auto">Slideshow</h2>
        <a href="slideshow_add.php" class="btn btn-primary mb-3">Add Slide</a>
    </div>
    <div class="table-responsive">
        <table class="table table-bordered table-hover align-middle">
            <thead class="table-dark">
                <tr>
                    <th width="60">ID</th>
                    <th>Title</th>
                    <th>Image</th>
                    <th width="120">Status</th>
                    <th width="180">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result->num_rows > 0): ?>
                    <?php while ($b = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= $b['id'] ?></td>
                            <td><?= htmlspecialchars($b['title']) ?></td>
                            <td>
                                <?php if (!empty($b['image'])): ?>
                                    <img src="../<?= htmlspecialchars($b['image']) ?>" style="width:70px;height:auto;" alt="<?= htmlspecialchars($row['title']) ?>">
                                <?php endif; ?>
                            </td>
                            <td>
                                <?= $b['status'] ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-secondary">Inactive</span>' ?>
                            </td>
                            <td>
                                <a href="slideshow_edit.php?id=<?= $b['id'] ?>" class="btn btn-primary btn-sm">Edit</a>
                                <a href="slideshow_delete.php?id=<?= $b['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?')">Delete</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4" class="text-center">No slideshow found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
        <nav>
            <ul class="pagination justify-content-center mt-3">

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

<?php include 'inc/footer.php'; ?>