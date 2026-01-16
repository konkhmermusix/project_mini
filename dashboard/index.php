<?php
session_start();
require 'inc/header.php';
require '../inc/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

$stats = [];


// Count total products
$product_count = $conn->query("SELECT COUNT(*) AS total FROM products")->fetch_assoc()['total'];

// Count total blog posts
$blog_count = $conn->query("SELECT COUNT(*) AS total FROM posts")->fetch_assoc()['total'];

// Count total users
$user_count = $conn->query("SELECT COUNT(*) AS total FROM users")->fetch_assoc()['total'];

// Total Orders
$res = $conn->query("SELECT COUNT(*) total FROM orders");
$stats['orders'] = $res->fetch_assoc()['total'];

// Total Revenue (Paid only)
$res = $conn->query("SELECT SUM(total_price) total FROM orders WHERE status='Paid'");
$stats['revenue'] = $res->fetch_assoc()['total'] ?? 0;

// Total Users
$res = $conn->query("SELECT COUNT(*) total FROM users");
$stats['users'] = $res->fetch_assoc()['total'];

// Orders Today
$res = $conn->query("
    SELECT COUNT(*) total FROM orders 
    WHERE DATE(created_at) = CURDATE()
");
$stats['today'] = $res->fetch_assoc()['total'];
?>


<div class="container mt-4">
    <h2 class="mb-4">Dashboard Summary</h2>
    <div class="row g-3">
        <div class="col-md-3">
            <div class="card p-3 shadow">
                <i class="bi bi-cart-check fs-1 text-primary me-3"></i>
                <h6>Total Orders</h6>
                <h3><?= $stats['orders'] ?></h3>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card p-3 shadow">
                <h6>Total Revenue</h6>
                <h3>$<?= number_format($stats['revenue'], 2) ?></h3>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card p-3 shadow">
                <h6>Total Users</h6>
                <h3><?= $stats['users'] ?></h3>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card p-3 shadow">
                <h6>Orders Today</h6>
                <h3><?= $stats['today'] ?></h3>
            </div>
        </div>


        <div class="col-md-4">
            <div class="card text-white bg-primary shadow-sm">
                <div class="card-body">
                    <h5 class="card-title">Products</h5>
                    <p class="card-text fs-3"><?= $product_count ?></p>
                    <a href="products.php" class="btn btn-light btn-sm">Manage Products</a>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card text-white bg-success shadow-sm">
                <div class="card-body">
                    <h5 class="card-title">Blog Posts</h5>
                    <p class="card-text fs-3"><?= $blog_count ?></p>
                    <a href="blog.php" class="btn btn-light btn-sm">Manage Blog</a>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card text-white bg-warning shadow-sm">
                <div class="card-body">
                    <h5 class="card-title">Users</h5>
                    <p class="card-text fs-3"><?= $user_count ?></p>
                    <a href="users.php" class="btn btn-light btn-sm">Manage Users</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'inc/footer.php'; ?>