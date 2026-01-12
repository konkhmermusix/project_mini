<?php
require 'inc/db.php';
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
        <!-- Contact Info -->
        <div class="col-md-5 mb-4">
            <h2 class="fw-bold mb-3">Get in Touch</h2>
            <p><i class="bi bi-geo-alt-fill me-2"></i>123 Main Street, Phnom Penh, Cambodia</p>
            <p><i class="bi bi-telephone-fill me-2"></i>+855 12 345 678</p>
            <p><i class="bi bi-envelope-fill me-2"></i>info@myshop.com</p>

            <h5 class="mt-4 mb-3">Follow Us</h5>
            <a href="#" class="me-2 text-decoration-none text-primary"><i class="bi bi-facebook fs-4"></i></a>
            <a href="#" class="me-2 text-decoration-none text-info"><i class="bi bi-twitter fs-4"></i></a>
            <a href="#" class="me-2 text-decoration-none text-danger"><i class="bi bi-instagram fs-4"></i></a>
        </div>

        <!-- Contact Form -->
        <div class="col-md-7">
            <h2 class="fw-bold mb-3">Send a Message</h2>
            <form action="contact_process.php" method="POST">
                <div class="form-outline mb-3">
                    <input type="text" id="name" class="form-control" name="name" placeholder=" " required>
                    <label for="name">Full Name</label>
                </div>
                <div class="form-outline mb-3">
                    <input type="email" id="email" class="form-control" name="email" placeholder=" " required>
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

<?php include 'inc/footer.php'; ?>