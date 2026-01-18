<?php

session_start();
require 'inc/db.php';

$message = '';

if (isset($_POST['login'])) {

    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // =========================
    // Fetch user securely
    // =========================
    $stmt = $conn->prepare("SELECT id, username, password, role FROM users WHERE email=? LIMIT 1");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($user && password_verify($password, $user['password'])) {

        // =========================
        // Set session
        // =========================
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['username'];
        $_SESSION['role'] = $user['role'];

        // =========================
        // Merge cookie cart into session cart
        // =========================
        if (isset($_COOKIE['cart'])) {
            $cookieCart = json_decode($_COOKIE['cart'], true);
            if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];

            foreach ($cookieCart as $pid => $item) {
                if (isset($_SESSION['cart'][$pid])) {
                    $_SESSION['cart'][$pid]['qty'] += $item['qty'];
                } else {
                    $_SESSION['cart'][$pid] = $item;
                }
            }
            // Update cookie
            setcookie('cart', json_encode($_SESSION['cart']), time() + (7 * 24 * 60 * 60), "/");
        }

        // =========================
        // Redirect to previous product or homepage
        // =========================
        if (isset($_SESSION['redirect_product'])) {
            $pid = $_SESSION['redirect_product'];
            unset($_SESSION['redirect_product']);
            header("Location: product_detail.php?id=" . $pid);
            exit;
        } else {
            header("Location: index.php");
            exit;
        }
    } else {
        $message = "
        <div class='alert alert-warning alert-dismissible fade show alert-right' role='alert'>
            <strong>Invalid email or password.</strong>
            <button type='button' class='btn-close shadow-none' data-bs-dismiss='alert' aria-label='Close'></button>
        </div>";
    }
}


// session_start();
// require 'inc/db.php';

// $message = '';

// if (isset($_POST['login'])) {
//     $email = trim($_POST['email']);
//     $password = $_POST['password'];

//     $sql = "SELECT * FROM users WHERE email='$email' LIMIT 1";
//     $result = $conn->query($sql);

//     if ($result && $result->num_rows === 1) {
//         $user = $result->fetch_assoc();

//         if (password_verify($password, $user['password'])) {
//             $_SESSION['user_id'] = $user['id'];
//             $_SESSION['role'] = $user['role'];

//             header("Location: index.php");
//             exit;
//         } else {
//             $message = "
//             <div class='alert alert-warning alert-dismissible fade show  alert-right' role='alert'>
//                 <strong>Wrong password.</strong>
//                 <button type='button' class='btn-close shadow-none' data-bs-dismiss='alert' aria-label='Close'></button>
//             </div>";
//         }
//     } else {
//         $message = "
//         <div class='alert alert-success alert-dismissible fade show  alert-right' role='alert'>
//             <strong>Login successful!</strong>
//             <button type='button' class='btn-close shadow-none' data-bs-dismiss='alert' aria-label='Close'></button>
//         </div>";
//     }
// }
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Login</title>
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

        .login-card {
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

        .alert-right {
            position: fixed;
            top: 20px;
            right: 20px;
            min-width: 300px;
            z-index: 1055;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
            border-radius: 8px;
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

    <?php if (!empty($message)) echo $message; ?>

    <div class="card login-card p-4 bg-white">
        <div class="text-center mb-4">
            <h3 class="fw-bold">Login</h3>
        </div>

        <form method="POST" autocomplete="off">

            <!-- Email -->
            <div class="form-outline mb-3">
                <input
                    type="email"
                    id="email"
                    class="form-control"
                    name="email"
                    placeholder=" "
                    required>
                <label for="email">Email address</label>
            </div>

            <!-- Password -->
            <div class="form-outline mb-3">
                <input
                    type="password"
                    id="password"
                    class="form-control"
                    name="password"
                    placeholder=" "
                    required>
                <label for="password">Password</label>
            </div>

            <div class="d-grid mt-4">
                <button class="btn btn-primary" name="login">
                    Login
                </button>
            </div>

        </form>

        <div class="text-center mt-4">
            <small class="text-muted">
                Don’t have an account?
                <a href="register.php" class="text-decoration-none fw-medium">Create one</a>
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