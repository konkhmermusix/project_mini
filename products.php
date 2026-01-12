<?php
require 'inc/db.php';
include 'inc/header.php';
?>

<!-- Page Banner -->
<div class="py-5 text-center text-white" style="background: linear-gradient(135deg, #4f46e5, #3b82f6);">
    <div class="container">
        <h1 class="fw-bold mb-2">Products</h1>
        <p class="lead">Explore our latest products</p>
    </div>
</div>

<!-- Product List -->
<div class="container my-5">
    <div class="row">
        <?php
        $result = $conn->query("SELECT * FROM products WHERE status = 1 ORDER BY id DESC");
        if ($result->num_rows > 0):
            while ($product = $result->fetch_assoc()):
        ?>
                <div class="col-md-4 mb-4">
                    <div class="card h-100 shadow-sm">
                        <?php if (!empty($product['image'])): ?>
                            <img src="<?= $product['image'] ?>" class="card-img-top" alt="<?= htmlspecialchars($product['name']) ?>">
                        <?php else: ?>
                            <img src="assets/default-product.png" class="card-img-top" alt="No image available">
                        <?php endif; ?>
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title"><?= htmlspecialchars($product['name']) ?></h5>
                            <p class="card-text"><?= substr(strip_tags($product['description']), 0, 80) ?>...</p>
                            <div class="mt-auto">
                                <span class="fw-bold text-primary">$<?= number_format($product['price'], 2) ?></span>
                                <a href="product_detail.php?id=<?= $product['id'] ?>" class="btn btn-sm btn-outline-primary float-end">View</a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php
            endwhile;
        else:
            ?>
            <p class="text-center">No products found.</p>
        <?php endif; ?>
    </div>
</div>

<?php include 'inc/footer.php'; ?>