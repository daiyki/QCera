<?php
$navItems = $navItems ?? [
    ['first.php', 'Go Back to Home', 'HOME'],
    ['#product', 'Products', 'PRODUCTS'],
    ['#shop', 'Shop by Category', 'CATEGORIES'],
    ['#about', 'About Us', 'ABOUT'],
    ['#foot', 'Contact Us', 'CONTACT'],
];
?>
    <header>
        <section class="name">
            <img src="imgvid/qcera-logo.png" alt="QCera Logo">
        </section>

        <nav class="bar">
            <?php foreach ($navItems as [$href, $title, $label]): ?>
                <a href="<?= h($href) ?>" title="<?= h($title) ?>"><?= h($label) ?></a>
            <?php endforeach; ?>
        </nav>

        <div class="symbol">
            <a href="addtocart.php" title="My Cart"><i class="fas fa-shopping-cart"></i></a>

            <?php if ($isLoggedIn): ?>
                <a href="#" class="fas fa-user" title="My Account" onclick="document.getElementById('user').showModal(); return false;"> </a>
            <?php else: ?>
                <a href="login.php" class="fas fa-user" title="Login"></a>
            <?php endif; ?>
        </div>

        <button type="button" class="menu-toggle" aria-label="Toggle menu" aria-expanded="false">
            <i class="fas fa-bars"></i>
        </button>

        <?php if ($isLoggedIn): ?>
            <dialog id="user">
                <div class="user-content">

                    <h2>My Account</h2>
                    <p>Welcome to QCera!</p>

                    <p class="username"> <?= h($_SESSION['username'] ?? '') ?> </p>

                    <p> <?= h($_SESSION['email'] ?? '') ?> </p>

                    <a href="orders.php" class="user-link"> <i class="fas fa-box"></i>
                        My Orders
                    </a>

                    <a href="account.php" class="user-link"> <i class="fas fa-user-edit"></i>
                        Edit Profile
                    </a>

                    <?php if (($_SESSION['role'] ?? '') === 'admin'): ?>
                        <a href="admin.php" class="user-link"> <i class="fas fa-user-shield"></i>
                            Admin Portal
                        </a>
                    <?php endif; ?>

                    <a href="logout.php" class="user-link"> <i class="fas fa-sign-out-alt"></i>
                        Logout
                    </a>

                    <button type="button" onclick="document.getElementById('user').close();">
                        Close
                    </button>
                </div>
            </dialog>
        <?php endif; ?>
    </header>

