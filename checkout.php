<?php
session_start();
require 'inc/db.php';

// Check login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Cart
$cart = $_SESSION['cart'] ?? [];
if (empty($cart)) {
    die("Your cart is empty.");
}

// Calculate total using selling_price
$total_price = 0;
foreach ($cart as $item) {
    $price = $item['selling_price'] ?? $item['price'] ?? 0;
    $total_price += $item['qty'] * $price;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $name    = trim($_POST['name']);
    $phone   = trim($_POST['phone']);
    $email   = trim($_POST['email']);
    $address = trim($_POST['address']);
    $method  = $_POST['payment_method'];

    if (empty($name) || empty($phone) || empty($email) || empty($address)) {
        die("Please fill in all required fields.");
    }

    if (!in_array($method, ['QR', 'CCD'])) die("Invalid payment method");

    // Create order
    $status = ($method === 'CCD') ? 'Paid' : 'Pending';
    $stmt = $conn->prepare("
        INSERT INTO orders 
        (user_id, name, phone, email, address, total_price, payment_method, status, created_at)
        VALUES (?,?,?,?,?,?,?,?,NOW())
    ");
    $stmt->bind_param("isssdsss", $user_id, $name, $phone, $email, $address, $total_price, $method, $status);
    $stmt->execute();
    $order_id = $stmt->insert_id;
    $stmt->close();

    // Insert order items
    foreach ($cart as $item) {
        if (!isset($item['id'])) continue;
        $price = $item['selling_price'] ?? $item['price'] ?? 0;

        $stmt = $conn->prepare("
            INSERT INTO order_items (order_id, product_id, qty, price)
            VALUES (?,?,?,?)
        ");
        $stmt->bind_param("iiid", $order_id, $item['id'], $item['qty'], $price);
        $stmt->execute();
        $stmt->close();
    }

    // Payment
    $transaction_ref = strtoupper(uniqid("TXN"));
    $card_name = $card_number = $card_expiry = $card_cvv = $proof_image = null;
    if ($method === 'CCD') {
        $card_name   = $_POST['card_name'] ?? '';
        $card_number = $_POST['card_number'] ?? '';
        $card_expiry = $_POST['card_expiry'] ?? '';
        $card_cvv    = $_POST['card_cvv'] ?? '';
    }
    $pay_status = ($method === 'CCD') ? 'Paid' : 'Pending';
    $stmt = $conn->prepare("
        INSERT INTO payments
        (order_id, transaction_ref, amount, method,
         card_name, card_number, card_expiry, card_cvv,
         status, proof_image, created_at)
        VALUES (?,?,?,?,?,?,?,?,?,?,NOW())
    ");
    $stmt->bind_param(
        "isdsssssss",
        $order_id,
        $transaction_ref,
        $total_price,
        $method,
        $card_name,
        $card_number,
        $card_expiry,
        $card_cvv,
        $pay_status,
        $proof_image
    );
    $stmt->execute();
    $stmt->close();

    // Clear cart
    unset($_SESSION['cart']);
    setcookie('cart', '', time() - 3600, "/");

    if ($method === 'QR') {
        header("Location: qr_payment.php?order_id=$order_id");
    } else {
        header("Location: order_success.php?id=$order_id");
    }
    exit;
}

require 'inc/header.php';
?>

<div class="container mt-4">
    <div class="bg-info shadow-sm p-2 mb-2 rounded-1">
        <h2 class="mb-0 text-white">Checkout</h2>
    </div>

    <div class="card shadow-sm rounded-1 p-4">
        <form method="POST">
            <div class="row">
                <div class="col-md-4">
                    <div class="form-outline mb-3">
                        <input type="text" name="name" class="form-control" required placeholder=" " value="<?= htmlspecialchars($_POST['name'] ?? '') ?>">
                        <label class="form-label fw-semibold">Full Name</label>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-outline mb-3">
                        <input type="text" name="phone" class="form-control" required placeholder=" " value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>">
                        <label class="form-label fw-semibold">Phone Number</label>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-outline mb-3">
                        <input type="email" name="email" class="form-control" required placeholder=" " value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                        <label class="form-label fw-semibold">Email</label>
                    </div>
                </div>
            </div>

            <div class="form-outline mb-3">
                <textarea name="address" class="form-control" rows="3" required><?= htmlspecialchars($_POST['address'] ?? '') ?></textarea>
                <label class="form-label fw-semibold">Shipping Address</label>
            </div>

            <div class="form-outline mb-3">
                <select name="payment_method" id="payment_method" class="form-select" required>
                    <option value="QR">QR Payment</option>
                    <option value="CCD">Credit Card</option>
                </select>
            </div>

            <!-- Credit Card Fields -->
            <div id="credit_form" style="display:none;">
                <div class="form-outline mb-3">
                    <input type="text" name="card_name" class="form-control" placeholder="Your name">
                    <label class="form-label fw-semibold">Card Name</label>
                </div>
                <div class="form-outline mb-3">
                    <input type="text" name="card_number" class="form-control" placeholder="1234 5678 9012 3456">
                    <label class="form-label fw-semibold">Card Number</label>
                </div>
                <div class="row">
                    <div class="form-outline col-md-6 mb-3">
                        <input type="text" name="card_expiry" class="form-control" placeholder="MM/YY">
                        <label class="form-label fw-semibold">Expiry Date</label>
                    </div>
                    <div class="form-outline col-md-6 mb-3">
                        <input type="text" name="card_cvv" class="form-control" placeholder="123">
                        <label class="form-label fw-semibold">CVV</label>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-between mt-3">
                <a href="cart.php" class="btn btn-secondary w-50 py-2 fw-bold text-center">Cancel</a>
                <button type="submit" class="btn btn-gradient w-50 py-2 fw-bold">Place Order ($<?= number_format($total_price, 2) ?>)</button>
            </div>
        </form>
    </div>
</div>

<?php require 'inc/footer.php'; ?>

<script>
    document.getElementById('payment_method').addEventListener('change', function() {
        document.getElementById('credit_form').style.display =
            this.value === 'CCD' ? 'block' : 'none';
    });
</script>

<style>
    .btn-gradient {
        background: linear-gradient(135deg, #4f46e5, #3b82f6);
        color: #fff;
        font-size: 1.1rem;
        border: none;
        transition: 0.3s;
    }

    .btn-gradient:hover {
        background: linear-gradient(135deg, #3b82f6, #4f46e5);
        color: #fff;
    }
</style>