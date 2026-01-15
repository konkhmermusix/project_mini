<?php
session_start();
require 'inc/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Get order ID
$order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;
if ($order_id <= 0) die("Invalid order ID.");

// Fetch order
$stmt = $conn->prepare("
    SELECT o.*, u.username, u.email
    FROM orders o
    LEFT JOIN users u ON o.user_id = u.id
    WHERE o.id = ? AND o.user_id = ?
");
$stmt->bind_param("ii", $order_id, $_SESSION['user_id']);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$order) die("Order not found.");

// Fetch order items
$stmt = $conn->prepare("
    SELECT oi.*, p.name, p.image
    FROM order_items oi
    LEFT JOIN products p ON oi.product_id = p.id
    WHERE oi.order_id = ?
");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$items = $stmt->get_result();
$stmt->close();

// Fetch payment info
$stmt = $conn->prepare("SELECT * FROM payments WHERE order_id=? LIMIT 1");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$payment = $stmt->get_result()->fetch_assoc();
$stmt->close();

include 'inc/header.php';
?>

<div class="container mt-5">
    <h2>Order Success!</h2>
    <p>Thank you for your order, <?= htmlspecialchars($order['username']) ?>.</p>
    <p>Order ID: <strong>#<?= $order['id'] ?></strong></p>
    <p>Order Status: <strong><?= $order['status'] ?></strong></p>
    <p>Payment Method: <strong><?= $order['payment_method'] ?></strong></p>

    <h4 class="mt-4">Shipping Address</h4>
    <p><?= nl2br(htmlspecialchars($order['address'])) ?></p>

    <h4 class="mt-4">Order Items</h4>
    <table class="table">
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
                $subtotal = $item['qty'] * $item['price'];
                $total += $subtotal;
            ?>
                <tr>
                    <td>
                        <?php if ($item['image']): ?>
                            <img src="<?= htmlspecialchars($item['image']) ?>" width="50" style="object-fit:cover;">
                        <?php endif; ?>
                        <?= htmlspecialchars($item['name']) ?>
                    </td>
                    <td><?= $item['qty'] ?></td>
                    <td>$<?= number_format($item['price'], 2) ?></td>
                    <td>$<?= number_format($subtotal, 2) ?></td>
                </tr>
            <?php endwhile; ?>
            <tr>
                <td colspan="3"><strong>Total</strong></td>
                <td><strong>$<?= number_format($total, 2) ?></strong></td>
            </tr>
        </tbody>
    </table>

    <?php if ($order['payment_method'] === 'QR'): ?>
        <h4>QR Payment</h4>
        <p>Please scan the QR code below and upload your payment proof:</p>
        <form method="post" enctype="multipart/form-data" action="upload_payment.php">
            <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
            <input type="file" name="proof_image" accept="image/*" required>
            <button type="submit" class="btn btn-primary mt-2">Upload Proof</button>
        </form>
        <?php if (!empty($payment['proof_image'])): ?>
            <p class="mt-2">Uploaded Proof:</p>
            <img src="<?= htmlspecialchars($payment['proof_image']) ?>" width="200">
        <?php endif; ?>
    <?php else: ?>
        <p>Payment will be collected on delivery (Cash on Delivery).</p>
    <?php endif; ?>
</div>

<?php include 'inc/footer.php'; ?>