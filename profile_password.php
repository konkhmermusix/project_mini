<?php
session_start();
require 'inc/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];


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

$stmt = $conn->prepare("SELECT username, email, phone, address FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current = $_POST['current_password'];
    $new = $_POST['new_password'];
    $confirm = $_POST['confirm_password'];

    $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();

    if (!password_verify($current, $user['password'])) {
        $_SESSION['error'] = "Current password is incorrect!";
    } elseif ($new !== $confirm) {
        $_SESSION['error'] = "New password and confirm password do not match!";
    } else {
        $hashed = password_hash($new, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE users SET password = ?, updated_at = NOW() WHERE id = ?");
        $stmt->bind_param("si", $hashed, $user_id);
        if ($stmt->execute()) {
            $_SESSION['success'] = "Password changed successfully!";
            header("Location: profile.php");
            exit();
        } else {
            $_SESSION['error'] = "Error updating password!";
        }
    }
}

require 'inc/header.php';
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
        <a href="profile.php" class="btn btn-warning w-25"><i class="bi bi-arrow-left"></i> Back</a>
    </div>
    <div class="card shadow-sm mt-4">
        <div class="card-body">
            <h5 class="card-title mb-3">Change Password</h5>
            <table class="table table-borderless">
                <form method="post">
                    <div class="form-outline mb-3">
                        <input type="password" name="current_password" class="form-control" placeholder=" " required>
                        <label class="form-label">Current Password</label>
                    </div>
                    <div class="form-outline mb-3">
                        <input type="password" name="new_password" class="form-control" placeholder=" " required>
                        <label class="form-label">New Password</label>
                    </div>
                    <div class="form-outline mb-3">
                        <input type="password" name="confirm_password" class="form-control" placeholder=" " required>
                        <label class="form-label">Confirm New Password</label>
                    </div>
                    <div class="d-flex">
                        <a href="profile.php" class="btn btn-secondary w-100 me-2">Cancel</a>
                        <button type="submit" class="btn btn-primary w-100">Change Password</button>
                    </div>
                </form>
            </table>
        </div>
    </div>
</div>

<?php require 'inc/footer.php'; ?>