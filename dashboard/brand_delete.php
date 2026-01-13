<?php
require '../inc/db.php';
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit;
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Option 1: Soft delete
$conn->query("UPDATE brands SET status=0 WHERE id=$id");

// Option 2: Hard delete
// $conn->query("DELETE FROM brands WHERE id=$id");

header("Location: brands.php");
exit;
