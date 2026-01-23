<?php
session_start();
require '../inc/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

$stats = [];

$stats['today'] = $conn->query("
    SELECT COUNT(*) total FROM orders 
    WHERE DATE(created_at) = CURDATE()
")->fetch_assoc()['total'];

$stats['revenue'] = $conn->query("
    SELECT SUM(total_price) total 
    FROM orders 
    WHERE status='Paid'
")->fetch_assoc()['total'] ?? 0;


$stats['products'] = $conn->query("SELECT COUNT(*) total FROM products")->fetch_assoc()['total'];
$stats['blogs']    = $conn->query("SELECT COUNT(*) total FROM posts")->fetch_assoc()['total'];
$stats['users']    = $conn->query("SELECT COUNT(*) total FROM users")->fetch_assoc()['total'];
$stats['orders']   = $conn->query("SELECT COUNT(*) total FROM orders")->fetch_assoc()['total'];


// Fetch orders for today with user info
$sql = "
    SELECT o.id, u.email AS user_email, o.total_price, o.payment_method, o.status, o.created_at
    FROM orders o
    LEFT JOIN users u ON o.user_id = u.id
    WHERE DATE(o.created_at) = CURDATE()
    ORDER BY o.created_at DESC
";
$result = $conn->query($sql);

require 'inc/header.php';
?>


<div class="px-2 mt-1 mb-5">
    <div class="card shadow-sm mb-3">
        <div class="card-body p-4 d-flex align-items-center">
            <h3 class="mb-0">Dashboard Summary</h3>
        </div>
    </div>

    <div class="row">
        <!-- Left: Today's Orders Report -->
        <div class="col-md-8">
            <div class="card shadow-sm mb-3">
                <h5 class="p-3">Today's Orders Report (<?= date('Y-m-d') ?>)</h5>
                <div class="table-responsive">
                    <table class="table table-striped table-bordered">
                        <thead class="">
                            <tr>
                                <th>ID</th>
                                <th>User</th>
                                <th>Price</th>
                                <th>Payment</th>
                                <th>Status</th>
                                <th>Time</th>
                                <th class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($result->num_rows > 0): ?>
                                <?php while ($row = $result->fetch_assoc()): ?>
                                    <tr>
                                        <td><?= $row['id'] ?></td>
                                        <td><?= htmlspecialchars($row['user_email']) ?></td>
                                        <td><?= number_format($row['total_price'], 2) ?></td>
                                        <td><?= htmlspecialchars($row['payment_method']) ?></td>
                                        <td>
                                            <?php
                                            if ($row['status'] == 'Paid') echo '<span class="badge bg-success">Paid</span>';
                                            elseif ($row['status'] == 'Pending') echo '<span class="badge bg-warning text-dark">Pending</span>';
                                            else echo '<span class="badge bg-secondary">' . htmlspecialchars($row['status']) . '</span>';
                                            ?>
                                        </td>
                                        <td><?= date('H:i', strtotime($row['created_at'])) ?></td>
                                        <td class="text-center">
                                            <a href="order_details.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-info me-1">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="text-center">No orders today.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Right: Summary Cards -->
        <div class="col-md-4">
            <div class="card p-3 shadow d-flex flex-row align-items-center mb-3">
                <i class="bi bi-calendar-check-fill fs-1 text-danger me-3"></i>
                <div>
                    <h6 class="mb-0">Orders Today</h6>
                    <h3><?= $stats['today'] ?></h3>
                </div>
            </div>

            <div class="card p-3 shadow d-flex flex-row align-items-center mb-3">
                <i class="bi bi-bag-check-fill fs-1 text-primary me-3"></i>
                <div>
                    <h6 class="mb-0">Total Orders</h6>
                    <h3><?= $stats['orders'] ?></h3>
                </div>
            </div>

            <div class="card p-3 shadow d-flex flex-row align-items-center mb-3">
                <i class="bi bi-currency-dollar fs-1 text-success me-3"></i>
                <div>
                    <h6 class="mb-0">Total Revenue</h6>
                    <h3>$<?= number_format($stats['revenue'], 2) ?></h3>
                </div>
            </div>

            <div class="card p-3 shadow d-flex flex-row align-items-center mb-3">
                <i class="bi bi-people-fill fs-1 text-warning me-3"></i>
                <div>
                    <h6 class="mb-0">Total Users</h6>
                    <h3><?= $stats['users'] ?></h3>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'inc/footer.php'; ?>