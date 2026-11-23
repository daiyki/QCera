<?php
require_once 'functions.php';
require_login();

$uid = (int)$_SESSION['user_id'];
$me  = db_one('SELECT id, username, email, address, password, role, created_at FROM users WHERE id = ?', 'i', [$uid]);
$errors = [];
$form = ['username' => $me['username'], 'email' => $me['email'], 'address' => $me['address']];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();

    $form['username'] = trim($_POST['username'] ?? '');
    $form['email']    = trim($_POST['email'] ?? '');
    $form['address']  = trim($_POST['address'] ?? '');
    $current = $_POST['current_password'] ?? '';
    $new     = $_POST['new_password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    if (!valid_username($form['username'])) {
        $errors[] = 'Username must be 3-30 characters (letters, numbers, spaces, dot, dash or underscore).';
    }
    if (!filter_var($form['email'], FILTER_VALIDATE_EMAIL) || strlen($form['email']) > 255) {
        $errors[] = 'Please enter a valid email address.';
    }
    if (mb_strlen($form['address']) < 5 || mb_strlen($form['address']) > 255) {
        $errors[] = 'Please enter a complete address (5-255 characters).';
    }

    $newHash = null;
    if ($new !== '' || $current !== '' || $confirm !== '') {
        if (!password_verify($current, $me['password'])) {
            $errors[] = 'Your current password is incorrect.';
        }
        if (!valid_password($new)) {
            $errors[] = PASSWORD_RULE;
        } elseif ($new !== $confirm) {
            $errors[] = 'The new passwords do not match.';
        } else {
            $newHash = password_hash($new, PASSWORD_DEFAULT);
        }
    }

    if (!$errors) {
        $dupe = db_one(
            'SELECT id FROM users WHERE (username = ? OR email = ?) AND id <> ?',
            'ssi',
            [$form['username'], $form['email'], $uid]
        );
        if ($dupe) {
            $errors[] = 'That username or email is already used by another account.';
        }
    }

    if (!$errors) {
        try {
            if ($newHash !== null) {
                db_exec(
                    'UPDATE users SET username = ?, email = ?, address = ?, password = ? WHERE id = ?',
                    'ssssi',
                    [$form['username'], $form['email'], $form['address'], $newHash, $uid]
                );
            } else {
                db_exec(
                    'UPDATE users SET username = ?, email = ?, address = ? WHERE id = ?',
                    'sssi',
                    [$form['username'], $form['email'], $form['address'], $uid]
                );
            }
            $_SESSION['username'] = $form['username'];
            $_SESSION['email']    = $form['email'];
            flash('success', 'Your profile has been updated.');
            header('Location: account.php');
            exit;
        } catch (mysqli_sql_exception $e) {
            error_log('account update: ' . $e->getMessage());
            $errors[] = 'That username or email is already in use, or the update failed. Please try again.';
        }
    }
}

$pageTitle = 'QCera | Edit Profile';
$css = ['header', 'store', 'footer'];
$navItems = account_nav();

include 'partials/head.php';
include 'partials/site_header.php';
?>

    <main class="page">
        <div class="wrap" style="max-width:800px;">

            <div class="page-head">
                <h1>EDIT <span>PROFILE</span></h1>
                <p>Manage your personal information</p>
            </div>

            <?= flash_html() ?>

            <?php if ($errors): ?>
                <div class="error-list">
                    <?php foreach ($errors as $e): ?><?= h($e) ?><br><?php endforeach; ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="account.php" autocomplete="off">
                <?= csrf_field() ?>

                <div class="card">
                    <h2><i class="fas fa-id-card"></i>Personal Information</h2>
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="username">Username</label>
                            <input type="text" id="username" name="username" value="<?= h($form['username']) ?>" maxlength="30" required>
                        </div>
                        <div class="form-group">
                            <label for="email">Email Address</label>
                            <input type="email" id="email" name="email" value="<?= h($form['email']) ?>" maxlength="255" required>
                        </div>
                        <div class="form-group full">
                            <label for="address">Address</label>
                            <textarea id="address" name="address" maxlength="255" required><?= h($form['address']) ?></textarea>
                        </div>
                    </div>
                    <p class="muted">Member since <?= h(date('F d, Y', strtotime($me['created_at']))) ?></p>
                </div>

                <div class="card">
                    <h2><i class="fas fa-lock"></i>Change Password</h2>
                    <p class="muted" style="margin-bottom:15px;">Leave these blank to keep your current password.</p>
                    <div class="form-grid">
                        <div class="form-group full">
                            <label for="current_password">Current Password</label>
                            <input type="password" id="current_password" name="current_password" autocomplete="current-password">
                        </div>
                        <div class="form-group">
                            <label for="new_password">New Password</label>
                            <input type="password" id="new_password" name="new_password" autocomplete="new-password">
                            <p class="form-hint">8+ characters, with a letter and a number.</p>
                        </div>
                        <div class="form-group">
                            <label for="confirm_password">Confirm New Password</label>
                            <input type="password" id="confirm_password" name="confirm_password" autocomplete="new-password">
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary btn-block">SAVE CHANGES</button>
            </form>

        </div>
    </main>

<?php include 'partials/site_footer.php'; ?>
</body>

</html>
