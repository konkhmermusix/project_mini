<?php
session_start();
require '../inc/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

require 'inc/header.php';
?>



<?php require 'inc/footer.php'; ?>