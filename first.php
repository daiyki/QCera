<?php
session_start();

$isLoggedIn = isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true;
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>QCera</title>

    <link rel="stylesheet" href="styles/face.css">
    <link rel="stylesheet" href="styles/header.css">
    <link rel="stylesheet" href="styles/slider.css">
    <link rel="stylesheet" href="styles/footer.css">
    <link rel="stylesheet" href="styles/latestprod.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.3.1/css/all.min.css">
</head>

<body>

    <header>
        <section class="name">
            <img src="imgvid/qcera-logo.png">
        </section>

        <nav class="bar">
            <a href="#">HOME</a>
            <a href="#about">ABOUT</a>
            <a href="#shop">PRODUCTS</a>
            <a href="#foot">CONTACT</a>
    
        </nav>

        <div class="symbol">
            <a href="addtocart.php"><i class="fas fa-shopping-cart"></i></a>

            <?php if ($isLoggedIn): ?>

                <a href="#" class="fas fa-user" title="My Account" onclick="document.getElementById('user').showModal(); return false;"> </a>

            <?php else: ?>
                <a href="login.php" class="fas fa-user" title="Login"></a>
            <?php endif; ?>

        </div>

        <?php if ($isLoggedIn): ?>

        <dialog id="user">

            <div class="user-content">

                <h2>My Account</h2>
                <p>Welcome to QCera!</p>

                <p class="username"> <?php echo htmlspecialchars($_SESSION['username']); ?> </p>

                <p> <?php echo htmlspecialchars($_SESSION['email']); ?> </p>

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

    <section class="products" id="product">
        <div class="heading">
            <h1>FEATURED PRODUCTS</h1> <p>Discover our top picks</p>
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
        <h1>LATEST PRODUCTS</h1> <p>Explore our latest collection</p>
    <a href="prods/keyboard2.php" class="keyra">
    <div class="con">
        <div class="product" title="QCera Keyboard - Keyra"> 
            <div class="title"><h4>QC<span>era</span></h4> </div>
        <div class="discount">20% DISCOUNT! <i class="fas fa-heart"></i></div>
        
        <img src="imgvid/Keyboard2.jpeg">

        <div class="name">
            <h2>KEYRA</h2>

            <div class="rating">
                <i class="fas fa-star"></i>
                <i class="fas fa-star"></i>
                <i class="fas fa-star"></i>
                <i class="fas fa-star"></i>
                <i class="fas fa-star-half-alt"></i>
                <small>(4.5)</small>
            </div>
            <p class="price">₱ 2,750.00</p>
        </div>

    </div>
    </a>

    <a href="shop.php" class="shop-link">
        <div class="product" title="QCera Mouse - Clickera"> 
        <div class="title"><h4>QC<span>era</span></h4> </div>
        <div class="discount">NOW AVAILABLE! <i class="fas fa-heart"></i></div>
        
        <img src="imgvid/Mouse2.jpeg">

        <div class="name">
            <h2>CLICKERA</h2>

            <div class="rating">
                <i class="fas fa-star"></i>
                <i class="fas fa-star"></i>
                <i class="fas fa-star"></i>
                <i class="fas fa-star"></i>
                <i class="fas fa-star"></i>
                <small>(5.0)</small>
            </div>
            <p class="price">₱ 1,500.00</p>
        </div>
    </div>
    </a>

    <a href="shop.php" class="shop-link">
        <div class="product" title="QCera Keyboard - Keyra"> 
        <div class="title"><h4>QC<span>era</span></h4> </div>
        <div class="discount">NEW RELEASE! <i class="fas fa-heart"></i></div>
        
        <img src="imgvid/Monitor1.jpeg">

        <div class="name">
            <h2>PANORA</h2>

            <div class="rating">
                <i class="fas fa-star"></i>
                <i class="fas fa-star"></i>
                <i class="fas fa-star"></i>
                <i class="fas fa-star"></i>
                <i class="fas fa-star"></i>
                <small>(5.0)</small>
            </div>
            <p class="price">₱10,500.00</p>
        </div>
    </div>
    </a>

</section>

    <section class="shop" id="shop">

        <h1>SHOP BY CATEGORY</h1> <p>Check our collection</p>

        <div class="container">

            <a href="mouse.php" class="prod">
                <img src="imgvid/Mouse1.jpeg" alt="Computer Mouse">

                <div class="name">
                    <span>QCERA</span>
                    <h2>Computer Mouse</h2>
                </div>
            </a>

            <a href="monitor.php" class="prod">
                <img src="imgvid/Monitor2.jpeg" alt="Computer Monitor">

                <div class="name">
                    <span>QCERA</span>
                    <h2>Computer Monitor</h2>
                </div>
            </a>

            <a href="keyboard.php" class="prod">
                <img src="imgvid/Keyboard3.jpeg" alt="Keyboard">
                <div class="name">
                    <span>QCERA</span>
                    <h2>Keyboard</h2>
                </div>
            </a>

            <a href="pc-components.php" class="prod">
                <img src="imgvid/PC4.jpeg" alt="PC Components">
                <div class="name">
                    <span>QCERA</span>
                    <h2>PC Components</h2>
                </div>
            </a>
        </div>
    </section>

    <footer class="foot" id="foot">
        <section class="name-foot">
            <img src="imgvid/qcera-logo-footer.png">
        </section>
        <p>
            One-stop shop for premium <br>PC components and accessories.
        </p>

        <div class="footlinks">
            <a href="" class="fab fa-facebook-f"></a>
            <a href="" class="fab fa-instagram"></a>
            <a href="" class="fab fa-youtube"></a>
            <a href="" class="fab fa-tiktok"></a>
        </div>

        <p class="copyright">
            &copy; 2026 QCera. All Rights Reserved.
        </p>

    </footer>

    <script src="script.js"></script>

</body>
</html>
