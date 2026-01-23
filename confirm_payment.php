<?php
session_start();
require 'inc/db.php';

// Check login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Get order_id from POST
$order_id = intval($_POST['order_id'] ?? 0);
if (!$order_id) {
    die("Invalid order.");
}

// Fetch payment info
$stmt = $conn->prepare("SELECT * FROM payments WHERE order_id=? AND method='QR'");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$payment = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$payment) {
    die("No QR payment found for this order.");
}

// Update payment status to Paid
$stmt = $conn->prepare("UPDATE payments SET status='Paid' WHERE order_id=? AND method='QR'");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$stmt->close();

// Update order status to Paid
$stmt = $conn->prepare("UPDATE orders SET status='Paid' WHERE id=?");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$stmt->close();

// Optional: Insert notification for admin
$message = "Order #$order_id has been paid via QR.";
$type = "payment";
$link = "order_detail.php?id=$order_id";

$stmt = $conn->prepare("INSERT INTO notifications (message, type, link) VALUES (?, ?, ?)");
$stmt->bind_param("sss", $message, $type, $link);
$stmt->execute();
$stmt->close();

// Redirect to order success page
header("Location: order_success.php?id=$order_id");
exit;
