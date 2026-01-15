<?php
require 'inc/db.php';
include 'inc/header.php';
?>

<section class="">
    <div class="swiper mySwiper">
        <div class="swiper-wrapper">
            <?php
            $result = $conn->query("SELECT * FROM slideshow WHERE status = 1 ORDER BY position ASC");
            while ($row = $result->fetch_assoc()):
            ?>
                <div class="swiper-slide">
                    <?php if (!empty($row['image'])): ?>
                        <a href="<?= htmlspecialchars($row['link']) ?>">
                            <img
                                src="<?= htmlspecialchars($row['image']) ?>"
                                class="img-fluid w-100"
                                style="height:350px; object-fit:cover;">
                        </a>
                    <?php endif; ?>

                    <?php if (!empty($row['title'])): ?>
                        <div class="position-absolute text-white p-3"
                            style="bottom:20px; left:20px; background:rgba(0,0,0,0.4); border-radius:8px;">
                            <h4><?= htmlspecialchars($row['title']) ?></h4>
                            <p><?= htmlspecialchars($row['description']) ?></p>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endwhile; ?>
        </div>

        <!-- Navigation -->
        <div class="swiper-button-next"></div>
        <div class="swiper-button-prev"></div>

        <!-- Pagination -->
        <div class="swiper-pagination"></div>

        <!-- Scrollbar -->
        <div class="swiper-scrollbar"></div>

    </div>
</section>

<section class="mt-5 mb-5">
    <h2 class="mb-4">Category</h2>
    <div class="row">
        <?php
        $categories = $conn->query("SELECT * FROM categories WHERE status = 1 ORDER BY name");
        while ($cat = $categories->fetch_assoc()):
        ?>
            <div class="col-6 col-md-2 mb-4">
                <a href="category.php?id=<?= $cat['id'] ?>" class="text-decoration-none">
                    <div class="card h-100 text-center shadow-sm">
                        <?php if (!empty($cat['image'])): ?>
                            <img src="<?= htmlspecialchars($cat['image']) ?>"
                                class="card-img-top"
                                style="height:150px; object-fit:cover;">
                        <?php endif; ?>
                        <div class="card-body">
                            <h6 class="card-title text-dark">
                                <?= htmlspecialchars($cat['name']) ?>
                            </h6>
                        </div>
                    </div>
                </a>
            </div>
        <?php endwhile; ?>
    </div>
</section>


<section class="mt-5 mb-5">
    <h2 class="mb-4">Brand</h2>
    <div class="row">
        <?php
        $brands = $conn->query("SELECT * FROM brands WHERE status = 1 ORDER BY name");
        while ($brand = $brands->fetch_assoc()):
        ?>
            <div class="col-6 col-md-2 mb-4">
                <a href="brand.php?id=<?= $brand['id'] ?>" class="text-decoration-none">
                    <div class="card p-3 text-center shadow-sm h-100 ">
                        <?php if (!empty($brand['logo'])): ?>
                            <img src="<?= htmlspecialchars($brand['logo']) ?>"
                                class="img-fluid"
                                style="height:60px; object-fit:contain;">
                        <?php else: ?>
                            <h6><?= htmlspecialchars($brand['name']) ?></h6>
                        <?php endif; ?>
                    </div>
                </a>
            </div>
        <?php endwhile; ?>
    </div>
</section>


<section class="mt-5 mb-5">
    <h2 class="mb-4">Products</h2>
    <div class="row">
        <?php
        $result = $conn->query("SELECT * FROM products WHERE status = 1");
        while ($row = $result->fetch_assoc()):
        ?>
            <div class="col-md-4 mb-4">
                <div class="card h-100 shadow-sm">

                    <?php if (!empty($row['image'])): ?>
                        <img src="<?= htmlspecialchars($row['image']) ?>"
                            class="card-img-top"
                            style="height:200px; object-fit:cover;">
                    <?php endif; ?>

                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title"><?= htmlspecialchars($row['name']) ?></h5>
                        <p class="card-text small text-muted">
                            <?= substr(strip_tags($row['description']), 0, 80) ?>...
                        </p>

                        <p class="fw-bold mb-2">$<?= number_format($row['price'], 2) ?></p>

                        <div class="mt-auto d-flex gap-2">
                            <!-- View -->
                            <a href="product_detail.php?id=<?= $row['id'] ?>"
                                class="btn btn-outline-primary w-50">
                                View
                            </a>

                            <!-- Add to Cart -->
                            <form action="cart_add.php" method="post" class="w-50">
                                <input type="hidden" name="product_id" value="<?= $row['id'] ?>">
                                <input type="hidden" name="qty" value="1">
                                <button class="btn btn-success w-100">
                                    Add to Cart
                                </button>
                            </form>
                        </div>
                    </div>

                </div>
            </div>
        <?php endwhile; ?>
    </div>
</section>

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