<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Admin guard
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Google Font -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">

    <style>
        body {
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
            display: flex;
            overflow-x: hidden;
        }

        /* Sidebar */
        .sidebar {
            width: 250px;
            background-color: #212529;
            color: #fff;
            height: 100vh;
            position: fixed;
            top: 0;

            /* hide by default on mobile */
            padding: 1rem 15px 15px 15px;
            transition: all 0.3s ease;
            z-index: 1050;
        }

        /* Show sidebar */
        .sidebar.show {
            left: 0;
        }

        /* Close button inside sidebar (mobile only) */
        .sidebar .close-sidebar {
            display: none;
            position: absolute;
            top: 10px;
            right: 10px;
            background: transparent;
            border: none;
            color: #fff;
            font-size: 1.5rem;
        }

        .sidebar .nav-link {
            color: #adb5bd;
            font-weight: 500;
            padding: 10px 20px;
            display: block;
            transition: 0.2s;
            margin: 5px 5px;
        }

        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            background-color: #0d6efd;
            color: #fff;
            border-radius: 5px;
            padding: 10px;
        }

        .sidebar-brand {
            font-size: 1.5rem;
            font-weight: 700;
            color: #fff;
            text-align: center;
            margin-bottom: 1.5rem;
            display: block;
        }

        /* Main content */
        .main-content {
            margin-left: 250px;
            width: calc(100% - 250px);
            padding: 2px;
        }

        /* Sticky Header */
        .header-sticky {
            z-index: 1020;
            border-bottom: 1px solid #dee2e6;
        }

        @media (max-width: 992px) {
            .sidebar {
                position: fixed;
                left: -250px;
                transition: 0.3s;
                z-index: 1050;
            }

            .sidebar.show {
                left: 0;
            }

            .main-content {
                margin-left: 0;
                width: 100%;
            }

            .sidebar-toggler {
                display: inline-block;
                position: fixed;
                top: 10px;
                left: 10px;
                z-index: 1060;
            }

            .sidebar .close-sidebar {
                display: block;
            }
        }
    </style>
</head>

<body>

    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <!-- Close button inside sidebar -->
        <!-- Sidebar Header -->
        <div class="d-flex align-items-center justify-content-between py-3 px-3 position-relative" style="height:60px;">

            <!-- Sidebar Brand / Title -->
            <a class="sidebar-brand text-decoration-none" href="index.php">
                <h3 style="line-height:1; margin-top: 30px;">Admin Fruite</h3>
            </a>

            <!-- Close button (mobile only) -->
            <button type="button" class="btn btn-close btn-close-white btn-sm d-lg-none position-absolute top-2 end-0 "
                id="closeSidebar" aria-label="Close"></button>

        </div>

        <!-- Admin Sidebar -->
        <nav class="nav flex-column">

            <!-- Dashboard -->
            <a class="nav-link <?php if (basename($_SERVER['PHP_SELF']) == 'index.php') echo 'active'; ?>" href="index.php">
                <i class="bi bi-speedometer2 me-2"></i> Dashboard
            </a>

            <!-- Products Group -->
            <a class="nav-link collapsed" data-bs-toggle="collapse" href="#productsMenu" role="button" aria-expanded="false" aria-controls="productsMenu">
                <i class="bi bi-box-seam me-2"></i> Posts
                <i class="bi bi-caret-down-fill ms-auto"></i>
            </a>
            <div class="collapse <?php if (in_array(basename($_SERVER['PHP_SELF']), ['products.php', 'categories.php', 'brands.php', 'slideshows.php'])) echo 'show'; ?>" id="productsMenu">
                <nav class="nav flex-column ms-3">
                    <a class="nav-link <?php if (basename($_SERVER['PHP_SELF']) == 'products.php') echo 'active'; ?>" href="products.php">
                        <i class="bi bi-box-seam me-2"></i> Products
                    </a>
                    <a class="nav-link <?php if (basename($_SERVER['PHP_SELF']) == 'categories.php') echo 'active'; ?>" href="categories.php">
                        <i class="bi bi-bag me-2"></i> Categories
                    </a>
                    <a class="nav-link <?php if (basename($_SERVER['PHP_SELF']) == 'brands.php') echo 'active'; ?>" href="brands.php">
                        <i class="bi bi-tags me-2"></i> Brands
                    </a>
                    <a class="nav-link <?php if (basename($_SERVER['PHP_SELF']) == 'slideshows.php') echo 'active'; ?>" href="slideshows.php">
                        <i class="bi bi-journal-text me-2"></i> Slide Shows
                    </a>
                </nav>
            </div>

            <!-- Blog -->
            <a class="nav-link <?php if (basename($_SERVER['PHP_SELF']) == 'blog.php') echo 'active'; ?>" href="blog.php">
                <i class="bi bi-journal-text me-2"></i> Blog
            </a>

            <!-- Orders / Reports Group -->
            <a class="nav-link collapsed" data-bs-toggle="collapse" href="#ordersMenu" role="button" aria-expanded="false" aria-controls="ordersMenu">
                <i class="bi bi-receipt me-2"></i>Reports
                <i class="bi bi-caret-down-fill ms-auto"></i>
            </a>
            <div class="collapse <?php if (in_array(basename($_SERVER['PHP_SELF']), ['orders_report.php', 'sales_report.php', 'top_products.php'])) echo 'show'; ?>" id="ordersMenu">
                <nav class="nav flex-column ms-3">
                    <a class="nav-link <?php if (basename($_SERVER['PHP_SELF']) == 'orders_report.php') echo 'active'; ?>" href="orders_report.php">
                        <i class="bi bi-bar-chart me-2"></i> Orders Report
                    </a>
                    <a class="nav-link <?php if (basename($_SERVER['PHP_SELF']) == 'sales_report.php') echo 'active'; ?>" href="sales_report.php">
                        <i class="bi bi-graph-up me-2"></i> Sales Report
                    </a>
                    <a class="nav-link <?php if (basename($_SERVER['PHP_SELF']) == 'top_products.php') echo 'active'; ?>" href="top_products.php">
                        <i class="bi bi-award me-2"></i> Top Products
                    </a>
                </nav>
            </div>

            <!-- Users -->
            <a class="nav-link <?php if (basename($_SERVER['PHP_SELF']) == 'users.php') echo 'active'; ?>" href="users.php">
                <i class="bi bi-people me-2"></i> Users
            </a>

            <!-- Logout -->
            <a class="nav-link text-warning" href="../logout.php">
                <i class="bi bi-box-arrow-right me-2"></i> Logout
            </a>

        </nav>

    </div>

    <!-- Main content -->
    <div class="main-content ms-auto">

        <!-- Header content (Sticky) -->
        <div class="sticky-top header-sticky bg-white shadow-sm p-3 mb-4 d-flex justify-content-between align-items-center flex-wrap">

            <!-- Left: Sidebar toggle + Page title & breadcrumb -->
            <div class="d-flex align-items-center gap-2">
                <!-- Sidebar toggle button -->
                <button class="btn btn-primary d-lg-none" id="sidebarToggle">
                    <i class="bi bi-list"></i>
                </button>

                <!-- Page title & breadcrumb -->
                <div>
                    <h2 class="h4 fw-bold mb-0">Dashboard</h2>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0 mt-1">
                            <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Dashboard</li>
                        </ol>
                    </nav>
                </div>
            </div>

            <!-- Right: Search / Notifications / Account -->
            <div class="d-flex align-items-center flex-wrap gap-2">
                <!-- Notifications -->
                <div class="dropdown">
                    <button class="btn btn-outline-secondary position-relative" type="button" id="notificationDropdown" data-bs-toggle="dropdown">
                        <i class="bi bi-bell"></i>
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">3
                            <span class="visually-hidden">unread messages</span>
                        </span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow" aria-labelledby="notificationDropdown" style="width:300px; max-height:250px; overflow-y:auto;">
                        <li class="dropdown-header">Notifications</li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>
                        <li><a class="dropdown-item" href="#">New user registered</a></li>
                        <li><a class="dropdown-item" href="#">Product updated</a></li>
                        <li><a class="dropdown-item" href="#">Server rebooted</a></li>
                    </ul>
                </div>

                <!-- Account menu -->
                <div class="dropdown">
                    <button class="btn btn-outline-secondary dropdown-toggle" type="button" id="accountDropdown" data-bs-toggle="dropdown">
                        <i class="bi bi-person-circle"></i> Admin
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow" aria-labelledby="accountDropdown">
                        <li><a class="dropdown-item" href="#">Profile</a></li>
                        <li><a class="dropdown-item" href="#">Settings</a></li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>
                        <li><a class="dropdown-item text-danger" href="../logout.php">Logout</a></li>
                    </ul>
                </div>

            </div>
        </div>

        <!-- Main dashboard content -->
        <div class="container-fluid">
            <!-- Example cards or tables go here -->