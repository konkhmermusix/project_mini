<?php
require 'inc/db.php';
session_start();

/* =========================
   Restore cart from cookie
========================= */
if (empty($_SESSION['cart']) && isset($_COOKIE['cart'])) {
    $cookie_cart = json_decode($_COOKIE['cart'], true);
    if (is_array($cookie_cart)) {
        $_SESSION['cart'] = $cookie_cart;
    }
}

$cart = $_SESSION['cart'] ?? [];
$total = 0;

/* =========================
   Handle POST
========================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Update Quantity
    if (isset($_POST['update_qty'])) {
        $pid = intval($_POST['product_id']);
        $qty = max(1, intval($_POST['qty']));

        if (isset($_SESSION['cart'][$pid])) {
            $_SESSION['cart'][$pid]['qty'] = $qty;
        }

        setcookie('cart', json_encode($_SESSION['cart']), time() + 604800, "/");
        header("Location: cart.php");
        exit;
    }

    // Remove Item
    if (isset($_POST['remove_item'])) {
        $pid = intval($_POST['product_id']);

        if (isset($_SESSION['cart'][$pid])) {
            unset($_SESSION['cart'][$pid]);
        }

        setcookie('cart', json_encode($_SESSION['cart']), time() + 604800, "/");
        header("Location: cart.php");
        exit;
    }
}

require 'inc/header.php';
?>

<div class="container mt-4">

    <div class="bg-info shadow-sm p-2 mb-3 rounded-1">
        <h2 class="mb-0 text-white">Your Cart</h2>
    </div>

    <?php if (empty($cart)): ?>
        <div class="text-center mt-5 mb-5">
            <h4>Your cart is currently empty.</h4>
            <a href="shop.php" class="btn btn-primary mt-3">Browse Products</a>
        </div>
    <?php else: ?>

        <div class="table-responsive">
            <table class="table table-bordered align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Image</th>
                        <th>Product</th>
                        <th width="120">Qty</th>
                        <th>Price</th>
                        <th>Subtotal</th>
                        <th width="80">Action</th>
                    </tr>
                </thead>
                <tbody>

                    <?php foreach ($cart as $item):
                        if (!is_array($item)) continue;

                        // ✅ SAFE PRICE HANDLING
                        $price = $item['selling_price']
                            ?? $item['price']
                            ?? 0;

                        $qty = $item['qty'];
                        $subtotal = $price * $qty;
                        $total += $subtotal;
                    ?>
                        <tr>
                            <td>
                                <?php if (!empty($item['image'])): ?>
                                    <img src="<?= htmlspecialchars($item['image']) ?>"
                                        style="width:70px;height:70px;object-fit:cover"
                                        class="rounded">
                                <?php endif; ?>
                            </td>

                            <td>
                                <strong><?= htmlspecialchars($item['name']) ?></strong>

                                <?php if (!empty($item['discount_percent'])): ?>
                                    <br>
                                    <small class="text-muted">
                                        <del>$<?= number_format($item['original_price'], 2) ?></del>
                                        <span class="text-danger ms-1">
                                            -<?= $item['discount_percent'] ?>%
                                        </span>
                                    </small>
                                <?php endif; ?>
                            </td>

                            <td>
                                <form method="post" class="d-flex">
                                    <input type="hidden" name="product_id" value="<?= $item['id'] ?>">
                                    <input type="number"
                                        name="qty"
                                        value="<?= $qty ?>"
                                        min="1"
                                        class="form-control me-1"
                                        onchange="this.form.submit()">
                                    <input type="hidden" name="update_qty">
                                </form>
                            </td>

                            <td>
                                $<?= number_format($price, 2) ?>
                            </td>

                            <td>
                                $<?= number_format($subtotal, 2) ?>
                            </td>

                            <td>
                                <form method="post">
                                    <input type="hidden" name="product_id" value="<?= $item['id'] ?>">
                                    <button name="remove_item" class="btn btn-sm btn-danger">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>

                    <tr class="table-light">
                        <td colspan="4" class="text-end fw-bold">Total</td>
                        <td colspan="2" class="fw-bold text-danger">
                            $<?= number_format($total, 2) ?>
                        </td>
                    </tr>

                </tbody>
            </table>
        </div>

        <div class="d-flex mt-3">
            <a href="shop.php" class="btn btn-success">
                Continue Shopping
            </a>
            <a href="checkout.php" class="btn btn-primary ms-auto">
                Checkout
            </a>
        </div>

    <?php endif; ?>
</div>

<?php require 'inc/footer.php'; ?>