<?php
require 'inc/header.php';

$order_id = $_GET['order_id'] ?? 0;
$total_price = $_GET['amount'] ?? 0.00;

if (!$order_id) {
    die("Invalid order");
}

// Generate QR Code
$qr_data = "ORDER:$order_id|AMOUNT:$total_price";
$qr_url = "https://api.qrserver.com/v1/create-qr-code/?size=250x250&data=" . urlencode($qr_data);
?>

<div class="container mt-5 mb-5">
    <div class="card shadow-sm rounded-4 p-4">
        <h3 class="text-primary fw-bold mb-4 text-center">Scan QR to Pay</h3>
        <p class="text-center mb-4"><strong>Amount:</strong> $<?= number_format($total_price, 2) ?></p>

        <div class="row">
            <!-- QR Code -->
            <div class="col-md-6 text-center mb-4">
                <div class="d-inline-block p-3 bg-light rounded-3 shadow-sm mb-3">
                    <img src="<?= $qr_url ?>" alt="QR Code" class="img-fluid" style="max-width:250px;">
                </div>
                <p id="qr-message" class="text-info fw-semibold">
                    Scan or Save QR using your camera or upload a QR image.
                </p>
            </div>

            <!-- Camera Scanner -->
            <div class="col-md-6 mb-4">
                <div id="reader" style="width:100%; height:300px; border: 2px dashed #4f46e5; border-radius: 10px;"></div>
            </div>
        </div>

        <!-- Pay Button -->
        <form method="POST" action="confirm_payment.php">
            <input type="hidden" name="order_id" value="<?= $order_id ?>">
            <button type="submit" id="payBtn" class="btn btn-gradient btn-lg fw-bold" disabled>
                I have paid
            </button>
        </form>
    </div>
</div>

<!-- Styles -->
<style>
    .btn-gradient {
        background: linear-gradient(135deg, #4f46e5, #3b82f6);
        color: #fff;
        border: none;
        width: 100%;
        transition: 0.3s;
    }

    .btn-gradient:hover {
        background: linear-gradient(135deg, #3b82f6, #4f46e5);
        color: #fff;
    }

    #reader {
        background-color: #f9f9f9;
    }
</style>

<!-- SweetAlert for message popup -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://unpkg.com/html5-qrcode"></script>
<script>
    const orderId = "<?= $order_id ?>";
    const amount = "<?= $total_price ?>";
    const payBtn = document.getElementById('payBtn');
    const qrMessage = document.getElementById('qr-message');

    function showPopup(title, text, icon = 'info') {
        Swal.fire({
            title: title,
            text: text,
            icon: icon,
            confirmButtonColor: '#4f46e5'
        });
    }

    function handleScan(decodedText) {
        if (decodedText === `ORDER:${orderId}|AMOUNT:${amount}`) {
            payBtn.disabled = false;
            qrMessage.textContent = "QR Scanned Successfully! You can now click 'I have paid'.";
            qrMessage.className = "text-success fw-bold";
            html5QrcodeScanner.clear();
            showPopup("Success!", "QR code scanned successfully.", "success");
        } else {
            qrMessage.textContent = "QR does not match this order. Try again.";
            qrMessage.className = "text-danger fw-bold";
            showPopup("Error", "Scanned QR does not match this order.", "error");
        }
    }

    // Camera scan
    var html5QrcodeScanner = new Html5QrcodeScanner(
        "reader", {
            fps: 10,
            qrbox: 250
        }
    );
    html5QrcodeScanner.render(handleScan);

    // File upload scan
    document.getElementById('qr-file').addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            Html5Qrcode.scanFile(file, true)
                .then(decodedText => handleScan(decodedText))
                .catch(err => {
                    qrMessage.textContent = "Could not scan QR from image. Try another file.";
                    qrMessage.className = "text-danger fw-bold";
                    showPopup("Error", "Could not scan QR from file.", "error");
                });
        }
    });
</script>

<?php require 'inc/footer.php'; ?>