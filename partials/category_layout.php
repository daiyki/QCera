<?php
// expects: $category (DB value), $pageHeading, $pageSubtitle
$products = db_all(
    'SELECT * FROM products WHERE productCategory = ? ORDER BY productPrice ASC, productId DESC',
    's',
    [$category]
);

$pageTitle = 'QCera';
$css = ['header', 'category', 'shop-categories', 'footer'];
include __DIR__ . '/head.php';
include __DIR__ . '/site_header.php';
?>

    <section class="product" id="product">

        <h1>QC<span>era</span> | <?= h($pageHeading) ?></h1>
        <p><?= h($pageSubtitle) ?></p>

        <div class="con">
            <?php foreach ($products as $p): ?>
                <a href="product.php?id=<?= (int)$p['productId'] ?>">
                    <div class="products" title="QCera <?= h($category) ?> - <?= h($p['productName']) ?>">
                        <div class="title">
                            <h4>QC<span>era</span> | <?= h($category) ?></h4>
                        </div>

                        <img src="<?= h($p['productImg']) ?>" alt="QCera <?= h($p['productName']) ?>">

                        <div class="name">
                            <h2><?= h($p['productName']) ?></h2>
                            <p class="price">₱ <?= number_format((float)$p['productPrice'], 2) ?></p>
                        </div>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    </section>

<?php
include __DIR__ . '/shop_categories.php';
include __DIR__ . '/site_about.php';
include __DIR__ . '/site_footer.php';
?>
</body>

</html>
