<?php
require 'inc/db.php';
include 'inc/header.php';
?>

<div class="py-5 text-center text-white" style="background: linear-gradient(135deg, #4f46e5, #3b82f6);">
    <div class="container">
        <h1 class="fw-bold mb-2">Products</h1>
        <p class="lead">Explore our latest products</p>
    </div>
</div>


<section class="mt-1 mb-5">
    <div class="cart shadow rounded-0 p-2 mt-1 mb-2 d-flex">

        <h2 class="mt-1 mb-1">Product All</h2>
        <div class="ms-auto mt-1 mb-1">
            <button type="button" class="btn btn-btn-outline-primary shadow-none"><i class="bi bi-list"></i></button>
            <button type="button" class="btn btn-btn-outline-primary shadow-none"><i class="bi bi-grid"></i></button>
        </div>
    </div>
    <div class="row">

        <div class="col-md-2">
            <h5 class="card p-3 shadow rounded-0 bg-primary text-white">Categories</h5>
            <div class="card shadow rounded-0 mb-3">
                <div class="p-1">
                    <?php
                    $result = $conn->query("SELECT * FROM categories WHERE status = 1");
                    while ($row = $result->fetch_assoc()):
                    ?>
                        <a href="categories_short.php?id=<?= $row['id'] ?>" class="btn rounded-0 w-100 text-start btn-hover">
                            <p class="card-title"><?= htmlspecialchars($row['name']) ?></p>
                            <!-- <span class="badge text-dark ms-auto">11</span> -->
                        </a>
                        <?php ?>
                    <?php endwhile; ?>
                </div>
            </div>

            <h5 class="card p-3 shadow rounded-0 bg-primary text-white">Brand</h5>
            <div class="card shadow rounded-0 mb-3">
                <div class="p-1">
                    <?php
                    $result = $conn->query("SELECT * FROM brands WHERE status = 1");
                    while ($row = $result->fetch_assoc()):
                    ?>
                        <a href="categories_short.php?id=<?= $row['id'] ?>" class="btn rounded-0 w-100 text-start btn-hover">
                            <p class="card-title"><?= htmlspecialchars($row['name']) ?></p>
                            <!-- <span class="badge text-dark ms-auto">11</span> -->
                        </a>
                        <?php ?>
                    <?php endwhile; ?>
                </div>
            </div>
        </div>
        <div class="col-md-10 mb-4 px-3">
            <div class="row g-3">

                <?php
                $result = $conn->query("SELECT * FROM products WHERE status = 1");
                while ($row = $result->fetch_assoc()):
                ?>

                    <div class="col-md-3 col-sm-6">
                        <div class="card shadow-sm h-100">

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

                                <p class="fw-bold mb-2 text-primary">
                                    $<?= number_format($row['price'], 2) ?>
                                </p>

                                <div class="mt-auto d-flex gap-2">
                                    <a href="product_detail.php?id=<?= $row['id'] ?>"
                                        class="btn btn-outline-primary btn-sm w-50">
                                        View
                                    </a>

                                    <form action="cart_add.php" method="post" class="w-50">
                                        <input type="hidden" name="product_id" value="<?= $row['id'] ?>">
                                        <input type="hidden" name="qty" value="1">
                                        <button class="btn btn-success btn-sm w-100">
                                            Add
                                        </button>
                                    </form>
                                </div>
                            </div>

                        </div>
                    </div>

                <?php endwhile; ?>

            </div>
        </div>
    </div>
</section>

<?php require('inc/footer.php');
