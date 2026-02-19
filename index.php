<?php
require 'inc/db.php';
session_start();

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

require 'inc/header.php';
?>


<?php require 'inc/footer.php'; ?>