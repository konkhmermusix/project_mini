<?php
session_start();
require 'inc/db.php';
include 'inc/header.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$order_id = intval($_GET['id']);
$user_id  = $_SESSION['user_id'];

// Fetch order
$stmt = $conn->prepare("
    SELECT * FROM orders
    WHERE id = ? AND user_id = ?
");
$stmt->bind_param("ii", $order_id, $user_id);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();

if (!$order) {
    die("Order not found");
}

// Fetch order items
$itemStmt = $conn->prepare("
    SELECT oi.*, p.name
    FROM order_items oi
    JOIN products p ON oi.product_id = p.id
    WHERE oi.order_id = ?
");
$itemStmt->bind_param("i", $order_id);
$itemStmt->execute();
$items = $itemStmt->get_result();
?>

<div class="container mt-4">
    <h2>Order #<?= $order['id'] ?></h2>

    <p><strong>Status:</strong> <?= $order['status'] ?></p>
    <p><strong>Payment:</strong> <?= $order['payment_method'] ?></p>
    <p><strong>Address:</strong> <?= htmlspecialchars($order['address']) ?></p>

    <table class="table mt-3">
        <thead>
            <tr>
                <th>Product</th>
                <th>Qty</th>
                <th>Price</th>
                <th>Subtotal</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $total = 0;
            while ($item = $items->fetch_assoc()):
                $sub = $item['qty'] * $item['price'];
                $total += $sub;
            ?>
                <tr>
                    <td><?= htmlspecialchars($item['name']) ?></td>
                    <td><?= $item['qty'] ?></td>
                    <td>$<?= number_format($item['price'], 2) ?></td>
                    <td>$<?= number_format($sub, 2) ?></td>
                </tr>
            <?php endwhile; ?>
            <tr>
                <td colspan="3"><strong>Total</strong></td>
                <td><strong>$<?= number_format($total, 2) ?></strong></td>
            </tr>
        </tbody>
    </table>

    <a href="my_orders.php" class="btn btn-secondary">Back</a>
</div>

<?php include 'inc/footer.php'; ?>