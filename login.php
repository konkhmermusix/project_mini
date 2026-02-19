<?php
session_start();
require 'inc/db.php';

$message = '';
$email = '';

if (isset($_POST['login'])) {

    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT id, username, password, role FROM users WHERE email=? LIMIT 1");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['username'];
        $_SESSION['role'] = $user['role'];

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
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <link rel="icon" href="static/image/favicon/icon.png" type="image/x-icon">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="static/css/bootstrap.min.css">
    <link rel="stylesheet" href="static/bootstrap-icons/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap" rel="stylesheet">

    <style>
        :root {
            --primary-color: #4e73df;
            --bg-gradient: linear-gradient(135deg, rgb(139, 137, 183), rgba(255, 247, 0, 0.24));
        }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--bg-gradient);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
            padding: 20px;
        }

        .login-card {
            max-width: 420px;
            width: 100%;
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
            border: none;
            padding: 40px 30px;
        }

        .login-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .login-header h3 {
            font-weight: 800;
            color: #333;
            letter-spacing: -0.5px;
        }

        .form-outline {
            position: relative;
            margin-bottom: 20px;
        }

        .form-control {
            height: 52px;
            background: #f8f9fa;
            border: 2px solid #eee;
            border-radius: 12px;
            padding: 10px 15px;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            background: #fff;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 4px rgba(78, 115, 223, 0.1);
            outline: none;
        }

        .form-outline label {
            position: absolute;
            top: 50%;
            left: 15px;
            transform: translateY(-50%);
            color: #999;
            padding: 0 5px;
            pointer-events: none;
            transition: all 0.2s ease;
        }

        .form-control:focus+label,
        .form-control:not(:placeholder-shown)+label {
            top: 0;
            font-size: 12px;
            font-weight: 700;
            color: var(--primary-color);
            background: white;
        }

        .btn-login {
            height: 52px;
            background: var(--primary-color);
            border: none;
            border-radius: 12px;
            font-weight: 600;
            color: white;
            transition: all 0.3s ease;
        }

        .btn-login:hover {
            background: #3e5ec2;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(78, 115, 223, 0.3);
        }

        .alert-right {
            position: fixed;
            top: 25px;
            right: 25px;
            z-index: 1060;
            border-radius: 12px;
            border: none;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
            animation: slideIn 0.5s ease-out;
        }

        @keyframes slideIn {
            from {
                transform: translateX(100%);
                opacity: 0;
            }

            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        .register-link {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 600;
        }
    </style>
</head>

<body>

    <?php if (!empty($message)) echo $message; ?>

    <div class="card login-card">
        <div class="login-header">
            <h3>Login</h3>
        </div>

        <form method="POST" autocomplete="off">
            <div class="form-outline">
                <input type="email" id="email" class="form-control" name="email"
                    value="<?php echo htmlspecialchars($email); ?>" placeholder=" " required>
                <label for="email"><i class="bi bi-envelope me-1"></i> Email address</label>
            </div>

            <div class="form-outline">
                <input type="password" id="password" class="form-control" name="password" placeholder=" " required>
                <label for="password"><i class="bi bi-lock me-1"></i> Password</label>
            </div>

            <div class="d-flex justify-content-between align-items-center mb-4 px-1">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="remember">
                    <label class="form-check-label small text-muted" for="remember">Remember me</label>
                </div>
                <a href="#" class="small text-decoration-none">Forgot password?</a>
            </div>

            <button type="submit" class="btn btn-login w-100" name="login">
                Login <i class="bi bi-arrow-right ms-1"></i>
            </button>
        </form>

        <div class="text-center mt-4">
            <small class="text-muted">
                Don’t have an account?
                <a href="register.php" class="register-link">Create Account</a>
            </small>
        </div>
    </div>

    <script src="static/js/bootstrap.bundle.min.js"></script>
    <script>
        setTimeout(() => {
            const alertElement = document.querySelector('.alert-right');
            if (alertElement) {
                const bsAlert = new bootstrap.Alert(alertElement);
                bsAlert.close();
            }
        }, 3000);
    </script>
</body>

</html>