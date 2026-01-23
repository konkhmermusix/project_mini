<?php
require 'inc/db.php';
require 'inc/header.php';

$order_id = intval($_GET['order_id']);

$order = $conn->query("
    SELECT total_price FROM orders WHERE id=$order_id
")->fetch_assoc();
?>

<h2>Scan QR to Pay</h2>
<p>Order ID: <?= $order_id ?></p>
<p>Amount: $<?= number_format($order['total_price'], 2) ?></p>

<img src="https://api.qrserver.com/v1/create-qr-code/?size=250x250&data=ORDER:<?= $order_id ?>|AMOUNT:<?= $order['total_price'] ?>">

<br><br>
<a href="order_success.php?id=<?= $order_id ?>" class="btn btn-success">
    I have paid
</a>

<?php require 'inc/footer.php'; ?>