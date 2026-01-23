<?php
require '../inc/db.php';
session_start();
// Admin Permision
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$conn->query("UPDATE slideshow SET status=0 WHERE id=$id");
// $conn->query("DELETE FROM slideshow WHERE id=$id");

header("Location: slideshows.php");
