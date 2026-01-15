</div>
<img src="static/image/website/image_footer.webp" alt="" width="100%" class="text-primary">
<!-- Footer -->
<footer style="background: linear-gradient(135deg,rgb(129, 128, 152),rgb(76, 160, 245)); color: white;" class="pt-5 pb-3 text-white">
    <div class="container">
        <div class="row">

            <!-- About / Logo -->
            <div class="col-md-3 mb-4">
                <h5 class="fw-bold">MyShop</h5>
                <p>Your one-stop online store for electronics, fashion, accessories, and more!</p>
            </div>

            <!-- Quick Links -->
            <div class="col-md-3 mb-4">
                <h6 class="fw-bold">Quick Links</h6>
                <ul class="list-unstyled">
                    <li>
                        <a href="index.php" class="text-white text-decoration-none <?php if (basename($_SERVER['PHP_SELF']) == 'index.php') echo 'active'; ?>">Home</a>
                    </li>
                    <li>
                        <a href="products.php" class="text-white text-decoration-none <?php if (basename($_SERVER['PHP_SELF']) == 'products.php') echo 'active'; ?>">Products</a>
                    </li>
                    <li>
                        <a href="shop.php" class="text-white text-decoration-none <?php if (basename($_SERVER['PHP_SELF']) == 'shop.php') echo 'active'; ?>">Shop</a>
                    </li>
                    <li class="nav-item me-3">
                        <a href="blog.php" class="text-white text-decoration-none <?php if (basename($_SERVER['PHP_SELF']) == 'blog.php') echo 'active'; ?>">Blog</a>
                    </li>
                    <li>
                        <a href="about.php" class="text-white text-decoration-none <?php if (basename($_SERVER['PHP_SELF']) == 'about.php') echo 'active'; ?>">About</a>
                    </li>
                    <li>
                        <a href="contact.php" class="text-white text-decoration-none <?php if (basename($_SERVER['PHP_SELF']) == 'contact.php') echo 'active'; ?>">Contact</a>
                    </li>
                </ul>
            </div>

            <!-- Customer Service -->
            <div class="col-md-3 mb-4">
                <h6 class="fw-bold">Customer Service</h6>
                <ul class="list-unstyled">
                    <li><a href="#" class="text-white text-decoration-none">FAQ</a></li>
                    <li><a href="#" class="text-white text-decoration-none">Shipping & Returns</a></li>
                    <li><a href="#" class="text-white text-decoration-none">Privacy Policy</a></li>
                    <li><a href="#" class="text-white text-decoration-none">Terms of Service</a></li>
                </ul>
            </div>

            <!-- Contact / Social -->
            <div class="col-md-3 mb-4">
                <h6 class="fw-bold">Contact Us</h6>
                <p>Email: support@myshop.com</p>
                <p>Phone: +1 234 567 890</p>
                <div class="d-flex gap-2 mt-2">
                    <a href="#" class="text-white fs-5"><i class="bi bi-facebook"></i></a>
                    <a href="#" class="text-white fs-5"><i class="bi bi-twitter"></i></a>
                    <a href="#" class="text-white fs-5"><i class="bi bi-instagram"></i></a>
                    <a href="#" class="text-white fs-5"><i class="bi bi-linkedin"></i></a>
                </div>
            </div>

        </div>

        <hr class="border-light">

        <div class="text-center small">
            &copy; <?php echo date('Y'); ?> MyShop. All Rights Reserved.
        </div>
    </div>

</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<!-- Swiper JS -->
<script src="https://cdn.jsdelivr.net/npm/swiper@12/swiper-bundle.min.js"></script>


<script>
    document.getElementById('addToCartForm').addEventListener('submit', function(e) {
        e.preventDefault();

        let formData = new FormData(this);

        fetch('cart_add_ajax.php', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    // Update navbar badge
                    document.getElementById('cartCountBadge').textContent = data.cartCount;
                } else if (data.login) {
                    // Redirect to login if not logged in
                    window.location.href = 'login.php';
                }
            })
            .catch(err => console.error(err));
    });
</script>


</body>

</html>