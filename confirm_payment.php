<?php
session_start();
require 'inc/db.php';

// Check login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Validate order_id
$order_id = intval($_POST['order_id'] ?? 0);
if ($order_id <= 0) {
    die("Invalid order.");
}

// Fetch QR payment
$stmt = $conn->prepare("
    SELECT * FROM payments 
    WHERE order_id=? AND method='QR'
");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$payment = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$payment) {
    die("QR payment not found.");
}

// Prevent double payment
if ($payment['status'] === 'Paid') {
    header("Location: order_success.php?id=$order_id");
    exit;
}

/* =============================
   Upload Proof Image
============================= */
$proof_image = null;

if (!empty($_FILES['proof_image']['name'])) {
    $ext = pathinfo($_FILES['proof_image']['name'], PATHINFO_EXTENSION);
    $allowed = ['jpg', 'jpeg', 'png'];

    if (!in_array(strtolower($ext), $allowed)) {
        die("Invalid image type.");
    }

    $proof_image = 'qr_' . time() . '.' . $ext;
    move_uploaded_file(
        $_FILES['proof_image']['tmp_name'],
        'uploads/' . $proof_image
    );
}

// Generate transaction ref if empty
$transaction_ref = $payment['transaction_ref'] ?: strtoupper(uniqid('QR'));

/* =============================
   Database Transaction
============================= */
$conn->begin_transaction();

try {
    // Update payment
    $stmt = $conn->prepare("
        UPDATE payments 
        SET status='Paid',
            transaction_ref=?,
            proof_image=?
        WHERE order_id=? AND method='QR'
    ");
    $stmt->bind_param(
        "ssi",
        $transaction_ref,
        $proof_image,
        $order_id
    );
    $stmt->execute();
    $stmt->close();

    // Update order
    $stmt = $conn->prepare("
        UPDATE orders 
        SET status='Paid' 
        WHERE id=?
    ");
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $stmt->close();

    // Insert notification
    $msg  = "Order #$order_id paid via QR";
    $type = "payment";
    $link = "order_detail.php?id=$order_id";

    $stmt = $conn->prepare("
        INSERT INTO notifications (message, type, link)
        VALUES (?,?,?)
    ");
    $stmt->bind_param("sss", $msg, $type, $link);
    $stmt->execute();
    $stmt->close();

    $conn->commit();
} catch (Exception $e) {
    $conn->rollback();
    die("Payment confirmation failed.");
}

// Redirect success
header("Location: order_success.php?id=$order_id");
exit;
