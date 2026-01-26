<?php
// ត្រូវការ $conn និង $brand_id មកពី file មេ (shop.php)
$brandResult = $conn->query("SELECT * FROM brands WHERE status = 1 ORDER BY name");
?>

<section class="p-4">
    <div class="bg-danger shadow-sm p-2 mb-2 rounded-1">
        <h2 class="mb-0 text-white">Brand</h2>
    </div>

    <div class="row">
        <?php while ($b = $brandResult->fetch_assoc()): ?>
            <div class="col-6 col-md-2 mb-2">
                <a href="shop.php?brand_id=<?= $b['id'] ?>"
                    class="btn rounded-0 w-100 text-start btn-hover <?= ($b['id'] == ($brand_id ?? 0)) ? 'active' : '' ?>">
                    <div class="card p-3 text-center shadow-sm h-100">
                        <h6 class="mb-0"><?= htmlspecialchars($b['name']) ?></h6>
                    </div>
                </a>
            </div>
        <?php endwhile; ?>
    </div>
</section>