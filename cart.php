<?php
require 'inc/db.php';
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$cart = $_SESSION['cart'] ?? [];
$total = 0;

// Handle POST (Update qty / remove)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Update quantity
    if (isset($_POST['update_qty'])) {
        $pid = intval($_POST['product_id']);
        $qty = intval($_POST['qty']);
        if ($qty < 1) $qty = 1;

        if (isset($_SESSION['cart'][$pid])) {
            $_SESSION['cart'][$pid]['qty'] = $qty;
        }

        setcookie('cart', json_encode($_SESSION['cart']), time() + (7 * 24 * 60 * 60), "/");
        header("Location: cart.php");
        exit;
    }

    // Remove item
    if (isset($_POST['remove_item'])) {
        $pid = intval($_POST['product_id']);
        if (isset($_SESSION['cart'][$pid])) {
            unset($_SESSION['cart'][$pid]);
        }

        setcookie('cart', json_encode($_SESSION['cart']), time() + (7 * 24 * 60 * 60), "/");
        header("Location: cart.php");
        exit;
    }
}
?>

<?php include 'inc/header.php'; ?>

<div class="container mt-4">
    <h2>Your Cart</h2>

    <?php if (empty($cart)): ?>
        <p>Your cart is empty.</p>
    <?php else: ?>
        <table class="table align-middle">
            <thead>
                <tr>
                    <th>Image</th>
                    <th>Product</th>
                    <th>Qty</th>
                    <th>Price</th>
                    <th>Subtotal</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>

                <?php foreach ($cart as $item):

                    if (!is_array($item)) continue;

                    $subtotal = $item['qty'] * $item['price'];
                    $total += $subtotal;
                ?>
                    <tr>
                        <td>
                            <?php if (!empty($item['image'])): ?>
                                <img src="<?= htmlspecialchars($item['image']) ?>" style="width:70px; height:70px; object-fit:cover;" class="rounded">
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($item['name']) ?></td>
                        <td>
                            <form method="post">
                                <input type="hidden" name="product_id" value="<?= $item['id'] ?>">
                                <input type="number" name="qty" value="<?= $item['qty'] ?>" min="1"
                                    onchange="this.form.submit()" class="form-control w-50 d-inline">
                                <input type="hidden" name="update_qty" value="1">
                            </form>
                        </td>
                        <td>$<?= number_format($item['price'], 2) ?></td>
                        <td>$<?= number_format($subtotal, 2) ?></td>
                        <td>
                            <form method="post" style="display:inline;">
                                <input type="hidden" name="product_id" value="<?= $item['id'] ?>">
                                <button type="submit" name="remove_item" class="btn btn-sm btn-danger">Remove</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <tr>
                    <td colspan="4"><strong>Total</strong></td>
                    <td colspan="2"><strong>$<?= number_format($total, 2) ?></strong></td>
                </tr>
            </tbody>
        </table>

        <a href="checkout.php" class="btn btn-success">Proceed to Checkout</a>
    <?php endif; ?>
</div>

<?php include 'inc/footer.php'; ?>