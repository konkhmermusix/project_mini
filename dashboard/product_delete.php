<?php
require '../inc/db.php';
session_start();
// Admin Permision
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$conn->query("UPDATE products SET status=0 WHERE id=$id");
// $conn->query("DELETE FROM products WHERE id=$id");

header("Location: products.php");
