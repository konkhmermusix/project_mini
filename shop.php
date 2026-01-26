<?php
session_start();
require 'inc/db.php';

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {

    $product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
    $qty = isset($_POST['qty']) ? intval($_POST['qty']) : 1;

    if ($product_id <= 0) {
        $_SESSION['cart_error'] = "Invalid product.";
        header("Location: " . $_SERVER['REQUEST_URI']);
        exit;
    }

    // Fetch product details from DB
    $stmt = $conn->prepare("SELECT id, name, price, image FROM products WHERE id=? AND status=1");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $product = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$product) {
        $_SESSION['cart_error'] = "Product not found";
        header("Location: " . $_SERVER['REQUEST_URI']);
        exit;
    }

    // Initialize cart if not exist
    if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];

    // Initialize cart item if not exist
    if (!isset($_SESSION['cart'][$product_id])) {
        $_SESSION['cart'][$product_id] = [
            'id' => $product['id'],
            'name' => $product['name'],
            'price' => $product['price'],
            'qty' => 0,
            'image' => $product['image'],
        ];
    }

    // Update quantity
    $_SESSION['cart'][$product_id]['qty'] += $qty;

    $_SESSION['cart_success'] = "Product added to cart!";
    header("Location: " . $_SERVER['REQUEST_URI']);
    exit;
}
// ============================
// Filters, Search, Sorting, Pagination
// ============================
$category_id = isset($_GET['category_id']) ? intval($_GET['category_id']) : 0;
$brand_id    = isset($_GET['brand_id']) ? intval($_GET['brand_id']) : 0;
$search      = isset($_GET['search']) ? trim($_GET['search']) : '';
$sort        = $_GET['sort'] ?? '';
$limit       = 16;
$page        = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset      = ($page - 1) * $limit;

$where = "WHERE status = 1";
$params = [];
$types = "";

if ($category_id > 0) {
    $where .= " AND category_id=?";
    $params[] = $category_id;
    $types .= "i";
}
if ($brand_id > 0) {
    $where .= " AND brand_id=?";
    $params[] = $brand_id;
    $types .= "i";
}
if ($search !== '') {
    $where .= " AND (name LIKE ? OR description LIKE ?)";
    $searchParam = "%$search%";
    $params[] = $searchParam;
    $params[] = $searchParam;
    $types .= "ss";
}

// Sorting
$order = "ORDER BY created_at DESC";
if ($sort === 'price_asc') $order = "ORDER BY cost_price ASC";
if ($sort === 'price_desc') $order = "ORDER BY cost_price DESC";
if ($sort === 'newest') $order = "ORDER BY created_at DESC";

// Total Items
$totalSql = "SELECT COUNT(*) AS total FROM products $where";
$stmtTotal = $conn->prepare($totalSql);
if ($types) $stmtTotal->bind_param($types, ...$params);
$stmtTotal->execute();
$totalItems = $stmtTotal->get_result()->fetch_assoc()['total'];
$totalPages = ceil($totalItems / $limit);
$stmtTotal->close();

// Fetch Products
$productSql = "SELECT * FROM products $where $order LIMIT ?, ?";
$stmt = $conn->prepare($productSql);

if ($types) {
    $paramsFull = $params;
    $paramsFull[] = $offset;
    $paramsFull[] = $limit;
    $typesFull = $types . "ii";
    $stmt->bind_param($typesFull, ...$paramsFull);
} else {
    $stmt->bind_param("ii", $offset, $limit);
}

$stmt->execute();
$products = $stmt->get_result();

require 'inc/header.php';
?>

<!-- Page Banner -->
<section class="py-5 text-center text-white" style="background: linear-gradient(135deg, #4f46e5, #3b82f6);">
    <div class="container">
        <h1 class="fw-bold mb-2">Shop</h1>
        <p class="lead">Browse our products</p>
    </div>
</section>

<section class="p-4">
    <div class="row">
        <!-- Sidebar -->
        <div class="col-md-2">
            <!-- Search -->
            <div class="card shadow rounded-0 mb-3 p-2">
                <form method="get" action="shop.php">
                    <input type="text" name="search" class="form-control mb-2" placeholder="Search products..." value="<?= htmlspecialchars($search) ?>">
                    <button type="submit" class="btn btn-primary w-100">Search</button>
                </form>
            </div>

            <!-- Sorting -->
            <div class="card shadow rounded-0 mb-3 p-2">
                <select class="form-select" onchange="location=this.value;">
                    <option value="shop.php?<?= http_build_query($_GET) ?>&sort=newest" <?= $sort == 'newest' ? 'selected' : '' ?>>Newest</option>
                    <option value="shop.php?<?= http_build_query($_GET) ?>&sort=price_asc" <?= $sort == 'price_asc' ? 'selected' : '' ?>>Price: Low → High</option>
                    <option value="shop.php?<?= http_build_query($_GET) ?>&sort=price_desc" <?= $sort == 'price_desc' ? 'selected' : '' ?>>Price: High → Low</option>
                </select>
            </div>

            <h5 class="card p-3 shadow rounded-0 bg-primary text-white">Categories</h5>
            <div class="card shadow rounded-0 mb-3">
                <div class="p-1">
                    <?php
                    $catResult = $conn->query("SELECT * FROM categories WHERE status=1 ORDER BY name");
                    while ($cat = $catResult->fetch_assoc()):
                    ?>
                        <a href="shop.php?category_id=<?= $cat['id'] ?>" class="btn rounded-0 w-100 text-start btn-hover <?= $cat['id'] == $category_id ? 'active' : '' ?>">
                            <p class="mb-0"><?= htmlspecialchars($cat['name']) ?></p>
                        </a>
                    <?php endwhile; ?>
                </div>
            </div>

            <h5 class="card p-3 shadow rounded-0 bg-primary text-white">Brands</h5>
            <div class="card shadow rounded-0 mb-3">
                <div class="p-1">
                    <?php
                    $brandResult = $conn->query("SELECT * FROM brands WHERE status=1 ORDER BY name");
                    while ($b = $brandResult->fetch_assoc()):
                    ?>
                        <a href="shop.php?brand_id=<?= $b['id'] ?>" class="btn rounded-0 w-100 text-start btn-hover <?= $b['id'] == $brand_id ? 'active' : '' ?>">
                            <p class="mb-0"><?= htmlspecialchars($b['name']) ?></p>
                        </a>
                    <?php endwhile; ?>
                </div>
            </div>
        </div>

        <!-- Products Grid -->
        <div class="col-md-10 mb-4 px-3">
            <div class="row g-3">
                <?php if ($products && $products->num_rows > 0): ?>
                    <?php while ($row = $products->fetch_assoc()):
                        $discount = 0;
                        if (!empty($row['cost_price']) && $row['cost_price'] < $row['price']) {
                            $discount = round((($row['price'] - $row['cost_price']) / $row['price']) * 100);
                        }
                    ?>
                        <div class="col-md-3 col-sm-6">
                            <div class="card shadow-sm h-100 position-relative">
                                <?php if ($discount > 0): ?>
                                    <span class="badge bg-danger position-absolute top-0 start-0 m-2">-<?= $discount ?>%</span>
                                <?php endif; ?>

                                <?php if (!empty($row['image'])): ?>
                                    <img src="<?= htmlspecialchars($row['image']) ?>" class="card-img-top" style="height:200px; object-fit:cover;">
                                <?php endif; ?>

                                <div class="card-body d-flex flex-column">
                                    <h6 class="card-title"><?= htmlspecialchars($row['name']) ?></h6>
                                    <p class="card-text small text-muted"><?= substr(strip_tags($row['description']), 0, 60) ?>...</p>

                                    <!-- Price -->
                                    <p class="mb-2">
                                        <span class="fw-bold text-success">$<?= number_format($row['cost_price'], 2) ?></span>
                                        <?php if ($discount > 0): ?>
                                            <del class="text-muted small ms-1">$<?= number_format($row['price'], 2) ?></del>
                                        <?php endif; ?>
                                    </p>

                                    <!-- Buttons -->
                                    <div class="mt-auto d-flex gap-2 mb-2">
                                        <a href="product_detail.php?id=<?= $row['id'] ?>" class="btn btn-outline-primary btn-sm w-100">
                                            View
                                        </a>
                                    </div>

                                    <form method="post" class="w-100">
                                        <input type="hidden" name="product_id" value="<?= $row['id'] ?>">
                                        <input type="hidden" name="qty" value="1">
                                        <button type="submit" name="add_to_cart" class="btn btn-success w-100">
                                            <i class="bi bi-cart-plus"></i> Add to Cart
                                        </button>
                                    </form>


                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p class="text-center">No products found.</p>
                <?php endif; ?>
            </div>

            <!-- Pagination -->
            <nav aria-label="Page navigation" class="mt-4">
                <ul class="pagination justify-content-center">
                    <?php
                    $query = $_GET;
                    unset($query['page']);
                    $baseUrl = 'shop.php?' . http_build_query($query);
                    ?>

                    <?php if ($page > 1): ?>
                        <li class="page-item"><a class="page-link" href="<?= $baseUrl ?>&page=<?= ($page - 1) ?>">Previous</a></li>
                    <?php endif; ?>

                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <li class="page-item <?= ($i == $page) ? 'active' : '' ?>">
                            <a class="page-link" href="<?= $baseUrl ?>&page=<?= $i ?>"><?= $i ?></a>
                        </li>
                    <?php endfor; ?>

                    <?php if ($page < $totalPages): ?>
                        <li class="page-item"><a class="page-link" href="<?= $baseUrl ?>&page=<?= ($page + 1) ?>">Next</a></li>
                    <?php endif; ?>

                </ul>
            </nav>
        </div>
    </div>
</section>

<?php include 'inc/footer.php'; ?>