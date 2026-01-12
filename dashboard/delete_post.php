<?php
require '../inc/db.php';
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') die("Access denied.");

$id = $_GET['id'];
$conn->query("DELETE FROM posts WHERE id=$id");
header("Location: blog.php");
