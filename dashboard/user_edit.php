<?php
require '../inc/db.php';
$id = (int)$_GET['id'];

$user = $conn->query("SELECT * FROM users WHERE id=$id")->fetch_assoc();

if ($_POST) {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $role = $_POST['role'];
    $status = $_POST['status'];

    $stmt = $conn->prepare(
        "UPDATE users SET username=?, email=?, role=?, status=? WHERE id=?"
    );
    $stmt->bind_param("sssii", $username, $email, $role, $status, $id);
    $stmt->execute();

    header("Location: users.php");
}
