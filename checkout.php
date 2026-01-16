<?php
session_start();
require 'inc/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$cart = $_SESSION['cart'] ?? [];
if (empty($cart)) {
    die("<div class='container mt-5 text-center'><h3>Cart is empty</h3></div>");
}

$total = 0;
foreach ($cart as $item) {
    $total += $item['qty'] * $item['price'];
}

include 'inc/header.php';
?>

<div class="container my-5">
    <div class="card shadow-sm rounded-4 p-4">
        <h2 class="mb-4 text-primary fw-bold">Checkout</h2>

        <form action="place_order.php" method="POST">
            <div class="mb-4">
                <label class="form-label fw-semibold">Shipping Address</label>
                <textarea class="form-control rounded-3 border-1 shadow-sm" name="address" rows="3" required><?= htmlspecialchars($_POST['address'] ?? '') ?></textarea>
            </div>

            <div class="mb-4">
                <label class="form-label fw-semibold">Payment Method</label>
                <select class="form-select rounded-3 border-1 shadow-sm" name="payment_method" id="payment_method" required>
                    <option value="COD">Cash On Delivery</option>
                    <option value="QR">QR Payment</option>
                    <option value="Credit">Credit Card</option>
                </select>
            </div>

            <div id="credit_form" class="border rounded-3 p-3 mb-4" style="display:none; background-color: #f9f9f9;">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Card Number</label>
                    <input type="text" name="card_number" class="form-control rounded-3" placeholder="1234 5678 9012 3456">
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Expiry Date</label>
                        <input type="text" name="card_expiry" class="form-control rounded-3" placeholder="MM/YY">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">CVV</label>
                        <input type="text" name="cvv" class="form-control rounded-3" placeholder="123">
                    </div>
                </div>
            </div>

            <input type="hidden" name="total_price" value="<?= $total ?>">

            <button type="submit" class="btn btn-gradient w-100 py-2 fw-bold">
                Place Order ($<?= number_format($total, 2) ?>)
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
    }

    textarea.form-control,
    input.form-control,
    select.form-select {
        transition: all 0.3s;
    }

    textarea.form-control:focus,
    input.form-control:focus,
    select.form-select:focus {
        box-shadow: 0 0 8px rgba(79, 70, 229, 0.3);
        border-color: #4f46e5;
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

<?php include 'inc/footer.php'; ?>