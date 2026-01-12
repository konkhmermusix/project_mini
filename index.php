<?php
require 'inc/db.php';
include 'inc/header.php';
?>

<div class="container-fluit">
    <!-- Swiper -->
    <div class="swiper slidshow">
        <div class="swiper-wrapper">
            <div class="swiper-slide">
                <img src="https://placehold.co/600x400@3x.png" alt="Slide 1" class="img-fluid">
            </div>
            <div class="swiper-slide">
                <img src="https://via.placeholder.com/600x400?text=Slide+2" alt="Slide 2" class="img-fluid">
            </div>
            <div class="swiper-slide">
                <img src="https://via.placeholder.com/600x400?text=Slide+3" alt="Slide 3" class="img-fluid">
            </div>
            <div class="swiper-slide">
                <img src="https://via.placeholder.com/600x400?text=Slide+4" alt="Slide 4" class="img-fluid">
            </div>
            <div class="swiper-slide">
                <img src="https://via.placeholder.com/600x400?text=Slide+5" alt="Slide 5" class="img-fluid">
            </div>
            <div class="swiper-slide">
                <img src="https://via.placeholder.com/600x400?text=Slide+6" alt="Slide 6" class="img-fluid">
            </div>
            <div class="swiper-slide">
                <img src="https://via.placeholder.com/600x400?text=Slide+7" alt="Slide 7" class="img-fluid">
            </div>
            <div class="swiper-slide">
                <img src="https://via.placeholder.com/600x400?text=Slide+8" alt="Slide 8" class="img-fluid">
            </div>
            <div class="swiper-slide">
                <img src="https://via.placeholder.com/600x400?text=Slide+9" alt="Slide 9" class="img-fluid">
            </div>
        </div>
        <div class="swiper-pagination"></div>
    </div>

    <h2 class="mb-4">Products</h2>
    <div class="row">
        <?php
        $result = $conn->query("SELECT * FROM products");
        while ($row = $result->fetch_assoc()):
        ?>
            <div class="col-md-4 mb-4">
                <div class="card h-100">
                    <?php if ($row['image'] != ''): ?>
                        <img src="<?= $row['image'] ?>" class="card-img-top" style="height:200px; object-fit:cover;">
                    <?php endif; ?>
                    <div class="card-body">
                        <h5 class="card-title"><?= $row['name'] ?></h5>
                        <p class="card-text"><?= $row['description'] ?></p>
                        <p class="card-text"><strong>Price: $<?= $row['price'] ?></strong></p>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
    </div>

    <h2 class="mb-4 mt-5">Blog Posts</h2>
    <?php
    $result = $conn->query("SELECT posts.*, users.username FROM posts JOIN users ON posts.user_id = users.id ORDER BY posts.created_at DESC");
    while ($row = $result->fetch_assoc()):
    ?>
        <div class="card mb-3">
            <div class="card-body">
                <h5 class="card-title"><?= $row['title'] ?></h5>
                <small class="text-muted">by <?= $row['username'] ?> | <?= $row['created_at'] ?></small>
                <p class="card-text mt-2"><?= nl2br($row['content']) ?></p>
            </div>
        </div>
    <?php endwhile; ?>

</div>



<!-- Hero slider -->
<div class="container mt-4">
    <div class="swiper hero-slider">
        <div class="swiper-wrapper">
            <div class="swiper-slide" style="background-image:url('https://via.placeholder.com/1200x400/4f46e5/fff?text=Slide+1');"></div>
            <div class="swiper-slide" style="background-image:url('https://via.placeholder.com/1200x400/f97316/fff?text=Slide+2');"></div>
            <div class="swiper-slide" style="background-image:url('https://via.placeholder.com/1200x400/10b981/fff?text=Slide+3');"></div>
        </div>
        <div class="swiper-pagination"></div>
    </div>
</div>

<!-- Featured Products -->
<div class="container mt-5">
    <h3 class="mb-4 text-center">Featured Products</h3>
    <div class="row g-4">
        <div class="col-md-3">
            <div class="card product-card shadow-sm">
                <img src="https://via.placeholder.com/300x200" class="card-img-top" alt="Product">
                <div class="card-body text-center">
                    <h5 class="card-title">Product 1</h5>
                    <p class="card-text">$49.99</p>
                    <a href="#" class="btn btn-primary btn-sm">Add to Cart</a>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card product-card shadow-sm">
                <img src="https://via.placeholder.com/300x200" class="card-img-top" alt="Product">
                <div class="card-body text-center">
                    <h5 class="card-title">Product 2</h5>
                    <p class="card-text">$29.99</p>
                    <a href="#" class="btn btn-primary btn-sm">Add to Cart</a>
                </div>
            </div>
        </div>
        <!-- Add more products here -->
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