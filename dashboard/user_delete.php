<?php
require '../inc/db.php';
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$conn->query("UPDATE users SET status=0 WHERE id=$id");
// $conn->query("DELETE FROM users WHERE id=$id");

header("Location: users.php");
