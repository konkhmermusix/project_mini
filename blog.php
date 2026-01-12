<?php
require 'inc/db.php';
include 'inc/header.php';
?>

<!-- Page Banner -->
<div class="py-5 text-center text-white" style="background: linear-gradient(135deg, #4f46e5, #3b82f6);">
    <div class="container">
        <h1 class="fw-bold mb-2">Blog</h1>
        <p class="lead">Latest news and updates from our store</p>
    </div>
</div>

<!-- Blog Posts -->
<div class="container my-5">
    <div class="row">
        <?php
        $result = $conn->query("SELECT * FROM posts ORDER BY created_at DESC");
        if ($result->num_rows > 0):
            while ($post = $result->fetch_assoc()):
        ?>
                <div class="col-md-4 mb-4">
                    <div class="card h-100 shadow-sm">
                        <?php if (!empty($post['image'])): ?>
                            <img src="<?= $post['image'] ?>" class="card-img-top" alt="<?= htmlspecialchars($post['title']) ?>">
                        <?php endif; ?>
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title"><?= htmlspecialchars($post['title']) ?></h5>
                            <p class="card-text"><?= substr(strip_tags($post['content']), 0, 100) ?>...</p>
                            <a href="single.php?id=<?= $post['id'] ?>" class="btn btn-primary mt-auto">Read More</a>
                        </div>
                        <div class="card-footer text-muted">
                            <?= date('F j, Y', strtotime($post['created_at'])) ?>
                        </div>
                    </div>
                </div>
            <?php
            endwhile;
        else:
            ?>
            <p class="text-center">No blog posts found.</p>
        <?php endif; ?>
    </div>
</div>

<?php include 'inc/footer.php'; ?>