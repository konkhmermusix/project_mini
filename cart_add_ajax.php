<?php
session_start();
require 'inc/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Check login
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['login' => true]);
        exit;
    }

    $product_id = intval($_POST['product_id']);
    $qty = intval($_POST['qty']);
    if ($qty < 1) $qty = 1;

    // Fetch product
    $stmt = $conn->prepare("SELECT id, name, price, qty, image FROM products WHERE id=? AND status=1");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $product = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$product) {
        echo json_encode(['success' => false]);
        exit;
    }

    if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];

    if (isset($_SESSION['cart'][$product_id])) {
        $newQty = $_SESSION['cart'][$product_id]['qty'] + $qty;
        if ($newQty > $product['qty']) $newQty = $product['qty'];
        $_SESSION['cart'][$product_id]['qty'] = $newQty;
    } else {
        $_SESSION['cart'][$product_id] = [
            'id'    => $product['id'],
            'name'  => $product['name'],
            'price' => $product['price'],
            'qty'   => $qty,
            'image' => $product['image']
        ];
    }

    // Save cart to cookie
    setcookie('cart', json_encode($_SESSION['cart']), time() + (7 * 24 * 60 * 60), "/");

    echo json_encode([
        'success' => true,
        'cartCount' => array_sum(array_column($_SESSION['cart'], 'qty'))
    ]);
}
