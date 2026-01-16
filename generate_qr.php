<?php
require 'phpqrcode/phpqrcode.php'; // path ត្រឹមត្រូវ

$order_id = $_GET['order_id'] ?? '0';
$total_price = $_GET['total_price'] ?? '0';

$data = "ORDER:{$order_id}|AMOUNT:{$total_price}";

// Output QR code directly
header('Content-Type: image/png');
QRcode::png($data);
exit;
