<?php
session_start();
require 'inc/db.php';

if (isset($_POST['product_id'], $_POST['qty'])) {
    $product_id = intval($_POST['product_id']);
    $qty = intval($_POST['qty']);

    if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];

    if (isset($_SESSION['cart'][$product_id])) {
        $_SESSION['cart'][$product_id]['qty'] += $qty;
    } else {
        // Fetch product info from DB
        $stmt = $conn->prepare("SELECT id, name, price, image, qty FROM products WHERE id=? AND status=1");
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        $product = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$product) {
            echo json_encode(['success' => false, 'msg' => 'Product not found']);
            exit;
        }

        $_SESSION['cart'][$product_id] = [
            'id' => $product['id'],
            'name' => $product['name'],
            'price' => $product['price'],
            'image' => $product['image'],
            'qty' => $qty
        ];
    }

    $totalItems = array_sum(array_column($_SESSION['cart'], 'qty'));
    echo json_encode(['success' => true, 'total' => $totalItems]);
    exit;
}

echo json_encode(['success' => false, 'msg' => 'Invalid request']);
