<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require '../inc/db.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../login.php");
    exit;
}


$conn->query("UPDATE notifications SET is_read = 1 WHERE is_read = 0");

$page = basename($_SERVER['PHP_SELF'], '.php');
$pageTitle = ucwords(str_replace('-', ' ', $page));


$unread = $conn->query("
    SELECT COUNT(*) AS total
    FROM notifications
    WHERE is_read = 0
")->fetch_assoc();

$notifications = $conn->query("
    SELECT *
    FROM notifications
    ORDER BY created_at DESC
    LIMIT 10
");

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title><?= $pageTitle ?> -Admin</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">

    <style>
        body {
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
            display: flex;
            overflow-x: hidden;
        }

        .sidebar {
            width: 250px;
            background-color: #212529;
            color: #fff;
            height: 100vh;
            position: fixed;
            top: 0;

            padding: 1rem 15px 15px 15px;
            transition: all 0.3s ease;
            z-index: 1050;
        }

        .sidebar.show {
            left: 0;
        }

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

        .main-content {
            margin-left: 250px;
            width: calc(100% - 250px);
            padding: 2px;
        }

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

        .form-outline {
            position: relative;
        }

        .form-outline input,
        .form-outline textarea,
        .form-outline select {
            height: 45px;
            padding: 16px 12px;
            border-radius: 5px;
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
            pointer-events: none;
            transition: .2s;
        }

        .form-outline input:focus+label,
        .form-outline input:not(:placeholder-shown)+label,
        .form-outline textarea:focus+label,
        .form-outline textarea:not(:placeholder-shown)+label,
        .form-outline select:focus+label,
        .form-outline select:not(:placeholder-shown)+label {
            top: 0;
            font-size: 12px;
            color: #0d6efd;
        }

        .alert-right {
            position: fixed;
            top: 20px;
            right: 20px;
            min-width: 300px;
            z-index: 1055;
            border-radius: 8px;
        }

        #preview {
            display: block;
            max-height: 200px;
            margin-top: 10px;
        }
    </style>
</head>

<body style="font-family: Verdana, Geneva, Tahoma, sans-serif;">
    <div class="sidebar" id="sidebar">
        <div class="d-flex align-items-center justify-content-between py-3 px-3 position-relative" style="height:60px;">
            <a class="sidebar-brand text-decoration-none" href="dashboard.php">
                <h4 style="line-height:1; margin-top: 30px;">Dashboard</h4>
            </a>
            <button type="button" class="btn btn-close btn-close-white btn-sm d-lg-none position-absolute top-2 end-0 "
                id="closeSidebar" aria-label="Close"></button>
        </div>

        <nav class="nav flex-column">
            <a class="nav-link <?php if (basename($_SERVER['PHP_SELF']) == 'dashboard.php') echo 'active'; ?>" href="dashboard.php">
                <i class="bi bi-speedometer2 me-2"></i> Dashboard
            </a>

            <a class="nav-link text-warning" href="../logout.php">
                <i class="bi bi-box-arrow-right me-2"></i> Logout
            </a>

        </nav>
    </div>

    <div class="main-content ms-auto">
        <div class="sticky-top header-sticky bg-white shadow-sm p-3 mb-4 d-flex justify-content-between align-items-center flex-wrap">
            <div class="d-flex align-items-center gap-2">
                <button class="btn btn-primary d-lg-none" id="sidebarToggle">
                    <i class="bi bi-list"></i>
                </button>
                <div>
                    <h2 class="h4 fw-bold mb-0"><?= $pageTitle ?></h2>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0 mt-1">
                            <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
                            <li class="breadcrumb-item active"><?= $pageTitle ?></li>
                        </ol>
                    </nav>
                </div>
            </div>

            <div class="d-flex align-items-center flex-wrap gap-2">

                <div class="dropdown">
                    <button class="btn btn-outline-secondary position-relative" data-bs-toggle="dropdown">
                        <i class="bi bi-bell"></i>
                        <?php if ($unread['total'] > 0): ?>
                            <span class="badge bg-danger position-absolute top-0 start-100 translate-middle">
                                <?= $unread['total'] ?>
                            </span>
                        <?php endif; ?>
                    </button>

                </div>

                <div class="dropdown">
                    <button class="btn btn-outline-secondary dropdown-toggle" type="button" id="accountDropdown" data-bs-toggle="dropdown">
                        <i class="bi bi-person-circle"></i> Admin
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow" aria-labelledby="accountDropdown">
                        <li class="">
                            <a class="dropdown-item" href="../index.php">Go Website</a>
                        </li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>
                        <li><a class="dropdown-item text-danger" href="../logout.php">Logout</a></li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="container-fluid overflow-hidden">