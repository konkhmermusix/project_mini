<?php
require '../inc/db.php';
$id = (int)$_GET['id'];

$conn->query("UPDATE users SET status=0 WHERE id=$id");

header("Location: users.php");
