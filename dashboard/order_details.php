<?php
session_start();
require '../inc/db.php';

// =========================
// CHECK ADMIN
// =========================
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

// =========================
// GET ORDER ID
// =========================
$order_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($order_id <= 0) {
    die("<div class='container mt-5 text-center text-danger'><h4>Invalid order</h4></div>");
}

// =========================
// FETCH ORDER
// =========================
$stmt = $conn->prepare("
    SELECT o.*, u.username 
    FROM orders o
    LEFT JOIN users u ON o.user_id = u.id
    WHERE o.id = ?
");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$order) {
    die("<div class='container mt-5 text-center text-danger'><h4>Order not found</h4></div>");
}

// =========================
// FETCH ORDER ITEMS
// =========================
$stmt = $conn->prepare("
    SELECT oi.*, p.name, p.image
    FROM order_items oi
    LEFT JOIN products p ON oi.product_id = p.id
    WHERE oi.order_id = ?
");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// =========================
// FETCH LATEST PAYMENT
// =========================
$stmt = $conn->prepare("
    SELECT * 
    FROM payments 
    WHERE order_id = ? 
    ORDER BY created_at DESC 
    LIMIT 1
");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$payment = $stmt->get_result()->fetch_assoc();
$stmt->close();

// =========================
// HANDLE STATUS UPDATE
// =========================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $new_status = $_POST['order_status'] ?? '';

    if (!in_array($new_status, ['Pending', 'Paid', 'Cancelled'])) {
        $error = "Invalid status selected.";
    } else {
        // Update orders table
        $stmt = $conn->prepare("UPDATE orders SET status=? WHERE id=?");
        $stmt->bind_param("si", $new_status, $order_id);
        $stmt->execute();
        $stmt->close();

        // Update latest payment
        if ($payment) {
            $stmt = $conn->prepare("UPDATE payments SET status=? WHERE id=?");
            $stmt->bind_param("si", $new_status, $payment['id']);
            $stmt->execute();
            $stmt->close();
        }

        // Refresh page
        header("Location: order_details.php?id=" . $order_id);
        exit;
    }
}

// =========================
// DETERMINE OVERALL STATUS
// =========================
$overall_status = 'Pending';
if ($order['status'] === 'Cancelled') {
    $overall_status = 'Cancelled';
} elseif ($payment && $payment['status'] === 'Paid') {
    $overall_status = 'Paid';
}

require 'inc/header.php';
?>

<div class="container mt-4">
    <div class="card shadow-sm p-4">

        <h3>Order #<?= $order['id'] ?> (<?= htmlspecialchars($order['order_code'] ?? '') ?>)</h3>

        <!-- ADMIN STATUS FORM -->
        <form method="POST" class="mb-3">
            <div class="row align-items-center">
                <div class="col-md-4">
                    <label for="order_status" class="form-label"><strong>Update Status</strong></label>
                    <select name="order_status" id="order_status" class="form-select">
                        <option value="Pending" <?= $overall_status == 'Pending' ? 'selected' : '' ?>>Pending</option>
                        <option value="Paid" <?= $overall_status == 'Paid' ? 'selected' : '' ?>>Paid</option>
                        <option value="Cancelled" <?= $overall_status == 'Cancelled' ? 'selected' : '' ?>>Cancelled</option>
                    </select>
                </div>
                <div class="col-md-2 mt-2">
                    <button type="submit" name="update_status" class="btn btn-success">Update</button>
                </div>
            </div>
        </form>

        <!-- ORDER INFO -->
        <p><strong>User:</strong> <?= htmlspecialchars($order['username'] ?? 'N/A') ?></p>
        <p><strong>Name:</strong> <?= htmlspecialchars($order['name'] ?? 'N/A') ?></p>
        <p><strong>Phone:</strong> <?= htmlspecialchars($order['phone'] ?? 'N/A') ?></p>
        <p><strong>Shipping Address:</strong> <?= htmlspecialchars($order['address'] ?? 'N/A') ?></p>

        <p><strong>Order Status:</strong>
            <?php
            $class = '';
            switch ($overall_status) {
                case 'Paid':
                    $class = 'text-success';
                    break;
                case 'Pending':
                    $class = 'text-warning';
                    break;
                case 'Cancelled':
                    $class = 'text-danger';
                    break;
            }
            ?>
            <span class="<?= $class ?>"><?= $overall_status ?></span>
        </p>

        <p><strong>Payment Method:</strong> <?= htmlspecialchars($order['payment_method'] ?? 'N/A') ?></p>
        <p><strong>Total:</strong> $<?= number_format($order['total_price'], 2) ?></p>
        <p><strong>Created At:</strong> <?= $order['created_at'] ?></p>

        <hr>
        <h5>Order Items</h5>
        <div class="row">
            <?php foreach ($items as $item): ?>
                <div class="col-md-6 mb-3">
                    <div class="card p-2 d-flex flex-row align-items-center">
                        <?php if (!empty($item['image'])): ?>
                            <img src="../<?= htmlspecialchars($item['image']) ?>" style="width:60px;height:60px;object-fit:cover;" class="me-2">
                        <?php endif; ?>
                        <div>
                            <strong><?= htmlspecialchars($item['name']) ?></strong><br>
                            Qty: <?= $item['qty'] ?> | Price: $<?= number_format($item['price'], 2) ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- PAYMENT INFO -->
        <?php if ($payment): ?>
            <hr>
            <h5>Payment Info</h5>
            <p><strong>Transaction Ref:</strong> <?= htmlspecialchars($payment['transaction_ref']) ?></p>
            <p><strong>Amount:</strong> $<?= number_format($payment['amount'], 2) ?></p>
            <p><strong>Method:</strong> <?= htmlspecialchars($payment['method']) ?></p>
            <p><strong>Payment Status:</strong>
                <?php
                $pclass = '';
                switch ($payment['status']) {
                    case 'Paid':
                        $pclass = 'text-success';
                        break;
                    case 'Pending':
                        $pclass = 'text-warning';
                        break;
                    case 'Cancelled':
                        $pclass = 'text-danger';
                        break;
                }
                ?>
                <span class="<?= $pclass ?>"><?= $payment['status'] ?></span>
            </p>
            <?php if (!empty($payment['proof_image'])): ?>
                <p><strong>Payment Proof:</strong></p>
                <img src="../<?= htmlspecialchars($payment['proof_image']) ?>" style="max-width:300px;" class="border rounded img-fluid">
            <?php endif; ?>
        <?php endif; ?>

        <a href="orders_report.php" class="btn btn-secondary mt-3">Back to Orders</a>
    </div>
</div>

<?php require 'inc/footer.php'; ?>