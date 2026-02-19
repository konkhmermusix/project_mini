<?php
session_start();

if (isset($_SESSION['cart'])) {
    setcookie('cart', json_encode($_SESSION['cart']), time() + (7 * 24 * 60 * 60), "/");
}

session_destroy();
header("Location: index.php");
exit;
