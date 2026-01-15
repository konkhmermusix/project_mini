<?php
session_start();
require 'inc/db.php';

// Check if user logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$cart = $_SESSION['cart'] ?? [];

if (empty($cart)) {
    die("<div class='container mt-5'><h3>Your cart is empty.</h3></div>");
}

// Calculate total price
$total_price = 0;
foreach ($cart as $item) {
    $total_price += $item['qty'] * $item['price'];
}

$message = '';

// ===========================
// Handle Checkout POST
// ===========================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['checkout'])) {
    $address = trim($_POST['address']);
    $payment_method = $_POST['payment_method']; // COD or QR

    // ---------------------------
    // 1. Insert into orders table
    // ---------------------------
    $stmt = $conn->prepare("
        INSERT INTO orders (user_id, total_price, status, payment_method, address)
        VALUES (?, ?, 'Pending', ?, ?)
    ");
    $stmt->bind_param("idss", $user_id, $total_price, $payment_method, $address);
    $stmt->execute();
    $order_id = $stmt->insert_id;
    $stmt->close();

    // ---------------------------
    // 2. Insert into order_items
    // ---------------------------
    foreach ($cart as $item) {
        $stmt = $conn->prepare("
            INSERT INTO order_items (order_id, product_id, qty, price)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->bind_param("iiid", $order_id, $item['id'], $item['qty'], $item['price']);
        $stmt->execute();
        $stmt->close();
    }

    // ---------------------------
    // 3. Insert into payments
    // ---------------------------
    $stmt = $conn->prepare("
        INSERT INTO payments (order_id, amount, method, status)
        VALUES (?, ?, ?, 'Pending')
    ");
    $stmt->bind_param("ids", $order_id, $total_price, $payment_method);
    $stmt->execute();
    $stmt->close();

    // ---------------------------
    // 4. Clear Cart
    // ---------------------------
    unset($_SESSION['cart']);
    setcookie('cart', '', time() - 3600, "/");

    // Redirect to order success page
    header("Location: order_success.php?order_id=" . $order_id);
    exit;
}

include 'inc/header.php';
?>

<div class="container mt-5">
    <h2>Checkout</h2>

    <table class="table mb-4">
        <thead>
            <tr>
                <th>Product</th>
                <th>Qty</th>
                <th>Price</th>
                <th>Subtotal</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($cart as $item):
                $subtotal = $item['qty'] * $item['price'];
            ?>
                <tr>
                    <td><?= htmlspecialchars($item['name']) ?></td>
                    <td><?= $item['qty'] ?></td>
                    <td>$<?= number_format($item['price'], 2) ?></td>
                    <td>$<?= number_format($subtotal, 2) ?></td>
                </tr>
            <?php endforeach; ?>
            <tr>
                <td colspan="3"><strong>Total</strong></td>
                <td><strong>$<?= number_format($total_price, 2) ?></strong></td>
            </tr>
        </tbody>
    </table>

    <form method="post">
        <div class="mb-3">
            <label for="address" class="form-label">Shipping Address</label>
            <textarea class="form-control" id="address" name="address" rows="3" required></textarea>
        </div>

        <div class="mb-3">
            <label class="form-label">Payment Method</label>
            <select name="payment_method" class="form-select" required>
                <option value="COD">Cash on Delivery (COD)</option>
                <option value="QR">QR Payment</option>
            </select>
        </div>

        <button type="submit" name="checkout" class="btn btn-success">Place Order</button>
    </form>
</div>

<?php include 'inc/footer.php'; ?>