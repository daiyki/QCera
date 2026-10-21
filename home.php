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
        <link rel= "stylesheet" href="styles/footer.css">
        <link rel ="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.3.1/css/all.min.css">

        
    </head>

    <body>

        <header>
            <section class="name">QC<span>ERA</span></section>

                <nav class="bar">
                    <a href="#">HOME</a>
                    <a href="#product">PRODUCTS</a>
                    <a href="#about">ABOUT</a>
                    <a href="#foot">CONTACT</a>
                </nav>

        <div class="symbol">
                <a href="#" class="fas fa-user" onclick="document.getElementById('user').showModal(); return false;"></a>
            </div>

            <dialog id="user">

<?php
    if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
        
        echo "<p>Welcome, " . htmlspecialchars($_SESSION['username']) . "!</p>";
        echo "<p><a href='home.php'>Logout</a></p><br>";
        session_destroy();

    } else {
        
        echo "<a href='login.php'>Login</a><br>";
    }
    ?>

                <button onclick="document.getElementById('user').close();">Close</button>
            </dialog>
        </header>

    <section class="ad">
        <video autoplay muted loop>
            <source src="adi.mp4" type="video/mp4">
        </video>
    </section>


         <section class="abt">
        <div class="about">

            <h1>
                UPGRADE YOUR DESK,
                <br>ENHANCE YOUR SETUP
            </h1>

            <p>
                Introducing QCera, where great performance starts with the right gear. A brand dedicated to produce PC
                products and reliable performance to enhance your everyday experience. From keyboards to essential PC
                accessories, we aim to bring convenience and style to every setup.
                <br>
                Offering practical products that suit your needs. Whether you're studying, working, gaming, or building
                your dream setup, QCera is here to help you make the most of every moment.
            </p>
        </div>


        <div class="model">
            <img src="model1.png">
        </div>
    </section>

        <section class="Products" id="product">
        <h1>LATEST PRODUCTS</h1>

    </section>

    <section class="product-ads">

        <div class="latest-product">
            <video autoplay muted loop>
                <source src="adi.mp4" type="video/mp4">
            </video>
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

    
    </body>


</html>