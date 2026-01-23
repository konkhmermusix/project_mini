<?php
session_start();
require 'inc/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Restore cart from cookie if session empty
if (empty($_SESSION['cart']) && isset($_COOKIE['cart'])) {
    $cookie_cart = json_decode($_COOKIE['cart'], true);
    if (is_array($cookie_cart)) {
        $_SESSION['cart'] = $cookie_cart;
    }
}

$cart = $_SESSION['cart'] ?? [];
if (empty($cart)) {
    require 'inc/header.php';
?>
    <div class="container mt-5 text-center">
        <h3 class="mb-3">Your Cart is Empty</h3>
        <p class="text-muted">You have no items in your cart. <a href="shop.php">Go Shopping</a></p>
        <a href="shop.php" class="btn btn-primary mt-3">Browse Products</a>
    </div>
<?php
    require 'inc/footer.php';
    exit;
}

// Calculate total
$total_price = 0;
foreach ($cart as $item) {
    if (!is_array($item)) continue;
    $total_price += $item['qty'] * $item['price'];
}

// Handle POST submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $address = trim($_POST['address'] ?? '');
    $payment_method = $_POST['payment_method'] ?? '';

    if (!in_array($payment_method, ['QR', 'Credit'])) {
        die("Invalid payment method.");
    }

    // Credit card validation
    if ($payment_method === 'Credit') {
        $card_number = $_POST['card_number'] ?? '';
        $card_name   = $_POST['card_name'] ?? 'Unknown';
        $card_expiry = $_POST['card_expiry'] ?? '';
        $card_cvv    = $_POST['card_cvv'] ?? '';
        if (empty($card_number) || empty($card_expiry) || empty($card_cvv)) {
            die("Credit card information is required.");
        }
    }

    // Insert order
    $stmt = $conn->prepare("INSERT INTO orders (user_id, address, total_price, payment_method, status) VALUES (?, ?, ?, ?, ?)");
    $status = 'Paid'; // Both QR and Credit will be considered Paid for demo
    $stmt->bind_param("isdss", $user_id, $address, $total_price, $payment_method, $status);
    $stmt->execute();
    $order_id = $stmt->insert_id;
    $stmt->close();

    // Insert order items
    foreach ($cart as $item) {
        if (!is_array($item)) continue;
        $pid   = (int)$item['id'];
        $qty   = (int)$item['qty'];
        $price = (float)$item['price'];
        $stmt = $conn->prepare("INSERT INTO order_items (order_id, product_id, qty, price) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("iiid", $order_id, $pid, $qty, $price);
        $stmt->execute();
        $stmt->close();
    }

    // Insert payment
    if ($payment_method === 'Credit') {

        $stmt = $conn->prepare("INSERT INTO payments (order_id, amount, method, card_name, card_number, card_expiry, card_cvv, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $paid_status = 'Paid';
        $stmt->bind_param(
            "idssssss",
            $order_id,
            $total_price, 
            $payment_method,
            $card_name,
            $card_number,
            $card_expiry,
            $card_cvv,
            $status
        );
        $stmt->execute();
        $stmt->close();
    } else {
        $stmt = $conn->prepare("INSERT INTO payments (order_id, amount, method, status) VALUES (?, ?, ?, ?)");
        $pending_status = 'Pending';
        $stmt->bind_param("idss", $order_id, $total_price, $payment_method, $pending_status);
        $stmt->execute();
        $stmt->close();
    }

    // Insert notification
    $notif_stmt = $conn->prepare("INSERT INTO notifications (message, type, link) VALUES (?, ?, ?)");
    $msg = "New order received";
    $type = "order";
    $link = "order_detail.php?id=" . $order_id;
    $notif_stmt->bind_param("sss", $msg, $type, $link);
    $notif_stmt->execute();
    $notif_stmt->close();

    // Clear cart
    unset($_SESSION['cart']);
    setcookie('cart', '', time() - 3600, '/');

    // Redirect QR to scan page
    if ($payment_method === 'QR') {
        header("Location: place_order.php?order_id=$order_id&amount=$total_price");
        exit;
    } elseif ($payment_method === 'Credit') {
        // Auto paid
        $conn->query("UPDATE payments SET status='Paid' WHERE order_id=$order_id");
        header("Location: order_success.php?id=$order_id");
        exit;
    }
}

require 'inc/header.php';
?>

<div class="container mt-4">
    <div class="bg-info shadow-sm p-2 mb-2 rounded-1">
        <h2 class="mb-0 text-white">Checkout</h2>
    </div>

    <div class="card shadow-sm rounded-1 p-4">
        <form method="POST">
            <div class="mb-4">
                <label class="form-label fw-semibold">Shipping Address</label>
                <textarea name="address" class="form-control" rows="3" required><?= htmlspecialchars($_POST['address'] ?? '') ?></textarea>
            </div>

            <div class="mb-4">
                <label class="form-label fw-semibold">Payment Method</label>
                <select name="payment_method" id="payment_method" class="form-select" required>
                    <option value="QR">QR Payment</option>
                    <option value="Credit">Credit Card</option>
                </select>
            </div>

            <div id="credit_form" style="display:none;">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Card Name</label>
                    <input type="text" name="card_name" class="form-control" placeholder="Your name">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Card Number</label>
                    <input type="text" name="card_number" class="form-control" placeholder="1234 5678 9012 3456">
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Expiry Date</label>
                        <input type="text" name="card_expiry" class="form-control" placeholder="MM/YY">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">CVV</label>
                        <input type="text" name="card_cvv" class="form-control" placeholder="123">
                    </div>
                </div>
            </div>

            <input type="hidden" name="total_price" value="<?= $total_price ?>">

            <button type="submit" class="btn btn-gradient w-100 py-2 fw-bold">
                Place Order ($<?= number_format($total_price, 2) ?>)
            </button>
        </form>
    </div>
</div>

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

<script>
    document.getElementById('payment_method').addEventListener('change', function() {
        const creditDiv = document.getElementById('credit_form');
        if (this.value === 'Credit') {
            creditDiv.style.display = 'block';
            creditDiv.querySelectorAll('input').forEach(i => i.required = true);
        } else {
            creditDiv.style.display = 'none';
            creditDiv.querySelectorAll('input').forEach(i => i.required = false);
        }
    });
</script>

<?php require 'inc/footer.php'; ?>