<?php
session_start();
require '../inc/db.php';

// --- Admin Check ---
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

// --- Get order ID ---
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    die("Invalid order ID.");
}

// --- Fetch Order Info ---
$order_sql = "
    SELECT o.*, u.username, u.email
    FROM orders o
    LEFT JOIN users u ON o.user_id = u.id
    WHERE o.id = ?
";
$stmt = $conn->prepare($order_sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();

if (!$order) {
    die("Order not found.");
}

// --- Fetch Order Items ---
$items_sql = "
    SELECT oi.*, p.name AS product_name
    FROM order_items oi
    LEFT JOIN products p ON oi.product_id = p.id
    WHERE oi.order_id = ?
";
$stmt_items = $conn->prepare($items_sql);
$stmt_items->bind_param("i", $id);
$stmt_items->execute();
$items_result = $stmt_items->get_result();

$grand_total = 0;


require 'inc/header.php';

?>

<div class="px-2 mt-4 mb-5">
    <div class="card shadow-sm mb-3">
        <div class="card-body p-4 d-flex align-items-center">
            <h3 class="mb-0">Order Details #<?= $order['id'] ?></h3>
            <div class="ms-auto">
                <!-- Change Status Dropdown -->
                <div class="btn-group">
                    <button type="button" class="btn btn-primary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                        Change Status
                    </button>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="update_order_status.php?id=<?= $order['id'] ?>&status=Paid">Paid</a></li>
                        <li><a class="dropdown-item" href="update_order_status.php?id=<?= $order['id'] ?>&status=Pending">Pending</a></li>
                        <li><a class="dropdown-item" href="update_order_status.php?id=<?= $order['id'] ?>&status=Delivered">Delivered</a></li>
                        <li><a class="dropdown-item text-danger" href="update_order_status.php?id=<?= $order['id'] ?>&status=Cancelled">Cancelled</a></li>
                    </ul>
                </div>
                <a href="invoice.php?id=<?= $order['id'] ?>" target="_blank" class="btn btn-success me-2 ms-2">
                    <i class="bi bi-printer"></i> Print Invoice
                </a>
                <a class="btn btn-secondary " href="orders_report.php"><i class="bi bi-arrow-left me-2"></i>Back</a>

            </div>
        </div>
    </div>

    <!-- User Info -->
    <div class="card mb-3">
        <div class="card-body">
            <h5>User Info</h5>
            <p><strong>Name:</strong> <?= htmlspecialchars($order['username']) ?></p>
            <p><strong>Email:</strong> <?= htmlspecialchars($order['email']) ?></p>
        </div>
    </div>

    <!-- Order Info -->
    <div class="card mb-3">
        <div class="card-body">
            <h5>Order Info</h5>
            <p><strong>Total Price:</strong> $<?= number_format($order['total_price'], 2) ?></p>
            <p><strong>Payment Method:</strong> <?= htmlspecialchars($order['payment_method']) ?></p>
            <p><strong>Status:</strong> <?= htmlspecialchars($order['status']) ?></p>
            <p><strong>Created At:</strong> <?= $order['created_at'] ?></p>
            <p><strong>Shipping Address:</strong> <?= htmlspecialchars($order['address']) ?></p>
        </div>
    </div>

    <!-- Order Items -->
    <div class="card mb-3">
        <div class="card-body">
            <h5>Order Items</h5>
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead class="">
                        <tr>
                            <th>Product</th>
                            <th>Qty</th>
                            <th>Price ($)</th>
                            <th>Subtotal ($)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($items_result->num_rows > 0): ?>
                            <?php while ($item = $items_result->fetch_assoc()): ?>
                                <tr>
                                    <td><?= htmlspecialchars($item['product_name']) ?></td>
                                    <td><?= $item['qty'] ?></td>
                                    <td><?= number_format($item['price'], 2) ?></td>
                                    <td>
                                        <?= number_format($item['price'] * $item['qty'], 2) ?>
                                    </td>
                                </tr>
                                <?php $grand_total += $item['price'] * $item['qty']; ?>
                            <?php endwhile; ?>
                            <tr class="table-info">
                                <td colspan="3"><strong>Total Amount</strong></td>
                                <td><strong>$<?= number_format($grand_total, 2) ?></strong></td>
                            </tr>

                        <?php else: ?>
                            <tr>
                                <td colspan="4" class="text-center">No items found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require 'inc/footer.php'; ?>