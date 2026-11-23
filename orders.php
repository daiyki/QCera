<?php
require_once 'functions.php';
require_login();

$uid = (int)$_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'cancel') {
    csrf_check();
    $err = set_order_status((int)($_POST['order_id'] ?? 0), 'cancelled', $uid);
    if ($err === '') {
        flash('success', 'Your order has been cancelled.');
    } else {
        flash('error', $err);
    }
    header('Location: orders.php');
    exit;
}

$placed = $_GET['placed'] ?? '';
$orders = db_all('SELECT * FROM orders WHERE user_id = ? ORDER BY id DESC', 'i', [$uid]);
$itemsBy = get_items_for_orders($orders);

$pageTitle = 'QCera | My Orders';
$css = ['header', 'store', 'footer'];
$navItems = account_nav();

include 'partials/head.php';
include 'partials/site_header.php';
?>

    <main class="page">
        <div class="wrap">

            <div class="page-head">
                <h1>MY <span>ORDERS</span></h1>
                <p>Track your orders and view your order history</p>
            </div>

            <?= flash_html() ?>

            <?php if (!$orders): ?>
                <div class="card empty-state">
                    <i class="fas fa-box-open"></i>
                    <p>You haven't placed any orders yet.</p>
                    <a href="first.php#latest" class="btn btn-primary">START SHOPPING</a>
                </div>
            <?php endif; ?>

            <?php foreach ($orders as $o):
                $status   = $o['order_status'];
                $stepIdx  = array_search($status, TRACK_STEPS, true);
                $lines    = $itemsBy[$o['id']] ?? [];
            ?>
                <div class="card order-card <?= $placed === $o['order_code'] ? 'highlight' : '' ?>">

                    <div class="order-top">
                        <div>
                            <div class="order-id">Order <span>#<?= h($o['order_code']) ?></span></div>
                            <p class="muted">Placed on <?= h(fmt_date($o['created_at'])) ?></p>
                        </div>
                        <span class="badge badge-<?= h($status) ?>"><?= h(ucfirst($status)) ?></span>
                    </div>

                    <?php if ($status === 'cancelled'): ?>
                        <div class="cancel-note"><i class="fas fa-ban"></i> This order was cancelled.<?= $o['payment_status'] === 'refunded' ? ' Your simulated payment was refunded.' : '' ?></div>
                    <?php else: ?>
                        <ol class="tracker">
                            <?php foreach (TRACK_STEPS as $i => $step): ?>
                                <li class="<?= $i < $stepIdx ? 'done' : ($i === $stepIdx ? 'current' : '') ?>"><?= h(ucfirst($step)) ?></li>
                            <?php endforeach; ?>
                        </ol>
                    <?php endif; ?>

                    <div class="info-grid">
                        <div><b>NAME</b><?= h($o['customer_name']) ?></div>
                        <div><b>EMAIL</b><?= h($o['customer_email']) ?></div>
                        <div><b>PAYMENT METHOD</b><?= h(payment_label($o['payment_method'])) ?>
                            <span class="badge badge-<?= h($o['payment_status']) ?>" style="margin-left:6px;"><?= h($o['payment_status']) ?></span>
                            <?php if ($o['payment_ref']): ?><br><span class="muted">Ref: <?= h($o['payment_ref']) ?></span><?php endif; ?>
                        </div>
                        <div><b>DELIVERY ADDRESS</b><?= h($o['shipping_address']) ?></div>
                    </div>

                    <div class="table-wrap">
                        <table class="data">
                            <thead>
                                <tr><th>PRODUCT</th><th class="num">PRICE</th><th class="num">QTY</th><th class="num">TOTAL</th></tr>
                            </thead>
                            <tbody>
                                <?php foreach ($lines as $l): ?>
                                    <tr>
                                        <td><?= h($l['product_name']) ?></td>
                                        <td class="num"><?= peso($l['unit_price']) ?></td>
                                        <td class="num"><?= (int)$l['quantity'] ?></td>
                                        <td class="num"><?= peso($l['line_total']) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot>
                                <tr><td colspan="3" class="num">Order Total</td><td class="num" style="color:rgb(168,16,16);"><?= peso($o['total_amount']) ?></td></tr>
                            </tfoot>
                        </table>
                    </div>

                    <?php if ($status === 'pending'): ?>
                        <form method="POST" action="orders.php" style="margin-top:18px;"
                              onsubmit="return confirm('Cancel this order?');">
                            <?= csrf_field() ?>
                            <input type="hidden" name="action" value="cancel">
                            <input type="hidden" name="order_id" value="<?= (int)$o['id'] ?>">
                            <button type="submit" class="btn btn-danger btn-sm"><i class="fas fa-times"></i> CANCEL ORDER</button>
                        </form>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>

        </div>
    </main>

<?php include 'partials/site_footer.php'; ?>
</body>

</html>
