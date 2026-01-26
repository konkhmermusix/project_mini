<?php
session_start();
require 'inc/db.php';
include 'inc/header.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// fetch user
$stmt = $conn->prepare("
    SELECT username, email, phone, address, role, status, created_at, last_login
    FROM users WHERE id = ?
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// avatar
$avatarLetter = strtoupper(mb_substr($user['username'], 0, 1));
$colors = ['#1877f2', '#e91e63', '#9c27b0', '#ff9800', '#4caf50', '#009688'];
$bg = $colors[crc32($user['username']) % count($colors)];

// cover gradient
$gradients = [
    'linear-gradient(135deg,#667eea,#764ba2)',
    'linear-gradient(135deg,#2193b0,#6dd5ed)',
    'linear-gradient(135deg,#cc2b5e,#753a88)',
    'linear-gradient(135deg,#ee9ca7,#ffdde1)',
    'linear-gradient(135deg,#42275a,#734b6d)',
];
$coverGradient = $gradients[crc32($user['username']) % count($gradients)];
?>

<div class="container my-5">

    <div class="profile-cover mb-4">
        <div class="cover-bg" style="background:<?= $coverGradient ?>"></div>
        <div class="avatar-wrapper">
            <div class="profile-avatar" style="background:<?= $bg ?>"> <?= $avatarLetter ?> </div>
            <h4 class="mt-2 mb-0 text-white"><?= htmlspecialchars($user['username']) ?></h4>
            <small class="text-light"><?= htmlspecialchars($user['email']) ?></small>
        </div>
    </div>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show text-center">
            <?= $_SESSION['success'] ?>
            <button class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php unset($_SESSION['success']);
    endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show text-center">
            <?= $_SESSION['error'] ?>
            <button class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php unset($_SESSION['error']);
    endif; ?>

    <div class="profile-buttons">
        <a href="profile_edit.php" class="btn btn-primary w-25"><i class="bi bi-pencil-square"></i> Edit</a>
        <a href="profile_password.php" class="btn btn-warning w-25"><i class="bi bi-lock"></i> Change Password</a>
    </div>

    <div class="card shadow-sm mt-4">
        <div class="card-body">
            <h5 class="card-title mb-3">Profile Information</h5>
            <table class="table table-borderless">
                <tr>
                    <th>Username</th>
                    <td><?= htmlspecialchars($user['username']) ?></td>
                </tr>
                <tr>
                    <th>Email</th>
                    <td><?= htmlspecialchars($user['email']) ?></td>
                </tr>
                <tr>
                    <th>Phone</th>
                    <td><?= htmlspecialchars($user['phone'] ?? '-') ?></td>
                </tr>
                <tr>
                    <th>Address</th>
                    <td><?= nl2br(htmlspecialchars($user['address'] ?? '-')) ?></td>
                </tr>
                <tr>
                    <th>Role</th>
                    <td>
                        <span class="badge bg-<?= $user['role'] == 'admin' ? 'danger' : 'primary' ?>">
                            <?= ucfirst($user['role']) ?>
                        </span>
                    </td>
                </tr>
                <tr>
                    <th>Status</th>
                    <td><?= $user['status'] ? '<span class="text-success">Active</span>' : '<span class="text-danger">Inactive</span>' ?></td>
                </tr>
                <tr>
                    <th>Joined</th>
                    <td><?= date('d M Y', strtotime($user['created_at'])) ?></td>
                </tr>
                <tr>
                    <th>Last Login</th>
                    <td><?= $user['last_login'] ? date('d M Y H:i', strtotime($user['last_login'])) : 'Never' ?></td>
                </tr>
            </table>
        </div>
    </div>
</div>

<?php require 'inc/footer.php'; ?>