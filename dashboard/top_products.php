<?php
session_start();
require '../inc/db.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header("Location: ../login.php");
    exit;
}

$from = $_GET['from'] ?? date('Y-m-01');
$to   = $_GET['to'] ?? date('Y-m-d');

if ($from > $to) {
    [$from, $to] = [$to, $from];
}

/* Top selling products */
$stmt = $conn->prepare("
    SELECT p.name AS product_name,
           SUM(oi.qty) AS sold,
           SUM(oi.qty * oi.price) AS revenue
    FROM order_items oi
    JOIN orders o ON oi.order_id = o.id
    JOIN products p ON oi.product_id = p.id
    WHERE o.status = 'Paid'
      AND DATE(o.created_at) BETWEEN ? AND ?
    GROUP BY oi.product_id
    ORDER BY sold DESC
    LIMIT 10
");
$stmt->bind_param("ss", $from, $to);
$stmt->execute();
$res = $stmt->get_result();

/* Summary */
$summary = $conn->prepare("
    SELECT SUM(oi.qty) AS total_sold,
           SUM(oi.qty * oi.price) AS total_revenue
    FROM order_items oi
    JOIN orders o ON oi.order_id = o.id
    WHERE o.status='Paid'
      AND DATE(o.created_at) BETWEEN ? AND ?
");
$summary->bind_param("ss", $from, $to);
$summary->execute();
$sum = $summary->get_result()->fetch_assoc();

include 'inc/header.php';
?>

<div class="card shadow-sm mb-3">
    <div class="card-body d-flex justify-content-between align-items-center">
        <h3 class="mb-0"> Top Selling Products </h3>
        <span class="text-muted"><?= $from ?> → <?= $to ?></span>
    </div>
</div>

<div class="card shadow-sm mb-3">
    <form class="row g-2 p-3">
        <div class="col-md-3">
            <input type="date" name="from" value="<?= $from ?>" class="form-control">
        </div>
        <div class="col-md-3">
            <input type="date" name="to" value="<?= $to ?>" class="form-control">
        </div>
        <div class="col-md-2">
            <button class="btn btn-primary w-100">
                <i class="bi bi-filter"></i> Filter
            </button>
        </div>
    </form>
</div>

<div class="row mb-3">
    <div class="col-md-6">
        <div class="card shadow-sm">
            <div class="card-body">
                <h6>
                    <i class="bi bi-box-seam"></i> Total Sold
                </h6>
                <h4><?= $sum['total_sold'] ?? 0 ?></h4>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card shadow-sm">
            <div class="card-body">
                <h6>
                    <i class="bi bi-cash-coin"></i> Total Revenue
                </h6>
                <h4>$<?= number_format($sum['total_revenue'] ?? 0, 2) ?></h4>
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm mb-4">
    <div class="table-responsive">
        <table class="table table-bordered table-hover table-active align-middle">
            <thead class="table-light">
                <tr>
                    <th class="text-center">#</th>
                    <th>Product</th>
                    <th class="text-center">Sold</th>
                    <th class="text-end">Revenue</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($res->num_rows > 0): ?>
                    <?php $i = 1;
                    while ($p = $res->fetch_assoc()): ?>
                        <tr>
                            <td class="text-center"><?= $i++ ?></td>
                            <td><?= htmlspecialchars($p['product_name']) ?></td>
                            <td class="text-center"><?= $p['sold'] ?></td>
                            <td class="text-end">$<?= number_format($p['revenue'], 2) ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4" class="text-center text-muted">
                            No data found
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include 'inc/footer.php'; ?>