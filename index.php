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
    $qty        = max(1, intval($_POST['qty']));

    // Fetch product (NEW TABLE)
    $stmt = $conn->prepare("
        SELECT id, name,
               original_price,
               selling_price,
               discount_percent,
               qty,
               image
        FROM products
        WHERE id=? AND status=1
    ");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $product = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$product) {
        die("Product not found");
    }

    // Stock protection
    if ($qty > $product['qty']) {
        $qty = $product['qty'];
    }

    // Init cart
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    // Add / Update cart
    if (isset($_SESSION['cart'][$product_id])) {

        $newQty = $_SESSION['cart'][$product_id]['qty'] + $qty;
        if ($newQty > $product['qty']) {
            $newQty = $product['qty'];
        }
        $_SESSION['cart'][$product_id]['qty'] = $newQty;
    } else {

        $_SESSION['cart'][$product_id] = [
            'id'               => $product['id'],
            'name'             => $product['name'],
            'original_price'   => $product['original_price'],
            'selling_price'    => $product['selling_price'],
            'discount_percent' => $product['discount_percent'],
            'qty'              => $qty,
            'image'            => $product['image']
        ];
    }

    // Save cart to cookie (optional but good)
    setcookie(
        'cart',
        json_encode($_SESSION['cart']),
        time() + (7 * 24 * 60 * 60),
        "/"
    );

    $_SESSION['cart_success'] = "Product added to cart!";
    header("Location: index.php");
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
        <div class="shadow-sm p-2 mb-4 rounded-1 d-flex align-items-center">
            <h4 class="mb-0 text-dark">Featured / Popular Products</h4>
            <a class="btn btn-sm btn-outline-light text-dark fw-bold ms-auto" href="shop.php">More</a>
        </div>

        <div class="row">
            <?php
            $featured = $conn->query("
                SELECT *
                FROM products
                WHERE status = 1
                  AND featured = 1
                  AND selling_price <= original_price
                ORDER BY created_at DESC
                LIMIT 8
            ");

            while ($row = $featured->fetch_assoc()):
                // Discount calculation
                $discount = 0;
                if ($row['selling_price'] < $row['original_price']) {
                    $discount = round(
                        (($row['original_price'] - $row['selling_price']) / $row['original_price']) * 100
                    );
                }
            ?>
                <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
                    <div class="card h-100 shadow-sm position-relative">

                        <!-- Discount Badge -->
                        <?php if ($discount > 0): ?>
                            <span class="badge bg-danger position-absolute top-0 start-0 m-2">
                                -<?= $discount ?>%
                            </span>
                        <?php endif; ?>


                        <!-- Product Image -->
                        <div class="product-img-box">
                            <?php if (!empty($row['image'])): ?>
                                <img src="<?= htmlspecialchars($row['image']) ?>"
                                    alt="<?= htmlspecialchars($row['name']) ?>"
                                    class="product-img">
                            <?php endif; ?>
                        </div>

                        <div class="card-body d-flex flex-column">
                            <h6 class="card-title">
                                <?= htmlspecialchars($row['name']) ?>
                            </h6>

                            <p class="card-text small text-muted">
                                <?= substr(strip_tags($row['description']), 0, 60) ?>...
                            </p>

                            <!-- Price -->
                            <?php if ($discount > 0): ?>
                                <p class="mb-2">
                                    <span class="text-danger fw-bold">
                                        $<?= number_format($row['selling_price'], 2) ?>
                                    </span>
                                    <small class="text-muted">
                                        (<s>$<?= number_format($row['original_price'], 2) ?></s>)
                                    </small>
                                </p>
                            <?php else: ?>
                                <p class="fw-bold mb-2">
                                    $<?= number_format($row['selling_price'], 2) ?>
                                </p>
                            <?php endif; ?>

                            <!-- Buttons -->
                            <div class="mt-auto d-flex gap-2">
                                <a href="product_detail.php?id=<?= $row['id'] ?>"
                                    class="btn btn-outline-primary btn-sm w-50">
                                    <i class="bi bi-eye-fill me-2"></i> View
                                </a>

                                <form method="post" class="w-50">
                                    <input type="hidden" name="product_id" value="<?= $row['id'] ?>">
                                    <input type="hidden" name="qty" value="1">
                                    <button type="submit" name="add_to_cart" class="btn btn-success btn-sm w-100">
                                        <i class="bi bi-cart-plus me-2"></i> Add
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

<section class="p-4 bg-light">
    <div class="container">
        <div class="shadow-sm p-2 mb-5 rounded-1">
            <h4 class="mb-0 text-dark">Deals / Flash Sales</h4>
        </div>

        <div class="row">
            <?php
            $deals = $conn->query("
                SELECT *
                FROM products
                WHERE status = 1
                  AND selling_price < original_price
                ORDER BY (original_price - selling_price) DESC
                LIMIT 4
            ");

            while ($row = $deals->fetch_assoc()):
                // Discount calculation
                $discount = 0;
                if ($row['selling_price'] < $row['original_price']) {
                    $discount = round(
                        (($row['original_price'] - $row['selling_price']) / $row['original_price']) * 100
                    );
                }
            ?>
                <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
                    <div class="card h-100 shadow-sm position-relative">

                        <!-- Discount Badge -->
                        <?php if ($discount > 0): ?>
                            <span class="badge bg-danger position-absolute top-0 start-0 m-2">
                                -<?= $discount ?>%
                            </span>
                        <?php endif; ?>

                        <!-- Product Image -->
                        <div class="product-img-box">
                            <?php if (!empty($row['image'])): ?>
                                <img src="<?= htmlspecialchars($row['image']) ?>"
                                    alt="<?= htmlspecialchars($row['name']) ?>"
                                    class="product-img">
                            <?php endif; ?>
                        </div>

                        <div class="card-body d-flex flex-column">
                            <h6 class="card-title">
                                <?= htmlspecialchars($row['name']) ?>
                            </h6>

                            <p class="card-text small text-muted">
                                <?= substr(strip_tags($row['description']), 0, 60) ?>...
                            </p>

                            <!-- Price -->
                            <?php if ($discount > 0): ?>
                                <p class="mb-2">
                                    <span class="text-danger fw-bold">
                                        $<?= number_format($row['selling_price'], 2) ?>
                                    </span>
                                    <small class="text-muted ms-1">
                                        (<s>$<?= number_format($row['original_price'], 2) ?></s>)
                                    </small>
                                </p>
                            <?php else: ?>
                                <p class="fw-bold mb-2">
                                    $<?= number_format($row['selling_price'], 2) ?>
                                </p>
                            <?php endif; ?>

                            <!-- Buttons -->
                            <div class="mt-auto d-flex gap-2">
                                <a href="product_detail.php?id=<?= $row['id'] ?>"
                                    class="btn btn-outline-primary btn-sm w-50">
                                    <i class="bi bi-eye-fill me-2"></i> View
                                </a>

                                <form method="post" class="w-50">
                                    <input type="hidden" name="product_id" value="<?= $row['id'] ?>">
                                    <input type="hidden" name="qty" value="1">
                                    <button type="submit" name="add_to_cart"
                                        class="btn btn-success btn-sm w-100">
                                        <i class="bi bi-cart-plus me-2"></i> Add
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
        <div class="shadow-sm p-2 mb-4 rounded-1">
            <h4 class="mb-0 text-dark">Promotion / Discount</h4>
        </div>

        <?php
        // Select 1 random product with discount
        $result = $conn->query("
            SELECT *
            FROM products
            WHERE status = 1
              AND selling_price < original_price
            ORDER BY RAND()
            LIMIT 1
        ");

        $row = $result->fetch_assoc();
        if ($row):
            $discount = 0;
            if ($row['selling_price'] < $row['original_price']) {
                $discount = round(
                    (($row['original_price'] - $row['selling_price']) / $row['original_price']) * 100
                );
            }
        ?>

            <div class="card mb-4 shadow-sm p-5 ">
                <div class="row g-0 align-items-center mt-5 mb-5">

                    <!-- Text Left -->
                    <div class="col-md-6">
                        <?php if ($discount > 0): ?>
                            <span class="badge bg-danger mb-2">
                                -<?= $discount ?>%
                            </span>
                        <?php endif; ?>

                        <h3 class="card-title">
                            <?= htmlspecialchars($row['name']) ?>
                        </h3>

                        <p class="card-text text-muted">
                            <?= substr(strip_tags($row['description']), 0, 120) ?>...
                        </p>

                        <!-- Price -->
                        <?php if ($discount > 0): ?>
                            <p class="mb-2">
                                <span class="text-danger fw-bold">
                                    $<?= number_format($row['selling_price'], 2) ?>
                                </span>
                                <small class="text-muted ms-1">
                                    (<s>$<?= number_format($row['original_price'], 2) ?></s>)
                                </small>
                            </p>
                        <?php else: ?>
                            <p class="fw-bold mb-2">
                                $<?= number_format($row['selling_price'], 2) ?>
                            </p>
                        <?php endif; ?>

                        <div class="d-flex gap-2">
                            <a href="product_detail.php?id=<?= $row['id'] ?>"
                                class="btn btn-outline-primary">
                                <i class="bi bi-eye-fill me-2"></i> View
                            </a>

                            <form method="post">
                                <input type="hidden" name="product_id" value="<?= $row['id'] ?>">
                                <input type="hidden" name="qty" value="1">
                                <button type="submit" name="add_to_cart"
                                    class="btn btn-success">
                                    <i class="bi bi-cart-plus me-2"></i> Add
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- Image Right -->
                    <div class="col-md-6 text-center">
                        <?php if (!empty($row['image'])): ?>
                            <img src="<?= htmlspecialchars($row['image']) ?>"
                                class="img-fluid rounded"
                                style="max-height:250px; object-fit:cover;"
                                alt="<?= htmlspecialchars($row['name']) ?>">
                        <?php endif; ?>
                    </div>
                </div>
            </div>

        <?php endif; ?>
    </div>
</section>

<section class="p-4">
    <div class="container">
        <div class="shadow-sm p-2 mb-4 rounded-1">
            <h4 class="mb-0 text-dark">New Arrival</h4>
        </div>

        <div class="row">
            <?php
            $result = $conn->query("
                SELECT *
                FROM products
                WHERE status = 1
                ORDER BY created_at DESC
                LIMIT 8
            ");

            while ($row = $result->fetch_assoc()):
                // Discount calculation
                $discount = 0;
                if ($row['selling_price'] < $row['original_price']) {
                    $discount = round(
                        (($row['original_price'] - $row['selling_price']) / $row['original_price']) * 100
                    );
                }

                // New badge (last 5 days)
                $isNew = (strtotime($row['created_at']) >= strtotime('-5 days'));
            ?>
                <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
                    <div class="card h-100 shadow-sm position-relative">

                        <!-- Discount Badge -->
                        <?php if ($discount > 0): ?>
                            <span class="badge bg-danger position-absolute top-0 start-0 m-2">
                                -<?= $discount ?>%
                            </span>
                        <?php endif; ?>

                        <!-- New Badge -->
                        <?php if ($isNew): ?>
                            <span class="badge bg-success position-absolute top-0 end-0 m-2">
                                NEW
                            </span>
                        <?php endif; ?>

                        <!-- Product Image -->
                        <div class="product-img-box">
                            <?php if (!empty($row['image'])): ?>
                                <img src="<?= htmlspecialchars($row['image']) ?>"
                                    alt="<?= htmlspecialchars($row['name']) ?>"
                                    class="product-img">
                            <?php endif; ?>
                        </div>

                        <div class="card-body d-flex flex-column">
                            <h6 class="card-title">
                                <?= htmlspecialchars($row['name']) ?>
                            </h6>

                            <p class="card-text small text-muted">
                                <?= substr(strip_tags($row['description']), 0, 60) ?>...
                            </p>

                            <!-- Price -->
                            <?php if ($discount > 0): ?>
                                <p class="mb-2">
                                    <span class="text-danger fw-bold">
                                        $<?= number_format($row['selling_price'], 2) ?>
                                    </span>
                                    <small class="text-muted ms-1">
                                        (<s>$<?= number_format($row['original_price'], 2) ?></s>)
                                    </small>
                                </p>
                            <?php else: ?>
                                <p class="fw-bold mb-2">
                                    $<?= number_format($row['selling_price'], 2) ?>
                                </p>
                            <?php endif; ?>

                            <!-- Actions -->
                            <div class="mt-auto d-flex gap-2">
                                <a href="product_detail.php?id=<?= $row['id'] ?>"
                                    class="btn btn-outline-primary btn-sm w-50">
                                    <i class="bi bi-eye-fill me-2"></i> View
                                </a>

                                <form method="post" class="w-50">
                                    <input type="hidden" name="product_id" value="<?= $row['id'] ?>">
                                    <input type="hidden" name="qty" value="1">
                                    <button type="submit" name="add_to_cart"
                                        class="btn btn-success btn-sm w-100">
                                        <i class="bi bi-cart-plus me-2"></i> Add
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
        <div class="shadow-sm p-2 mb-4 rounded-1">
            <h4 class="mb-0 text-dark">Best Seller Products</h4>
        </div>

        <div class="swiper products">
            <div class="swiper-wrapper">
                <?php
                $result = $conn->query("
                    SELECT * FROM products 
                    WHERE status = 1 
                    ORDER BY RAND() 
                    LIMIT 10
                ");

                while ($row = $result->fetch_assoc()):
                    // Discount calculation
                    $discount = 0;
                    if ($row['selling_price'] < $row['original_price']) {
                        $discount = round((($row['original_price'] - $row['selling_price']) / $row['original_price']) * 100);
                    }
                ?>
                    <div class="swiper-slide">
                        <div class="card rounded-0 h-80 shadow-none">

                            <!-- Discount Badge -->
                            <?php if ($discount > 0): ?>
                                <span class="badge bg-danger position-absolute m-2" style="font-size: 12px;">-<?= $discount ?>%</span>
                            <?php endif; ?>

                            <!-- Product Image -->
                            <div class="product-img-box">
                                <?php if (!empty($row['image'])): ?>
                                    <img src="<?= htmlspecialchars($row['image']) ?>"
                                        alt="<?= htmlspecialchars($row['name']) ?>"
                                        class="product-img"
                                        style=" object-fit: contain;">

                                <?php endif; ?>
                            </div>

                            <div class="card-body d-flex flex-column">
                                <h6 class="card-title"><?= htmlspecialchars($row['name']) ?></h6>

                                <p class="card-text small text-muted" style="font-size: 12px;">
                                    <?= substr(strip_tags($row['description']), 0, 60) ?>...
                                </p>

                                <!-- Price -->
                                <?php if ($discount > 0): ?>
                                    <p class="mb-2" style="font-size: 16px;">
                                        <span class="text-danger fw-bold">
                                            $<?= number_format($row['selling_price'], 2) ?>
                                        </span>
                                        <small class="text-muted ms-1">
                                            (<s>$<?= number_format($row['original_price'], 2) ?></s>)
                                        </small>
                                    </p>
                                <?php else: ?>
                                    <p class="fw-bold mb-2" style="font-size: 16px;">
                                        $<?= number_format($row['selling_price'], 2) ?>
                                    </p>
                                <?php endif; ?>

                                <!-- Actions -->
                                <a href="product_detail.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-eye-fill me-2"></i> View
                                </a>
                                <div class="mt-1 d-flex align-items-stretch">
                                    <form method="post" class="flex-fill m-0">
                                        <input type="hidden" name="product_id" value="<?= $row['id'] ?>">
                                        <input type="hidden" name="qty" value="1">
                                        <button name="add_to_cart" class="btn btn-success btn-sm w-100">
                                            <i class="bi bi-cart-plus me-2"></i> Add
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>

            <!-- Swiper Navigation -->
            <div class="swiper-button-next products-next"></div>
            <div class="swiper-button-prev products-prev"></div>
        </div>
    </div>
</section>

<section class="p-4">
    <div class="container">
        <div class="shadow-sm p-2 mb-4 rounded-1">
            <h4 class="mb-0 text-dark">Customer Reviews</h4>
        </div>

        <div id="reviewCarousel" class="carousel slide" data-bs-ride="carousel">
            <div class="carousel-inner">

                <?php
                $reviews = $conn->query("SELECT * FROM reviews WHERE status=1 ORDER BY created_at DESC");
                $active = "active";
                $i = 0;

                while ($review = $reviews->fetch_assoc()):
                    $firstLetter = strtoupper(substr($review['user_name'], 0, 1));
                    $profileUrl = "profile_user.php?id=" . $review['user_id'];


                    // open slide every 2 items
                    if ($i % 2 == 0):
                ?>
                        <div class="carousel-item <?= $active ?>">
                            <div class="row justify-content-center">
                            <?php endif; ?>

                            <div class="col-md-6 mb-3">
                                <div class="card review-card p-3 shadow-sm h-100" data-aos="zoom-in" data-aos-delay="200">
                                    <div class="d-flex align-items-center mb-2">
                                        <a href="<?= $profileUrl ?>" class="d-flex align-items-center text-decoration-none text-dark">
                                            <?php if (!empty($review['profile_image'])): ?>
                                                <img src="<?= htmlspecialchars($review['profile_image']) ?>"
                                                    class="rounded-circle me-2"
                                                    style="width:40px;height:40px;object-fit:cover;">
                                            <?php else: ?>
                                                <div class="rounded-circle bg-primary text-white d-flex justify-content-center align-items-center me-2"
                                                    style="width:40px;height:40px;font-weight:bold;">
                                                    <?= $firstLetter ?>
                                                </div>
                                            <?php endif; ?>
                                            <div>
                                                <strong><?= htmlspecialchars($review['user_name']) ?></strong><br>
                                                <small class="text-warning">
                                                    <?php for ($s = 1; $s <= 5; $s++): ?>
                                                        <?= ($s <= $review['rating']) ? '<i class="bi bi-star-fill"></i>' : '<i class="bi bi-star"></i>'; ?>
                                                    <?php endfor; ?>
                                                </small>
                                            </div>
                                        </a>
                                    </div>
                                    <p class="small text-muted mb-0">
                                        <?= htmlspecialchars($review['comment']) ?>
                                    </p>
                                </div>
                            </div>

                            <?php
                            // close slide every 2 items
                            if ($i % 2 == 1):
                            ?>
                            </div>
                        </div>
                <?php
                                $active = "";
                            endif;
                            $i++;
                        endwhile;

                        // close last slide if odd number
                        if ($i % 2 != 0):
                            echo '</div></div>';
                        endif;
                ?>

            </div>

            <!-- Controls -->
            <button class="carousel-control-prev" type="button" data-bs-target="#reviewCarousel" data-bs-slide="prev">
                <span class="carousel-control-prev-icon"></span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#reviewCarousel" data-bs-slide="next">
                <span class="carousel-control-next-icon"></span>
            </button>
        </div>
    </div>
</section>

<section class="p-4 bg-warning">
    <div class="container text-center mt-5 mb-5">
        <h4>Subscribe to our Newsletter</h4>
        <p>Get latest updates and offers</p>

        <?php if (isset($_SESSION['user_id'])): ?>
            <form method="post" class="d-flex justify-content-center">
                <div class="form-outline w-50 me-2">
                    <input type="email" name="email" class="form-control" placeholder=" " required>
                    <label for="">Enter your email</label>
                </div>
                <button type="submit" name="subscribe" class="btn btn-primary">
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

    var productsSwiper = new Swiper('.products', {
        slidesPerView: 3,
        spaceBetween: 20,
        loop: true,
        autoplay: {
            delay: 2500,
            disableOnInteraction: false
        },
        navigation: {
            nextEl: '.products-next',
            prevEl: '.products-prev'
        },
        breakpoints: {
            0: {
                slidesPerView: 1
            },
            576: {
                slidesPerView: 2
            },
            768: {
                slidesPerView: 3
            },
            992: {
                slidesPerView: 4
            }
        }
    });
</script>