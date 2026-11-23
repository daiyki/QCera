<?php
require_once 'functions.php';
require_login();

$uid  = (int)$_SESSION['user_id'];
$mode = (($_REQUEST['mode'] ?? '') === 'buy') ? 'buy' : 'cart';
$items = get_checkout_items($mode, $uid);

if (!$items) {
    flash('error', 'There is nothing to check out.');
    header('Location: addtocart.php');
    exit;
}

$me = db_one('SELECT username, email, address FROM users WHERE id = ?', 'i', [$uid]);
$form = [
    'name'    => $me['username'],
    'email'   => $me['email'],
    'address' => $me['address'],
    'payment' => 'cod',
];
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();

    $form['name']    = trim($_POST['name'] ?? '');
    $form['email']   = trim($_POST['email'] ?? '');
    $form['address'] = trim($_POST['address'] ?? '');
    $form['payment'] = $_POST['payment_method'] ?? '';

    if ($form['name'] === '' || mb_strlen($form['name']) > 100) {
        $errors[] = 'Please enter your full name (up to 100 characters).';
    }
    if (!filter_var($form['email'], FILTER_VALIDATE_EMAIL) || strlen($form['email']) > 255) {
        $errors[] = 'Please enter a valid email address.';
    }
    if (mb_strlen($form['address']) < 5 || mb_strlen($form['address']) > 255) {
        $errors[] = 'Please enter a complete delivery address (5-255 characters).';
    }
    if (!in_array($form['payment'], ['cod', 'online'], true)) {
        $errors[] = 'Please choose a payment method.';
    }

    if (!$errors) {
        mysqli_begin_transaction($conn);
        try {
            // lock the products so two buyers can't take the last item at the same time
            $lines = [];
            $total = 0;
            foreach ($items as $it) {
                $p   = db_one('SELECT * FROM products WHERE productId = ? FOR UPDATE', 'i', [(int)$it['productId']]);
                $qty = (int)$it['qty'];
                if (!$p) {
                    throw new RuntimeException('"' . $it['productName'] . '" is no longer available.');
                }
                if ((int)$p['productStock'] < $qty) {
                    throw new RuntimeException(
                        (int)$p['productStock'] > 0
                            ? 'Only ' . (int)$p['productStock'] . ' left of "' . $p['productName'] . '". Please lower the quantity.'
                            : '"' . $p['productName'] . '" is out of stock.'
                    );
                }
                $line   = (int)$p['productPrice'] * $qty;   // quantity x price
                $total += $line;
                $lines[] = [$p, $qty, $line];
            }

            $payStatus = $form['payment'] === 'online' ? 'paid' : 'unpaid';
            $payRef    = $form['payment'] === 'online' ? 'SIM-' . strtoupper(bin2hex(random_bytes(5))) : null; // simulation only

            $orderId = 0;
            $code = '';
            for ($try = 0; $try < 5 && !$orderId; $try++) {
                $code = generate_order_code();
                try {
                    db_exec(
                        'INSERT INTO orders (order_code, user_id, customer_name, customer_email, shipping_address,
                                             payment_method, payment_status, payment_ref, total_amount)
                         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)',
                        'sissssssi',
                        [$code, $uid, $form['name'], $form['email'], $form['address'], $form['payment'], $payStatus, $payRef, $total]
                    );
                    $orderId = (int)mysqli_insert_id($conn);
                } catch (mysqli_sql_exception $e) {
                    if ((int)$e->getCode() !== 1062) {   // 1062 = duplicate code, just try another
                        throw $e;
                    }
                }
            }
            if (!$orderId) {
                throw new RuntimeException('Could not generate an order ID. Please try again.');
            }

            foreach ($lines as [$p, $qty, $line]) {
                db_exec(
                    'INSERT INTO order_items (order_id, product_id, product_name, unit_price, quantity, line_total)
                     VALUES (?, ?, ?, ?, ?, ?)',
                    'iisiii',
                    [$orderId, (int)$p['productId'], $p['productName'], (int)$p['productPrice'], $qty, $line]
                );
                // stock goes down automatically
                $n = db_exec(
                    'UPDATE products SET productStock = productStock - ? WHERE productId = ? AND productStock >= ?',
                    'iii',
                    [$qty, (int)$p['productId'], $qty]
                );
                if ($n !== 1) {
                    throw new RuntimeException('"' . $p['productName'] . '" just went out of stock.');
                }
            }

            if ($mode === 'cart') {
                db_exec('DELETE FROM cart WHERE user_id = ?', 'i', [$uid]);
            }

            mysqli_commit($conn);

            if ($mode === 'buy') {
                unset($_SESSION['buy_now']);
            }
            flash('success', 'Order placed! Your order ID is ' . $code . '.');
            header('Location: orders.php?placed=' . urlencode($code));
            exit;
        } catch (RuntimeException $e) {
            mysqli_rollback($conn);
            $errors[] = $e->getMessage();
            $items = get_checkout_items($mode, $uid);
            if (!$items) {
                flash('error', $e->getMessage());
                header('Location: addtocart.php');
                exit;
            }
        } catch (Throwable $e) {
            mysqli_rollback($conn);
            error_log('checkout: ' . $e->getMessage());
            $errors[] = 'Something went wrong while placing your order. Please try again.';
        }
    }
}

$total = 0;
foreach ($items as $it) {
    $total += (int)$it['productPrice'] * (int)$it['qty'];
}

$pageTitle = 'QCera | Checkout';
$css = ['header', 'store', 'footer'];
$navItems = account_nav();

include 'partials/head.php';
include 'partials/site_header.php';
?>

    <main class="page">
        <div class="wrap">

            <div class="page-head">
                <h1><span>CHECK</span>OUT</h1>
                <p>Confirm your details and choose how to pay</p>
            </div>

            <?php if ($errors): ?>
                <div class="error-list">
                    <?php foreach ($errors as $e): ?><?= h($e) ?><br><?php endforeach; ?>
                </div>
            <?php endif; ?>

            <div class="two-col">

                <form method="POST" action="checkout.php" id="checkout-form">
                    <?= csrf_field() ?>
                    <input type="hidden" name="mode" value="<?= h($mode) ?>">

                    <div class="card">
                        <h2><i class="fas fa-truck"></i>Delivery Details</h2>

                        <div class="form-grid">
                            <div class="form-group">
                                <label for="name">Full Name</label>
                                <input type="text" id="name" name="name" value="<?= h($form['name']) ?>" maxlength="100" required>
                            </div>
                            <div class="form-group">
                                <label for="email">Email Address</label>
                                <input type="email" id="email" name="email" value="<?= h($form['email']) ?>" maxlength="255" required>
                            </div>
                            <div class="form-group full">
                                <label for="address">Delivery Address</label>
                                <textarea id="address" name="address" maxlength="255" required><?= h($form['address']) ?></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <h2><i class="fas fa-wallet"></i>Payment Method</h2>

                        <label class="pay-option">
                            <input type="radio" name="payment_method" value="cod" <?= $form['payment'] === 'cod' ? 'checked' : '' ?>>
                            <span class="pay-title"><i class="fas fa-money-bill-wave"></i>Cash on Delivery</span>
                            <span class="pay-desc">Pay in cash when your order arrives.</span>
                        </label>

                        <label class="pay-option">
                            <input type="radio" name="payment_method" value="online" <?= $form['payment'] === 'online' ? 'checked' : '' ?>>
                            <span class="pay-title"><i class="fas fa-credit-card"></i>Online Payment</span>
                            <span class="pay-desc">Simulation only - no real transaction is made. The order is marked as paid.</span>
                        </label>
                    </div>
                </form>

                <aside class="card summary">
                    <h2><i class="fas fa-receipt"></i>Order Summary</h2>

                    <?php foreach ($items as $it): ?>
                        <div class="mini-item">
                            <span><?= h($it['productName']) ?> × <?= (int)$it['qty'] ?></span>
                            <span><?= peso((int)$it['productPrice'] * (int)$it['qty']) ?></span>
                        </div>
                    <?php endforeach; ?>

                    <div class="sum-row total" style="margin-top:10px;"><span>Total</span><span><?= peso($total) ?></span></div>

                    <button type="submit" form="checkout-form" class="btn btn-primary btn-block" style="margin-top:15px;">PLACE ORDER</button>
                    <a href="<?= $mode === 'buy' ? 'first.php#latest' : 'addtocart.php' ?>" class="btn btn-outline btn-block" style="margin-top:10px;">BACK</a>
                </aside>

            </div>
        </div>
    </main>

<?php include 'partials/site_footer.php'; ?>
</body>

</html>
