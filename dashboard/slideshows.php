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
$sql = "SELECT * FROM slideshow WHERE status = 1 ORDER BY position ASC LIMIT $limit OFFSET $offset";
$result = $conn->query($sql);

?>

<div class="px-2 mt-4 mb-5">
    <div class="card shadow-sm mb-3">
        <div class="card-body p-4 d-flex align-items-center">
            <h3 class="mb-0">Slideshow</h3>
            <a class="btn btn-secondary ms-auto" href="slideshow_add.php"><i class="bi bi-plus-circle me-2"></i>Add</a>
        </div>
    </div>

    <div class="card shadow-sm mb-3">
        <div class="table-responsive">
            <table class="table table-bordered table-hover table-active align-middle">
                <thead class="">
                    <tr>
                        <th class="text-center" width="60">ID</th>
                        <th>Image</th>
                        <th>Title</th>
                        <th>Description</th>
                        <th width="120">Status</th>
                        <th class="text-center" width="180">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result->num_rows > 0): ?>
                        <?php while ($b = $result->fetch_assoc()): ?>
                            <tr>
                                <td class="text-center"><?= $b['id'] ?></td>

                                <td>
                                    <?php if (!empty($b['image'])): ?>
                                        <img src="../<?= htmlspecialchars($b['image']) ?>" style="width:70px;height:auto;" alt="<?= htmlspecialchars($row['title']) ?>">
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($b['title']) ?></td>
                                <td><?= htmlspecialchars($b['description']) ?></td>
                                <td>
                                    <?= $b['status'] ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-secondary">Inactive</span>' ?>
                                </td>
                                <td class="text-center">
                                    <a href="slideshow_edit.php?id=<?= $b['id'] ?>" class="btn btn-primary btn-sm"><i class="bi bi-pencil-square"></i></a>
                                    <a href="slideshow_delete.php?id=<?= $b['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?')"><i class="bi bi-trash"></i></a>
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