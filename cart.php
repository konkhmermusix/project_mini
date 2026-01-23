<?php
require 'inc/db.php';
session_start();

// Restore cart from cookie if session is empty
if (empty($_SESSION['cart']) && isset($_COOKIE['cart'])) {
    $cookie_cart = json_decode($_COOKIE['cart'], true);
    if (is_array($cookie_cart)) {
        $_SESSION['cart'] = $cookie_cart;
    }
}

$cart = $_SESSION['cart'] ?? [];
$total = 0;

// Handle POST for update qty or remove item
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Update Quantity
    if (isset($_POST['update_qty'])) {
        $pid = intval($_POST['product_id']);
        $qty = intval($_POST['qty']);
        if ($qty < 1) $qty = 1;

        if (isset($_SESSION['cart'][$pid])) {
            $_SESSION['cart'][$pid]['qty'] = $qty;
        }

        // Update cookie
        setcookie('cart', json_encode($_SESSION['cart']), time() + (7 * 24 * 60 * 60), "/");
        header("Location: cart.php");
        exit;
    }

    // Remove Item
    if (isset($_POST['remove_item'])) {
        $pid = intval($_POST['product_id']);
        if (isset($_SESSION['cart'][$pid])) {
            unset($_SESSION['cart'][$pid]);
        }

        // Update cookie
        setcookie('cart', json_encode($_SESSION['cart']), time() + (7 * 24 * 60 * 60), "/");
        header("Location: cart.php");
        exit;
    }
}

require 'inc/header.php';
?>

<div class="container mt-4">
    <div class="bg-info shadow-sm p-2 mb-2 rounded-1">
        <h2 class="mb-0 text-white">Your Cart</h2>
    </div>

    <?php if (empty($cart)): ?>
        <div class="text-center mt-5 mb-5">
            <h4>Your cart is empty.</h4>
            <a href="shop.php" class="btn btn-primary mt-3">Browse Products</a>
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-bordered table-hover table-active align-middle">
                <thead>
                    <tr>
                        <th>Image</th>
                        <th>Name</th>
                        <th width="120px">Quantity</th>
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
                                    <img src="<?= htmlspecialchars($item['image']) ?>" class="rounded" style="width:70px; height:70px; object-fit:cover;">
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($item['name']) ?></td>
                            <td>
                                <form method="post" class="d-flex">
                                    <input type="hidden" name="product_id" value="<?= $item['id'] ?>">
                                    <input type="number" name="qty" value="<?= $item['qty'] ?>" class="form-control w-50 me-2" onchange="this.form.submit()">
                                    <input type="hidden" name="update_qty">
                                </form>
                            </td>
                            <td>$<?= number_format($item['price'], 2) ?></td>
                            <td>$<?= number_format($subtotal, 2) ?></td>
                            <td>
                                <form method="post" style="display:inline;">
                                    <input type="hidden" name="product_id" value="<?= $item['id'] ?>">
                                    <button type="submit" name="remove_item" class="btn btn-sm btn-danger">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <tr>
                        <td colspan="4" class="text-end"><strong>Total:</strong></td>
                        <td colspan="2"><strong>$<?= number_format($total, 2) ?></strong></td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="d-flex mt-3">
            <a href="shop.php" class="btn btn-success">Continue Shopping</a>
            <div class="ms-auto">
                <a href="checkout.php" class="btn btn-primary">Checkout</a>
            </div>
        </div>
    <?php endif; ?>
</div>


<?php require 'inc/footer.php'; ?>