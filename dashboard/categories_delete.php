
<?php
require '../inc/db.php';
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Soft delete (set status = 0)
$conn->query("UPDATE categories SET status=0 WHERE id=$id");
// $conn->query("DELETE FROM categories WHERE id=$id");

header("Location: categories.php");
exit;
