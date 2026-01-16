<?php
session_start();
require '../inc/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}


$from = $_GET['from'] ?? date('Y-m-01');
$to   = $_GET['to'] ?? date('Y-m-d');

$stmt = $conn->prepare("
    SELECT DATE(created_at) day,
           COUNT(*) orders,
           SUM(total_price) revenue
    FROM orders
    WHERE status='Paid'
    AND DATE(created_at) BETWEEN ? AND ?
    GROUP BY day
    ORDER BY day DESC
");
$stmt->bind_param("ss", $from, $to);
$stmt->execute();
$data = $stmt->get_result();
?>

<?php include 'inc/header.php'; ?>

<div class="container mt-4">
    <h2>Sales Report</h2>

    <form class="row g-2 mb-3">
        <div class="col-md-3">
            <input type="date" name="from" value="<?= $from ?>" class="form-control">
        </div>
        <div class="col-md-3">
            <input type="date" name="to" value="<?= $to ?>" class="form-control">
        </div>
        <div class="col-md-2">
            <button class="btn btn-primary">Filter</button>
        </div>
    </form>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Date</th>
                <th>Orders</th>
                <th>Revenue</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($r = $data->fetch_assoc()): ?>
                <tr>
                    <td><?= $r['day'] ?></td>
                    <td><?= $r['orders'] ?></td>
                    <td>$<?= number_format($r['revenue'], 2) ?></td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<?php include 'inc/footer.php'; ?>