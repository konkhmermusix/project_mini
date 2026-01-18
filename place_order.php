<?php
session_start();
require 'inc/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Cart check
$cart = $_SESSION['cart'] ?? [];
if (empty($cart)) {
    include 'inc/header.php';
?>
    <div class="container mt-5 text-center">
        <h3 class="mb-3">Your Cart is Empty</h3>
        <p class="text-muted">You have no items in your cart. <a href="index.php">Go Shopping</a></p>
        <a href="index.php" class="btn btn-primary mt-3">Browse Products</a>
    </div>
<?php
    include 'inc/footer.php';
    exit;
}

// POST data
$user_id = $_SESSION['user_id'];
$address = trim($_POST['address'] ?? '');
$payment_method = $_POST['payment_method'] ?? 'COD';
$total_price = floatval($_POST['total_price'] ?? 0);

// Insert into orders
$stmt = $conn->prepare("INSERT INTO orders (user_id, total_price, payment_method, address) VALUES (?, ?, ?, ?)");
$stmt->bind_param("idss", $user_id, $total_price, $payment_method, $address);
$stmt->execute();
$order_id = $stmt->insert_id;
$stmt->close();

// Insert order items
foreach ($cart as $item) {
    if (!is_array($item)) continue;
    if (!isset($item['id'], $item['qty'], $item['price'])) continue;
    $pid   = (int) $item['id'];
    $qty   = (int) $item['qty'];
    $price = (float) $item['price'];

    $stmt = $conn->prepare("
        INSERT INTO order_items (order_id, product_id, qty, price)
        VALUES (?, ?, ?, ?)
    ");
    $stmt->bind_param("iiid", $order_id, $pid, $qty, $price);
    $stmt->execute();
    $stmt->close();
}

// Payment
$status = 'Pending';
if ($payment_method === 'Credit') {
    $status = 'Paid'; // simulate credit payment success
}
$stmt = $conn->prepare("INSERT INTO payments (order_id, amount, method, status) VALUES (?, ?, ?, ?)");
$stmt->bind_param("idss", $order_id, $total_price, $payment_method, $status);
$stmt->execute();
$stmt->close();

// Clear cart
unset($_SESSION['cart']);
setcookie('cart', '', time() - 3600, '/');

include 'inc/header.php';
// Handle payment method
if ($payment_method === 'QR') {
    // Show QR
?>
    <?php
    // Assume this is part of place_order.php
    if ($payment_method === 'QR') {
    ?>
        <div class="container my-5">
            <div class="row">
                <div class="col-md-4">
                </div>
                <div class="col-md-4">
                    <div class="card shadow-sm rounded-4 p-4 text-center">
                        <h2 class="text-primary fw-bold mb-3">Scan QR to Pay</h2>
                        <p class="mb-1"><strong>Order ID:</strong> <?= $order_id ?></p>
                        <p class="mb-3"><strong>Amount:</strong> $<?= number_format($total_price, 2) ?></p>

                        <div class="d-inline-block p-3 bg-light rounded-3 shadow-sm mb-4">

                            <img src="https://api.qrserver.com/v1/create-qr-code/?size=250x250&data=ORDER:<?= $order_id ?>|AMOUNT:<?= $total_price ?>" alt="QR Code" class="img-fluid">
                        </div>

                        <p class="text-muted mb-4">Use your banking app to scan the QR code and complete payment.</p>

                        <a href="order_success.php?id=<?= $order_id ?>" class="btn btn-gradient btn-lg px-4 fw-bold">I have paid</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
            </div>
        </div>

        <style>
            .btn-gradient {
                background: linear-gradient(135deg, #4f46e5, #3b82f6);
                color: #fff;
                border: none;
                transition: 0.3s;
            }

            .btn-gradient:hover {
                background: linear-gradient(135deg, #3b82f6, #4f46e5);
                color: #fff;
            }
        </style>
        <?php

        require 'inc/footer.php';
        ?>
<?php
        exit;
    }
} elseif ($payment_method === 'Credit') {
    // Credit card payment simulated: redirect to success page
    header("Location: order_success.php?id=$order_id");
    exit;
} else {
    // COD
    header("Location: order_success.php?id=$order_id");
    exit;
}

?>