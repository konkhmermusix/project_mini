<?php
session_start();
require '../inc/db.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header("Location: ../login.php");
    exit;
}

$from = $_GET['from'] ?? date('Y-m-01');
$to   = $_GET['to'] ?? date('Y-m-d');

// Validate date range 
if ($from > $to) {
    [$from, $to] = [$to, $from];
}

// Daily sales
$stmt = $conn->prepare("
    SELECT DATE(created_at) AS day,
           COUNT(*) AS orders,
           SUM(total_price) AS revenue
    FROM orders
    WHERE status = 'Paid'
      AND DATE(created_at) BETWEEN ? AND ?
    GROUP BY day
    ORDER BY day DESC
");
$stmt->bind_param("ss", $from, $to);
$stmt->execute();
$data = $stmt->get_result();

// Summary 
$summary = $conn->prepare("
    SELECT COUNT(*) AS total_orders,
           SUM(total_price) AS total_revenue
    FROM orders
    WHERE status='Paid'
      AND DATE(created_at) BETWEEN ? AND ?
");
$summary->bind_param("ss", $from, $to);
$summary->execute();
$sum = $summary->get_result()->fetch_assoc();

require 'inc/header.php';
?>

<div class="card shadow-sm mb-3">
    <div class="card-body d-flex justify-content-between align-items-center">
        <h3 class="mb-0">Sales Report</h3>
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
            <button class="btn btn-primary w-100">Filter</button>
        </div>
    </form>
</div>

<div class="row mb-3">
    <div class="col-md-6">
        <div class="card shadow-sm">
            <div class="card-body">
                <h6>Total Orders</h6>
                <h4><?= $sum['total_orders'] ?? 0 ?></h4>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card shadow-sm">
            <div class="card-body">
                <h6>Total Revenue</h6>
                <h4>$<?= number_format($sum['total_revenue'] ?? 0, 2) ?></h4>
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm">
    <div class="table-responsive">
        <table class="table table-striped table-bordered mb-0">
            <thead class="table-light">
                <tr>
                    <th>Date</th>
                    <th class="text-center">Orders</th>
                    <th class="text-end">Revenue</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($data->num_rows > 0): ?>
                    <?php while ($r = $data->fetch_assoc()): ?>
                        <tr>
                            <td><?= $r['day'] ?></td>
                            <td class="text-center"><?= $r['orders'] ?></td>
                            <td class="text-end">$<?= number_format($r['revenue'], 2) ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="3" class="text-center text-muted">
                            No sales data found
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require 'inc/footer.php'; ?>