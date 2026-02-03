<?php
require 'inc/db.php';
session_start();

// GET product ID (optional, e.g., from view link)
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// POST: Add to Cart
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit;
    }

    $product_id = intval($_POST['product_id']);
    $qty        = max(1, intval($_POST['qty']));

    // Fetch product
    $stmt = $conn->prepare("SELECT id, name, selling_price, qty, image FROM products WHERE id=? AND status=1");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $product = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$product) {
        die("Product not found");
    }

    // Adjust qty if exceeding stock
    $qty = min($qty, $product['qty']);

    // Init cart
    if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];

    // Add/update cart
    if (isset($_SESSION['cart'][$product_id])) {
        $_SESSION['cart'][$product_id]['qty'] = min(
            $_SESSION['cart'][$product_id]['qty'] + $qty,
            $product['qty']
        );
    } else {
        $_SESSION['cart'][$product_id] = [
            'id' => $product['id'],
            'name' => $product['name'],
            'price' => $product['selling_price'],
            'qty' => $qty,
            'image' => $product['image']
        ];
    }

    $_SESSION['cart_success'] = "Product added to cart!";
    header("Location: search.php?q=" . urlencode($_GET['q'] ?? ''));
    exit;
}

// Search Query
$q = isset($_GET['q']) ? trim($_GET['q']) : '';

// Base SQL
$sql = "SELECT * FROM products WHERE status=1";

// Add search filter
$params = [];
$types = "";
if ($q !== '') {
    $sql .= " AND (name LIKE ? OR description LIKE ?)";
    $searchTerm = "%$q%";
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $types .= "ss";
}

// Order by discount (computed as discount_percent)
$sql .= " ORDER BY discount_percent DESC";

// Prepare statement
$stmt = $conn->prepare($sql);
if ($types) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

require 'inc/header.php';
?>


<!-- Page Banner -->
<div class="py-5 text-center text-white" style="background: linear-gradient(135deg,rgb(47, 47, 93),rgb(61, 68, 60));">
    <div class="container">
        <h1 class="fw-bold mb-2" data-aos="fade-up">Search</h1>
        <p class="lead" data-aos="fade-up">Product search <?= htmlspecialchars($q) ?></p>
    </div>
</div>

<section class="p-4">
    <div class="bg-success shadow-sm p-2 mb-2 rounded-1">
        <h2 class="mb-0 text-white">Search result for: <?= htmlspecialchars($q) ?></h2>
    </div>

    <div class="row">
        <?php if ($result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
                    <div class="card h-100 shadow-sm position-relative">

                        <?php if (!empty($row['discount_percent']) && $row['discount_percent'] > 0): ?>
                            <span class="badge bg-danger position-absolute top-0 start-0 m-2">
                                -<?= $row['discount_percent'] ?>%
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

                            <p class="mb-2">
                                <span class="text-danger fw-bold">
                                    $<?= number_format($row['selling_price'], 2) ?>
                                </span>
                                <?php if (!empty($row['discount_percent']) && $row['discount_percent'] > 0): ?>
                                    <del class="text-muted small ms-1">
                                        $<?= number_format($row['original_price'], 2) ?>
                                    </del>
                                <?php endif; ?>
                            </p>

                            <div class="mt-auto d-flex gap-2">
                                <a href="product_detail.php?id=<?= $row['id'] ?>"
                                    class="btn btn-outline-primary btn-sm w-50">
                                    View
                                </a>

                                <form method="post" class="w-50">
                                    <input type="hidden" name="product_id" value="<?= $row['id'] ?>">
                                    <input type="hidden" name="qty" value="1">
                                    <button name="add_to_cart" class="btn btn-success btn-sm w-100">
                                        Add to Cart
                                    </button>
                                </form>
                            </div>
                        </div>

                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="col-12">
                <div class="card h-100 shadow-sm position-relative">
                    <h5 class="text-center p-5">No products found.</h5>
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php require 'inc/footer.php'; ?>