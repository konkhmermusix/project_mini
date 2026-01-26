<?php
require_once 'inc/db.php';
require_once 'inc/header.php';

// Handle search
$search = $_GET['search'] ?? '';
$searchEscaped = $conn->real_escape_string($search);

// Pagination setup
$limit = 6;
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$offset = ($page - 1) * $limit;

// Featured latest post
$featuredResult = $conn->query("
    SELECT posts.*, users.username 
    FROM posts 
    JOIN users ON posts.user_id = users.id
    WHERE posts.status = 1
    " . (!empty($search) ? " AND (posts.title LIKE '%$searchEscaped%' OR posts.content LIKE '%$searchEscaped%')" : "") . "
    ORDER BY posts.created_at DESC LIMIT 1
");
$featured = $featuredResult->fetch_assoc();

// Fetch other posts (exclude featured)
$postsResult = $conn->query("
    SELECT posts.*, users.username 
    FROM posts 
    JOIN users ON posts.user_id = users.id
    WHERE posts.status = 1
    " . (!empty($search) ? " AND (posts.title LIKE '%$searchEscaped%' OR posts.content LIKE '%$searchEscaped%')" : "") . "
    " . ($featured ? "AND posts.id != {$featured['id']}" : "") . "
    ORDER BY posts.created_at DESC
    LIMIT $limit OFFSET $offset
");

// Count total posts for pagination
$countResult = $conn->query("
    SELECT COUNT(*) AS total
    FROM posts
    WHERE status = 1
    " . (!empty($search) ? " AND (title LIKE '%$searchEscaped%' OR content LIKE '%$searchEscaped%')" : "") . "
");
$totalPosts = $countResult->fetch_assoc()['total'];
$totalPages = ceil($totalPosts / $limit);
?>

<!-- Page Banner -->
<section class="py-5 text-center text-white" style="background: linear-gradient(135deg,rgb(0, 210, 154),rgb(180, 114, 156));">
    <div class="container">
        <h1 class="fw-bold mb-2">Blog</h1>
        <p class="lead">Latest news and updates from our store</p>
    </div>
</section>

<div class="container py-4">

    <!-- Search -->
    <form method="get" class="mb-4">
        <div class="input-group">
            <input type="text" name="search" class="form-control" placeholder="Search posts..." value="<?= htmlspecialchars($search) ?>">
            <button class="btn btn-primary" type="submit"><i class="bi bi-search"></i> Search</button>
        </div>
    </form>

    <!-- Featured Post -->
    <?php if ($featured): ?>
        <div class="card mb-4 shadow-lg">
            <?php if (!empty($featured['image'])): ?>
                <img src="<?= htmlspecialchars($featured['image']) ?>" class="card-img-top" loading="lazy">
            <?php endif; ?>
            <div class="card-body">
                <h2><?= htmlspecialchars($featured['title']) ?></h2>
                <p class="text-muted">by <?= htmlspecialchars($featured['username']) ?> | <?= date('F j, Y', strtotime($featured['created_at'])) ?></p>
                <?php
                $wordCount = str_word_count(strip_tags($featured['content']));
                $readTime = ceil($wordCount / 200);
                ?>
                <small class="text-muted"><?= $readTime ?> min read</small>
                <p><?= substr(strip_tags($featured['content']), 0, 300) ?>...</p>
                <a href="single.php?id=<?= $featured['id'] ?>" class="btn btn-primary">Read More</a>
            </div>
        </div>
    <?php endif; ?>

    <!-- View toggle -->
    <div class="d-flex align-items-center mb-3">
        <h3 class="mb-0">Blog Posts</h3>
        <div class="ms-auto">
            <button id="listView" class="btn btn-outline-primary btn-sm shadow-none"><i class="bi bi-list"></i></button>
            <button id="gridView" class="btn btn-outline-primary btn-sm shadow-none"><i class="bi bi-grid"></i></button>
        </div>
    </div>

    <!-- Posts Grid/List -->
    <div class="row g-3" id="postsContainer">
        <?php if ($postsResult->num_rows > 0): ?>
            <?php while ($post = $postsResult->fetch_assoc()): ?>
                <?php
                $wordCount = str_word_count(strip_tags($post['content']));
                $readTime = ceil($wordCount / 200);
                ?>
                <div class="col-md-4 mb-4 post-item">
                    <div class="card h-100 shadow-sm post-card">
                        <?php if (!empty($post['image'])): ?>
                            <img src="<?= htmlspecialchars($post['image']) ?>" class="card-img-top" loading="lazy" alt="<?= htmlspecialchars($post['title']) ?>">
                        <?php endif; ?>
                        <div class="card-body d-flex flex-column">
                            <h6 class="text-muted mb-1">by <?= htmlspecialchars($post['username']) ?> | <?= date('F j, Y', strtotime($post['created_at'])) ?></h6>
                            <h5 class="card-title"><?= htmlspecialchars($post['title']) ?></h5>
                            <small class="text-muted"><?= $readTime ?> min read</small>
                            <p class="card-text"><?= substr(strip_tags($post['content']), 0, 200) ?>...</p>
                            <a href="single.php?id=<?= $post['id'] ?>" class="btn btn-primary mt-auto">Read More</a>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p class="text-center">No blog posts found.</p>
        <?php endif; ?>
    </div>

    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
        <nav>
            <ul class="pagination justify-content-center mt-4">
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                        <a class="page-link" href="?search=<?= urlencode($search) ?>&page=<?= $i ?>"><?= $i ?></a>
                    </li>
                <?php endfor; ?>
            </ul>
        </nav>
    <?php endif; ?>

</div>

<style>
    .post-card:hover {
        transform: translateY(-3px);
        transition: 0.3s;
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.15);
    }
</style>

<script>
    // Grid / List Toggle
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

<?php require_once 'inc/footer.php'; ?>