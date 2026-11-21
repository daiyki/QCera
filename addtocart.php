<?php
session_start();
require_once 'db.php';
require_once 'shop_helpers.php';

require_login(); // guests get sent to login.php

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $action = $_POST['action'] ?? '';
    $id     = $_POST['id'] ?? '';

    if ($action === 'remove') {
        unset($_SESSION['cart'][$id]);
        $_SESSION['flash'] = "Item removed.";

    } elseif ($action === 'update') {
        $qty = (int) ($_POST['qty'] ?? 0);
        if (isset($_SESSION['cart'][$id])) {
            if ($qty < 1) {
                unset($_SESSION['cart'][$id]);
            } else {
                $_SESSION['cart'][$id] = min($qty, 99);
            }
        }
        $_SESSION['flash'] = "Cart updated.";

    } elseif ($action === 'checkout') {
        $cart = $_SESSION['cart'] ?? [];
        if (empty($cart)) {
            $_SESSION['flash'] = "Your cart is empty.";
        } elseif (place_order($conn, $cart)) {
            unset($_SESSION['cart']);
            $_SESSION['flash'] = "Order placed! Thank you for shopping at QCera.";
        } else {
            $_SESSION['flash'] = "Sorry, we could not place your order. Please try again.";
        }
    }

    header("Location: addtocart.php");
    exit();
}

$message = '';
if (isset($_SESSION['flash'])) {
    $message = $_SESSION['flash'];
    unset($_SESSION['flash']);
}

$cart  = $_SESSION['cart'] ?? [];
$total = 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QCera - Cart</title>
</head>
<body>
    <h1>My Cart</h1>

    <?php if ($message !== ''): ?>
        <p><?php echo htmlspecialchars($message); ?></p>
    <?php endif; ?>

    <?php if (empty($cart)): ?>
        <p>Your cart is empty.</p>
    <?php else: ?>
        <table border="1" cellpadding="6">
            <tr>
                <th>Product</th>
                <th>Price</th>
                <th>Quantity</th>
                <th>Subtotal</th>
                <th></th>
            </tr>

            <?php foreach ($cart as $id => $qty): ?>
                <?php
                if (!isset($PRODUCTS[$id])) { continue; }
                $price    = $PRODUCTS[$id]['price'];
                $subtotal = $price * $qty;
                $total   += $subtotal;
                ?>
                <tr>
                    <td><?php echo htmlspecialchars($PRODUCTS[$id]['name']); ?></td>
                    <td>₱ <?php echo number_format($price, 2); ?></td>
                    <td>
                        <form method="post">
                            <input type="hidden" name="action" value="update">
                            <input type="hidden" name="id" value="<?php echo htmlspecialchars($id); ?>">
                            <input type="number" class="cart-qty" name="qty" value="<?php echo (int) $qty; ?>" min="1" max="99"
                                   data-unit-price="<?php echo (float) $price; ?>">
                            <input type="submit" value="Update">
                        </form>
                    </td>
                    <td class="cart-subtotal">₱ <span><?php echo number_format($subtotal, 2); ?></span></td>
                    <td>
                        <form method="post">
                            <input type="hidden" name="action" value="remove">
                            <input type="hidden" name="id" value="<?php echo htmlspecialchars($id); ?>">
                            <input type="submit" value="Remove">
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>

        <p><strong>Total: ₱ <?php echo number_format($total, 2); ?></strong></p>

        <form method="post">
            <input type="hidden" name="action" value="checkout">
            <input type="submit" value="Checkout">
        </form>
    <?php endif; ?>

    <p><a href="first.php">Continue shopping</a></p>

    <script>
        const cartQuantities = document.querySelectorAll('.cart-qty');
        const cartTotal = document.querySelector('p strong');

        cartQuantities.forEach(function (quantityInput) {
            quantityInput.addEventListener('input', function () {
                let quantity = parseInt(this.value, 10);
                if (!Number.isFinite(quantity)) {
                    quantity = 1;
                }
                quantity = Math.min(Math.max(quantity, 1), 99);

                const unitPrice = parseFloat(this.dataset.unitPrice);
                const row = this.closest('tr');
                row.querySelector('.cart-subtotal span').textContent =
                    (unitPrice * quantity).toLocaleString(undefined, {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    });

                let total = 0;
                document.querySelectorAll('.cart-qty').forEach(function (input) {
                    const qty = Math.min(Math.max(parseInt(input.value, 10) || 1, 1), 99);
                    total += parseFloat(input.dataset.unitPrice) * qty;
                });
                if (cartTotal) {
                    cartTotal.textContent = 'Total: ₱ ' + total.toLocaleString(undefined, {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    });
                }
            });
        });
    </script>
</body>
</html>
