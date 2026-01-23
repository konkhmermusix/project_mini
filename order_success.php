<?php
require 'inc/header.php';
$order_id = $_GET['id'] ?? 0;

if (!$order_id) {
    die("<div class='container mt-5 text-center'><h3>Invalid order</h3></div>");
}
?>

<div class="container my-5">
    <div class="card shadow-sm rounded-4 p-4 text-center">
        <div class="mb-4">
            <i class="bi bi-check-circle-fill text-success" style="font-size:4rem;"></i>
        </div>

        <h2 class="mb-3 text-success fw-bold">Order Successful!</h2>
        <p class="mb-1">Thank you for your purchase.</p>

        <a href="index.php" class="btn btn-gradient btn-lg px-4 fw-bold">Continue Shopping</a>
    </div>
</div>

<style>
    .btn-gradient {
        background: linear-gradient(135deg, #4f46e5, #3b82f6);
        color: #fff;
        border: none;
        transition: 0.3s;
    }

    .btn-gradient:hover {
        background: linear-gradient(135deg, #3b82f6, #4f46e5);
        color: #fff;
    }
</style>

<?php
require 'inc/footer.php';
?>