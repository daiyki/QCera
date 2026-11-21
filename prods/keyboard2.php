<<<<<<< HEAD
<?php
session_start();
// This file lives in /QCera/prods/, so the shared files are one folder up.
require_once '../db.php';
require_once '../shop_helpers.php';

$productId = 'keyra'; // must match a key in $PRODUCTS (shop_helpers.php)
$product = $PRODUCTS[$productId];
$unitPrice = $product['price'];

// One-time message shown after an action (see the spot inside the form below).
$message = '';
if (isset($_SESSION['flash'])) {
    $message = $_SESSION['flash'];
    unset($_SESSION['flash']);
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    require_login(); // guests get sent to login.php

    $qty = (int) ($_POST['qty'] ?? 1);
    $qty = min(max($qty, 1), 99);

    if (isset($_POST["cart"])) {
        $_SESSION['flash'] = add_to_cart($productId, $qty)
            ? "Added {$qty} item(s) to cart."
            : "Could not add this item to your cart.";
    } elseif (isset($_POST["buy"])) {
        $_SESSION['flash'] = place_order($conn, [$productId => $qty])
            ? "Order placed! Thank you for shopping at QCera."
            : "Sorry, we could not place your order. Please try again.";
    }

    // Redirect so refreshing the page doesn't resubmit the form.
    header("Location: " . $_SERVER['REQUEST_URI']);
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QCera</title>
</head>
<body>
    <button><a href="/qcera/first.php">Home</a></button>
    <form method="post">
    <img src="/QCera/assets/kb2.png" alt="placeholder" width="500" height="250">

    <h2><?php echo htmlspecialchars($product['name']); ?></h2>
    <p>Price: ₱ <span id="unit-price"><?php echo number_format($unitPrice, 2); ?></span></p>

    <label for="qty">Quantity:</label>
    <input type="number" id="qty" name="qty" value="1" min="1" max="99">
    <p><strong>Total: ₱ <span id="display-total"><?php echo number_format($unitPrice, 2); ?></span></strong></p>

    <?php
    if ($message !== '') {
        echo '<p>' . htmlspecialchars($message) . '</p>';
    }
    ?>
    <p></p>
    <input type="submit" value="Add to Cart" name="cart" id="cart">
    <input type="submit" value="Buy" name="buy" id="buy">

    </form>

    <script>
        const unitPrice = <?php echo json_encode((float) $unitPrice); ?>;
        const quantityInput = document.getElementById('qty');
        const displayTotal = document.getElementById('display-total');

        quantityInput.addEventListener('input', function () {
            let quantity = parseInt(this.value, 10);
            if (!Number.isFinite(quantity)) {
                quantity = 1;
            }
            quantity = Math.min(Math.max(quantity, 1), 99);
            displayTotal.textContent = (unitPrice * quantity).toLocaleString(undefined, {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        });
    </script>
</body>
</html>
=======
<?php
session_start();
// This file lives in /QCera/prods/, so the shared files are one folder up.
require_once '../db.php';
require_once '../shop_helpers.php';

$productId = 'keyra'; // must match a key in $PRODUCTS (shop_helpers.php)
$product = $PRODUCTS[$productId];
$unitPrice = $product['price'];

// One-time message shown after an action (see the spot inside the form below).
$message = '';
if (isset($_SESSION['flash'])) {
    $message = $_SESSION['flash'];
    unset($_SESSION['flash']);
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    require_login(); // guests get sent to login.php

    $qty = (int) ($_POST['qty'] ?? 1);
    $qty = min(max($qty, 1), 99);

    if (isset($_POST["cart"])) {
        $_SESSION['flash'] = add_to_cart($productId, $qty)
            ? "Added {$qty} item(s) to cart."
            : "Could not add this item to your cart.";
    } elseif (isset($_POST["buy"])) {
        $_SESSION['flash'] = place_order($conn, [$productId => $qty])
            ? "Order placed! Thank you for shopping at QCera."
            : "Sorry, we could not place your order. Please try again.";
    }

    // Redirect so refreshing the page doesn't resubmit the form.
    header("Location: " . $_SERVER['REQUEST_URI']);
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QCera</title>
</head>
<body>
    <button><a href="/qcera/first.php">Home</a></button>
    <form method="post">
    <img src="/QCera/assets/kb2.png" alt="placeholder" width="500" height="250">

    <h2><?php echo htmlspecialchars($product['name']); ?></h2>
    <p>Price: ₱ <span id="unit-price"><?php echo number_format($unitPrice, 2); ?></span></p>

    <label for="qty">Quantity:</label>
    <input type="number" id="qty" name="qty" value="1" min="1" max="99">
    <p><strong>Total: ₱ <span id="display-total"><?php echo number_format($unitPrice, 2); ?></span></strong></p>

    <?php
    if ($message !== '') {
        echo '<p>' . htmlspecialchars($message) . '</p>';
    }
    ?>
    <p></p>
    <input type="submit" value="Add to Cart" name="cart" id="cart">
    <input type="submit" value="Buy" name="buy" id="buy">

    </form>

    <script>
        const unitPrice = <?php echo json_encode((float) $unitPrice); ?>;
        const quantityInput = document.getElementById('qty');
        const displayTotal = document.getElementById('display-total');

        quantityInput.addEventListener('input', function () {
            let quantity = parseInt(this.value, 10);
            if (!Number.isFinite(quantity)) {
                quantity = 1;
            }
            quantity = Math.min(Math.max(quantity, 1), 99);
            displayTotal.textContent = (unitPrice * quantity).toLocaleString(undefined, {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        });
    </script>
</body>
</html>
>>>>>>> e17bb4b792d6e82657e33b0815f0946485e9a5ee
