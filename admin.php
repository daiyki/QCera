<?php
require_once 'functions.php';
require_admin();

$byStatus = [];
foreach (db_all('SELECT order_status, COUNT(*) AS c, COALESCE(SUM(total_amount),0) AS s FROM orders GROUP BY order_status') as $r) {
    $byStatus[$r['order_status']] = $r;
}
$totalOrders = array_sum(array_map(fn($r) => (int)$r['c'], $byStatus));
$revenue = 0;
foreach ($byStatus as $st => $r) {
    if ($st !== 'cancelled') {
        $revenue += (int)$r['s'];
    }
}

$users    = (int)db_one("SELECT COUNT(*) AS c FROM users WHERE role = 'user'")['c'];
$admins   = (int)db_one("SELECT COUNT(*) AS c FROM users WHERE role = 'admin'")['c'];
$products = (int)db_one('SELECT COUNT(*) AS c FROM products')['c'];
$outCount = (int)db_one('SELECT COUNT(*) AS c FROM products WHERE productStock <= 0')['c'];
$lowCount = (int)db_one('SELECT COUNT(*) AS c FROM products WHERE productStock BETWEEN 1 AND 5')['c'];

$stockWatch = db_all('SELECT * FROM products ORDER BY productStock ASC, productName ASC LIMIT 10');
$recent     = db_all('SELECT * FROM orders ORDER BY id DESC LIMIT 8');

$adminActive = 'dashboard';
$pageTitle = 'QCera | Admin Dashboard';
include 'partials/admin_top.php';
?>

    <main class="admin-page">
        <div class="admin-wrap">

            <div class="page-head">
                <h1>ADMIN <span>DASHBOARD</span></h1>
                <p>Welcome back, <?= h($_SESSION['username']) ?></p>
            </div>

            <?= flash_html() ?>

            <div class="stats">
                <div class="stat-card"><b>TOTAL ORDERS</b><span><?= $totalOrders ?></span></div>
                <div class="stat-card"><b>REVENUE</b><span><?= peso($revenue) ?></span><small>excluding cancelled orders</small></div>
                <div class="stat-card"><b>CUSTOMERS</b><span><?= $users ?></span><small><?= $admins ?> admin account(s)</small></div>
                <div class="stat-card"><b>PRODUCTS</b><span><?= $products ?></span><small><?= $lowCount ?> low · <?= $outCount ?> out of stock</small></div>
            </div>

            <div class="grid-2">

                <div class="card">
                    <h2><i class="fas fa-clipboard-list"></i>Order Summary</h2>
                    <div class="table-wrap">
                        <table class="data">
                            <thead><tr><th>STATUS</th><th class="num">ORDERS</th><th class="num">AMOUNT</th></tr></thead>
                            <tbody>
                                <?php foreach (ORDER_STATUSES as $st): $r = $byStatus[$st] ?? ['c' => 0, 's' => 0]; ?>
                                    <tr>
                                        <td><a href="admin-orders.php?status=<?= h($st) ?>"><span class="badge badge-<?= h($st) ?>"><?= h(ucfirst($st)) ?></span></a></td>
                                        <td class="num"><?= (int)$r['c'] ?></td>
                                        <td class="num"><?= peso($r['s']) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="card">
                    <h2><i class="fas fa-boxes-stacked"></i>Stock Watch <span class="muted">(lowest first)</span></h2>
                    <div class="table-wrap">
                        <table class="data">
                            <thead><tr><th>PRODUCT</th><th class="num">STOCK</th><th>STATUS</th></tr></thead>
                            <tbody>
                                <?php foreach ($stockWatch as $p): $s = (int)$p['productStock']; ?>
                                    <tr>
                                        <td><?= h($p['productName']) ?></td>
                                        <td class="num"><?= $s ?></td>
                                        <td><span class="badge <?= $s <= 0 ? 'badge-out' : ($s <= 5 ? 'badge-low' : 'badge-ok') ?>"><?= $s <= 0 ? 'Out of stock' : ($s <= 5 ? 'Low' : 'In stock') ?></span></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <a href="admin-products.php" class="btn btn-outline btn-sm" style="margin-top:15px;">MANAGE ALL STOCK</a>
                </div>

            </div>

            <div class="card">
                <h2><i class="fas fa-clock"></i>Recent Orders</h2>
                <div class="table-wrap">
                    <table class="data">
                        <thead>
                            <tr><th>ORDER ID</th><th>CUSTOMER</th><th>PAYMENT</th><th>STATUS</th><th class="num">TOTAL</th><th>DATE</th></tr>
                        </thead>
                        <tbody>
                            <?php if (!$recent): ?><tr><td colspan="6" class="muted">No orders yet.</td></tr><?php endif; ?>
                            <?php foreach ($recent as $o): ?>
                                <tr>
                                    <td><a href="admin-orders.php?q=<?= h(urlencode($o['order_code'])) ?>"><b><?= h($o['order_code']) ?></b></a></td>
                                    <td><?= h($o['customer_name']) ?><br><span class="muted"><?= h($o['customer_email']) ?></span></td>
                                    <td><?= h(payment_label($o['payment_method'])) ?></td>
                                    <td><span class="badge badge-<?= h($o['order_status']) ?>"><?= h(ucfirst($o['order_status'])) ?></span></td>
                                    <td class="num"><?= peso($o['total_amount']) ?></td>
                                    <td><?= h(fmt_date($o['created_at'])) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </main>

<?php include 'partials/site_footer.php'; ?>
</body>

</html>
