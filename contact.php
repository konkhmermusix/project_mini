<?php
session_start();
require 'inc/db.php';

// Pre-fill name/email if logged in
$user_name = '';
$user_email = '';
$user_id = null;

if (isset($_SESSION['user_id'])) {
    $stmt = $conn->prepare("SELECT username, email FROM users WHERE id = ?");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($user) {
        $user_name = $user['username'];
        $user_email = $user['email'];
        $user_id = $_SESSION['user_id'];
    }
}

// Handle form submission
$success_msg = '';
$error_msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $message = trim($_POST['message']);

    if (!$name || !$email || !$message) {
        $error_msg = "All fields are required!";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_msg = "Invalid email address!";
    } else {
        $stmt = $conn->prepare("INSERT INTO contacts (user_id, name, email, message) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("isss", $user_id, $name, $email, $message);
        if ($stmt->execute()) {
            $success_msg = "Message sent successfully!";
        } else {
            $error_msg = "Failed to send message. Please try again.";
        }
        $stmt->close();
    }
}

include 'inc/header.php';
?>


<style>
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

<?php if (!empty($success_msg)): ?>
    <div class="alert alert-success alert-dismissible fade show alert-top-right" role="alert">
        <?= htmlspecialchars($success_msg) ?>
        <button type="button" class="btn-close shadow-none" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php elseif (!empty($error_msg)): ?>
    <div class="alert alert-danger alert-dismissible fade show alert-top-right" role="alert">
        <?= htmlspecialchars($error_msg) ?>
        <button type="button" class="btn-close shadow-none" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<!-- Page Banner -->
<div class="py-5 text-center text-white" style="background: linear-gradient(135deg, #4f46e5, #3b82f6);">
    <div class="container">
        <h1 class="fw-bold mb-2">Contact Us</h1>
        <p class="lead">We'd love to hear from you!</p>
    </div>
</div>


<!-- Contact Form & Info -->
<div class="container my-5">
    <div class="row">
        <div class="container">
            <div class="row">
                <div class="col-lg-12 col-md-12 mb-lg-0 mb-3 bg-white rounded">
                    <iframe class="w-100 rounded" height="400" src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d31231.947488773283!2d105.80059892438963!3d11.905544975923839!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x310c8a1e9cf5afa9%3A0xe46a9a61b1adb51b!2sHeng%20Samrin%20Tboung%20Khmum%20University!5e0!3m2!1sen!2skh!4v1768725293188!5m2!1sen!2skh"></iframe>
                </div>
            </div>
        </div>
        <!-- Contact Info -->
        <div class="col-md-5 mb-4">
            <h2 class="fw-bold mb-3">Get in Touch</h2>
            <p><i class="bi bi-geo-alt-fill me-2"></i>73 Street, Tbong Khmum, Cambodia</p>
            <p><i class="bi bi-telephone-fill me-2"></i>+885 964 301 974</p>
            <p><i class="bi bi-envelope-fill me-2"></i>lstech26@shop.com</p>
            <h5 class="mt-4 mb-3">Follow Us</h5>
            <a href="https://web.facebook.com/lstechcambodia/" class="me-2 text-decoration-none text-primary"><i class="bi bi-facebook fs-4"></i></a>
            <a href="https://t.me/+eAa1Nx77HxM1MTg1" class="me-2 text-decoration-none text-primary"><i class="bi bi-telegram fs-4"></i></a>
        </div>

        <!-- Contact Form -->
        <div class="col-md-7">
            <h2 class="fw-bold mb-3">Send a Message</h2>
            <form method="POST">
                <div class="form-outline mb-3">
                    <input type="text" name="name" id="name" class="form-control"
                        placeholder=" " required
                        value="<?= htmlspecialchars($user_name) ?>">
                    <label for="name">Full Name</label>
                </div>
                <div class="form-outline mb-3">
                    <input type="email" id="email" class="form-control" name="email" placeholder=" " required
                        value="<?= htmlspecialchars($user_email) ?>">
                    <label for="email">Email Address</label>
                </div>

                <div class="form-outline mb-3">
                    <textarea class="form-control" name="message" id="message" rows="5" placeholder=" " required></textarea>
                    <label for="message">Message</label>
                </div>
                <button type="submit" class="btn btn-primary">Send Message</button>
            </form>
        </div>
    </div>
</div>

<?php require 'inc/footer.php'; ?>

<script>
    setTimeout(() => {
        const alert = document.querySelector('.alert-top-right');
        if (alert) alert.remove();
    }, 3000);
</script>