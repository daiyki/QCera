<?php
require_once 'functions.php';

// "Latest products" prices always come from the products table (single source of truth)
$lp = array_column(
    db_all('SELECT productId, productPrice FROM products WHERE productId IN (1, 3, 11, 16)'),
    'productPrice',
    'productId'
);
$price = fn($id) => number_format((float)($lp[$id] ?? 0), 2);

$pageTitle = 'QCera';
$css = ['face', 'header', 'slider', 'shop-categories', 'latestprod', 'footer'];
$navItems = [
    ['#', 'Home', 'HOME'],
    ['#about', 'About Us', 'ABOUT'],
    ['#featured', 'Newest Products', 'ARRIVALS'],
    ['#latest', 'Products', 'SHOP'],
    ['#foot', 'Contact Us', 'CONTACT'],
];

include 'partials/head.php';
include 'partials/site_header.php';
?>

    <section class="ad">
        <video autoplay muted loop>
            <source src="imgvid/adi.mp4" type="video/mp4">
        </video>
    </section>

    <section class="abt">
        <div class="about" id="about">
            <h1> UPGRADE YOUR DESK,<br>ENHANCE YOUR SETUP</h1>

            <p>
                Introducing QCera, where great performance starts with the right gear. A brand dedicated to produce PC products and reliable performance to enhance your everyday experience. From keyboards to essential PC accessories, we aim to bring convenience and style to every setup. Offering practical products that suit your needs. Whether you're studying, working, gaming, or building your dream setup, QCera is here to help you make the most of every moment. At QCera, we believe that the right tools can make all the difference. That's why we offer a range of products designed to help you work smarter, play harder, and create a space that reflects your unique style.
            </p>
        </div>

        <div class="model">
            <img src="imgvid/model1.png" alt="QCera Model">
        </div>

    </section>

    <section class="featured" id="featured">
        <div class="heading">
            <h1>FEATURED PRODUCTS</h1>
            <p>Discover our top picks</p>
        </div>

        <div class="slidewrap">

            <div class="slider" id="slider">

                <div class="slide" id="slide1">
                    <img src="imgvid/ad3.png" alt="QCera Product 1">
                </div>

                <div class="slide" id="slide2">
                    <img src="imgvid/ad2.png" alt="QCera Product 2">
                </div>

                <div class="slide" id="slide3">
                    <img src="imgvid/ad4.png" alt="QCera Product 3">
                </div>

                <div class="slide" id="slide4">
                    <img src="imgvid/ad1.png" alt="QCera Product 4">
                </div>

            </div>

            <div class="slidernav">
                <a href="#slide1"></a>
                <a href="#slide2"></a>
                <a href="#slide3"></a>
                <a href="#slide4"></a>
            </div>

        </div>
    </section>

    <section class="latest" id="latest">
        <h1>LATEST PRODUCTS</h1>
        <p>Check out our newest arrivals</p>
        <a href="product.php?id=1" class="keyan">
            <div class="con">

                <div class="products" title="QCera Keyboard - Keyra">
                    <div class="title">
                        <h4>QC<span>era</span></h4>
                    </div>
                    <div class="discount">NOW AVAILABLE! <i class="fas fa-heart"></i></div>

                    <img src="imgvid/Keyan.png">

                    <div class="name">
                        <h2>KEYAN</h2>

                        <p class="price">₱ <?= $price(1) ?></p>
                    </div>

                </div>
        </a>

        <a href="product.php?id=3" class="crema">
            <div class="products" title="QCera Keyboard - Crema">
                <div class="title">
                    <h4>QC<span>era</span></h4>
                </div>
                <div class="discount">NOW AVAILABLE! <i class="fas fa-heart"></i></div>

                <img src="imgvid/Crema.jpeg">

                <div class="name">
                    <h2>CREMA</h2>

                    <p class="price">₱ <?= $price(3) ?></p>
                </div>
            </div>
        </a>

        <a href="product.php?id=11" class="clickera">
            <div class="products" title="QCera Mouse - Clickera">
                <div class="title">
                    <h4>QC<span>era</span></h4>
                </div>
                <div class="discount">NOW AVAILABLE! <i class="fas fa-heart"></i></div>

                <img src="imgvid/Clickera.jpeg">

                <div class="name">
                    <h2>CLICKERA</h2>

                    <p class="price">₱ <?= $price(11) ?></p>
                </div>
            </div>
        </a>

        <a href="product.php?id=16" class="panora">
            <div class="products" title="QCera Monitor - Panora">
                <div class="title">
                    <h4>QC<span>era</span></h4>
                </div>
                <div class="discount">NEW RELEASE! <i class="fas fa-heart"></i></div>

                <img src="imgvid/Panora.jpeg">

                <div class="name">
                    <h2>PANORA</h2>

                    <p class="price">₱ <?= $price(16) ?></p>
                </div>
            </div>
        </a>

    </section>

<?php
include 'partials/shop_categories.php';
include 'partials/site_footer.php';
?>
</body>

</html>
