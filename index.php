<?php
require 'inc/db.php';
session_start();

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// POST: Add to Cart
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit;
    }

    $product_id = intval($_POST['product_id']);
    $qty        = intval($_POST['qty']);
    if ($qty < 1) $qty = 1;

    // Fetch product
    $stmt = $conn->prepare("SELECT id, name, price, qty, image FROM products WHERE id=? AND status=1");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $product = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$product) {
        die("Product not found");
    }

    if ($qty > $product['qty']) $qty = $product['qty'];

    // Init cart
    if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];

    // Add/update cart
    if (isset($_SESSION['cart'][$product_id])) {
        $newQty = $_SESSION['cart'][$product_id]['qty'] + $qty;
        if ($newQty > $product['qty']) $newQty = $product['qty'];
        $_SESSION['cart'][$product_id]['qty'] = $newQty;
    } else {
        $_SESSION['cart'][$product_id] = [
            'id' => $product['id'],
            'name' => $product['name'],
            'price' => $product['price'],
            'qty' => $qty,
            'image' => $product['image']
        ];
    }

    $_SESSION['cart_success'] = "Product added to cart!";
    header("Location: index.php?id=" . $product_id);
    exit;
}

// SUBSCRIBE NEWSLETTER
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['subscribe'])) {

    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit;
    }

    $email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
    if (!$email) {
        $_SESSION['sub_error'] = "Invalid email address";
        header("Location: index.php");
        exit;
    }

    $stmt = $conn->prepare(
        "INSERT IGNORE INTO subscribers (email, user_id) VALUES (?, ?)"
    );
    $stmt->bind_param("si", $email, $_SESSION['user_id']);
    $stmt->execute();
    $stmt->close();

    $_SESSION['sub_success'] = "Subscribed successfully!";
    header("Location: index.php");
    exit;
}


require 'inc/header.php';
?>

<section>
    <div class="swiper slideshow">
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
                                class="img-fluid w-100">
                        </a>
                    <?php endif; ?>

                    <?php if (!empty($row['title'])): ?>
                        <div class="position-absolute text-white p-3 text-center mt-5"
                            style=" background:rgba(0,0,0,0.4); border-radius:5px;">
                            <h4><?= htmlspecialchars($row['title']) ?></h4>
                            <p class="fs-5"><?= htmlspecialchars($row['description']) ?></p>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endwhile; ?>
        </div>

        <div class="swiper-button-next"></div>
        <div class="swiper-button-prev"></div>
        <div class="swiper-pagination"></div>
        <div class="swiper-scrollbar"></div>
    </div>
</section>

<section class="p-4">
    <div class="container">
        <h4 class="mb-4">Shop by Category</h4>
        <div class="row g-3">
            <?php
            $categories = $conn->query("SELECT * FROM categories WHERE status=1 ORDER BY name ASC");
            while ($cat = $categories->fetch_assoc()):
            ?>
                <div class="col-6 col-sm-4 col-md-3 col-lg-2" data-aos="fade-up">
                    <a href="shop.php?category_id=<?= $cat['id'] ?>" class="text-decoration-none text-dark category-card d-block text-center p-2 border rounded shadow-sm bg-white">

                        <?php if (!empty($cat['image'])): ?>
                            <img src="<?= htmlspecialchars($cat['image']) ?>" alt="<?= htmlspecialchars($cat['name']) ?>" class="img-fluid mb-2 category-img">
                        <?php else: ?>
                            <i class="bi bi-grid fs-1 mb-2"></i>
                        <?php endif; ?>

                        <p class="mb-0 fw-bold"><?= htmlspecialchars($cat['name']) ?></p>
                    </a>
                </div>
            <?php endwhile; ?>
        </div>
    </div>
</section>

<section class="p-4 bg-light">
    <div class="container">
        <h4 class="mb-4">Shop by Brand</h4>
        <div class="row g-3">
            <?php
            $brands = $conn->query("SELECT * FROM brands WHERE status=1 ORDER BY name ASC");
            while ($brand = $brands->fetch_assoc()):
            ?>
                <div class="col-4 col-sm-3 col-md-2 text-center" data-aos="fade-up">
                    <a href="shop.php?brand_id=<?= $brand['id'] ?>" class="text-decoration-none text-dark category-card d-block text-center p-2 border rounded shadow-sm bg-white">
                        <?php if (!empty($brand['logo'])): ?>
                            <img src="<?= htmlspecialchars($brand['logo']) ?>" class="img-fluid mb-2 rounded" alt="<?= htmlspecialchars($brand['name']) ?>">
                        <?php else: ?>
                            <i class="bi bi-shop fs-1 mb-2"></i>
                        <?php endif; ?>
                        <p class="mb-0"><?= htmlspecialchars($brand['name']) ?></p>
                    </a>
                </div>
            <?php endwhile; ?>
        </div>
    </div>
</section>

<section class="p-4">
    <div class="container">
        <div class="bg-info shadow-sm p-2 mb-2 rounded-1">
            <h4 class="mb-0 text-white">Featured / Popular Products</h4>
        </div>
        <div class="row">
            <?php
            $featured = $conn->query("
                SELECT * 
                FROM products
                WHERE status = 1 AND cost_price < price
                ORDER BY created_at DESC
                LIMIT 8
            ");
            while ($row = $featured->fetch_assoc()):
                $discount = 0;
                if (!empty($row['cost_price']) && $row['cost_price'] < $row['price']) {
                    $discount = round((($row['price'] - $row['cost_price']) / $row['price']) * 100);
                }
            ?>
                <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
                    <div class="card h-100 shadow-sm position-relative">

                        <?php if ($discount > 0): ?>
                            <span class="badge bg-danger position-absolute top-0 start-0 m-2">
                                -<?= $discount ?>%
                            </span>
                        <?php endif; ?>

                        <?php if (!empty($row['image'])): ?>
                            <img src="<?= htmlspecialchars($row['image']) ?>"
                                class="card-img-top"
                                style="height:200px; object-fit:cover;">
                        <?php endif; ?>

                        <div class="card-body d-flex flex-column">
                            <h6 class="card-title"><?= htmlspecialchars($row['name']) ?></h6>

                            <p class="card-text small text-muted">
                                <?= substr(strip_tags($row['description']), 0, 60) ?>...
                            </p>

                            <?php if ($row['cost_price'] < $row['price']): ?>
                                <p class="mb-2">
                                    <span class="text-danger fw-bold">
                                        $<?= number_format($row['cost_price'], 2) ?>
                                    </span>
                                    <del class="text-muted small ms-1">
                                        $<?= number_format($row['price'], 2) ?>
                                    </del>
                                </p>
                            <?php else: ?>
                                <p class="fw-bold mb-2">
                                    $<?= number_format($row['price'], 2) ?>
                                </p>
                            <?php endif; ?>

                            <div class="mt-auto d-flex gap-2">
                                <a href="product_detail.php?id=<?= $row['id'] ?>"
                                    class="btn btn-outline-primary btn-sm w-50">
                                    View
                                </a>

                                <!-- Form ដដែល / no reload logic optional -->
                                <form method="post" class="w-50">
                                    <input type="hidden" name="product_id" value="<?= $row['id'] ?>">
                                    <input type="hidden" name="qty" value="1">
                                    <button name="add_to_cart"
                                        class="btn btn-success btn-sm w-100">
                                        Add to Cart
                                    </button>
                                </form>
                            </div>
                        </div>

                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </div>
</section>

<section class="p-4">
    <div class="container">
        <div class="bg-info shadow-sm p-2 mb-2 rounded-1">
            <h4 class="mb-0 text-white">Deals / Flash Sales</h4>
        </div>
        <div class="row">
            <?php
            $deals = $conn->query("
            SELECT * 
            FROM products
            WHERE status = 1 AND cost_price < price
            ORDER BY price DESC
            LIMIT 8
        ");
            while ($row = $deals->fetch_assoc()):
                $discount = 0;
                if (!empty($row['cost_price']) && $row['cost_price'] < $row['price']) {
                    $discount = round((($row['price'] - $row['cost_price']) / $row['price']) * 100);
                }
            ?>
                <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
                    <div class="card h-100 shadow-sm position-relative">

                        <?php if ($discount > 0): ?>
                            <span class="badge bg-danger position-absolute top-0 start-0 m-2">
                                -<?= $discount ?>%
                            </span>
                        <?php endif; ?>

                        <?php if (!empty($row['image'])): ?>
                            <img src="<?= htmlspecialchars($row['image']) ?>"
                                class="card-img-top"
                                style="height:200px; object-fit:cover;">
                        <?php endif; ?>

                        <div class="card-body d-flex flex-column">
                            <h6 class="card-title"><?= htmlspecialchars($row['name']) ?></h6>

                            <p class="card-text small text-muted">
                                <?= substr(strip_tags($row['description']), 0, 60) ?>...
                            </p>

                            <?php if ($row['cost_price'] < $row['price']): ?>
                                <p class="mb-2">
                                    <span class="text-danger fw-bold">
                                        $<?= number_format($row['cost_price'], 2) ?>
                                    </span>
                                    <del class="text-muted small ms-1">
                                        $<?= number_format($row['price'], 2) ?>
                                    </del>
                                </p>
                            <?php else: ?>
                                <p class="fw-bold mb-2">
                                    $<?= number_format($row['price'], 2) ?>
                                </p>
                            <?php endif; ?>

                            <div class="mt-auto d-flex gap-2">
                                <a href="product_detail.php?id=<?= $row['id'] ?>"
                                    class="btn btn-outline-primary btn-sm w-50">
                                    View
                                </a>

                                <!-- Form ដដែល / no reload logic optional -->
                                <form method="post" class="w-50">
                                    <input type="hidden" name="product_id" value="<?= $row['id'] ?>">
                                    <input type="hidden" name="qty" value="1">
                                    <button name="add_to_cart"
                                        class="btn btn-success btn-sm w-100">
                                        Add to Cart
                                    </button>
                                </form>
                            </div>
                        </div>

                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </div>
</section>

<section class="p-4">
    <div class="container">
        <div class="bg-info shadow-sm p-2 mb-2 rounded-1">
            <h4 class="mb-0 text-white">Promotion / Discount</h4>
        </div>
        <div class="row">
            <?php
            $result = $conn->query("SELECT *, (price - cost_price) AS discount_amount FROM products WHERE status = 1 AND cost_price < price ORDER BY discount_amount DESC LIMIT 4 ");
            while ($row = $result->fetch_assoc()):
            ?>
                <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
                    <div class="card h-100 shadow-sm position-relative">

                        <?php
                        $discount = 0;
                        if (!empty($row['cost_price']) && $row['cost_price'] < $row['price']) {
                            $discount = round((($row['price'] - $row['cost_price']) / $row['price']) * 100);
                        }
                        ?>

                        <?php if ($discount > 0): ?>
                            <span class="badge bg-danger position-absolute top-0 start-0 m-2">
                                -<?= $discount ?>%
                            </span>
                        <?php endif; ?>

                        <?php if (!empty($row['image'])): ?>
                            <img src="<?= htmlspecialchars($row['image']) ?>"
                                class="card-img-top"
                                style="height:200px; object-fit:cover;">
                        <?php endif; ?>

                        <div class="card-body d-flex flex-column">
                            <h6 class="card-title"><?= htmlspecialchars($row['name']) ?></h6>

                            <p class="card-text small text-muted">
                                <?= substr(strip_tags($row['description']), 0, 60) ?>...
                            </p>

                            <?php if ($row['cost_price'] < $row['price']): ?>
                                <p class="mb-2">
                                    <span class="text-danger fw-bold">
                                        $<?= number_format($row['cost_price'], 2) ?>
                                    </span>
                                    <del class="text-muted small ms-1">
                                        $<?= number_format($row['price'], 2) ?>
                                    </del>
                                </p>
                            <?php else: ?>
                                <p class="fw-bold mb-2">
                                    $<?= number_format($row['price'], 2) ?>
                                </p>
                            <?php endif; ?>

                            <div class="mt-auto d-flex gap-2">
                                <a href="product_detail.php?id=<?= $row['id'] ?>"
                                    class="btn btn-outline-primary btn-sm w-50">
                                    View
                                </a>

                                <!-- Form ដដែល / no reload logic optional -->
                                <form method="post" class="w-50">
                                    <input type="hidden" name="product_id" value="<?= $row['id'] ?>">
                                    <input type="hidden" name="qty" value="1">
                                    <button name="add_to_cart"
                                        class="btn btn-success btn-sm w-100">
                                        Add to Cart
                                    </button>
                                </form>
                            </div>
                        </div>

                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </div>
</section>

<section class="p-4">
    <div class="container">
        <div class="bg-info shadow-sm p-2 mb-2 rounded-1">
            <h4 class="mb-0 text-white">New Arrival</h4>
        </div>
        <div class="row">
            <?php
            $result = $conn->query(" SELECT * FROM products WHERE status = 1 ORDER BY created_at DESC LIMIT 8");
            while ($row = $result->fetch_assoc()):
            ?>
                <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
                    <div class="card h-100 shadow-sm position-relative">

                        <?php
                        if (!empty($row['cost_price']) && $row['cost_price'] < $row['price']) {
                            $discount = round((($row['price'] - $row['cost_price']) / $row['price']) * 100);
                        }
                        ?>

                        <?php if (!empty($discount)): ?>
                            <span class="badge bg-danger position-absolute top-0 start-0 m-2">
                                -<?= $discount ?>%
                            </span>

                        <?php endif; ?>

                        <?php
                        $isNew = (strtotime($row['created_at']) >= strtotime('-5 days'));
                        ?>

                        <?php if ($isNew): ?>
                            <span class="badge bg-success position-absolute top-0 end-0 m-2">
                                NEW
                            </span>
                        <?php endif; ?>

                        <?php if (!empty($row['image'])): ?>
                            <img src="<?= htmlspecialchars($row['image']) ?>"
                                class="card-img-top"
                                style="height:200px; object-fit:cover;">
                        <?php endif; ?>

                        <div class="card-body d-flex flex-column">
                            <h6 class="card-title"><?= htmlspecialchars($row['name']) ?></h6>

                            <p class="card-text small text-muted">
                                <?= substr(strip_tags($row['description']), 0, 60) ?>...
                            </p>

                            <!-- Price -->
                            <?php if (!empty($row['cost_price']) && $row['cost_price'] < $row['price']): ?>
                                <p class="mb-2">
                                    <span class="text-danger fw-bold">
                                        $<?= number_format($row['cost_price'], 2) ?>
                                    </span>
                                    <del class="text-muted small ms-1">
                                        $<?= number_format($row['price'], 2) ?>
                                    </del>
                                </p>
                            <?php else: ?>
                                <p class="fw-bold mb-2">
                                    $<?= number_format($row['price'], 2) ?>
                                </p>
                            <?php endif; ?>

                            <div class="mt-auto d-flex gap-2">
                                <a href="product_detail.php?id=<?= $row['id'] ?>"
                                    class="btn btn-outline-primary btn-sm w-50">
                                    View
                                </a>

                                <form method="post" class="w-50">
                                    <input type="hidden" name="product_id" value="<?= $row['id'] ?>">
                                    <input type="hidden" name="qty" value="1">
                                    <button name="add_to_cart"
                                        class="btn btn-success btn-sm w-100">
                                        Add to Cart
                                    </button>
                                </form>
                            </div>
                        </div>

                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </div>
</section>

<section class="p-4">
    <div class="container">
        <div class="bg-warning shadow-sm p-2 mb-2 rounded-1">
            <h4 class="mb-0 text-white">Best Seller Products</h4>
        </div>
        <div class="row">
            <?php
            $result = $conn->query("
            SELECT * 
            FROM products 
            WHERE status = 1 
            ORDER BY created_at  DESC
            LIMIT 8
        ");
            while ($row = $result->fetch_assoc()):
            ?>
                <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
                    <div class="card h-100 shadow-sm" data-aos="fade-up">

                        <?php if (!empty($row['image'])): ?>
                            <img src="<?= htmlspecialchars($row['image']) ?>"
                                class="card-img-top"
                                style="height:200px; object-fit:cover;">
                        <?php endif; ?>

                        <div class="card-body d-flex flex-column">
                            <h6 class="card-title"><?= htmlspecialchars($row['name']) ?></h6>

                            <p class="card-text small text-muted">
                                <?= substr(strip_tags($row['description']), 0, 60) ?>...
                            </p>

                            <p class="fw-bold mb-2">$<?= number_format($row['price'], 2) ?></p>

                            <div class="mt-auto d-grid gap-2">
                                <a href="product_detail.php?id=<?= $row['id'] ?>"
                                    class="btn btn-outline-primary btn-sm">
                                    View
                                </a>

                                <form method="post">
                                    <input type="hidden" name="product_id" value="<?= $row['id'] ?>">
                                    <input type="hidden" name="qty" value="1">
                                    <button name="add_to_cart"
                                        class="btn btn-success btn-sm w-100">
                                        Add to Cart
                                    </button>
                                </form>
                            </div>
                        </div>

                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </div>
</section>

<section class="p-4">
    <div class="container">
        <div class="bg-secondary shadow-sm p-2 mb-3 rounded-1">
            <h4 class="mb-0 text-white">Customer Reviews</h4>
        </div>

        <div class="row">
            <?php
            $reviews = $conn->query("SELECT * FROM reviews ORDER BY created_at DESC LIMIT 6");
            while ($review = $reviews->fetch_assoc()):
                $firstLetter = strtoupper(substr($review['user_name'], 0, 1));
            ?>
                <div class="col-md-6 mb-3" data-aos="fade-up">
                    <div class="card p-3 shadow-sm h-100">

                        <div class="d-flex align-items-center mb-2">
                            <!-- Profile -->
                            <?php if (!empty($review['profile_image'])): ?>
                                <img src="<?= htmlspecialchars($review['profile_image']) ?>"
                                    class="review-avatar me-2">
                            <?php else: ?>
                                <div class="avatar-text me-2">
                                    <?= $firstLetter ?>
                                </div>
                            <?php endif; ?>

                            <div>
                                <strong><?= htmlspecialchars($review['user_name']) ?></strong><br>
                                <small class="text-muted">
                                    <?= $review['rating'] ?>/5 ⭐
                                </small>
                            </div>
                        </div>

                        <p class="small text-muted mb-0">
                            <?= htmlspecialchars($review['comment']) ?>
                        </p>

                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </div>
</section>

<section class="p-4 bg-light">
    <div class="container text-center">
        <h4>Subscribe to our Newsletter</h4>
        <p>Get latest updates and offers</p>

        <?php if (isset($_SESSION['user_id'])): ?>
            <form method="post" class="d-flex justify-content-center">
                <input type="email"
                    name="email"
                    class="form-control w-50 me-2"
                    placeholder="Enter your email"
                    required>
                <button type="submit"
                    name="subscribe"
                    class="btn btn-primary">
                    Subscribe
                </button>
            </form>
        <?php else: ?>
            <div class="alert alert-warning mt-3">
                Please <a href="login.php" class="fw-bold">Login</a> to subscribe our newsletter
            </div>
        <?php endif; ?>

    </div>
</section>

<?php require 'inc/footer.php'; ?>

<script>
    var swiper = new Swiper(".slideshow", {
        loop: true,
        autoplay: {
            delay: 4000,
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
            draggable: true,
        },
    });
</script>