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
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);

    if (empty($username) || empty($email)) {
        $_SESSION['error'] = "Username and Email are required!";
    } else {
        $stmt = $conn->prepare("UPDATE users SET username = ?, email = ?, phone = ?, address = ?, updated_at = NOW() WHERE id = ?");
        $stmt->bind_param("ssssi", $username, $email, $phone, $address, $user_id);
        if ($stmt->execute()) {
            $_SESSION['success'] = "Profile updated successfully!";
            header("Location: profile.php");
            exit();
        } else {
            $_SESSION['error'] = "Error updating profile!";
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
        <a href="profile.php" class="btn btn-primary w-25"><i class="bi bi-arrow-left"></i> Back</a>
        <a href="profile_password.php" class="btn btn-warning w-25"><i class="bi bi-lock"></i> Change Password</a>
    </div>

    <div class="card shadow-sm mt-4">
        <div class="card-body">
            <h5 class="card-title mb-3">Edit Profile</h5>
            <table class="table table-borderless">

                <form method="post">
                    <div class="form-outline mb-3">
                        <input type="text" name="username" class="form-control" placeholder=" " value="<?= htmlspecialchars($user['username']) ?>" required>
                        <label class="form-label">Username</label>
                    </div>
                    <div class="form-outline mb-3">
                        <input type="email" name="email" class="form-control" placeholder=" " value="<?= htmlspecialchars($user['email']) ?>" required>
                        <label class="form-label">Email</label>
                    </div>
                    <div class="form-outline mb-3">
                        <input type="text" name="phone" class="form-control" placeholder=" " value="<?= htmlspecialchars($user['phone']) ?>">
                        <label class="form-label">Phone</label>
                    </div>
                    <div class="form-outline mb-3">
                        <textarea name="address" class="form-control" rows="4" placeholder=" "><?= htmlspecialchars($user['address']) ?></textarea>
                        <label class="form-label">Address</label>
                    </div>
                    <div class="d-flex">
                        <a href="profile.php" class="btn btn-secondary w-100 me-2">Cancel</a>
                        <button type="submit" class="btn btn-primary w-100">Update Profile</button>
                    </div>
                </form>
            </table>
        </div>
    </div>
</div>

<?php require 'inc/footer.php'; ?>