<?php
require 'inc/header.php';
require '../inc/db.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}


// Count total products
$product_count = $conn->query("SELECT COUNT(*) AS total FROM products")->fetch_assoc()['total'];

// Count total blog posts
$blog_count = $conn->query("SELECT COUNT(*) AS total FROM posts")->fetch_assoc()['total'];

// Count total users
$user_count = $conn->query("SELECT COUNT(*) AS total FROM users")->fetch_assoc()['total'];
?>

<h2 class="mb-4">Dashboard Summary</h2>
<div class="row g-4">
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

<?php include 'inc/footer.php'; ?>