<?php
session_start();
require 'inc/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;

// Fetch order
$stmt = $conn->prepare("SELECT * FROM orders WHERE id=? AND user_id=?");
$stmt->bind_param("ii", $order_id, $_SESSION['user_id']);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$order) {
    die("Order not found");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $card_name   = trim($_POST['card_name']);
    $card_number = trim($_POST['card_number']);
    $card_expiry = trim($_POST['expiry']);

    $stmt = $conn->prepare("
        UPDATE payments 
        SET status='Paid', 
            card_name=?, 
            card_number=?, 
            card_expiry=?
        WHERE order_id=? AND method='Credit'
    ");
    $stmt->bind_param("sssi", $card_name, $card_number, $card_expiry, $order_id);
    $stmt->execute();
    $stmt->close();

    // Update order status
    $stmt = $conn->prepare("UPDATE orders SET status='Paid' WHERE id=?");
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $stmt->close();

    header("Location: order_success.php?id=$order_id");
    exit;
}

include 'inc/header.php';
?>

<div class="container my-5">
    <h2>Credit Card Payment</h2>
    <p>Order ID: <?= $order['id'] ?></p>
    <p>Total: $<?= number_format($order['total_price'], 2) ?></p>

    <form method="POST">
        <div class="mb-3">
            <label>Card Name</label>
            <input type="text" name="card_name" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>Card Number</label>
            <input type="text" name="card_number" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>Expiry Date</label>
            <input type="text" name="expiry" class="form-control" placeholder="MM/YY" required>
        </div>
        <button class="btn btn-success" type="submit">Pay Now</button>
    </form>
</div>

<?php include 'inc/footer.php'; ?>