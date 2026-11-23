<?php
require_once 'functions.php';

if (is_logged_in()) {
    header('Location: first.php');
    exit;
}

$errors = [];
$old = ['email' => '', 'username' => '', 'address' => ''];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    csrf_check();

    // no more FILTER_SANITIZE_STRING (deprecated) - values are validated and always escaped on output
    $old['email']    = trim($_POST['email'] ?? '');
    $old['username'] = trim($_POST['username'] ?? '');
    $old['address']  = trim($_POST['address'] ?? '');
    $passwordRaw     = $_POST['password'] ?? '';

    if (!filter_var($old['email'], FILTER_VALIDATE_EMAIL) || strlen($old['email']) > 255) {
        $errors[] = 'Please enter a valid email address.';
    }
    if (!valid_username($old['username'])) {
        $errors[] = 'Username must be 3-30 characters (letters, numbers, spaces, dot, dash or underscore).';
    }
    if (mb_strlen($old['address']) < 5 || mb_strlen($old['address']) > 255) {
        $errors[] = 'Please enter a complete address (5-255 characters).';
    }
    if (!valid_password($passwordRaw)) {
        $errors[] = PASSWORD_RULE;
    }

    if (!$errors) {
        $exists = db_one(
            'SELECT id FROM users WHERE username = ? OR email = ?',
            'ss',
            [$old['username'], $old['email']]
        );

        if ($exists) {
            $errors[] = 'Username or email already exists.';
        } else {
            try {
                // new sign-ups are ALWAYS regular users; admins are created only inside the admin portal
                db_exec(
                    "INSERT INTO users (username, email, address, password, role) VALUES (?, ?, ?, ?, 'user')",
                    'ssss',
                    [$old['username'], $old['email'], $old['address'], password_hash($passwordRaw, PASSWORD_DEFAULT)]
                );
                flash('success', 'Registration successful! You can now log in.');
                header('Location: login.php');
                exit;
            } catch (mysqli_sql_exception $e) {
                if ((int)$e->getCode() === 1062) {
                    $errors[] = 'Username or email already exists.';
                } else {
                    error_log('register: ' . $e->getMessage());
                    $errors[] = 'Something went wrong. Please try again.';
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>QCera | Register</title>

    <link rel="stylesheet" href="styles/register.css">
    <link rel="stylesheet" href="styles/responsive.css">
</head>

<body>

    <div class="bg">
        <video autoplay muted loop playsinline>
            <source src="imgvid/adi.mp4" type="video/mp4">
        </video>
    </div>

    <div class="register-container">

        <a href="first.php" class="exit-btn">&times;</a>

        <div class="register-header">
            <h1>QC<span>ERA</span></h1>
            <p>Create your account</p>
        </div>

        <?php if ($errors): ?>
            <p class="error-message">
                <?php foreach ($errors as $e): ?><?= h($e) ?><br><?php endforeach; ?>
            </p>
        <?php endif; ?>

        <form method="POST" action="register.php">
            <?= csrf_field() ?>

            <div class="register-group">
                <input type="email" id="email" name="email" value="<?= h($old['email']) ?>" maxlength="255" required>
                <label for="email">Email</label>
                <span class="underline"></span>
            </div>

            <div class="register-group">
                <input type="text" id="username" name="username" value="<?= h($old['username']) ?>" maxlength="30" required>
                <label for="username">Username</label>
                <span class="underline"></span>
            </div>

            <div class="register-group">
                <input type="text" id="address" name="address" value="<?= h($old['address']) ?>" maxlength="255" required>
                <label for="address">Address</label>
                <span class="underline"></span>
            </div>

            <div class="register-group">
                <input type="password" id="password" name="password" minlength="8" maxlength="72" required>
                <label for="password">Password</label>
                <span class="underline"></span>
            </div>

            <input type="submit" value="Register" class="register-btn">

        </form>

        <div class="register-footer"> Already have an account?
            <br><a href="login.php">Login</a>
        </div>
    </div>
</body>

</html>
