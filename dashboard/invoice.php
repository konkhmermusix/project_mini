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
if (!$order) die("Order not found.");

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

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Invoice #<?= $order['id'] ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .invoice-box {
            max-width: 800px;
            margin: auto;
            padding: 30px;
            border: 1px solid #eee;
        }

        .table th,
        .table td {
            vertical-align: middle;
        }
    </style>
</head>

<body>
    <div class="invoice-box mt-5">
        <div class="text-center mb-4">
            <h2>Invoice</h2>
            <h5>Order #<?= $order['id'] ?></h5>
            <small><?= date('Y-m-d H:i', strtotime($order['created_at'])) ?></small>
        </div>

        <!-- User Info -->
        <div class="row mb-3">
            <div class="col-md-6">
                <h6>Customer:</h6>
                <p>
                    <?= htmlspecialchars($order['username']) ?><br>
                    <?= htmlspecialchars($order['email']) ?><br>
                    <?= htmlspecialchars($order['address'] ?? '') ?>
                </p>
            </div>
            <div class="col-md-6 text-end">
                <h6>Payment Method:</h6>
                <p><?= htmlspecialchars($order['payment_method']) ?><br>Status: <?= htmlspecialchars($order['status']) ?></p>
            </div>
        </div>

        <!-- Items Table -->
        <div class="table-responsive mb-3">
            <table class="table table-bordered">
                <thead class="table-dark">
                    <tr>
                        <th>Product</th>
                        <th>Quantity</th>
                        <th>Price ($)</th>
                        <th>Subtotal ($)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $grand_total = 0;
                    while ($item = $items_result->fetch_assoc()):
                        $subtotal = $item['price'] * $item['qty'];
                        $grand_total += $subtotal;
                    ?>
                        <tr>
                            <td><?= htmlspecialchars($item['product_name']) ?></td>
                            <td><?= $item['qty'] ?></td>
                            <td><?= number_format($item['price'], 2) ?></td>
                            <td><?= number_format($subtotal, 2) ?></td>
                        </tr>
                    <?php endwhile; ?>
                    <tr class="table-dark">
                        <td colspan="3" class="text-end"><strong>Grand Total</strong></td>
                        <td><strong>$<?= number_format($grand_total, 2) ?></strong></td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="text-center mt-4">
            <button onclick="window.print()" class="btn btn-success">
                <i class="bi bi-printer"></i> Print Invoice
            </button>
            <a href="orders_report.php" class="btn btn-secondary">Back to Orders</a>
        </div>
    </div>

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
</body>

</html>