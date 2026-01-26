<?php
session_start();
require 'inc/db.php';
include 'inc/header.php';

// Check login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Fetch user orders
$sql = "
    SELECT o.*, COALESCE(p.status,'Pending') AS payment_status
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
    <h2 class="mb-4">My Orders</h2>

    <?php if ($orders->num_rows === 0): ?>
        <div class="alert alert-info text-center">
            You have no orders yet.
        </div>
    <?php else: ?>
        <?php while ($o = $orders->fetch_assoc()): ?>
            <div class="card mb-4 shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <strong>Order #<?= $o['id'] ?></strong>
                        <span class="text-muted">| <?= date('d M Y H:i', strtotime($o['created_at'])) ?></span>
                    </div>
                    <div class="d-flex gap-2">
                        <span class="badge bg-<?= $o['payment_status'] == 'Paid' ? 'success' : ($o['payment_status'] == 'Pending' ? 'warning' : 'danger') ?>">
                            <?= htmlspecialchars($o['payment_status']) ?>
                        </span>
                        <span class="badge bg-info text-dark">
                            <?= htmlspecialchars($o['status']) ?>
                        </span>
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

                    <div class="table-responsive">
                        <table class="table table-bordered mb-0 align-middle text-center">
                            <thead class="table-light">
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
                                        <td class="text-start">
                                            <?php if ($item['image']): ?>
                                                <img src="<?= htmlspecialchars($item['image']) ?>" width="50" class="me-2 rounded">
                                            <?php endif; ?>
                                            <?= htmlspecialchars($item['name']) ?>
                                        </td>
                                        <td><?= intval($item['qty']) ?></td>
                                        <td>$<?= number_format($item['price'], 2) ?></td>
                                        <td>$<?= number_format($item['qty'] * $item['price'], 2) ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php $itemsStmt->close(); ?>

                    <div class="mt-3 text-end">
                        <strong>Total: $<?= number_format($o['total_price'], 2) ?></strong><br>
                        <em>Payment Method: <?= htmlspecialchars($o['payment_method']) ?></em>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
    <?php endif; ?>
</div>

<style>
    .card-header .badge {
        font-size: 0.9rem;
        padding: 0.35em 0.65em;
    }

    .post-card img {
        object-fit: cover;
    }
</style>

<?php include 'inc/footer.php'; ?>