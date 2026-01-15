<?php
require 'inc/db.php';
include 'inc/header.php';

// Get product id
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Fetch product
$stmt = $conn->prepare("SELECT p.*, b.name AS brand_name, c.name AS category_name 
                        FROM products p
                        LEFT JOIN brands b ON p.brand_id = b.id
                        LEFT JOIN categories c ON p.category_id = c.id
                        WHERE p.id = ? AND p.status = 1");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$product = $result->fetch_assoc();
$stmt->close();

if (!$product) {
    die("<div class='container mt-5'><h3>Product not found!</h3></div>");
}
?>

<div class="row">
    <!-- Left: Images -->
    <div class="col-md-6">
        <?php if (!empty($product['image'])): ?>
            <img src="<?= htmlspecialchars($product['image']) ?>" class="img-fluid mb-3" style="height:400px; object-fit:cover;">
        <?php endif; ?>

        <?php
        // Gallery images
        if (!empty($product['gallery'])):
            $gallery = json_decode($product['gallery'], true);
        ?>
            <div class="d-flex flex-wrap gap-2">
                <?php foreach ($gallery as $img): ?>
                    <img src="<?= htmlspecialchars($img) ?>" width="100" style="object-fit:cover; cursor:pointer;"
                        onclick="document.getElementById('mainImg').src='<?= htmlspecialchars($img) ?>'">
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Right: Details -->
    <div class="col-md-6">
        <h2><?= htmlspecialchars($product['name']) ?></h2>
        <p class="text-muted">Category: <?= htmlspecialchars($product['name']) ?> | Brand: <?= htmlspecialchars($product['name']) ?></p>

        <!-- Rating -->
        <p>
            Rating: <?= $product['rating'] ?> / 5 (<?= $product['reviews_count'] ?> reviews)
        </p>

        <!-- Price -->
        <?php
        $discount = $product['discount_percent'];
        $final_price = $product['price'] - ($product['price'] * $discount / 100);
        ?>
        <p class="h4 text-danger">
            $<?= number_format($final_price, 2) ?>
            <?php if ($discount > 0): ?>
                <small class="text-muted text-decoration-line-through">$<?= $product['price'] ?></small>
            <?php endif; ?>
        </p>

        <!-- Stock -->
        <p>Stock: <?= $product['qty'] > 0 ? $product['qty'] : 'Out of stock' ?></p>

        <!-- Description -->
        <p><?= nl2br(htmlspecialchars($product['description'])) ?></p>

        <!-- Add to Cart -->
        <?php if ($product['qty'] > 0): ?>
            <form action="cart_add.php" method="post" class="d-flex gap-2">
                <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                <input type="number" name="qty" value="1" min="1" max="<?= $product['qty'] ?>" class="form-control w-25">
                <button class="btn btn-success">Add to Cart</button>
            </form>
        <?php else: ?>
            <button class="btn btn-secondary" disabled>Out of Stock</button>
        <?php endif; ?>
    </div>
</div>






<?php include 'inc/footer.php'; ?>

<!-- Initialize Swiper -->
<script>
    var swiper = new Swiper(".slidshow", {
        pagination: {
            el: ".swiper-pagination",
            clickable: true,
            renderBullet: function(index, className) {
                return '<span class="' + className + '">' + (index + 1) + "</span>";
            },
        },
    });
</script>

<script>
    var swiper = new Swiper(".mySwiper", {
        loop: true,
        autoplay: {
            delay: 3000,
            disableOnInteraction: false,
        },
        pagination: {
            el: ".swiper-pagination",
            clickable: true,
        },
        navigation: {
            nextEl: ".swiper-button-next",
            prevEl: ".swiper-button-prev",
        },
        scrollbar: {
            el: ".swiper-scrollbar",
        },
    });
</script>