<?php
require 'inc/db.php';
require 'inc/header.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "<div class='text-center mt-5'><h3>Invalid user ID</h3></div>";
    exit;
}

$user_id = intval($_GET['id']);

// Get user info from users table
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    echo "<div class='text-center mt-5'><h3>User not found</h3></div>";
    exit;
}
?>

<section class="container py-5">
    <div class="card shadow-sm p-4 text-center mx-auto" style="max-width:500px; border-radius:15px;">
        <!-- Avatar: first letter -->
        <div class="rounded-circle bg-primary text-white d-flex justify-content-center align-items-center mb-3 mx-auto"
            style="width:120px; height:120px; font-weight:bold; font-size:50px; box-shadow: 0 2px 10px rgba(0,0,0,0.2);">
            <?= strtoupper(substr($user['username'], 0, 1)) ?>
        </div>

        <h3 class="fw-bold"><?= htmlspecialchars($user['username']) ?></h3>
        <p class="text-muted mb-3"><?= htmlspecialchars($user['email']) ?></p>

        <hr>

        <h5 class="mb-3">Reviews by <?= htmlspecialchars($user['username']) ?></h5>

        <ul class="list-group list-group-flush">
            <?php
            $reviews = $conn->prepare("SELECT * FROM reviews WHERE user_id = ? ORDER BY created_at DESC");
            $reviews->bind_param("i", $user_id);
            $reviews->execute();
            $revResult = $reviews->get_result();
            if ($revResult->num_rows == 0):
            ?>
                <li class="list-group-item text-center text-muted">No reviews yet.</li>
                <?php
            else:
                while ($rev = $revResult->fetch_assoc()):
                ?>
                    <li class="list-group-item mb-2 rounded shadow-sm p-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span>
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <?= ($i <= intval($rev['rating'])) ? '<i class="bi bi-star-fill"></i>' : '<i class="bi bi-star"></i>'; ?>
                                <?php endfor; ?>
                            </span>
                            <small class="text-muted"><?= date('M d, Y', strtotime($rev['created_at'])) ?></small>
                        </div>
                        <p class="mb-0"><?= htmlspecialchars($rev['comment']) ?></p>
                    </li>
            <?php endwhile;
            endif; ?>
        </ul>
    </div>
</section>

<?php require 'inc/footer.php'; ?>