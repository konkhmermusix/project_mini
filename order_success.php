<?php
session_start();
require 'inc/db.php';

$id = intval($_GET['id']);

// update payment + order
$conn->query("
    UPDATE payments SET status='Paid' WHERE order_id=$id
");
$conn->query("
    UPDATE orders SET status='Paid' WHERE id=$id
");

// safety clear again
unset($_SESSION['cart']);
setcookie('cart', '', time() - 3600, '/');

include 'inc/header.php';
?>

<h2>Order Successful</h2>
<p>Your order ID: <?= $id ?></p>

<a href="index.php" class="btn btn-primary">Continue Shopping</a>

<?php include 'inc/footer.php'; ?>