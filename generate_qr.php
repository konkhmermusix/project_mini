<?php
require 'phpqrcode/phpqrcode.php'; // path ត្រឹមត្រូវ

$order_id = $_GET['order_id'] ?? '0';
$total_price = $_GET['total_price'] ?? '0';

$data = "ORDER:{$order_id}|AMOUNT:{$total_price}";

$conn->query("
   INSERT INTO notifications (message, type, link)
    VALUES (
    'Order payment completed',
    'payment',
    'order_detail.php?id='.$order_id
    );
");

// Output QR code directly
header('Content-Type: image/png');
QRcode::png($data);
exit;
