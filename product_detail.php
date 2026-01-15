<?php
session_start();
require 'inc/db.php';

// Get Product ID
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// ==========================
// Initialize Cart
// ==========================
if (!isset($_SESSION['cart'])) {
    if (isset($_COOKIE['cart'])) {
        $_SESSION['cart'] = json_decode($_COOKIE['cart'], true);
    } else {
        $_SESSION['cart'] = [];
    }
}

// ==========================
// Redirect guest to login
// ==========================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
    if (!isset($_SESSION['user_id'])) {
        // Save attempted product in session (optional)
        $_SESSION['redirect_product'] = intval($_POST['product_id']);
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

    if (!$product) die("Product not found");
    if ($qty > $product['qty']) $qty = $product['qty'];

    // Add/update cart in session
    if (isset($_SESSION['cart'][$product_id])) {
        $newQty = $_SESSION['cart'][$product_id]['qty'] + $qty;
        if ($newQty > $product['qty']) $newQty = $product['qty'];
        $_SESSION['cart'][$product_id]['qty'] = $newQty;
    } else {
        $_SESSION['cart'][$product_id] = [
            'id'    => $product['id'],
            'name'  => $product['name'],
            'price' => $product['price'],
            'qty'   => $qty,
            'image' => $product['image']
        ];
    }

    // Save cart in cookie (7 days)
    setcookie('cart', json_encode($_SESSION['cart']), time() + (7 * 24 * 60 * 60), "/");

    $_SESSION['cart_success'] = "Product added to cart!";
    header("Location: product_detail.php?id=" . $product_id);
    exit;
}

// Load product details
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$stmt = $conn->prepare("SELECT * FROM products WHERE id=? AND status=1");
$stmt->bind_param("i", $id);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$product) die("Product not found");

// Handle Review 
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_review'])) {

    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit;
    }

    $product_id = intval($_POST['product_id']);
    $user_id    = $_SESSION['user_id'];
    $rating     = intval($_POST['rating']);
    $comment    = trim($_POST['comment']);

    if ($product_id > 0 && $rating >= 1 && $rating <= 5) {

        $chk = $conn->prepare("  // Check duplicate review
            SELECT id FROM reviews
            WHERE product_id = ? AND user_id = ?
        ");
        $chk->bind_param("ii", $product_id, $user_id);
        $chk->execute();
        $chk->store_result();

        if ($chk->num_rows === 0) {
            $stmt = $conn->prepare("
                INSERT INTO reviews (product_id, user_id, rating, comment, status)
                VALUES (?, ?, ?, ?, 1)
            ");
            $stmt->bind_param("iiis", $product_id, $user_id, $rating, $comment);
            $stmt->execute();
            $stmt->close();

            header("Location: product_detail.php?id=" . $product_id);
            exit;
        } else {
            $error = "You already reviewed this product.";
        }
        $chk->close();
    } else {
        $error = "Invalid rating.";
    }
}


// Fetch Product
$stmt = $conn->prepare("
    SELECT p.*, 
           b.name AS brand_name,
           c.name AS category_name
    FROM products p
    LEFT JOIN brands b ON p.brand_id = b.id
    LEFT JOIN categories c ON p.category_id = c.id
    WHERE p.id = ? AND p.status = 1
");
$stmt->bind_param("i", $id);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$product) {
    die("<div class='container mt-5'><h3>Product not found!</h3></div>");
}

// Rating Summary
$rateStmt = $conn->prepare("
    SELECT AVG(rating) AS avg_rating, COUNT(*) AS total_reviews
    FROM reviews
    WHERE product_id = ? AND status = 1
");
$rateStmt->bind_param("i", $id);
$rateStmt->execute();
$rate = $rateStmt->get_result()->fetch_assoc();
$rateStmt->close();

$avgRating    = round($rate['avg_rating'], 1);
$totalReviews = $rate['total_reviews'];

//    Check User Reviewed
$userReviewed = false;
if (isset($_SESSION['user_id'])) {
    $chk = $conn->prepare("
        SELECT id FROM reviews
        WHERE product_id = ? AND user_id = ?
    ");
    $chk->bind_param("ii", $id, $_SESSION['user_id']);
    $chk->execute();
    $chk->store_result();
    $userReviewed = $chk->num_rows > 0;
    $chk->close();
}

include 'inc/header.php';
?>

<div class="container my-5">
    <div class="row">

        <!-- Image -->
        <div class="col-md-6">
            <img src="<?= htmlspecialchars($product['image']) ?>"
                class="img-fluid rounded shadow"
                style="height:400px; object-fit:cover;">
        </div>

        <!-- Info -->
        <div class="col-md-6">
            <h2><?= htmlspecialchars($product['name']) ?></h2>

            <p class="text-muted">
                Category: <?= htmlspecialchars($product['category_name']) ?> |
                Brand: <?= htmlspecialchars($product['brand_name']) ?>
            </p>

            <!-- Rating -->
            <p>
                <?php for ($i = 1; $i <= 5; $i++): ?>
                    <i class="bi <?= $i <= round($avgRating) ? 'bi-star-fill' : 'bi-star' ?> text-warning"></i>
                <?php endfor; ?>
                <small>(<?= $totalReviews ?> reviews)</small>
            </p>

            <!-- Price -->
            <?php
            $discount   = $product['discount_percent'] ?? 0;
            $finalPrice = $product['price'] - ($product['price'] * $discount / 100);
            ?>
            <p class="h4 text-danger">
                $<?= number_format($finalPrice, 2) ?>
                <?php if ($discount > 0): ?>
                    <small class="text-muted text-decoration-line-through">
                        $<?= number_format($product['price'], 2) ?>
                    </small>
                <?php endif; ?>
            </p>

            <!-- Add to Cart -->
            <?php if ($product['qty'] > 0): ?>
                <form method="post" id="addToCartForm" class="d-flex gap-2">
                    <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                    <input type="number" name="qty" value="1" min="1"
                        max="<?= $product['qty'] ?>"
                        class="form-control w-25 shadow-none">
                    <button name="add_to_cart" class="btn btn-success">Add to Cart</button>
                </form>
            <?php else: ?>
                <button class="btn btn-secondary" disabled>Out of Stock</button>
            <?php endif; ?>
        </div>
    </div>

    <!-- Tabs -->
    <ul class="nav nav-tabs mt-5">
        <li class="nav-item">
            <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#desc">
                Description
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#reviews">
                Reviews (<?= $totalReviews ?>)
            </button>
        </li>
    </ul>

    <div class="tab-content border p-4">

        <!-- Description -->
        <div class="tab-pane fade show active" id="desc">
            <?= nl2br(htmlspecialchars($product['description'])) ?>
        </div>

        <!-- Reviews -->
        <div class="tab-pane fade" id="reviews">

            <?php
            $revStmt = $conn->prepare("
                SELECT r.*, u.username
                FROM reviews r
                JOIN users u ON r.user_id = u.id
                WHERE r.product_id = ? AND r.status = 1
                ORDER BY r.created_at DESC
            ");
            $revStmt->bind_param("i", $id);
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
                <p>No reviews yet.</p>
            <?php endif; ?>
            <?php $revStmt->close(); ?>

            <hr>

            <!-- Review Form -->
            <?php if (!isset($_SESSION['user_id'])): ?>
                <div class="alert alert-warning">
                    Please <a href="login.php">login</a> to write a review.
                </div>

            <?php elseif ($userReviewed): ?>
                <div class="alert alert-info">
                    You already reviewed this product.
                </div>

            <?php else: ?>
                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger"><?= $error ?></div>
                <?php endif; ?>

                <form method="post">
                    <input type="hidden" name="product_id" value="<?= $product['id'] ?>">

                    <select name="rating" class="form-select mb-2" required>
                        <option value="">Select rating</option>
                        <option value="5">★★★★★</option>
                        <option value="4">★★★★</option>
                        <option value="3">★★★</option>
                        <option value="2">★★</option>
                        <option value="1">★</option>
                    </select>

                    <textarea name="comment" class="form-control mb-2"
                        placeholder="Your review"></textarea>

                    <button class="btn btn-primary" name="submit_review">
                        Submit Review
                    </button>
                </form>
            <?php endif; ?>

        </div>
    </div>
</div>

<?php include 'inc/footer.php'; ?>