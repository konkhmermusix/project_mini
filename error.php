<?php
// error.php
session_start();

// Optional: custom error message
$message = isset($_GET['msg']) ? htmlspecialchars($_GET['msg']) : "Something went wrong!";
$redirect = isset($_GET['back']) ? $_GET['back'] : "javascript:history.back()";

require 'inc/header.php';
?>

<style>
    .error-box {
        margin: 50px 50px 50px 50px;
        text-align: center;
        padding: 40px;
        background: #fff;
        border-radius: 10px;
        box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
    }

    .error-box h1 {
        font-size: 6rem;
        color: #dc3545;
    }

    .error-box p {
        font-size: 1.2rem;
    }
</style>

<div class="error-box">
    <h1>Oops!</h1>
    <p><?= $message ?></p>
    <a href="<?= $redirect ?>" class="btn btn-primary mt-3">Go Back</a>
    <a href="index.php" class="btn btn-secondary mt-3">Home</a>
</div>


<?php require 'inc/footer.php'; ?>