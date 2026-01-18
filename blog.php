<?php
require 'inc/db.php';
include 'inc/header.php';
?>

<!-- Page Banner -->
<section class="py-5 text-center text-white" style="background: linear-gradient(135deg, #4f46e5, #3b82f6);">
    <div class="container">
        <h1 class="fw-bold mb-2">Blog</h1>
        <p class="lead">Latest news and updates from our store</p>
    </div>
</section>

<section class="p-4">
    <div class="cart shadow rounded-0 p-2 mt-1 mb-4 d-flex align-items-center">
        <h2 class="mt-1 mb-1">Blog Posts</h2>
        <div class="ms-auto mt-1 mb-1">
            <button id="listView" type="button" class="btn btn-btn-outline-primary shadow-none"><i class="bi bi-list"></i></button>
            <button id="gridView" type="button" class="btn btn-btn-outline-primary shadow-none"><i class="bi bi-grid"></i></button>
        </div>
    </div>

    <div class="row g-3" id="postsContainer">
        <?php
        $result = $conn->query("
            SELECT posts.*, users.username 
            FROM posts
            JOIN users ON posts.user_id = users.id
            WHERE posts.status = 1
            ORDER BY posts.created_at DESC
        ");
        if ($result->num_rows > 0):
            while ($post = $result->fetch_assoc()):
        ?>
                <div class="col-md-4 mb-4 post-item">
                    <div class="card h-100 shadow-sm">
                        <?php if (!empty($post['image'])): ?>
                            <img src="<?= htmlspecialchars($post['image']) ?>" class="card-img-top" alt="<?= htmlspecialchars($post['title']) ?>">
                        <?php endif; ?>
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title"><?= htmlspecialchars($post['title']) ?></h5>
                            <small class="text-muted mb-2">by <?= htmlspecialchars($post['username']) ?> | <?= date('F j, Y', strtotime($post['created_at'])) ?></small>
                            <p class="card-text"><?= substr(strip_tags($post['content']), 0, 100) ?>...</p>
                            <a href="single.php?id=<?= $post['id'] ?>" class="btn btn-primary mt-auto">Read More</a>
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
</section>

<script>
    // Toggle Grid / List View
    const gridBtn = document.getElementById('gridView');
    const listBtn = document.getElementById('listView');
    const container = document.getElementById('postsContainer');
    const items = container.querySelectorAll('.post-item');

    gridBtn.addEventListener('click', () => {
        items.forEach(i => i.classList.remove('col-12'));
        items.forEach(i => i.classList.add('col-md-4'));
    });
    listBtn.addEventListener('click', () => {
        items.forEach(i => i.classList.remove('col-md-4'));
        items.forEach(i => i.classList.add('col-12'));
    });
</script>

<?php include 'inc/footer.php'; ?>