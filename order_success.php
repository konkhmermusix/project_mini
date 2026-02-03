<?php
session_start();
require 'inc/db.php';

// =====================
// GET ORDER ID
// =====================
$order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;
if ($order_id <= 0) {
    die("<div class='container mt-5 text-center text-danger'><h4>Invalid order</h4></div>");
}

// =====================
// FETCH ORDER
// =====================
$stmt = $conn->prepare("SELECT * FROM orders WHERE id=?");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$order) {
    die("<div class='container mt-5 text-center text-danger'><h4>Invalid order</h4></div>");
}

// =====================
// FETCH ORDER ITEMS
// =====================
$stmt = $conn->prepare("
    SELECT oi.*, p.name, p.image 
    FROM order_items oi
    JOIN products p ON oi.product_id = p.id
    WHERE oi.order_id=?
");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// =====================
// FETCH PAYMENT
// =====================
$stmt = $conn->prepare("SELECT * FROM payments WHERE order_id=? ORDER BY created_at DESC LIMIT 1");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$payment = $stmt->get_result()->fetch_assoc();
$stmt->close();

require 'inc/header.php';
?>

<div class="container mt-4">
    <div class="card shadow-sm p-4">
        <h3 class="mb-3">Order #<?= $order['id'] ?></h3>

        <!-- USER INFO -->
        <p><strong>Name:</strong> <?= htmlspecialchars($order['name'] ?? 'N/A') ?></p>
        <p><strong>Phone:</strong> <?= htmlspecialchars($order['phone'] ?? 'N/A') ?></p>
        <p><strong>Email:</strong> <?= htmlspecialchars($order['email'] ?? 'N/A') ?></p>
        <p><strong>Shipping Address:</strong> <?= htmlspecialchars($order['address'] ?? 'N/A') ?></p>
        <p><strong>Status:</strong> <?= htmlspecialchars($order['status'] ?? 'N/A') ?></p>
        <p><strong>Payment Method:</strong> <?= htmlspecialchars($order['payment_method'] ?? 'N/A') ?></p>
        <p><strong>Total:</strong> $<?= number_format($order['total_price'] ?? 0, 2) ?></p>

        <hr>

        <!-- ORDER ITEMS -->
        <h5>Order Items</h5>
        <div class="row">
            <?php foreach ($items as $item): ?>
                <div class="col-md-6 mb-3">
                    <div class="card p-2 d-flex flex-row align-items-center">
                        <?php if (!empty($item['image'])): ?>
                            <img src="<?= htmlspecialchars($item['image']) ?>" style="width:60px;height:60px;object-fit:cover;" class="me-2">
                        <?php endif; ?>
                        <div>
                            <strong><?= htmlspecialchars($item['name']) ?></strong><br>
                            Qty: <?= $item['qty'] ?> | Price: $<?= number_format($item['price'], 2) ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- PAYMENT PROOF -->
        <?php if ($payment && !empty($payment['proof_image'])): ?>
            <hr>
            <h5>Payment Proof</h5>
            <img src="<?= htmlspecialchars($payment['proof_image']) ?>" alt="Payment Proof" class="img-fluid border rounded" style="max-width:300px;">
        <?php endif; ?>

        <a href="shop.php" class="btn btn-primary mt-3">Continue Shopping</a>
    </div>
</div>

<?php require 'inc/footer.php'; ?>
<?php
session_start();
require 'inc/db.php';

// =====================
// GET ORDER ID
// =====================
$order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;
if ($order_id <= 0) {
    die("<div class='container mt-5 text-center text-danger'><h4>Invalid order</h4></div>");
}

// =====================
// FETCH ORDER
// =====================
$stmt = $conn->prepare("SELECT * FROM orders WHERE id=?");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$order) {
    die("<div class='container mt-5 text-center text-danger'><h4>Invalid order</h4></div>");
}

// =====================
// FETCH ORDER ITEMS
// =====================
$stmt = $conn->prepare("
    SELECT oi.*, p.name, p.image 
    FROM order_items oi
    JOIN products p ON oi.product_id = p.id
    WHERE oi.order_id=?
");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// =====================
// FETCH PAYMENT
// =====================
$stmt = $conn->prepare("SELECT * FROM payments WHERE order_id=? ORDER BY created_at DESC LIMIT 1");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$payment = $stmt->get_result()->fetch_assoc();
$stmt->close();

require 'inc/header.php';
?>

<div class="container mt-4">
    <div class="card shadow-sm p-4">
        <h3 class="mb-3">Order #<?= $order['id'] ?></h3>

        <!-- USER INFO -->
        <p><strong>Name:</strong> <?= htmlspecialchars($order['name'] ?? 'N/A') ?></p>
        <p><strong>Phone:</strong> <?= htmlspecialchars($order['phone'] ?? 'N/A') ?></p>
        <p><strong>Shipping Address:</strong> <?= htmlspecialchars($order['address'] ?? 'N/A') ?></p>
        <p><strong>Status:</strong> <?= htmlspecialchars($order['status'] ?? 'N/A') ?></p>
        <p><strong>Payment Method:</strong> <?= htmlspecialchars($order['payment_method'] ?? 'N/A') ?></p>
        <p><strong>Total:</strong> $<?= number_format($order['total_price'] ?? 0, 2) ?></p>

        <hr>

        <!-- ORDER ITEMS -->
        <h5>Order Items</h5>
        <div class="row">
            <?php foreach ($items as $item): ?>
                <div class="col-md-6 mb-3">
                    <div class="card p-2 d-flex flex-row align-items-center">
                        <?php if (!empty($item['image'])): ?>
                            <img src="<?= htmlspecialchars($item['image']) ?>" style="width:60px;height:60px;object-fit:cover;" class="me-2">
                        <?php endif; ?>
                        <div>
                            <strong><?= htmlspecialchars($item['name']) ?></strong><br>
                            Qty: <?= $item['qty'] ?> | Price: $<?= number_format($item['price'], 2) ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- PAYMENT PROOF -->
        <?php if ($payment && !empty($payment['proof_image'])): ?>
            <hr>
            <h5>Payment Proof</h5>
            <img src="<?= htmlspecialchars($payment['proof_image']) ?>" alt="Payment Proof" class="img-fluid border rounded" style="max-width:300px;">
        <?php endif; ?>

        <a href="shop.php" class="btn btn-primary mt-3">Continue Shopping</a>
    </div>
</div>

<?php require 'inc/footer.php'; ?>