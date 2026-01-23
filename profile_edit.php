<?php
session_start();
require 'inc/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

if (isset($_POST['update_profile'])) {
    $username = $_POST['username'];
    $email    = $_POST['email'];
    $phone    = $_POST['phone'];
    $address  = $_POST['address'];

    $stmt = $conn->prepare("
        UPDATE users 
        SET username=?, email=?, phone=?, address=?, updated_at=NOW() 
        WHERE id=?
    ");
    $stmt->bind_param("ssssi", $username, $email, $phone, $address, $user_id);

    if ($stmt->execute()) {
        $_SESSION['success'] = "Profile updated successfully";
    } else {
        $_SESSION['error'] = "Something went wrong";
    }
}

// redirect back to profile (edit tab)
header("Location: profile.php#edit");
exit();
