<?php
require 'inc/db.php';
include 'inc/header.php';

// ============================
// Filter by category or brand
// ============================
$category_id = isset($_GET['category_id']) ? intval($_GET['category_id']) : 0;
$brand_id    = isset($_GET['brand_id']) ? intval($_GET['brand_id']) : 0;

// ============================
// Pagination Settings
// ============================
$limit = 16;
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $limit;

// ============================
// Build WHERE condition
// ============================
$where = "WHERE status = 1";
$params = [];
$types = "";

if ($category_id > 0) {
    $where .= " AND category_id = ?";
    $params[] = $category_id;
    $types .= "i";
}
if ($brand_id > 0) {
    $where .= " AND brand_id = ?";
    $params[] = $brand_id;
    $types .= "i";
}

// ============================
// Get total items for pagination
// ============================
$totalSql = "SELECT COUNT(*) AS total FROM products $where";
$stmtTotal = $conn->prepare($totalSql);
if ($types) $stmtTotal->bind_param($types, ...$params);
$stmtTotal->execute();
$totalRow = $stmtTotal->get_result()->fetch_assoc();
$totalItems = $totalRow['total'];
$totalPages = ceil($totalItems / $limit);
$stmtTotal->close();

// ============================
// Fetch products for current page
// ============================
$productSql = "SELECT * FROM products $where LIMIT ?, ?";
$stmt = $conn->prepare($productSql);

if ($types) {
    // បង្កើត array ពី params + offset + limit
    $paramsFull = $params; // copy original
    $paramsFull[] = $offset;
    $paramsFull[] = $limit;

    // create types string
    $typesFull = $types . "ii";

    // Bind parameters dynamically
    $stmt->bind_param($typesFull, ...$paramsFull); // ត្រឹមត្រូវ
} else {
    $stmt->bind_param("ii", $offset, $limit);
}

$stmt->execute();
$products = $stmt->get_result();

?>

<section class="py-5 text-center text-white" style="background: linear-gradient(135deg, #4f46e5, #3b82f6);">
    <div class="container">
        <h1 class="fw-bold mb-2">Shop</h1>
        <p class="lead">Browse our products</p>
    </div>
</section>

<section class="p-4">
    <div class="cart shadow rounded-0 p-2 mt-1 mb-4 d-flex">
        <h2 class="mt-1 mb-1">Product All</h2>
    </div>

    <div class="row">
        <!-- Sidebar: Categories & Brands -->
        <div class="col-md-2">
            <h5 class="card p-3 shadow rounded-0 bg-primary text-white">Categories</h5>
            <div class="card shadow rounded-0 mb-3">
                <div class="p-1">
                    <?php
                    $catResult = $conn->query("SELECT * FROM categories WHERE status = 1 ORDER BY name");
                    while ($cat = $catResult->fetch_assoc()):
                    ?>
                        <a href="shop.php?category_id=<?= $cat['id'] ?>"
                            class="btn rounded-0 w-100 text-start btn-hover <?= $cat['id'] == $category_id ? 'active' : '' ?>">
                            <p class="mb-0"><?= htmlspecialchars($cat['name']) ?></p>
                        </a>
                    <?php endwhile; ?>
                </div>
            </div>

            <h5 class="card p-3 shadow rounded-0 bg-primary text-white">Brands</h5>
            <div class="card shadow rounded-0 mb-3">
                <div class="p-1">
                    <?php
                    $brandResult = $conn->query("SELECT * FROM brands WHERE status = 1 ORDER BY name");
                    while ($b = $brandResult->fetch_assoc()):
                    ?>
                        <a href="shop.php?brand_id=<?= $b['id'] ?>"
                            class="btn rounded-0 w-100 text-start btn-hover <?= $b['id'] == $brand_id ? 'active' : '' ?>">
                            <p class="mb-0"><?= htmlspecialchars($b['name']) ?></p>
                        </a>
                    <?php endwhile; ?>
                </div>
            </div>
        </div>

        <!-- Products -->
        <div class="col-md-10 mb-4 px-3">
            <div class="row g-3">
                <?php while ($row = $products->fetch_assoc()): ?>
                    <div class="col-md-3 col-sm-6">
                        <div class="card shadow-sm h-100">
                            <?php if (!empty($row['image'])): ?>
                                <img src="<?= htmlspecialchars($row['image']) ?>" class="card-img-top" style="height:200px; object-fit:cover;">
                            <?php endif; ?>
                            <div class="card-body d-flex flex-column">
                                <h6 class="card-title"><?= htmlspecialchars($row['name']) ?></h6>
                                <p class="card-text small text-muted"><?= substr(strip_tags($row['description']), 0, 60) ?>...</p>
                                <p class="fw-bold mb-2 text-primary">$<?= number_format($row['price'], 2) ?></p>
                                <div class="mt-auto d-flex gap-2">
                                    <a href="product_detail.php?id=<?= $row['id'] ?>" class="btn btn-outline-primary btn-sm w-50">View</a>
                                    <form action="cart_add.php" method="post" class="w-50">
                                        <input type="hidden" name="product_id" value="<?= $row['id'] ?>">
                                        <input type="hidden" name="qty" value="1">
                                        <button class="btn btn-success btn-sm w-100">Add</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>

            <!-- Pagination -->
            <nav aria-label="Page navigation" class="mt-4">
                <ul class="pagination justify-content-center">
                    <?php
                    // Build base URL with filters
                    $baseUrl = "shop.php?";
                    if ($category_id) $baseUrl .= "category_id=$category_id&";
                    if ($brand_id) $baseUrl .= "brand_id=$brand_id&";
                    ?>

                    <?php if ($page > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="<?= $baseUrl ?>page=<?= $page - 1 ?>">Previous</a>
                        </li>
                    <?php endif; ?>

                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                            <a class="page-link" href="<?= $baseUrl ?>page=<?= $i ?>"><?= $i ?></a>
                        </li>
                    <?php endfor; ?>

                    <?php if ($page < $totalPages): ?>
                        <li class="page-item">
                            <a class="page-link" href="<?= $baseUrl ?>page=<?= $page + 1 ?>">Next</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </nav>

        </div>
    </div>
</section>

<?php include 'inc/footer.php'; ?>