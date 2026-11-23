<?php
require_once 'functions.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $oid = (int)($_POST['order_id'] ?? 0);
    $action = $_POST['action'] ?? '';
    $back = 'admin-orders.php' . (!empty($_POST['return']) && preg_match('/^[a-zA-Z0-9=&_\-%.]*$/', $_POST['return']) ? '?' . $_POST['return'] : '');

    if ($action === 'status') {
        $err = set_order_status($oid, $_POST['new_status'] ?? '');
        $err === '' ? flash('success', 'Order status updated.') : flash('error', $err);
    } elseif ($action === 'mark_paid') {
        $n = db_exec("UPDATE orders SET payment_status = 'paid' WHERE id = ? AND payment_status = 'unpaid' AND order_status <> 'cancelled'", 'i', [$oid]);
        $n ? flash('success', 'Payment marked as paid.') : flash('error', 'Nothing to update.');
    }
    header('Location: ' . $back);
    exit;
}

$status = $_GET['status'] ?? '';
$q = trim($_GET['q'] ?? '');

$where = [];
$types = '';
$params = [];
if (in_array($status, ORDER_STATUSES, true)) {
    $where[] = 'order_status = ?';
    $types .= 's';
    $params[] = $status;
}
if ($q !== '') {
    $where[] = '(order_code LIKE ? OR customer_name LIKE ? OR customer_email LIKE ?)';
    $types .= 'sss';
    array_push($params, "%$q%", "%$q%", "%$q%");
}
$sql = 'SELECT * FROM orders' . ($where ? ' WHERE ' . implode(' AND ', $where) : '') . ' ORDER BY id DESC LIMIT 200';
$orders  = db_all($sql, $types, $params);
$itemsBy = get_items_for_orders($orders);

$counts = [];
foreach (db_all('SELECT order_status, COUNT(*) AS c FROM orders GROUP BY order_status') as $r) {
    $counts[$r['order_status']] = (int)$r['c'];
}
$returnQs = http_build_query(array_filter(['status' => $status, 'q' => $q]));

$adminActive = 'orders';
$pageTitle = 'QCera | Orders';
include 'partials/admin_top.php';
?>

    <main class="admin-page">
        <div class="admin-wrap">

            <div class="page-head">
                <h1>MANAGE <span>ORDERS</span></h1>
                <p>View every order and update its status</p>
            </div>

            <?= flash_html() ?>

            <div class="chips">
                <a class="chip <?= $status === '' ? 'on' : '' ?>" href="admin-orders.php">All (<?= array_sum($counts) ?>)</a>
                <?php foreach (ORDER_STATUSES as $st): ?>
                    <a class="chip <?= $status === $st ? 'on' : '' ?>" href="admin-orders.php?status=<?= h($st) ?>"><?= h(ucfirst($st)) ?> (<?= $counts[$st] ?? 0 ?>)</a>
                <?php endforeach; ?>
            </div>

            <div class="toolbar">
                <form method="GET" action="admin-orders.php">
                    <?php if ($status): ?><input type="hidden" name="status" value="<?= h($status) ?>"><?php endif; ?>
                    <input type="text" name="q" value="<?= h($q) ?>" placeholder="Order ID, name or email">
                    <button class="btn btn-outline btn-sm" type="submit">SEARCH</button>
                </form>
            </div>

            <?php if (!$orders): ?>
                <div class="card empty-state"><i class="fas fa-box-open"></i><p>No orders found.</p></div>
            <?php endif; ?>

            <?php foreach ($orders as $o):
                $cur = $o['order_status'];
                $curIdx = array_search($cur, ORDER_STATUSES, true);
                $next = [];
                if (!in_array($cur, ['delivered', 'cancelled'], true)) {
                    foreach (ORDER_STATUSES as $i => $s) {
                        if ($s === 'cancelled' || $i > $curIdx) $next[] = $s;
                    }
                }
            ?>
                <details class="order-box" <?= ($q !== '' && count($orders) === 1) ? 'open' : '' ?>>
                    <summary>
                        <span class="sum-code"><?= h($o['order_code']) ?></span>
                        <span><?= h($o['customer_name']) ?><br><span class="muted"><?= h($o['customer_email']) ?></span></span>
                        <span><?= peso($o['total_amount']) ?></span>
                        <span class="muted"><?= h(fmt_date($o['created_at'])) ?></span>
                        <span class="badge badge-<?= h($cur) ?>"><?= h(ucfirst($cur)) ?></span>
                    </summary>

                    <div class="order-body">
                        <div class="info-grid">
                            <div><b>ORDER ID</b><?= h($o['order_code']) ?></div>
                            <div><b>CUSTOMER</b><?= h($o['customer_name']) ?> (<?= h($o['customer_email']) ?>)</div>
                            <div><b>PAYMENT</b><?= h(payment_label($o['payment_method'])) ?>
                                <span class="badge badge-<?= h($o['payment_status']) ?>"><?= h($o['payment_status']) ?></span>
                                <?php if ($o['payment_ref']): ?><br><span class="muted">Ref: <?= h($o['payment_ref']) ?></span><?php endif; ?></div>
                            <div><b>DELIVERY ADDRESS</b><?= h($o['shipping_address']) ?></div>
                        </div>

                        <div class="table-wrap">
                            <table class="data">
                                <thead><tr><th>PRODUCT</th><th class="num">PRICE</th><th class="num">QTY</th><th class="num">TOTAL</th></tr></thead>
                                <tbody>
                                    <?php foreach ($itemsBy[$o['id']] ?? [] as $l): ?>
                                        <tr>
                                            <td><?= h($l['product_name']) ?></td>
                                            <td class="num"><?= peso($l['unit_price']) ?></td>
                                            <td class="num"><?= (int)$l['quantity'] ?></td>
                                            <td class="num"><?= peso($l['line_total']) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                                <tfoot><tr><td colspan="3" class="num">Order Total</td><td class="num"><?= peso($o['total_amount']) ?></td></tr></tfoot>
                            </table>
                        </div>

                        <?php if ($next): ?>
                            <form method="POST" action="admin-orders.php" class="status-form">
                                <?= csrf_field() ?>
                                <input type="hidden" name="action" value="status">
                                <input type="hidden" name="order_id" value="<?= (int)$o['id'] ?>">
                                <input type="hidden" name="return" value="<?= h($returnQs) ?>">
                                <label for="s<?= (int)$o['id'] ?>"><b>Update status:</b></label>
                                <select id="s<?= (int)$o['id'] ?>" name="new_status">
                                    <?php foreach ($next as $s): ?><option value="<?= h($s) ?>"><?= h(ucfirst($s)) ?></option><?php endforeach; ?>
                                </select>
                                <button class="btn btn-primary btn-sm" type="submit">UPDATE</button>
                            </form>
                        <?php else: ?>
                            <p class="muted" style="margin-top:15px;">This order is <?= h($cur) ?> and can no longer be changed.</p>
                        <?php endif; ?>

                        <?php if ($o['payment_status'] === 'unpaid' && $cur !== 'cancelled'): ?>
                            <form method="POST" action="admin-orders.php" class="status-form">
                                <?= csrf_field() ?>
                                <input type="hidden" name="action" value="mark_paid">
                                <input type="hidden" name="order_id" value="<?= (int)$o['id'] ?>">
                                <input type="hidden" name="return" value="<?= h($returnQs) ?>">
                                <button class="btn btn-outline btn-sm" type="submit">MARK AS PAID</button>
                            </form>
                        <?php endif; ?>
                    </div>
                </details>
            <?php endforeach; ?>

        </div>
    </main>

<?php include 'partials/site_footer.php'; ?>
</body>

</html>
