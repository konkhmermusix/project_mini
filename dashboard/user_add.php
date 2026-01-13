<?php
require '../inc/db.php';

if ($_POST) {
    $username = $_POST['username'];
    $email    = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role     = $_POST['role'];

    $stmt = $conn->prepare(
        "INSERT INTO users(username,email,password,role) VALUES(?,?,?,?)"
    );
    $stmt->bind_param("ssss", $username, $email, $password, $role);
    $stmt->execute();

    header("Location: users.php");
}
