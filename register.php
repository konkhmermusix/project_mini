<?php
session_start();
require 'inc/db.php';

$message = '';

if (isset($_POST['register'])) {
    $username = trim($_POST['username']);
    $email    = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm  = $_POST['confirm_password'];

    // Check password match
    if ($password !== $confirm) {
        $message = "<div class='alert alert-danger'>Passwords do not match.</div>";
    } elseif (strlen($password) < 8) {
        $message = "<div class='alert alert-danger'>Password must be at least 8 characters.</div>";
    } else {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        $sql = "INSERT INTO users (username, email, password)
                VALUES ('$username', '$email', '$hashedPassword')";

        if ($conn->query($sql)) {
            $message = "
             <div class='alert alert-success alert-dismissible fade show  alert-right' role='alert'>
                <strong>Registration successful! <a href='login.php'>Login here</a></strong>
                <button type='button' class='btn-close shadow-none' data-bs-dismiss='alert' aria-label='Close'></button>
            </div>";
        } else {
            $message = "
            <div class='alert alert-warning alert-dismissible fade show  alert-right' role='alert'>
                <strong>Error: {$conn->error}</strong>
                <button type='button' class='btn-close shadow-none' data-bs-dismiss='alert' aria-label='Close'></button>
            </div>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Create Account</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Google Font -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, rgb(139, 137, 183), rgba(255, 247, 0, 0.24));
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .register-card {
            max-width: 420px;
            width: 100%;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
        }

        .btn-primary {
            border-radius: 5px;
            padding: 10px;
            font-weight: 500;
        }

        /* ===== Outline Floating Label ===== */
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

    <div class="card register-card p-4 bg-white">
        <div class="text-center mb-4">
            <h3 class="fw-bold">Create Account</h3>
        </div>

        <?php if (!empty($message)) echo $message; ?>
        <form method="POST" autocomplete="off">

            <!-- Username -->
            <div class="form-outline mb-3">
                <input type="text" id="username" class="form-control" name="username" placeholder=" " required>
                <label for="username">Username</label>
            </div>

            <!-- Email -->
            <div class="form-outline mb-3">
                <input type="email" id="email" class="form-control" name="email" placeholder=" " required>
                <label for="email">Email address</label>
            </div>

            <!-- Password -->
            <div class="form-outline mb-3">
                <input type="password" id="password" class="form-control" name="password" placeholder=" " required>
                <label for="password">Password</label>
            </div>

            <!-- Confirm Password -->
            <div class="form-outline mb-3">
                <input type="password" id="confirm_password" class="form-control" name="confirm_password" placeholder=" " required>
                <label for="confirm_password">Confirm Password</label>
            </div>

            <div class="d-grid mt-4">
                <button class="btn btn-primary" name="register">
                    Create Account
                </button>
            </div>

        </form>

        <div class="text-center mt-4">
            <small class="text-muted">
                Already have an account?
                <a href="login.php" class="text-decoration-none fw-medium">Login</a>
            </small>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        setTimeout(() => {
            const alert = document.querySelector('.alert-right');
            if (alert) alert.remove();
        }, 3000);
    </script>

</body>

</html>