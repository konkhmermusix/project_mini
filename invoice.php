<?php
session_start();
require 'inc/db.php';

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
// DETERMINE OVERALL STATUS
// =========================
$overall_status = 'Pending'; // default
if ($order['status'] === 'Cancelled') {
    $overall_status = 'Cancelled';
} elseif ($payment && $payment['status'] === 'Paid') {
    $overall_status = 'Paid';
}

require 'inc/header.php';
?>

<div class="container mt-5 mb-5">
    <div class="card shadow-sm border-0">
        <!-- Invoice Header -->
        <div class="card-body">
            <div class="row mb-4">
                <div class="col-md-6">
                    <h2 class="text-primary">LSTECH</h2>
                    <p>73 Main Street<br>TBK, Cambodia<br>Email: lstech26@shop.com<br>Phone: +88596 4301 974</p>
                </div>
                <div class="col-md-6 text-end">
                    <h4 class="text-dark">Invoice</h4>
                    <p>
                        <strong>#<?= $order['id'] ?></strong><br>
                        Order Code: <?= htmlspecialchars($order['order_code'] ?? '') ?><br>
                        Date: <?= date('d M Y', strtotime($order['created_at'])) ?><br>
                        Status:
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
                </div>
            </div>

            <!-- Customer Info -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <h5>Bill To:</h5>
                    <p>
                        <?= htmlspecialchars($order['name'] ?? 'N/A') ?><br>
                        <?= htmlspecialchars($order['address'] ?? 'N/A') ?><br>
                        Phone: <?= htmlspecialchars($order['phone'] ?? 'N/A') ?>
                    </p>
                </div>
                <div class="col-md-6 text-end">
                    <h6>Payment Method: <?= htmlspecialchars($order['payment_method'] ?? 'N/A') ?></h6>
                </div>
            </div>

            <!-- Items Table -->
            <div class="table-responsive mb-4">
                <table class="table table-bordered table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Product</th>
                            <th>Image</th>
                            <th>Quantity</th>
                            <th>Unit Price</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $total = 0;
                        foreach ($items as $item):
                            $subtotal = $item['qty'] * $item['price'];
                            $total += $subtotal;
                        ?>
                            <tr>
                                <td><?= htmlspecialchars($item['name']) ?></td>
                                <td>
                                    <?php if (!empty($item['image'])): ?>
                                        <img src="<?= htmlspecialchars($item['image']) ?>" style="width:50px;height:50px;object-fit:cover;">
                                    <?php endif; ?>
                                </td>
                                <td><?= $item['qty'] ?></td>
                                <td>$<?= number_format($item['price'], 2) ?></td>
                                <td>$<?= number_format($subtotal, 2) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="4" class="text-end">Total</th>
                            <th>$<?= number_format($total, 2) ?></th>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <!-- Payment Section -->
            <?php if ($payment): ?>
                <div class="row mb-4">
                    <div class="col-md-6">
                        <h5>Payment Details</h5>
                        <p>
                            Transaction Ref: <?= htmlspecialchars($payment['transaction_ref']) ?><br>
                            Amount: $<?= number_format($payment['amount'], 2) ?><br>
                            Method: <?= htmlspecialchars($payment['method']) ?><br>
                            Status:
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
                    </div>
                    <div class="col-md-6 text-end">
                        <?php if (!empty($payment['proof_image'])): ?>
                            <p><strong>Payment Proof:</strong></p>
                            <img src="<?= htmlspecialchars($payment['proof_image']) ?>" style="max-width:150px;" class="border rounded img-fluid">
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Footer Buttons -->
            <div class="d-flex justify-content-between">
                <a href="shop.php" class="btn btn-primary">Back to Shop</a>
            </div>
        </div>
    </div>
</div>

<style>
    @media print {
        .btn {
            display: none;
        }
    }
</style>

<?php require 'inc/footer.php'; ?>