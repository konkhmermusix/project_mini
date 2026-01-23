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
    header("Location: search.php?id=" . $product_id);
    exit;
}

$q = '';
if (isset($_GET['q'])) {
    $q = trim($_GET['q']);
}

$sql = "
    SELECT *, (price - cost_price) AS discount_amount 
    FROM products 
    WHERE status = 1 
    AND cost_price < price
";

if (!empty($q)) {
    $sql .= " AND (name LIKE ? OR description LIKE ?)";
}

$sql .= " ORDER BY discount_amount DESC";

$stmt = $conn->prepare($sql);

if (!empty($q)) {
    $search = "%$q%";
    $stmt->bind_param("ss", $search, $search);
}

$stmt->execute();
$result = $stmt->get_result();
require 'inc/header.php';

?>
<section class="p-4">
    <div class="bg-success shadow-sm p-2 mb-2 rounded-1">
        <h2 class="mb-0 text-white"> Search result for: <?= htmlspecialchars($q) ?></h2>
    </div>
    <div class="row">
        <?php if ($result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
                    <div class="card h-100 shadow-sm position-relative">

                        <?php
                        $discount = 0;
                        if ($row['cost_price'] < $row['price']) {
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

                            <p class="mb-2">
                                <span class="text-danger fw-bold">
                                    $<?= number_format($row['cost_price'], 2) ?>
                                </span>
                                <del class="text-muted small ms-1">
                                    $<?= number_format($row['price'], 2) ?>
                                </del>
                            </p>

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
        <?php else: ?>
            <div class="col-lg-12 col-md-4 col-sm-6 mb-4">
                <div class="card h-100 shadow-sm position-relative">
                    <h5 class="text-center p-5">
                        No products found.
                    </h5>
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php require 'inc/footer.php'; ?>