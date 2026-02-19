</div>

<footer style="background: linear-gradient(135deg,rgb(33, 32, 60),rgb(73, 102, 131)); color: white;" class="pt-5 pb-3 text-white">
    <div class="container">
        <div class="row">

            <div class="col-md-3 mb-4">
                <h5 class="fw-bold">Leav Sis</h5>
                <p>Your one-stop online store for electronics, fashion, accessories, and more!</p>
            </div>

            <!-- Quick Links -->
            <div class="col-md-3 mb-4">
                <h4 class="fw-bold">Quick Links</h4>
                <ul class="list-unstyled">
                    <li>
                        <a href="index.php" class="text-white text-decoration-none <?php if (basename($_SERVER['PHP_SELF']) == 'index.php') echo 'active'; ?>">Home</a>
                    </li>
                    <li>
                        <a href="#" class="text-white text-decoration-none <?php if (basename($_SERVER['PHP_SELF']) == 'products.php') echo 'active'; ?>">Products</a>
                    </li>
                    <li>
                        <a href="#" class="text-white text-decoration-none <?php if (basename($_SERVER['PHP_SELF']) == 'shop.php') echo 'active'; ?>">Shop</a>
                    </li>
                    <li class="nav-item me-3">
                        <a href="#" class="text-white text-decoration-none <?php if (basename($_SERVER['PHP_SELF']) == 'blog.php') echo 'active'; ?>">Blog</a>
                    </li>
                    <li>
                        <a href="#" class="text-white text-decoration-none <?php if (basename($_SERVER['PHP_SELF']) == 'about.php') echo 'active'; ?>">About</a>
                    </li>
                    <li>
                        <a href="#" class="text-white text-decoration-none <?php if (basename($_SERVER['PHP_SELF']) == 'contact.php') echo 'active'; ?>">Contact</a>
                    </li>
                </ul>
            </div>

            <!-- Customer Service -->
            <div class="col-md-3 mb-4">
                <h4 class="fw-bold">Customer Service</h4>
                <ul class="list-unstyled">
                    <li><a href="#" class="text-white text-decoration-none">FAQ</a></li>
                    <li><a href="#" class="text-white text-decoration-none">Shipping & Returns</a></li>
                    <li><a href="#" class="text-white text-decoration-none">Privacy Policy</a></li>
                    <li><a href="#" class="text-white text-decoration-none">Terms of Service</a></li>
                </ul>
            </div>

            <div class="col-md-3 mb-4">
                <h4 class="fw-bold">Contact Us</h4>

                <p>
                    <i class="bi bi-telephone-fill me-2"></i>
                    <a href="tel:+885964301974" class="text-decoration-none text-white">
                        +885 964 301 974
                    </a>
                </p>

                <p>
                    <i class="bi bi-envelope-fill me-2"></i>
                    <a href="mailto:leavsis24@shop.com" class="text-decoration-none text-white">
                        leavsis24@shop.com
                    </a>
                </p>

                <div class="d-flex gap-3 mt-3">

                    <a href="https://exampleapp.liveblog365.com" target="_blank" class="text-white fs-4">
                        <i class="bi bi-globe"></i>
                    </a>

                    <a href="https://github.com/konkhmermusix/crud_app.git" target="_blank" class="text-white fs-4">
                        <i class="bi bi-github"></i>
                    </a>

                    <a href="https://www.facebook.com/share/1GQUaHs57o/" target="_blank" class="text-white fs-4">
                        <i class="bi bi-facebook"></i>
                    </a>

                    <a href="https://t.me/if00109" target="_blank" class="text-white fs-4">
                        <i class="bi bi-telegram"></i>
                    </a>

                    <a href="https://x.com/LeavSis26330" target="_blank" class="text-white fs-4">
                        <i class="bi bi-twitter-x"></i>
                    </a>

                    <a href="https://www.instagram.com/leav_sis" target="_blank" class="text-white fs-4">
                        <i class="bi bi-instagram"></i>
                    </a>

                    <a href="https://www.threads.com/@leav_sis" target="_blank" class="text-white fs-4">
                        <i class="bi bi-threads"></i>
                    </a>
                    <a href="https://www.tiktok.com/@leavsis" target="_blank" class="text-white fs-4">
                        <i class="bi bi-tiktok"></i>
                    </a>
                    <a href="https://youtube.com/@leavsis" target="_blank" class="text-white fs-4">
                        <i class="bi bi-youtube"></i>
                    </a>
                </div>
            </div>
        </div>

        <hr class="border-light">

        <div class="text-center small">
            &copy; <?php echo date('Y'); ?> Leav Sis. All Rights Reserved.
        </div>
    </div>
</footer>

<script src="static/js/bootstrap.bundle.min.js"></script>
<script src="static/js/swiper-bundle.min.js"></script>
<script src="static/js/aos.js"></script>
<script>
    AOS.init({
        duration: 800,
        once: true
    });
</script>
<script src="static/js/jquery-3.6.0.min.js"></script>


</body>

</html>