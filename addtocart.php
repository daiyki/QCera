<?php
require_once 'functions.php';
require_login();

$uid   = (int)$_SESSION['user_id'];
$items = get_cart_items($uid);

$total = 0;
$count = 0;
$blocked = false;
foreach ($items as $it) {
    $total += (int)$it['productPrice'] * (int)$it['qty'];   // quantity x price
    $count += (int)$it['qty'];
    if ((int)$it['productStock'] < (int)$it['qty']) {
        $blocked = true;
    }
}

$pageTitle = 'QCera | My Cart';
$css = ['header', 'store', 'footer'];
$navItems = account_nav();

include 'partials/head.php';
include 'partials/site_header.php';
?>

    <main class="page">
        <div class="wrap">

            <div class="page-head">
                <h1>MY <span>CART</span></h1>
                <p>Review the items you've added</p>
            </div>

            <?= flash_html() ?>

            <?php if (!$items): ?>
                <div class="card empty-state">
                    <i class="fas fa-shopping-cart"></i>
                    <p>Your cart is empty.</p>
                    <a href="first.php#latest" class="btn btn-primary">START SHOPPING</a>
                </div>
            <?php else: ?>
                <div class="two-col">

                    <div class="card">
                        <?php foreach ($items as $it): $line = (int)$it['productPrice'] * (int)$it['qty']; ?>
                            <div class="cart-item">
                                <img src="<?= h($it['productImg']) ?>" alt="<?= h($it['productName']) ?>">

                                <div class="ci-info">
                                    <a href="product.php?id=<?= (int)$it['productId'] ?>"><h3><?= h($it['productName']) ?></h3></a>
                                    <p class="muted">QCERA | <?= h($it['productCategory']) ?></p>
                                    <p class="unit-price"><?= peso($it['productPrice']) ?></p>
                                    <?php if ((int)$it['productStock'] <= 0): ?>
                                        <p class="stock-warn">Out of stock - please remove this item.</p>
                                    <?php elseif ((int)$it['productStock'] < (int)$it['qty']): ?>
                                        <p class="stock-warn">Only <?= (int)$it['productStock'] ?> left - please lower the quantity.</p>
                                    <?php endif; ?>
                                </div>

                                <div class="ci-actions">
                                    <form method="POST" action="cart_action.php" class="inline-form">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="action" value="update">
                                        <input type="hidden" name="product_id" value="<?= (int)$it['productId'] ?>">
                                        <input type="number" name="quantity" value="<?= (int)$it['qty'] ?>" min="1" max="<?= max(1, (int)$it['productStock']) ?>" aria-label="Quantity">
                                        <button type="submit" class="btn btn-outline btn-sm">UPDATE</button>
                                    </form>

                                    <p class="line-total"><?= peso($line) ?></p>

                                    <form method="POST" action="cart_action.php">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="action" value="remove">
                                        <input type="hidden" name="product_id" value="<?= (int)$it['productId'] ?>">
                                        <button type="submit" class="link-danger">Remove</button>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <aside class="card summary">
                        <h2><i class="fas fa-receipt"></i>Order Summary</h2>
                        <div class="sum-row"><span>Items</span><span><?= $count ?></span></div>
                        <div class="sum-row total"><span>Total</span><span><?= peso($total) ?></span></div>

                        <?php if ($blocked): ?>
                            <p class="stock-warn" style="margin:10px 0;">Fix the highlighted items to continue.</p>
                            <button class="btn btn-primary btn-block" disabled>PROCEED TO CHECKOUT</button>
                        <?php else: ?>
                            <a href="checkout.php" class="btn btn-primary btn-block" style="margin-top:15px;">PROCEED TO CHECKOUT</a>
                        <?php endif; ?>
                        <a href="first.php#latest" class="btn btn-outline btn-block" style="margin-top:10px;">CONTINUE SHOPPING</a>
                    </aside>

                </div>
            <?php endif; ?>

        </div>
    </main>

<?php include 'partials/site_footer.php'; ?>
</body>

</html>
