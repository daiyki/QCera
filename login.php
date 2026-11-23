<?php
require_once 'functions.php';

if ($_SERVER["REQUEST_METHOD"] === "GET") {
    if (isset($_GET['redirect'])) {
        $_SESSION['redirect_after_login'] = (string)$_GET['redirect'];
    } else {
        unset($_SESSION['redirect_after_login']);
    }
}

if (is_logged_in()) {
    header('Location: first.php');
    exit;
}

$error = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    csrf_check();

    $email    = trim($_POST["email"] ?? '');
    $password = $_POST["password"] ?? '';

    // basic brute-force protection: 5 failed tries = 60 second pause
    if (($_SESSION['login_lock_until'] ?? 0) > time()) {
        $error = "Too many failed attempts. Please wait a minute and try again.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email or password.";
    } else {
        $user = db_one('SELECT * FROM users WHERE email = ?', 's', [$email]);

        if ($user && password_verify($password, $user["password"])) {
            session_regenerate_id(true);

            unset($_SESSION['login_fail'], $_SESSION['login_lock_until']);
            $_SESSION['loggedin'] = true;
            $_SESSION['user_id']  = (int)$user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['email']    = $user['email'];
            $_SESSION['role']     = $user['role'];

            $redirect = ($user['role'] === 'admin') ? 'admin.php' : 'first.php';
            if (!empty($_SESSION['redirect_after_login'])) {
                $target = $_SESSION['redirect_after_login'];
                unset($_SESSION['redirect_after_login']);

                if (preg_match('/^[a-zA-Z0-9_\-]+\.php(\?[a-zA-Z0-9=&_\-]*)?$/', $target)) {
                    $redirect = $target;
                }
            }

            header("Location: " . $redirect);
            exit();
        }

        $_SESSION['login_fail'] = ($_SESSION['login_fail'] ?? 0) + 1;
        if ($_SESSION['login_fail'] >= 5) {
            $_SESSION['login_lock_until'] = time() + 60;
            $_SESSION['login_fail'] = 0;
        }
        usleep(300000);
        $error = "Invalid email or password.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" href="styles/login.css">
    <link rel="stylesheet" href="styles/responsive.css">

    <title>QCera | Login</title>
</head>

<body>

    <section class="bg">
        <video autoplay muted loop playsinline>
            <source src="imgvid/adi.mp4" type="video/mp4">
        </video>
    </section>

    <div class="login-container">

        <a href="first.php" class="exit-btn" title="Back to Home"> &times; </a>

        <div class="login-header">

            <h1 class="name">QC<span>ERA</span></h1>
            <p>Please login to your account</p>

        </div>

        <?= flash_html() ?>

        <?php if (!empty($error)): ?>
            <p class="error-message">
                <?php echo htmlspecialchars($error); ?>
            </p>
        <?php endif; ?>

        <form method="POST" action="login.php">
            <?= csrf_field() ?>

            <div class="input-group">
                <input type="email" id="email" name="email" required>
                <label for="email">Enter Email Address</label>
                <span class="underline"></span>
            </div>

            <div class="input-group">
                <input type="password" id="password" name="password" required>
                <label for="password">Enter Password</label>
                <span class="underline"></span>
            </div>

            <button type="submit" class="login-btn">Login</button>
        </form>

        <div class="logfooter">
            <p>No account?</p>
            <a href="register.php">Create Account</a>
        </div>

    </div>

</body>

</html>
