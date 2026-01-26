<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Example user data
$username = $_SESSION['username'] ?? 'Guest';
$role = $_SESSION['role'] ?? '';
$firstLetter = strtoupper($username[0] ?? 'G');
$cartCount = $_SESSION['cart_count'] ?? 0;

$page = basename($_SERVER['PHP_SELF'], '.php');
$pageTitle = ucwords(str_replace('-', ' ', $page));
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title><?= $pageTitle ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Kantumruy+Pro:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="static/css/bootstrap.min.css">
    <link rel="stylesheet" href="static/bootstrap-icons/bootstrap-icons.min.css">
    <link rel="stylesheet" href="static/css/swiper-bundle.min.css">
    <link rel="stylesheet" href="static/css/aos.css">


    <style>
        body {
            font-family: 'Kantumruy Pro', sans-serif;
        }


        /* Navbar style */
        .navbar-brand {
            font-weight: 600;
            font-size: 1.4rem;
        }

        .nav-link {
            font-weight: 500;
            transition: 0.2s ease-in-out;
        }

        .nav-link:hover {
            color: #0d6efd !important;
            text-decoration: underline;
        }

        .btn-nav {
            border-radius: 5px;
            padding: 5px 12px;
            font-weight: 500;
        }

        .active {
            font-weight: 600;
            color: #0d6efd !important;
        }

        /* Circle avatar */
        .avatar-circle {
            width: 38px;
            height: 38px;
            border-radius: 50%;
            background: #0d6efd;
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 1rem;
        }

        .swiper {
            width: 100%;
            height: 100%;
        }

        .swiper-slide {
            text-align: center;
            font-size: 18px;
            background: #444;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .swiper-slide img {
            display: block;
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .swiper {
            width: 100%;
            height: 400px;
            margin: 0px auto;

        }

        .swiper-slide {
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 30px;
            color: #fff;
        }

        .slide1 {
            background: #1abc9c;
        }

        .slide2 {
            background: #3498db;
        }

        .slide3 {
            background: #9b59b6;
        }

        .review-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            object-fit: cover;
        }

        .avatar-text {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: #0d6efd;
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 18px;
        }

        .alert-right {
            position: fixed;
            top: 20px;
            right: 20px;
            min-width: 300px;
            z-index: 1055;
            border-radius: 8px;
        }

        .alert-top-right {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1050;
            /* Bootstrap modal higher z-index */
            min-width: 250px;
        }

        .product-container {
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
        }

        .product-card {
            background: #fff;
            width: 220px;
            padding: 15px;
            border-radius: 10px;
            text-align: center;
            position: relative;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .product-card img {
            width: 100%;
            height: 180px;
            object-fit: contain;
        }

        .discount {
            position: absolute;
            top: 10px;
            right: 10px;
            background: #0d6efd;
            color: #fff;
            font-size: 12px;
            padding: 4px 6px;
            border-radius: 5px;
        }

        .product-card h3 {
            font-size: 14px;
            margin: 10px 0;
        }

        .price .new {
            font-weight: bold;
            font-size: 16px;
        }

        .price .old {
            text-decoration: line-through;
            color: #999;
            font-size: 13px;
            margin-left: 5px;
        }

        .save {
            color: green;
            font-size: 13px;
        }

        .btn-group {
            display: flex;
            gap: 5px;
            justify-content: center;
            margin-top: 10px;
        }

        .product-slider-wrapper {
            overflow: hidden;
            width: 100%;
        }

        .product-slider {
            display: flex;
            gap: 16px;
            scroll-behavior: smooth;
            overflow-x: auto;
            scrollbar-width: none;
        }

        .product-slider::-webkit-scrollbar {
            display: none;
        }

        .product-slide {
            min-width: 220px;
            max-width: 220px;
            flex-shrink: 0;
        }

        .product-card {
            border-radius: 8px;
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
        }

        .card-img-top {
            width: 100%;
            height: 180px;
            object-fit: cover;
            object-position: center;
            border-radius: 6px;
            transition: transform 0.3s;
        }

        .card-img-top:hover {
            transform: scale(1.05);
        }

        @media (max-width: 768px) {
            .card-img-top {
                height: 150px;
            }
        }

        @media (max-width: 576px) {
            .card-img-top {
                height: 130px;
            }
        }

        .profile-cover {
            position: relative;
            height: 280px;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 8px 20px rgba(0, 0, 0, .15);
        }

        .cover-bg {
            width: 100%;
            height: 100%;
            background-size: cover;
            background-position: center;
        }

        .avatar-wrapper {
            position: absolute;
            bottom: -60px;
            left: 50%;
            transform: translateX(-50%);
            text-align: center;
        }

        .profile-avatar {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            color: #fff;
            font-size: 50px;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 5px solid #fff;
            box-shadow: 0 8px 20px rgba(0, 0, 0, .3);
            margin: auto;
        }

        .profile-buttons {
            margin-top: 80px;
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            justify-content: center;
        }

        .profile-buttons .btn {
            flex: 1 1 45%;
            padding: 10px 0;
            font-weight: 600;
            border-radius: 8px;
            transition: transform 0.2s;
        }

        .profile-buttons .btn:hover {
            transform: translateY(-3px);
        }

        .table th {
            width: 160px;
        }

        .profile-cover {
            position: relative;
            height: 260px;
            border-radius: 14px;
            overflow: hidden;
        }

        .cover-bg {
            width: 100%;
            height: 100%;
        }

        .avatar-wrapper {
            position: absolute;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%);
            text-align: center;
        }

        .profile-avatar {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            color: #fff;
            font-size: 48px;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 6px solid #fff;
            box-shadow: 0 8px 18px rgba(0, 0, 0, .3);
            margin: auto;
        }

        .btn-primary {
            border-radius: 5px;
            padding: 10px;
            font-weight: 500;
        }

        .form-outline {
            position: relative;
        }

        .form-outline input {
            height: 45px;
            border-radius: 5px;
            padding: 16px 12px;
        }

        .form-outline label {
            position: absolute;
            top: 50%;
            left: 12px;
            transform: translateY(-50%);
            background: #fff;
            padding: 0 6px;
            color: #6c757d;
            font-size: 14px;
            transition: 0.2s ease;
            pointer-events: none;
        }

        .form-outline input:focus+label,
        .form-outline input:not(:placeholder-shown)+label {
            top: 0;
            font-size: 12px;
            color: #0d6efd;
        }
    </style>
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm p-3 sticky-top">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <h3>LSTECH</h3>
            </a>
            <button class="navbar-toggler shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav mx-auto mb-2 mb-lg-0 text-center">
                    <li class="nav-item me-3">
                        <a class="nav-link <?php if (basename($_SERVER['PHP_SELF']) == 'index.php') echo 'active'; ?>" href="index.php">
                            Home
                        </a>
                    </li>
                    <li class="nav-item me-3">
                        <a class="nav-link <?php if (basename($_SERVER['PHP_SELF']) == 'shop.php') echo 'active'; ?>" href="shop.php">
                            Shop
                        </a>
                    </li>
                    <li class="nav-item me-3">
                        <a class="nav-link <?php if (basename($_SERVER['PHP_SELF']) == 'blog.php') echo 'active'; ?>" href="blog.php">Blog</a>
                    </li>
                    <li class="nav-item me-3">
                        <a class="nav-link <?php if (basename($_SERVER['PHP_SELF']) == 'about.php') echo 'active'; ?>" href="about.php">About</a>
                    </li>
                    <li class="nav-item me-3">
                        <a class="nav-link <?php if (basename($_SERVER['PHP_SELF']) == 'contact.php') echo 'active'; ?>" href="contact.php">Contact</a>
                    </li>
                </ul>

                <ul class="navbar-nav ms-auto align-items-lg-center">
                    <li class="nav-item me-2">
                        <form action="search.php" method="GET" class="input-group">
                            <input
                                type="text"
                                name="q"
                                class="form-control shadow-sm"
                                placeholder="Search products"
                                required>
                            <button class="btn btn-outline-primary btn-nav shadow-none" type="submit">
                                <i class="bi bi-search"></i>
                            </button>
                        </form>
                    </li>


                    <li class="nav-item me-2">
                        <a class="btn btn-outline-primary btn-nav position-relative" href="cart.php" id="cartLink">
                            <i class="bi bi-cart"></i>
                            <span id="cartCountBadge" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                <?= isset($_SESSION['cart']) ? array_sum(array_column($_SESSION['cart'], 'qty')) : 0 ?>
                            </span>
                        </a>
                    </li>

                    <!-- <a href="cart.php" class="btn btn-outline-primary position-relative">
                        <i class="bi bi-cart"></i>
                        <span id="cartCountBadge" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                           
                            //  isset($_SESSION['cart']) ? array_sum(array_column($_SESSION['cart'], 'qty')) : 0 
                    
                        </span>
                    </a> -->

                    <?php if (isset($_SESSION['user_id'])): ?>
                        <li class="nav-item dropdown me-2">
                            <a class="btn btn-nav avatar-circle dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <?= $firstLetter ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end shadow">
                                <li><a class="dropdown-item" href="profile.php">Profile</a></li>
                                <li><a class="dropdown-item" href="my_orders.php">My Orders</a></li>
                                <?php if ($role == 'admin'): ?>
                                    <li><a class="dropdown-item" href="dashboard/dashboard.php">Dashboard</a></li>
                                <?php endif; ?>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li><a class="dropdown-item text-danger" href="logout.php">Logout</a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item me-2"><a class="btn btn-outline-primary btn-nav" href="login.php">Login</a></li>
                        <li class="nav-item"><a class="btn btn-outline-primary btn-nav" href="register.php">Register</a></li>
                    <?php endif; ?>

                </ul>
            </div>
        </div>
    </nav>

    <div>