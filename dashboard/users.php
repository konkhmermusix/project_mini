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

$sql = "SELECT * FROM users ORDER BY id DESC LIMIT $limit OFFSET $offset";
$result = $conn->query($sql);
?>


<div class="px-2 mt-4 mb-5">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="me-auto">Users</h3>
        <a class="btn btn-success" href="add_blog.php">Add User</a>
    </div>

    <table class="table table-bordered table-hover">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Username</th>
                <th>Email</th>
                <th>Role</th>
                <th>Status</th>
                <th width="180">Action</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($u = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= $u['id'] ?></td>
                    <td><?= htmlspecialchars($u['username']) ?></td>
                    <td><?= htmlspecialchars($u['email']) ?></td>
                    <td>
                        <span class="badge bg-info"><?= $u['role'] ?></span>
                    </td>
                    <td>
                        <?= $u['status'] ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-secondary">Inactive</span>' ?>
                    </td>
                    <td>
                        <a href="user_edit.php?id=<?= $u['id'] ?>" class="btn btn-primary btn-sm">Edit</a>
                        <a href="user_delete.php?id=<?= $u['id'] ?>" class="btn btn-danger btn-sm"
                            onclick="return confirm('Delete this user?')">Delete</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

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