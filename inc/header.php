<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Example user data
$username = $_SESSION['username'] ?? 'Guest';
$role = $_SESSION['role'] ?? '';
$firstLetter = strtoupper($username[0] ?? 'G');
$cartCount = $_SESSION['cart_count'] ?? 0;
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>My PHP Project</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Google Font -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">

    <!-- Link Swiper's CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@12/swiper-bundle.min.css" />

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">



    <style>
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
            margin: 30px auto;
            border-radius: 10px;
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
    </style>
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm p-3 sticky-top">
        <div class="container">

            <!-- Brand -->
            <a class="navbar-brand" href="index.php">MyShop</a>
            <button class="navbar-toggler shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarNav">

                <!-- Left nav links -->
                <ul class="navbar-nav mx-auto mb-2 mb-lg-0 text-center">
                    <li class="nav-item me-3">
                        <a class="nav-link <?php if (basename($_SERVER['PHP_SELF']) == 'index.php') echo 'active'; ?>" href="index.php">
                            Home
                        </a>
                    </li>
                    <li class="nav-item me-3">
                        <a class="nav-link <?php if (basename($_SERVER['PHP_SELF']) == 'products.php') echo 'active'; ?>" href="products.php">
                            Products
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

                <!-- Right buttons -->
                <ul class="navbar-nav ms-auto align-items-lg-center">

                    <!-- Search -->
                    <li class="nav-item me-2">
                        <a class="btn btn-outline-primary btn-nav" href="#"><i class="bi bi-search"></i></a>
                    </li>

                    <!-- Cart -->
                    <li class="nav-item me-2">
                        <a class="btn btn-outline-primary btn-nav position-relative" href="cart.php" id="cartLink">
                            <i class="bi bi-cart"></i> Cart
                            <span id="cartCountBadge" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                <?= $cartCount ?? 0 ?>
                            </span>
                        </a>
                    </li>


                    <!-- Account / avatar -->
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <li class="nav-item dropdown me-2">
                            <a class="btn btn-nav avatar-circle dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <?= $firstLetter ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end shadow">
                                <li><a class="dropdown-item" href="profile.php">Profile</a></li>
                                <li><a class="dropdown-item" href="settings.php">Settings</a></li>
                                <?php if ($role == 'admin'): ?>
                                    <li><a class="dropdown-item" href="dashboard/index.php">Dashboard</a></li>
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

    <div class="container mt-4 mb-4">