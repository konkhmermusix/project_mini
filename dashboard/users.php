<?php
require 'inc/header.php';
require '../inc/db.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

$limit = 5;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

$total = $conn->query("SELECT COUNT(*) AS t FROM users")->fetch_assoc()['t'];
$totalPages = ceil($total / $limit);

$sql = "SELECT * FROM users WHERE status = 1 ORDER BY id DESC LIMIT $limit OFFSET $offset";
$result = $conn->query($sql);
?>


<div class="px-2 mt-4 mb-5">
    <div class="card shadow-sm mb-3">
        <div class="card-body p-4 d-flex align-items-center">
            <h3 class="mb-0">Users</h3>
            <a class="btn btn-secondary ms-auto" href="user_add.php"><i class="bi bi-plus-circle me-2"></i>Add</a>
        </div>
    </div>

    <div class="card shadow-sm mb-3">
        <div class="table-responsive">
            <table class="table table-bordered table-hover table-active align-middle">
                <thead class="">
                    <tr>
                        <th class="text-center">ID</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th class="text-center" width="180">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($u = $result->fetch_assoc()): ?>
                        <tr>
                            <td class="text-center"><?= $u['id'] ?></td>
                            <td><?= htmlspecialchars($u['username']) ?></td>
                            <td><?= htmlspecialchars($u['email']) ?></td>
                            <td>
                                <span class="badge bg-info"><?= $u['role'] ?></span>
                            </td>
                            <td>
                                <?= $u['status'] ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-secondary">Inactive</span>' ?>
                            </td>
                            <td class="text-center">
                                <a href="user_edit.php?id=<?= $u['id'] ?>" class="btn btn-primary btn-sm"><i class="bi bi-pencil-square"></i></a>
                                <a href="user_delete.php?id=<?= $u['id'] ?>" class="btn btn-danger btn-sm"
                                    onclick="return confirm('Delete this user?')"><i class="bi bi-trash"></i></a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>

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
</div>

<?php require 'inc/footer.php'; ?>