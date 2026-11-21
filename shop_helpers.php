<?php
// Put this file in the QCera root folder (same folder as first.php and db.php).

// Server-side product catalog. Prices live HERE, never trust prices sent by the browser.
// Add new products here as you build their pages (key = product id used in the cart).
$PRODUCTS = [
    'keyra'    => ['name' => 'Keyra',    'price' => 2750],
    'clickera' => ['name' => 'Clickera', 'price' => 1500],
    'panora'   => ['name' => 'Panora',   'price' => 10500],
];

function is_logged_in(): bool {
    return isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true;
}

// Sends guests to the login page.
function require_login(): void {
    if (!is_logged_in()) {
        header("Location: /QCera/login.php");
        exit();
    }
}

// Cart is stored in the session as [productId => quantity].
function add_to_cart(string $id, int $qty = 1): bool {
    global $PRODUCTS;
    if (!isset($PRODUCTS[$id]) || $qty < 1) {
        return false;
    }
    $current = $_SESSION['cart'][$id] ?? 0;
    $_SESSION['cart'][$id] = min($current + $qty, 99);
    return true;
}

// Saves one row in `orders` per item. $items = [productId => quantity].
// Name and address come from the logged-in user's row in `users`.
function place_order(mysqli $conn, array $items): bool {
    global $PRODUCTS;

    $email = $_SESSION['email'] ?? '';

    $stmt = mysqli_prepare($conn, "SELECT username, address FROM users WHERE email = ?");
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    $user = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    if (!$user) {
        return false;
    }

    $name    = $user['username'] !== '' ? $user['username'] : $email;
    $address = $user['address'];

    $insert = mysqli_prepare(
        $conn,
        "INSERT INTO orders (orderName, orderAddress, product, quantity, price) VALUES (?, ?, ?, ?, ?)"
    );

    $saved = 0;
    mysqli_begin_transaction($conn);
    try {
        foreach ($items as $id => $qty) {
            $qty = (int) $qty;
            if (!isset($PRODUCTS[$id]) || $qty < 1) {
                continue;
            }
            $product  = $PRODUCTS[$id]['name'];
            $unitPrice = $PRODUCTS[$id]['price'];
            $subtotal = $unitPrice * $qty;
            mysqli_stmt_bind_param($insert, "sssii", $name, $address, $product, $qty, $subtotal);
            mysqli_stmt_execute($insert);
            $saved++;
        }
        if ($saved === 0) {
            mysqli_rollback($conn);
            return false;
        }
        mysqli_commit($conn);
        return true;
    } catch (Throwable $e) {
        mysqli_rollback($conn);
        return false;
    }
}
