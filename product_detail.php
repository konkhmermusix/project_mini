<?php
session_start();
require 'inc/db.php';

// Get Product ID
$productId = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($productId <= 0) die("Invalid product.");

// Add to Cart Handling
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {

    if (!isset($_SESSION['user_id'])) {
        $_SESSION['redirect_product'] = $productId;
        header("Location: login.php");
        exit;
    }

    $qty = max(1, intval($_POST['qty']));

    $stmt = $conn->prepare("SELECT id, name, price, qty, image, cost_price FROM products WHERE id=? AND status=1");
    $stmt->bind_param("i", $productId);
    $stmt->execute();
    $productCart = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$productCart) die("Product not found");
    if ($qty > $productCart['qty']) $qty = $productCart['qty'];

    if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];

    if (isset($_SESSION['cart'][$productId])) {
        $newQty = $_SESSION['cart'][$productId]['qty'] + $qty;
        $_SESSION['cart'][$productId]['qty'] = min($newQty, $productCart['qty']);
    } else {
        $_SESSION['cart'][$productId] = [
            'id' => $productCart['id'],
            'name' => $productCart['name'],
            'price' => $productCart['price'],
            'qty' => $qty,
            'image' => $productCart['image'],
            'cost_price' => $productCart['cost_price']
        ];
    }

    setcookie('cart', json_encode($_SESSION['cart']), time() + 7 * 24 * 60 * 60, "/");
    $_SESSION['cart_success'] = "Product added to cart!";
    header("Location: product_detail.php?id=$productId");
    exit;
}

// Load Product Details
$stmt = $conn->prepare("
    SELECT p.*, b.name AS brand_name, c.name AS category_name
    FROM products p
    LEFT JOIN brands b ON p.brand_id=b.id
    LEFT JOIN categories c ON p.category_id=c.id
    WHERE p.id=? AND p.status=1
");
$stmt->bind_param("i", $productId);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$product) die("Product not found!");

$category_id = $product['category_id'];

// Load Reviews & Rating
$rateStmt = $conn->prepare("SELECT AVG(rating) AS avg_rating, COUNT(*) AS total_reviews FROM reviews WHERE product_id=? AND status=1");
$rateStmt->bind_param("i", $productId);
$rateStmt->execute();
$rate = $rateStmt->get_result()->fetch_assoc();
$rateStmt->close();

$avgRating = round($rate['avg_rating'], 1);
$totalReviews = $rate['total_reviews'];

// Check if user already reviewed
$userReviewed = false;
if (isset($_SESSION['user_id'])) {
    $chk = $conn->prepare("SELECT id FROM reviews WHERE product_id=? AND user_id=?");
    $chk->bind_param("ii", $productId, $_SESSION['user_id']);
    $chk->execute();
    $chk->store_result();
    $userReviewed = $chk->num_rows > 0;
    $chk->close();
}

require 'inc/header.php';
?>

<section class="py-5">
    <div class="container">
        <div class="row g-4">
            <div class="col-md-6">
                <div class="card shadow-sm">
                    <div class="col-md-12">
                        <img src="<?= htmlspecialchars($product['image']) ?>" class="img-fluid rounded shadow" style="height:400px; object-fit:cover;">
                    </div>
                </div>
            </div>
            <div class="col-md-6 d-flex flex-column">
                <h2 class="fw-bold"><?= htmlspecialchars($product['name']) ?></h2>
                <p class="text-muted mb-2">
                    Category: <?= htmlspecialchars($product['category_name']) ?> |
                    Brand: <?= htmlspecialchars($product['brand_name']) ?>
                </p>
                <div class="mb-3">
                    <?php for ($i = 1; $i <= 5; $i++): ?>
                        <i class="bi <?= $i <= round($avgRating) ? 'bi-star-fill' : 'bi-star' ?> text-warning"></i>
                    <?php endfor; ?>
                    <small class="text-muted">(<?= $totalReviews ?> reviews)</small>
                </div>
                <?php
                $discount = $product['discount_percent'] ?? 0;
                $finalPrice = $product['price'] - ($product['price'] * $discount / 100);
                ?>
                <div class="mb-3">
                    <h3 class="text-danger">
                        $<?= number_format($finalPrice, 2) ?>
                        <?php if ($discount > 0): ?>
                            <small class="text-muted text-decoration-line-through">
                                $<?= number_format($product['price'], 2) ?>
                            </small>
                            <span class="badge bg-danger"><?= $discount ?>% OFF</span>
                        <?php endif; ?>
                    </h3>
                </div>
                <div class="mb-4">
                    <?php if ($product['qty'] > 0): ?>
                        <form method="post" class="d-flex align-items-center gap-2">
                            <input type="hidden" name="product_id" value="<?= $productId ?>">
                            <input type="number" name="qty" value="1" min="1" max="<?= $product['qty'] ?>" class="form-control w-25">
                            <button name="add_to_cart" class="btn btn-success">Add to Cart <i class="bi bi-cart-plus"></i></button>
                        </form>
                    <?php else: ?>
                        <button class="btn btn-secondary" disabled>Out of Stock</button>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="row mt-5">
            <div class="col-12">
                <ul class="nav nav-tabs" id="productTab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="desc-tab" data-bs-toggle="tab" data-bs-target="#desc" type="button" role="tab">Description</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="reviews-tab" data-bs-toggle="tab" data-bs-target="#reviews" type="button" role="tab">
                            Reviews (<?= $totalReviews ?>)
                        </button>
                    </li>
                </ul>
                <div class="tab-content border border-top-0 p-4">
                    <!-- Description -->
                    <div class="tab-pane fade show active" id="desc" role="tabpanel">
                        <?= nl2br(htmlspecialchars($product['description'])) ?>
                    </div>

                    <!-- Reviews -->
                    <div class="tab-pane fade" id="reviews" role="tabpanel">
                        <?php
                        $revStmt = $conn->prepare("
                            SELECT r.*, u.username 
                            FROM reviews r 
                            JOIN users u ON r.user_id=u.id 
                            WHERE r.product_id=? AND r.status=1 
                            ORDER BY r.created_at DESC
                        ");
                        $revStmt->bind_param("i", $productId);
                        $revStmt->execute();
                        $reviews = $revStmt->get_result();
                        ?>

                        <?php if ($reviews->num_rows > 0): ?>
                            <?php while ($r = $reviews->fetch_assoc()): ?>
                                <div class="border-bottom mb-3 pb-2">
                                    <strong><?= htmlspecialchars($r['username']) ?></strong>
                                    <div>
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <i class="bi <?= $i <= $r['rating'] ? 'bi-star-fill' : 'bi-star' ?> text-warning"></i>
                                        <?php endfor; ?>
                                    </div>
                                    <p><?= htmlspecialchars($r['comment']) ?></p>
                                    <small class="text-muted"><?= $r['created_at'] ?></small>
                                </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <p class="text-muted">No reviews yet.</p>
                        <?php endif; ?>
                        <?php $revStmt->close(); ?>

                        <hr>

                        <!-- Review Form -->
                        <?php if (!isset($_SESSION['user_id'])): ?>
                            <div class="alert alert-warning">Please <a href="login.php">login</a> to write a review.</div>
                        <?php elseif ($userReviewed): ?>
                            <div class="alert alert-info">You already reviewed this product.</div>
                        <?php else: ?>
                            <?php if (!empty($error)): ?>
                                <div class="alert alert-danger"><?= $error ?></div>
                            <?php endif; ?>
                            <form method="post" class="mt-3">
                                <input type="hidden" name="product_id" value="<?= $productId ?>">
                                <div class="mb-2">
                                    <label for="rating" class="form-label">Rating</label>
                                    <select name="rating" id="rating" class="form-select" required>
                                        <option value="">Select rating</option>
                                        <option value="5">★★★★★</option>
                                        <option value="4">★★★★</option>
                                        <option value="3">★★★</option>
                                        <option value="2">★★</option>
                                        <option value="1">★</option>
                                    </select>
                                </div>
                                <div class="mb-2">
                                    <label for="comment" class="form-label">Comment</label>
                                    <textarea name="comment" id="comment" class="form-control" placeholder="Your review" required></textarea>
                                </div>
                                <button class="btn btn-primary" name="submit_review">Submit Review</button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

    </div>
</section>

<!-- Related Products -->
<section class="py-5">
    <div class="container">
        <div class="bg-secondary shadow-sm p-2 mb-2 rounded-1 d-flex justify-content-between align-items-center">
            <h4 class="mb-0 text-white">Related Products</h4>
            <div>
                <button class="btn btn-sm btn-light" onclick="slideLeft()"><i class="bi bi-arrow-left"></i></button>
                <button class="btn btn-sm btn-light" onclick="slideRight()"><i class="bi bi-arrow-right"></i></button>
            </div>
        </div>

        <div class="product-slider-wrapper">
            <div class="product-slider" id="productSlider">

                <?php
                $stmt = $conn->prepare("
                SELECT id, name, price, cost_price, image, created_at
                FROM products
                WHERE status=1 AND category_id=? AND id!=?
                ORDER BY created_at DESC
                LIMIT 16
            ");
                $stmt->bind_param("ii", $category_id, $productId);
                $stmt->execute();
                $result = $stmt->get_result();

                // Fallback
                if ($result->num_rows == 0) {
                    $stmt = $conn->prepare("
                    SELECT id,name,price,image
                    FROM products
                    WHERE status=1 AND id!=?
                    ORDER BY created_at DESC
                    LIMIT 16
                ");
                    $stmt->bind_param("i", $productId);
                    $stmt->execute();
                    $result = $stmt->get_result();
                }

                while ($row = $result->fetch_assoc()):
                ?>

                    <div class="product-slide">
                        <div class="card h-100 shadow-sm position-relative">
                            <?php if (strtotime($row['created_at']) >= strtotime('-5 days')): ?>
                                <span class="badge bg-success position-absolute top-0 end-0 m-2">NEW</span>
                            <?php endif; ?>
                            <img src="<?= htmlspecialchars($row['image']) ?>" class="card-img-top" style="height:180px;object-fit:cover;">
                            <div class="card-body text-center">
                                <h6 class="card-title"><?= htmlspecialchars($row['name']) ?></h6>
                                <span class="text-danger fw-bold">$<?= number_format($row['price'], 2) ?></span>
                                <div class="card-footer">
                                    <div class="mt-auto d-flex">
                                        <a href="product_detail.php?id=<?= $row['id'] ?>"
                                            class="btn btn-outline-primary btn-sm w-50 d-flex align-items-center justify-content-center">
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
                    </div>

                <?php endwhile; ?>
            </div>
        </div>
    </div>
</section>


<style>
    /* ===== Slider Wrapper ===== */
    .product-slider-wrapper {
        overflow: hidden;
        position: relative;
    }

    .product-slider {
        display: flex;
        gap: 16px;
        transition: transform 0.4s ease;
        will-change: transform;
    }

    .product-slide {
        flex: 0 0 220px;
    }

    .product-slide .card {
        border: none;
        border-radius: 10px;
        overflow: hidden;
        transition: all 0.25s ease;
    }

    .product-slide .card:hover {
        transform: translateY(-6px);
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.12);
    }

    /* ===== Image ===== */
    .product-slide img {
        border-bottom: 1px solid #eee;
    }

    /* ===== Card Body ===== */
    .product-slide .card-body {
        display: flex;
        flex-direction: column;
        padding: 12px;
    }

    /* ===== Title ===== */
    .product-slide .card-title {
        font-size: 0.9rem;
        font-weight: 600;
        min-height: 36px;
    }

    /* ===== Price ===== */
    .product-slide .text-danger {
        font-size: 1rem;
        margin-bottom: 10px;
    }

    /* ===== Footer Buttons ===== */
    .product-slide .card-footer {
        padding: 0;
        background: transparent;
        border: none;
    }

    .product-slide .card-footer .btn {
        border-radius: 6px;
        font-size: 0.8rem;
        transition: all 0.2s ease;
    }

    .product-slide .card-footer .btn:hover {
        transform: scale(1.03);
    }

    /* ===== NEW Badge ===== */
    .product-slide .badge {
        font-size: 0.7rem;
        padding: 6px 8px;
        border-radius: 20px;
    }

    /* ===== Slider Header ===== */
    .bg-secondary h4 {
        font-size: 1.1rem;
        font-weight: 600;
    }

    /* ===== Arrow Buttons ===== */
    .bg-secondary button {
        width: 34px;
        height: 34px;
        border-radius: 50%;
        padding: 0;
    }
</style>
<?php require 'inc/footer.php'; ?>