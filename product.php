<?php
require_once 'functions.php';
require_login();

$id = (int)($_GET['id'] ?? 0);
$product = db_one('SELECT * FROM products WHERE productId = ?', 'i', [$id]);

if (!$product) {
    http_response_code(404);
    exit('Product not found.');
}

$related = db_all(
    'SELECT * FROM products WHERE productCategory = ? AND productId <> ? ORDER BY productPrice ASC, productId DESC',
    'si',
    [$product['productCategory'], $id]
);

$stock = (int)$product['productStock'];
$inStock = $stock > 0;

$pageTitle = 'QCera';
$css = ['header', 'product-page', 'shop-categories', 'other-products', 'footer'];
$navItems = [
    ['first.php', 'Go Back to Home', 'HOME'],
    ['#shop', 'Shop by Category', 'CATEGORIES'],
    ['#product', 'Products', 'PRODUCTS'],
    ['#about', 'About Us', 'ABOUT'],
    ['#foot', 'Contact Us', 'CONTACT'],
];

include 'partials/head.php';
include 'partials/site_header.php';
?>

    <section class="product-page">

        <div class="product-display">

            <div class="product-image">
                <img src="<?= h($product['productImg']) ?>" alt="<?= h($product['productName']) ?>">
            </div>

            <div class="product-info">

                <?= flash_html() ?>

                <span class="product-category">
                    QCERA | <?= h(strtoupper($product['productCategory'])) ?>
                </span>

                <h1><?= h($product['productName']) ?></h1>

                <div class="price-section">
                    <h2>₱ <?= number_format((float)$product['productPrice'], 2) ?></h2>
                </div>

                <p class="product-description">
                    <?= h($product['productDescription']) ?>
                </p>

                <?php if ($inStock): ?>
                    <div class="availability">
                        <i class="fas fa-check-circle"></i>
                        Available<?= $stock <= 5 ? ' - only ' . $stock . ' left' : '' ?>
                    </div>
                <?php else: ?>
                    <div class="availability" style="color:#d63031;">
                        <i class="fas fa-times-circle"></i>
                        Out of Stock
                    </div>
                <?php endif; ?>

                <form method="POST" action="cart_action.php">
                    <?= csrf_field() ?>
                    <input type="hidden" name="product_id" value="<?= (int)$product['productId'] ?>">

                    <div class="quantity-section">

                        <label for="quantity">Quantity:</label>

                        <div class="quantity-control">
                            <button type="button" onclick="this.nextElementSibling.stepDown()" <?= $inStock ? '' : 'disabled' ?>>−</button>

                            <input type="number" id="quantity" name="quantity" value="1" min="1" max="<?= max(1, $stock) ?>" <?= $inStock ? '' : 'disabled' ?>>

                            <button type="button" onclick="this.previousElementSibling.stepUp()" <?= $inStock ? '' : 'disabled' ?>>+</button>
                        </div>

                    </div>

                    <div class="purchase-buttons">

                        <button type="submit" name="action" value="add" class="cart-btn" <?= $inStock ? '' : 'disabled style="opacity:.5;cursor:not-allowed;"' ?>>
                            <i class="fas fa-shopping-cart"></i>
                            ADD TO CART
                        </button>

                        <button type="submit" name="action" value="buy" class="buy-btn" <?= $inStock ? '' : 'disabled style="opacity:.5;cursor:not-allowed;"' ?>>
                            BUY NOW
                            <i class="fas fa-arrow-right"></i>
                        </button>

                    </div>
                </form>

            </div>

        </div>

    </section>

<?php include 'partials/shop_categories.php'; ?>

    <?php if (!empty($related)): ?>
        <section class="product" id="product">
            <h1>OTHER RELATED PRODUCTS</h1>
            <p>Explore related collection</p>

            <div class="con">
                <?php foreach ($related as $item): ?>
                    <a href="product.php?id=<?= (int)$item['productId'] ?>">
                        <div class="products" title="QCera <?= h($item['productCategory']) ?> - <?= h($item['productName']) ?>">
                            <div class="title">
                                <h4>QC<span>era</span> | <?= h($item['productCategory']) ?></h4>
                            </div>

                            <img src="<?= h($item['productImg']) ?>" alt="QCera <?= h($item['productName']) ?>">

                            <div class="name">
                                <h2><?= h($item['productName']) ?></h2>
                                <p class="price">₱ <?= number_format((float)$item['productPrice'], 2) ?></p>
                            </div>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endif; ?>

<?php
include 'partials/site_about.php';
include 'partials/site_footer.php';
?>
</body>

</html>
