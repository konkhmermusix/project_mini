<?php
require_once __DIR__ . '/db.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$cartCount = $_SESSION['cart_count'] ?? 0;
$page = basename($_SERVER['PHP_SELF'], '.php');
$pageTitle = ucwords(str_replace('-', ' ', $page));

$user = null;
$firstLetter = '';
$role = 'guest';

//  LOGIN USER
if (isset($_SESSION['user_id'])) {

    $user_id = $_SESSION['user_id'];

    $stmt = $conn->prepare("
        SELECT username, email, phone, address, role, status, created_at, last_login
        FROM users WHERE id = ?
    ");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();

    $username = $user['username'] ?? '';
    $role = $user['role'] ?? 'user';

    if (preg_match('/[\x{1780}-\x{17FF}]/u', $username)) {
        $firstLetter = '<i class="bi bi-person"></i>';
    } else {
        $firstLetter = strtoupper(mb_substr(trim($username), 0, 1, 'UTF-8'));
    }
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title><?= $pageTitle ?></title>
    <link rel="icon" href="static/image/favicon/icon.png" type="image/x-icon">
    <meta name="viewport" content="width=device-width, initial-scale=0.8">
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
                <h3>Leav Sis</h3>
            </a>
            <button class="navbar-toggler shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav mx-auto mb-2 mb-lg-0">
                    <li class="nav-item me-3">
                        <a class="nav-link <?php if (basename($_SERVER['PHP_SELF']) == 'index.php') echo 'active'; ?>" href="index.php">
                            Home
                        </a>
                    </li>
                    <li class="nav-item me-3">
                        <a class="nav-link <?php if (basename($_SERVER['PHP_SELF']) == '') echo 'active'; ?>" href="#">
                            Shop
                        </a>
                    </li>
                    <li class="nav-item me-3">
                        <a class="nav-link <?php if (basename($_SERVER['PHP_SELF']) == '') echo 'active'; ?>" href="#">About</a>
                    </li>
                    <li class="nav-item me-3">
                        <a class="nav-link <?php if (basename($_SERVER['PHP_SELF']) == '') echo 'active'; ?>" href="#">Contact</a>
                    </li>
                </ul>
                <div class="d-flex list-unstyled">
                    <li class="nav-item me-2">
                        <form action="" method="GET" class="input-group">
                            <input type="text" name="q" class="form-control border-dark" placeholder="Search products" required>
                            <button class="btn btn-outline-dark border-dark" type="submit">
                                <i class="bi bi-search"></i>
                            </button>
                        </form>
                    </li>

                    <li class="nav-item me-2">
                        <a class="btn btn-outline-dark btn-nav position-relative" href="#" id="cartLink">
                            <i class="bi bi-cart"></i>
                            <span id="cartCountBadge" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                2
                            </span>
                        </a>
                    </li>

                    <?php if (isset($_SESSION['user_id'])): ?>
                        <li class="nav-item dropdown me-2 d-flex">
                            <a class="btn btn-nav avatar-circle shadow-sm fw-bold" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <?= $firstLetter  ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end shadow">
                                <li><a class="dropdown-item" href="#">Profile</a></li>
                                <li><a class="dropdown-item" href="#">My Orders</a></li>
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
                        <li class="nav-item me-2"><a class="btn btn-outline-dark shadow-none btn-nav" href="login.php">Login</a></li>
                        <li class="nav-item"><a class="btn btn-outline-dark shadow-none btn-nav" href="register.php">Register</a></li>
                    <?php endif; ?>

                </div>
            </div>
        </div>
    </nav>
    <div>