<?php
session_start();
require '../inc/db.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

// Filters
$search = $_GET['search'] ?? '';
$status_filter = $_GET['status'] ?? '';
$start_date = $_GET['start_date'] ?? '';
$end_date = $_GET['end_date'] ?? '';

// Pagination
$limit = 10;
$page = max(1, (int)($_GET['page'] ?? 1));
$offset = ($page - 1) * $limit;

// Build SQL WHERE conditions
$where = "WHERE 1=1";
$params = [];
$types = '';

if ($search) {
    $where .= " AND u.email LIKE ?";
    $params[] = "%$search%";
    $types .= 's';
}
if ($status_filter) {
    $where .= " AND o.status = ?";
    $params[] = $status_filter;
    $types .= 's';
}
if ($start_date) {
    $where .= " AND DATE(o.created_at) >= ?";
    $params[] = $start_date;
    $types .= 's';
}
if ($end_date) {
    $where .= " AND DATE(o.created_at) <= ?";
    $params[] = $end_date;
    $types .= 's';
}

// Total Orders Count for Pagination
$count_sql = "SELECT COUNT(*) AS total FROM orders o LEFT JOIN users u ON o.user_id = u.id $where";
$stmt_count = $conn->prepare($count_sql);
if ($params) $stmt_count->bind_param($types, ...$params);
$stmt_count->execute();
$total_orders = $stmt_count->get_result()->fetch_assoc()['total'];
$total_pages = ceil($total_orders / $limit);

// Fetch orders with limit & offset
$sql = "
    SELECT o.id, u.email AS user_email, o.total_price, o.payment_method, o.status, o.created_at
    FROM orders o
    LEFT JOIN users u ON o.user_id = u.id
    $where
    ORDER BY o.created_at DESC
    LIMIT ?, ?
";
$stmt = $conn->prepare($sql);

$types_with_limit = $types . 'ii';
$bind_params = array_merge($params, [$offset, $limit]);

$tmp = [];
foreach ($bind_params as $key => $value) {
    $tmp[$key] = &$bind_params[$key];
}

$stmt->bind_param($types_with_limit, ...$tmp);

$stmt->execute();
$result = $stmt->get_result();

// Monthly Summary (current month)
$summary_sql = "
    SELECT 
        COUNT(*) AS total_orders,
        SUM(total_price) AS total_revenue
    FROM orders
    WHERE MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE())
";
$summary = $conn->query($summary_sql)->fetch_assoc();

require 'inc/header.php';
?>

<div class="px-2 mt-4 mb-5">
    <div class="card shadow-sm mb-3">
        <div class="card-body p-4 d-flex align-items-center">
            <h3 class="mb-0">Orders Report</h3>
        </div>
    </div>

    <div class="card shadow-sm mb-3">
        <form class="row g-3  p-4" method="GET">
            <div class="col-md-3">
                <input type="text" name="search" class="form-control" placeholder="Search by User Email" value="<?= htmlspecialchars($search) ?>">
            </div>
            <div class="col-md-2">
                <select name="status" class="form-select">
                    <option value="">All Status</option>
                    <option value="Paid" <?= $status_filter == 'Paid' ? 'selected' : '' ?>>Paid</option>
                    <option value="Pending" <?= $status_filter == 'Pending' ? 'selected' : '' ?>>Pending</option>
                    <option value="Cancelled" <?= $status_filter == 'Cancelled' ? 'selected' : '' ?>>Cancelled</option>
                </select>
            </div>
            <div class="col-md-2">
                <input type="date" name="start_date" class="form-control" value="<?= $start_date ?>">
            </div>
            <div class="col-md-2">
                <input type="date" name="end_date" class="form-control" value="<?= $end_date ?>">
            </div>
            <div class="col-md-3">
                <button class="btn btn-primary" type="submit">Filter</button>
                <a href="orders_report.php" class="btn btn-secondary">Reset</a>
            </div>
        </form>
    </div>

    <div class="card shadow-sm mb-3">
        <div class="table-responsive">
            <table class="table table-bordered table-hover table-active align-middle">
                <thead class="">
                    <tr>
                        <th class="text-center">ID</th>
                        <th>User</th>
                        <th>Total ($)</th>
                        <th>Payment</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result->num_rows > 0): ?>
                        <?php while ($order = $result->fetch_assoc()): ?>
                            <tr>
                                <td class="text-center"><?= $order['id'] ?></td>
                                <td><?= htmlspecialchars($order['user_email']) ?></td>
                                <td><?= number_format($order['total_price'], 2) ?></td>
                                <td><?= htmlspecialchars($order['payment_method']) ?></td>
                                <td>
                                    <?php
                                    $status_class = '';
                                    switch ($order['status']) {
                                        case 'Paid':
                                            $status_class = 'text-success';
                                            break;
                                        case 'Pending':
                                            $status_class = 'text-warning';
                                            break;
                                        case 'Cancelled':
                                            $status_class = 'text-danger';
                                            break;
                                    }
                                    ?>
                                    <span class="<?= $status_class ?>"><?= $order['status'] ?></span>
                                </td>
                                <td><?= $order['created_at'] ?></td>
                                <td class="text-center">
                                    <a href="order_details.php?id=<?= $order['id'] ?>" class="btn btn-sm btn-info me-1">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="delete_order.php?id=<?= $order['id'] ?>"
                                        onclick="return confirm('Are you sure you want to delete this order?');"
                                        class="btn btn-sm btn-danger">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                </td>

                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center">No orders found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <nav>
            <ul class="pagination justify-content-center">
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                        <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>"><?= $i ?></a>
                    </li>
                <?php endfor; ?>
            </ul>
        </nav>
    </div>
    <div class="card shadow-sm mb-3">
        <div class="p-3 bg-light border rounded">
            <h5>Monthly Summary (<?= date('F Y') ?>)</h5>
            <p>Total Orders: <strong><?= $summary['total_orders'] ?></strong></p>
            <p>Total Revenue: <strong>$<?= number_format($summary['total_revenue'], 2) ?></strong></p>
        </div>

    </div>
</div>

<?php require 'inc/footer.php'; ?>