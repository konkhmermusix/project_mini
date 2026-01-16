<?php
session_start();
require '../inc/db.php';

// Check admin login
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

// Fetch orders with user info
$sql = "
    SELECT o.id, u.email AS user_email, o.total_price, o.payment_method, o.status, o.created_at
    FROM orders o
    LEFT JOIN users u ON o.user_id = u.id
    ORDER BY o.created_at DESC
";
$result = $conn->query($sql);

include 'inc/header.php';
?>

<div class="container mt-4">
    <h2>Orders Report</h2>
    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>ID</th>
                <th>User</th>
                <th>Total</th>
                <th>Payment</th>
                <th>Status</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($result->num_rows > 0): ?>
                <?php while ($order = $result->fetch_assoc()): ?>
                    <tr>
                        <td>#<?= $order['id'] ?></td>
                        <td><?= htmlspecialchars($order['user_email']) ?></td>
                        <td>$<?= number_format($order['total_price'], 2) ?></td>
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
                                case 'Delivered':
                                    $status_class = 'text-primary';
                                    break;
                                case 'Cancelled':
                                    $status_class = 'text-danger';
                                    break;
                            }
                            ?>
                            <span class="<?= $status_class ?>"><?= $order['status'] ?></span>
                        </td>
                        <td><?= $order['created_at'] ?></td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6" class="text-center">No orders found.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include 'inc/footer.php'; ?>