<?php
// expects: $adminActive, optional $pageTitle. Call require_admin() BEFORE including this.
$adminActive = $adminActive ?? '';
$pageTitle = $pageTitle ?? 'QCera | Admin';
$css = ['header', 'store', 'admin', 'footer'];
$adminNav = [
    ['admin.php', 'Dashboard', 'DASHBOARD', 'dashboard'],
    ['admin-products.php', 'Products & Stock', 'PRODUCTS', 'products'],
    ['admin-orders.php', 'Orders', 'ORDERS', 'orders'],
    ['admin-users.php', 'Users', 'USERS', 'users'],
];
include __DIR__ . '/head.php';
?>
    <header>
        <section class="name">
            <img src="imgvid/qcera-logo.png" alt="QCera Logo">
        </section>

        <nav class="bar">
            <?php foreach ($adminNav as [$href, $title, $label, $key]): ?>
                <a href="<?= h($href) ?>" title="<?= h($title) ?>" class="<?= $adminActive === $key ? 'active' : '' ?>"><?= h($label) ?></a>
            <?php endforeach; ?>
        </nav>

        <div class="symbol">
            <a href="first.php" title="View Store"><i class="fas fa-store"></i></a>
            <a href="account.php" title="My Profile"><i class="fas fa-user"></i></a>
            <a href="logout.php" title="Logout"><i class="fas fa-sign-out-alt"></i></a>
        </div>

        <button type="button" class="menu-toggle" aria-label="Toggle menu" aria-expanded="false">
            <i class="fas fa-bars"></i>
        </button>
    </header>

