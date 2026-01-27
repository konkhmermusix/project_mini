<?php
session_start();
require 'inc/db.php';
// Quick safety check: ensure order_id is present
$order_id = isset($_GET['order_id']) ? (int)$_GET['order_id'] : 0;

$res = $conn->query("SELECT amount FROM payments WHERE order_id=$order_id AND method='QR'");
$pay = $res->fetch_assoc();

if ($pay) {
    $amount = $pay['amount'];
    $qr_data = "ORDER:$order_id|AMOUNT:$amount";
    $qr_url  = "https://api.qrserver.com/v1/create-qr-code/?size=250x250&data=" . urlencode($qr_data);
} else {
    die("Order not found or payment method mismatch.");
}
require 'inc/header.php';
?>

<div class="container mt-5 mb-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow-lg border-0">
                <div class="card-header bg-success text-white text-center py-3">
                    <h4 class="mb-0">Payment Checkout</h4>
                </div>
                <div class="card-body p-4 text-center">
                    <div class="payment-qr-section mb-4">
                        <p class="text-muted">Scan the QR code below to pay</p>
                        <h3 class="fw-bold text-dark mb-3">$<?= number_format($amount, 2) ?></h3>
                        <img src="<?= $qr_url ?>" alt="Payment QR Code" class="img-fluid rounded border p-2 bg-light">
                    </div>

                    <hr class="my-4">

                    <form method="POST" action="confirm_payment.php" enctype="multipart/form-data" class="text-start">
                        <input type="hidden" name="order_id" value="<?= $order_id ?>">

                        <div class="mb-3">
                            <label for="proof_image" class="form-label fw-bold text-secondary">
                                <i class="bi bi-upload me-1"></i> Upload Payment Proof
                            </label>
                            <input type="file"
                                name="proof_image" id="proof_image" class="form-control" accept="image/*" required>
                            <div class="form-text">Please upload a clear screenshot of your transaction.</div>
                        </div>

                        <div class="d-grid mt-4">
                            <button type="submit" class="btn btn-success btn-lg shadow-sm">
                                Confirm Payment
                            </button>
                        </div>
                    </form>
                </div>
                <div class="card-footer text-center bg-light">
                    <small class="text-muted">Order ID: #<?= $order_id ?></small>
                </div>
            </div>
        </div>
    </div>
</div>

<?php 'inc/footer.php'; ?>