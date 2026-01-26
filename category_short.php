<?php
// ត្រូវការ $conn និង $category_id មកពី file មេ (shop.php)
$catResult = $conn->query("SELECT * FROM categories WHERE status = 1 ORDER BY name");
?>

<section class="p-4">
    <div class="bg-success shadow-sm p-2 mb-2 rounded-1">
        <h2 class="mb-0 text-white">Category</h2>
    </div>

    <div class="row">
        <?php while ($cat = $catResult->fetch_assoc()): ?>
            <div class="col-6 col-md-2 mb-2">
                <a href="shop.php?category_id=<?= $cat['id'] ?>"
                    class="btn rounded-0 w-100 text-start btn-hover <?= ($cat['id'] == ($category_id ?? 0)) ? 'active' : '' ?>">
                    <div class="card p-3 text-center shadow-sm h-100">
                        <h6 class="mb-0"><?= htmlspecialchars($cat['name']) ?></h6>
                    </div>
                </a>
            </div>
        <?php endwhile; ?>
    </div>
</section>