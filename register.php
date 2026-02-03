<?php
session_start();
require 'inc/db.php';

$message = '';
// បង្កើត Variable សម្រាប់រក្សាទិន្នន័យដែលវាយបញ្ចូល
$username = '';
$email = '';

if (isset($_POST['register'])) {
    $username = trim($_POST['username']);
    $email    = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm  = $_POST['confirm_password'];

    // 1. ឆែកមើល Username ឬ Email ថាមានរួចហើយឬនៅ
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
    // 2. ឆែក Password
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
        // បើត្រឹមត្រូវទាំងអស់ ទើបធ្វើការ Hash និង Save
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $username, $email, $hashedPassword);

        if ($stmt->execute()) {
            // បើជោគជ័យ បោះ Notification ចូល Table
            $conn->query("INSERT INTO notifications (message, type) VALUES ('New user: $username', 'user')");

            $message = "<div class='alert alert-success alert-dismissible fade show alert-right' role='alert'>
                            <strong>Registration successful! Redirecting...</strong>
                        </div>";
            // រង់ចាំ ២វិនាទី រួចទៅកាន់ទំព័រ Login
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
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, rgb(139, 137, 183), rgba(255, 247, 0, 0.24));
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .alert-right {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
            min-width: 300px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

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

    <div class="card register-card p-4 bg-white">
        <div class="text-center mb-4 d-flex">
            <h3 class="fw-bold">Create Account</h3>
        </div>

        <form method="POST" autocomplete="off">
            <div class="form-outline mb-3">
                <input type="text" id="username" class="form-control" name="username"
                    value="<?php echo htmlspecialchars($username); ?>" placeholder=" " required>
                <label for="username">Username</label>
            </div>

            <div class="form-outline mb-3">
                <input type="email" id="email" class="form-control" name="email"
                    value="<?php echo htmlspecialchars($email); ?>" placeholder=" " required>
                <label for="email">Email address</label>
            </div>

            <div class="form-outline mb-3">
                <input type="password" id="password" class="form-control" name="password" placeholder=" " required>
                <label for="password">Password</label>
            </div>

            <div class="form-outline mb-3">
                <input type="password" id="confirm_password" class="form-control" name="confirm_password" placeholder=" " required>
                <label for="confirm_password">Confirm Password</label>
            </div>

            <div class="d-grid mt-4">
                <button class="btn btn-primary" name="register">Create Account</button>
            </div>
        </form>

        <div class="text-center mt-4">
            <small class="text-muted">Already have an account? <a href="login.php">Login</a></small>
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
        }, 3000);
    </script>
</body>

</html>