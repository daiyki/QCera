<?php
session_start();
?>

<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <title>QCera</title>
        <link rel= "stylesheet" href="styles/face.css">
        <link rel="stylesheet" href="styles/header.css">
        <link rel= "stylesheet" href="styles/slider.css">
        <link rel= "stylesheet" href="styles/footer.css">
        <link rel= "stylesheet" href="imgvid/Keyboard1.jpeg">
        <link rel= "stylesheet" href="imgvid/model1.png">
        <link rel= "stylesheet" href="imgvid/Mouse1.jpeg">
        <link rel= "stylesheet" href="imgvid/Monitor2.jpeg">
        <link rel= "stylesheet" href="imgvid/adi.mp4">
        <link rel ="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.3.1/css/all.min.css">
    </head>

    <body>

        <header>
            <section class="name">QC<span>ERA</span></section>

                <nav class="bar">
                    <a href="#">HOME</a>
                    <a href="#about">ABOUT</a>
                    <a href="#product">PRODUCTS</a>
                    <a href="#foot">CONTACT</a>
                </nav>

        <div class="symbol">
    <a href="#" class="fas fa-user"
       onclick="document.getElementById('user').showModal(); return false;">
    </a>
</div>

<dialog id="user">

    <div class="user-content">

        <div class="user-icon">
            <i class="fas fa-user"></i>
        </div>

        <?php
        if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
            echo "<h2>Welcome!</h2>";
            echo "<p class='username'>" . htmlspecialchars($_SESSION['username']) . "</p>";

        } else {
            echo "<h2>Welcome to QCera!</h2>";
            echo "<p>Log in to acces your account.</p>";
            echo "<a href='login.php' class='user-link'>Login</a>";
        }
        ?>

        <button type="button"
            onclick="document.getElementById('user').close();">
            Close
        </button>

    </div>

</dialog>
            
        </header>

    <section class="ad">
        <video autoplay muted loop>
            <source src="imgvid/adi.mp4" type="video/mp4">
        </video>
    </section>

         <section class="abt">
        <div class="about" id="about">

            <h1>
                UPGRADE YOUR DESK,
                <br>ENHANCE YOUR SETUP
            </h1>
        
            <p>
                Introducing QCera, where great performance starts with the right gear. A brand dedicated to produce PC
                products and reliable performance to enhance your everyday experience. From keyboards to essential PC
                accessories, we aim to bring convenience and style to every setup.
                Offering practical products that suit your needs. Whether you're studying, working, gaming, or building
                your dream setup, QCera is here to help you make the most of every moment.
                
                At QCera, we believe that the right tools can make all the difference. That's why we offer a range of products designed to help you work smarter, play harder, and create a space that reflects your unique style.
            </p>

        </div>


        <div class="model">
            <img src="imgvid/model1.png">
        </div>
    </section>

    <section class="products" id="product">
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
                    <img src="imgvid/ad4.png" alt="QCera Product 2">
                </div>

                <div class="slide" id="slide4">
                    <img src="imgvid/ad1.png" alt="QCera Product 2">
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
    <p>Explore our latest collection</p> 
</section>

<section class="shop" id="shop">
    <h1>SHOP BY CATEGORY</h1>
    <p>Check our collection</p>

    <div class="container">

        <a href="mouse.html" class="prod">
            <img src="imgvid/Mouse1.jpeg">

            <div class="name">
                <span>QCera</span>
                <h5>Computer Mouse</h5>
            </div>
        </a>

        <a href="monitor.html" class="prod">
            <img src="imgvid/Monitor2.jpeg">

            <div class="name">
                <span>QCera</span>
                <h5>Computer Monitor</h5>
            </div>

        <a href="keyboard.html" class="prod">
            <img src="imgvid/Keyboard3.jpeg">

            <div class="name">
                <span>QCera</span>
                <h5>Keyboard</h5>
            </div>
        </a>

        <a href="pc-components.html" class="prod">
            <img src="imgvid/.jpeg">

            <div class="name">
                <span>QCera</span>
                <h5>PC Components</h5>
            </div>
        </a>

    </div>

    
</section>
        
        <footer class="foot" id="foot">
        <h1>QC<span>ERA</span></h1>
        <p>One-stop shop for premium <br>PC components and accessories.</p>

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