<?php
session_start();
require 'inc/db.php';
include 'inc/header.php';

// Login check
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Get user orders
$sql = "
    SELECT o.*, p.status AS payment_status
    FROM orders o
    LEFT JOIN payments p ON o.id = p.order_id
    WHERE o.user_id = ?
    ORDER BY o.created_at DESC
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$orders = $stmt->get_result();
$stmt->close();
?>

<div class="container my-5">
    <h2>My Orders</h2>

    <?php if ($orders->num_rows == 0): ?>
        <p>You have no orders yet.</p>
    <?php else: ?>
        <?php while ($o = $orders->fetch_assoc()): ?>
            <div class="card mb-4 shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <strong>Order #<?= $o['id'] ?></strong> |
                        <span class="text-muted"><?= $o['created_at'] ?></span>
                    </div>
                    <div>
                        <span class="badge bg-<?= $o['payment_status'] == 'Paid' ? 'success' : ($o['payment_status'] == 'Pending' ? 'warning' : 'danger') ?>">
                            <?= $o['payment_status'] ?>
                        </span>
                        <span class="badge bg-info"><?= $o['status'] ?></span>
                    </div>
                </div>
                <div class="card-body">
                    <?php
                    // Fetch order items
                    $itemsStmt = $conn->prepare("
                        SELECT oi.*, pr.name, pr.image
                        FROM order_items oi
                        LEFT JOIN products pr ON oi.product_id = pr.id
                        WHERE oi.order_id = ?
                    ");
                    $itemsStmt->bind_param("i", $o['id']);
                    $itemsStmt->execute();
                    $items = $itemsStmt->get_result();
                    ?>

                    <table class="table table-bordered mb-0">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Qty</th>
                                <th>Price</th>
                                <th>Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($item = $items->fetch_assoc()): ?>
                                <tr>
                                    <td>
                                        <?php if ($item['image']): ?>
                                            <img src="<?= htmlspecialchars($item['image']) ?>" width="50" class="me-2">
                                        <?php endif; ?>
                                        <?= htmlspecialchars($item['name']) ?>
                                    </td>
                                    <td><?= $item['qty'] ?></td>
                                    <td>$<?= number_format($item['price'], 2) ?></td>
                                    <td>$<?= number_format($item['qty'] * $item['price'], 2) ?></td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                    <?php $itemsStmt->close(); ?>

                    <div class="mt-3 text-end">
                        <strong>Total: $<?= number_format($o['total_price'], 2) ?></strong> |
                        <em>Payment Method: <?= $o['payment_method'] ?></em>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
    <?php endif; ?>
</div>

<?php include 'inc/footer.php'; ?>