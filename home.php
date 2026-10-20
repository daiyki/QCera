<?php
//starts the session of the user ONLY if the user is LOGGED IN
session_start();
?>

<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <title>QCera</title>
        <link rel= "stylesheet" href="styles/face.css">
        <link rel="stylesheet" href="styles/header.css">
        <link rel ="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.3.1/css/all.min.css">

        
    </head>

    <body>

        <header>
    <!--nav bar dawg-->
            <section class="name">QC<span>ERA</span></section>

            <nav class="bar">
                <a href="#">HOME</a>
                <a href="#about" id=>ABOUT</a>
                <a href="#review">REVIEWS</a>
                <a href="#contact">CONTACT</a>
            </nav>
            <!-- user icon -->
            <div class ="symbol">
                <a href="#" class="fas fa-user" onclick="document.getElementById('user').showModal(); return false;"></a>
            </div>

            <dialog id="user">


<?php
    // check if the user is logged in
    if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
        // content visible ONLY to logged IN users
        echo "<p>Welcome, " . htmlspecialchars($_SESSION['username']) . "!</p>";
        echo "<p><a href='home.php'>Logout</a></p><br>";
        session_destroy();

    } else {
        // content visible ONLY to logged OUT guests
        echo "<a href='login.php'>Login</a><br>";
    }
    ?>

                <button onclick="document.getElementById('user').close();">Close</button>
            </dialog>
        </header>

    <!--video / ad-->
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
                    Introducing QCera. We offer different PC accessories that are useful for powering up your desk, such as keyboards, mouse, and other accessories. We created this online store to provide PC components in an easier and more convenient way. Our goal is to create a website that makes browsing easier for customers while providing useful information and affordable prices.
                </p>


            </div>


            <div class="model">
                <img src="model1.png">
            </div>
        </section>

        <section class="Products" id="product">
			<h1>LATEST PRODUCTS</h1>

		

        </section>
        
        <section class="con" id="contact">

			<h1>CONTACT US</h1>

				<div class="social">
					<br>
						<a href="https://facebook.com">
						<img src="facebooklogo.png">   Facebook
						</a>

					<br>
						<a href="https://gmail.com">
						<img src="emaillogo.png">   E-mail
						</a>

				</div>
	
            
        </section>

    
    <script src="script.js"></script>
    </body>


</html>