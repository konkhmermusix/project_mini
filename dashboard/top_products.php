<?php
session_start();
require '../inc/db.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}


$res = $conn->query("
    SELECT p.name,
           SUM(oi.qty) sold,
           SUM(oi.qty * oi.price) revenue
    FROM order_items oi
    JOIN products p ON oi.product_id = p.id
    GROUP BY oi.product_id
    ORDER BY sold DESC
    LIMIT 10
");
?>

<?php include 'inc/header.php'; ?>

<div class="container mt-4">
    <h2>Top Selling Products</h2>

    <table class="table table-striped">
        <thead>
            <tr>
                <th>Product</th>
                <th>Sold</th>
                <th>Revenue</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($p = $res->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($p['name']) ?></td>
                    <td><?= $p['sold'] ?></td>
                    <td>$<?= number_format($p['revenue'], 2) ?></td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<?php include 'inc/footer.php'; ?>