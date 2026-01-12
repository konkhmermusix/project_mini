<?php
require '../inc/db.php';
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') die("Access denied.");

include 'inc/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h2>Blog Posts</h2>
    <a class="btn btn-success" href="add_blog.php">Add New Post</a>
</div>

<div class="table-responsive">
    <table class="table table-bordered table-hover align-middle">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Title</th>
                <th>Author</th>
                <th>Created At</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $result = $conn->query("SELECT posts.*, users.username FROM posts JOIN users ON posts.user_id = users.id ORDER BY posts.created_at DESC");
            while ($row = $result->fetch_assoc()):
            ?>
                <tr>
                    <td><?= $row['id'] ?></td>
                    <td><?= $row['title'] ?></td>
                    <td><?= $row['username'] ?></td>
                    <td><?= $row['created_at'] ?></td>
                    <td>
                        <a class="btn btn-primary btn-sm" href="edit_post.php?id=<?= $row['id'] ?>">Edit</a>
                        <a class="btn btn-danger btn-sm" href="delete_post.php?id=<?= $row['id'] ?>" onclick="return confirm('Are you sure?')">Delete</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<?php include 'inc/footer.php'; ?>