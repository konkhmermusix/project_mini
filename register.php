<?php
session_start();
require 'inc/db.php';

$message = '';
$username = '';
$email = '';

if (isset($_POST['register'])) {
    $username = trim($_POST['username']);
    $email    = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm  = $_POST['confirm_password'];

    $checkUser = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
    $checkUser->bind_param("ss", $username, $email);
    $checkUser->execute();
    $result = $checkUser->get_result();

    if ($result->num_rows > 0) {
        $message = "<div class='alert alert-warning alert-dismissible fade show alert-right' role='alert'>
                        <strong>Username or Email already exists!</strong>
                        <button type='button' class='btn-close' data-bs-dismiss='alert'></button>
                    </div>";
    }

    elseif ($password !== $confirm) {
        $message = "<div class='alert alert-danger alert-dismissible fade show alert-right' role='alert'>
                        <strong>Passwords do not match.</strong>
                        <button type='button' class='btn-close' data-bs-dismiss='alert'></button>
                    </div>";
    } elseif (strlen($password) < 8) {
        $message = "<div class='alert alert-danger alert-dismissible fade show alert-right' role='alert'>
                        <strong>Password must be at least 8 characters.</strong>
                        <button type='button' class='btn-close' data-bs-dismiss='alert'></button>
                    </div>";
    } else {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $username, $email, $hashedPassword);

        if ($stmt->execute()) {

            $message = "<div class='alert alert-success alert-dismissible fade show alert-right' role='alert'>
                            <strong>Registration successful! Redirecting...</strong>
                        </div>";
            header("refresh:2;url=login.php");
        } else {
            $message = "<div class='alert alert-danger alert-right'>Error: Could not register.</div>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Register</title>
    <link rel="icon" href="static/image/favicon/icon.png" type="image/x-icon">
    <meta name="viewport" content="width=device-width, initial-scale=0.8">
    <link rel="stylesheet" href="static/css/bootstrap.min.css">
    <link rel="stylesheet" href="static/bootstrap-icons/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #4e73df;
            --bg-gradient: linear-gradient(135deg, rgb(139, 137, 183), rgba(255, 247, 0, 0.24));
        }

        body {
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            background: var(--bg-gradient);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
            padding: 20px;
        }

        .register-card {
            max-width: 450px;
            width: 100%;
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
            border: none;
            overflow: hidden;
            transition: transform 0.3s ease;
        }

        .card-header-custom {
            background: transparent;
            padding: 30px 30px 30px 30px;
            text-align: center;
            border: none;
        }

        .card-header-custom h3 {
            color: #333;
            font-weight: 800;
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
            font-size: 15px;
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
            background: transparent;
        }

        .form-control:focus+label,
        .form-control:not(:placeholder-shown)+label {
            top: 0;
            left: 12px;
            font-size: 12px;
            font-weight: 700;
            color: var(--primary-color);
            background: white;
        }

        .btn-register {
            height: 52px;
            background: var(--primary-color);
            border: none;
            border-radius: 12px;
            font-weight: 600;
            font-size: 16px;
            color: white;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(78, 115, 223, 0.2);
        }

        .btn-register:hover {
            background: #3e5ec2;
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(78, 115, 223, 0.3);
        }

        .alert-right {
            position: fixed;
            top: 25px;
            right: 25px;
            z-index: 1050;
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

        .login-link {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 600;
        }

        .login-link:hover {
            text-decoration: underline;
        }
    </style>
</head>

<body>

    <?php if (!empty($message)) echo $message; ?>

    <div class="card register-card p-4">
        <div class="card-header-custom">
            <h3>Create Account</h3>
        </div>

        <form method="POST" autocomplete="off" class="px-2">
            <div class="form-outline">
                <input type="text" id="username" class="form-control" name="username"
                    value="<?php echo htmlspecialchars($username); ?>" placeholder=" " required>
                <label for="username"><i class="bi bi-person me-1"></i> Username</label>
            </div>

            <div class="form-outline">
                <input type="email" id="email" class="form-control" name="email"
                    value="<?php echo htmlspecialchars($email); ?>" placeholder=" " required>
                <label for="email"><i class="bi bi-envelope me-1"></i> Email address</label>
            </div>

            <div class="form-outline">
                <input type="password" id="password" class="form-control" name="password" placeholder=" " required>
                <label for="password"><i class="bi bi-lock me-1"></i> Password</label>
            </div>

            <div class="form-outline">
                <input type="password" id="confirm_password" class="form-control" name="confirm_password" placeholder=" " required>
                <label for="confirm_password"><i class="bi bi-shield-check me-1"></i> Confirm Password</label>
            </div>

            <button type="submit" class="btn btn-register w-100 mt-3" name="register">
                Sign Up
            </button>
        </form>

        <div class="text-center mt-4 mb-2">
            <span class="text-muted">Already have an account?</span>
            <a href="login.php" class="login-link">Login Now</a>
        </div>
    </div>

    <script src="static/js/bootstrap.bundle.min.js"></script>
    <script>
        setTimeout(() => {
            const alerts = document.querySelectorAll('.alert-right');
            alerts.forEach(alert => {
                let bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            });
        }, 4000);
    </script>
</body>

</html>