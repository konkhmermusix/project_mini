<?php
session_start();
require 'inc/db.php';

// =====================
// CHECK LOGIN
// =====================
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// =====================
// USER INFO
// =====================
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT username, phone FROM users WHERE id=?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

$username = $user['username'] ?? '';
$phone    = $user['phone'] ?? '';

// =====================
// CART
// =====================
$cart = $_SESSION['cart'] ?? [];
if (empty($cart)) {
    echo "<div class='container mt-5 text-center'>
            <h4 class='text-danger'>Your cart is empty</h4>
            <a href='shop.php' class='btn btn-primary mt-3'>Go Shopping</a>
          </div>";
    exit;
}

// =====================
// TOTAL PRICE
// =====================
$total_price = 0;
foreach ($cart as $item) {
    $price = $item['selling_price'] ?? $item['price'];
    $total_price += $price * $item['qty'];
}

// =====================
// SUBMIT CHECKOUT
// =====================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name    = trim($_POST['name']);
    $phone_i = trim($_POST['phone']);
    $address = trim($_POST['address']);
    $method  = $_POST['payment_method'];

    if (!$name || !$phone_i || !$address || !$method) {
        die("All fields are required");
    }

    if (!in_array($method, ['QR', 'ABA', 'AC', 'CCD'])) {
        die("Invalid payment method");
    }

    $status = ($method === 'CCD') ? 'Paid' : 'Pending';

    // =====================
    // INSERT ORDER
    // =====================
    $stmt = $conn->prepare("
        INSERT INTO orders 
        (user_id,name,phone,total_price,address,payment_method,status,created_at)
        VALUES (?,?,?,?,?,?,?,NOW())
    ");
    $stmt->bind_param(
        "issssss",
        $user_id,
        $name,
        $phone_i,
        $total_price,
        $address,
        $method,
        $status
    );
    $stmt->execute();
    $order_id = $stmt->insert_id;
    $stmt->close();

    // =====================
    // ORDER ITEMS
    // =====================
    foreach ($cart as $item) {
        $price = $item['selling_price'] ?? $item['price'];
        $stmt = $conn->prepare("
            INSERT INTO order_items (order_id,product_id,qty,price)
            VALUES (?,?,?,?)
        ");
        $stmt->bind_param(
            "iiid",
            $order_id,
            $item['id'],
            $item['qty'],
            $price
        );
        $stmt->execute();
        $stmt->close();
    }

    // =====================
    // UPLOAD QR SCREENSHOT
    // =====================
    $proof_image = null;
    if (in_array($method, ['QR', 'ABA', 'AC']) && isset($_FILES['proof_image'])) {
        if ($_FILES['proof_image']['error'] === 0) {
            $dir = "uploads/payments/";
            if (!is_dir($dir)) mkdir($dir, 0777, true);

            $ext = strtolower(pathinfo($_FILES['proof_image']['name'], PATHINFO_EXTENSION));
            $allow = ['jpg', 'jpeg', 'png', 'webp'];
            if (!in_array($ext, $allow)) die("Invalid image type");

            $file_name = "pay_" . time() . "_" . rand(1000, 9999) . "." . $ext;
            move_uploaded_file($_FILES['proof_image']['tmp_name'], $dir . $file_name);
            $proof_image = $dir . $file_name;
        }
    }

    // =====================
    // CREDIT CARD DATA
    // =====================
    $card_name = $card_number = $card_expiry = $card_cvv = null;
    if ($method === 'CCD') {
        $card_name   = $_POST['card_name'] ?? '';
        $card_number = $_POST['card_number'] ?? '';
        $card_expiry = $_POST['card_expiry'] ?? '';
        $card_cvv    = $_POST['card_cvv'] ?? '';
    }

    // =====================
    // INSERT PAYMENT
    // =====================
    $transaction_ref = strtoupper(uniqid("TXN"));

    $stmt = $conn->prepare("
        INSERT INTO payments
        (order_id,transaction_ref,amount,method,card_name,card_number,card_expiry,card_cvv,status,proof_image,created_at)
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
        $status,
        $proof_image
    );
    $stmt->execute();
    $stmt->close();

    // CLEAR CART
    unset($_SESSION['cart']);
    setcookie('cart', '', time() - 3600, '/');

    // REDIRECT
    if ($method === 'CCD') {
        header("Location: invoice.php?id=$order_id");
    } else {
        header("Location: order_success.php?order_id=$order_id");
    }
    exit;
}

// =====================
// AUTO QR URL (PHP)
// =====================
$auto_qr_data = "PAY:$total_price";
$auto_qr_url = "https://api.qrserver.com/v1/create-qr-code/?size=250x250&data=" . urlencode($auto_qr_data);


require 'inc/header.php';
?>

<div class="container mt-4">
    <div class="card p-4 shadow-sm">
        <h3 class="mb-3">Checkout</h3>

        <form method="POST" enctype="multipart/form-data">
            <div class="row mb-3">
                <div class="col-md-6">
                    <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($username) ?>" placeholder="Full Name" required>
                </div>
                <div class="col-md-6">
                    <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($phone) ?>" placeholder="Phone" required>
                </div>
            </div>

            <textarea name="address" class="form-control mb-3" placeholder="Shipping Address" required></textarea>

            <select name="payment_method" id="payment_method" class="form-select mb-3" required>
                <option value="">-- Select Payment --</option>
                <option value="QR">Auto QR</option>
                <option value="ABA">ABA QR</option>
                <option value="AC">ACLEDA QR</option>
                <option value="CCD">Credit Card</option>
            </select>

            <div class="row">
                <div class="col-md-6">
                    <div id="qr_box" style="display:none;" class="mb-3">
                        <h5 class="mb-2">Scan to Pay</h5>
                        <img id="qr_image" src="<?= $auto_qr_url ?>" width="150" class="border rounded p-2">
                        <p class="text-muted mt-2">Scan QR & Upload screenshot</p>
                    </div>
                </div>
                <div class="col-md-6">
                    <div id="qr_upload" style="display:none;">
                        <label class="form-label">Upload Payment Screenshot</label>
                        <input type="file" name="proof_image" class="form-control" accept="image/*">
                    </div>
                </div>
            </div>

            <div id="credit_form" style="display:none;">
                <input type="text" name="card_name" class="form-control mb-2" placeholder="Card Name">
                <input type="text" name="card_number" class="form-control mb-2" placeholder="Card Number">
                <div class="row">
                    <div class="col-md-6">
                        <input type="text" name="card_expiry" class="form-control mb-2" placeholder="MM/YY">
                    </div>
                    <div class="col-md-6">
                        <input type="text" name="card_cvv" class="form-control mb-2" placeholder="CVV">
                    </div>
                </div>
            </div>

            <button class="btn btn-primary w-100 mt-3">
                Place Order ($<?= number_format($total_price, 2) ?>)
            </button>
        </form>
    </div>
</div>

<script>
    const payment = document.getElementById('payment_method');
    const qrBox = document.getElementById('qr_box');
    const qrImg = document.getElementById('qr_image');
    const upload = document.getElementById('qr_upload');
    const credit = document.getElementById('credit_form');

    payment.addEventListener('change', function() {
        qrBox.style.display = 'none';
        upload.style.display = 'none';
        credit.style.display = 'none';

        if (this.value === 'ABA') {
            qrImg.src = 'static/image/qr/aba.png';
            qrBox.style.display = 'block';
            upload.style.display = 'block';
        }
        if (this.value === 'AC') {
            qrImg.src = 'static/image/qr/acleda.png';
            qrBox.style.display = 'block';
            upload.style.display = 'block';
        }
        if (this.value === 'QR') {
            qrImg.src = '<?= $auto_qr_url ?>';
            qrBox.style.display = 'block';
            upload.style.display = 'block';
        }
        if (this.value === 'CCD') {
            credit.style.display = 'block';
        }
    });
</script>

<?php require 'inc/footer.php'; ?>