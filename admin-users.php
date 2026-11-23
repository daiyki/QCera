<?php
require_once 'functions.php';
require_admin();

$me = (int)$_SESSION['user_id'];
$adminCount = fn() => (int)db_one("SELECT COUNT(*) AS c FROM users WHERE role = 'admin'")['c'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $action = $_POST['action'] ?? '';

    /* ----- create a new ADMIN (this is the ONLY place admins can be created) ----- */
    if ($action === 'add_admin') {
        $username = trim($_POST['username'] ?? '');
        $email    = trim($_POST['email'] ?? '');
        $address  = trim($_POST['address'] ?? '') ?: 'QCera HQ, Quezon City';
        $pass     = $_POST['password'] ?? '';
        $errors = [];

        if (!valid_username($username)) $errors[] = 'Username must be 3-30 characters (letters, numbers, spaces, dot, dash or underscore).';
        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 255) $errors[] = 'Please enter a valid email.';
        if (mb_strlen($address) > 255) $errors[] = 'Address is too long.';
        if (!valid_password($pass)) $errors[] = PASSWORD_RULE;
        if (!$errors && db_one('SELECT id FROM users WHERE username = ? OR email = ?', 'ss', [$username, $email])) {
            $errors[] = 'Username or email already exists.';
        }

        if ($errors) {
            flash('error', implode(' ', $errors));
        } else {
            db_exec(
                "INSERT INTO users (username, email, address, password, role) VALUES (?, ?, ?, ?, 'admin')",
                'ssss',
                [$username, $email, $address, password_hash($pass, PASSWORD_DEFAULT)]
            );
            flash('success', 'Admin account created for ' . $username . '.');
        }
        header('Location: admin-users.php');
        exit;
    }

    /* ----- edit any user's profile ----- */
    if ($action === 'update_user') {
        $uid = (int)($_POST['user_id'] ?? 0);
        $u = db_one('SELECT * FROM users WHERE id = ?', 'i', [$uid]);
        $back = 'admin-users.php?user=' . $uid;

        $username = trim($_POST['username'] ?? '');
        $email    = trim($_POST['email'] ?? '');
        $address  = trim($_POST['address'] ?? '');
        $role     = $_POST['role'] ?? 'user';
        $pass     = $_POST['new_password'] ?? '';
        $errors = [];

        if (!$u) {
            flash('error', 'User not found.');
            header('Location: admin-users.php');
            exit;
        }
        if (!valid_username($username)) $errors[] = 'Username must be 3-30 characters (letters, numbers, spaces, dot, dash or underscore).';
        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 255) $errors[] = 'Please enter a valid email.';
        if (mb_strlen($address) < 5 || mb_strlen($address) > 255) $errors[] = 'Address must be 5-255 characters.';
        if (!in_array($role, ['user', 'admin'], true)) $role = $u['role'];
        if ($u['role'] === 'admin' && $role === 'user' && ($uid === $me || $adminCount() <= 1)) {
            $errors[] = 'You cannot remove the admin role from yourself or from the last admin.';
        }
        if ($pass !== '' && !valid_password($pass)) $errors[] = PASSWORD_RULE;
        if (!$errors && db_one('SELECT id FROM users WHERE (username = ? OR email = ?) AND id <> ?', 'ssi', [$username, $email, $uid])) {
            $errors[] = 'That username or email is already used by another account.';
        }

        if ($errors) {
            flash('error', implode(' ', $errors));
        } else {
            if ($pass !== '') {
                db_exec('UPDATE users SET username=?, email=?, address=?, role=?, password=? WHERE id=?', 'sssssi',
                    [$username, $email, $address, $role, password_hash($pass, PASSWORD_DEFAULT), $uid]);
            } else {
                db_exec('UPDATE users SET username=?, email=?, address=?, role=? WHERE id=?', 'ssssi',
                    [$username, $email, $address, $role, $uid]);
            }
            flash('success', 'Profile updated.');
        }
        header('Location: ' . $back);
        exit;
    }

    /* ----- delete a user (their orders stay in the history) ----- */
    if ($action === 'delete_user') {
        $uid = (int)($_POST['user_id'] ?? 0);
        $u = db_one('SELECT * FROM users WHERE id = ?', 'i', [$uid]);
        if (!$u) {
            flash('error', 'User not found.');
        } elseif ($uid === $me) {
            flash('error', 'You cannot delete your own account here.');
        } elseif ($u['role'] === 'admin' && $adminCount() <= 1) {
            flash('error', 'You cannot delete the last admin.');
        } else {
            db_exec('DELETE FROM users WHERE id = ?', 'i', [$uid]);
            flash('success', 'User deleted. Their past orders remain in the order list.');
        }
        header('Location: admin-users.php');
        exit;
    }
}

$adminActive = 'users';
$pageTitle = 'QCera | Users';
$detail = isset($_GET['user']) ? db_one('SELECT * FROM users WHERE id = ?', 'i', [(int)$_GET['user']]) : null;

include 'partials/admin_top.php';
?>

    <main class="admin-page">
        <div class="admin-wrap">

            <div class="page-head">
                <h1>MANAGE <span>USERS</span></h1>
                <p>View profiles, edit accounts and see every user's order history</p>
            </div>

            <?= flash_html() ?>

<?php if ($detail):
    $orders  = db_all('SELECT * FROM orders WHERE user_id = ? ORDER BY id DESC', 'i', [(int)$detail['id']]);
    $spent   = 0;
    foreach ($orders as $o) { if ($o['order_status'] !== 'cancelled') $spent += (int)$o['total_amount']; }
?>
            <p style="margin-bottom:15px;"><a href="admin-users.php" class="btn btn-outline btn-sm"><i class="fas fa-arrow-left"></i> BACK TO USERS</a></p>

            <div class="grid-2">
                <div class="card">
                    <h2><i class="fas fa-id-card"></i>Profile <span class="badge badge-<?= h($detail['role']) ?>"><?= h($detail['role']) ?></span></h2>

                    <form method="POST" action="admin-users.php" autocomplete="off">
                        <?= csrf_field() ?>
                        <input type="hidden" name="action" value="update_user">
                        <input type="hidden" name="user_id" value="<?= (int)$detail['id'] ?>">

                        <div class="form-grid">
                            <div class="form-group">
                                <label for="username">Username</label>
                                <input type="text" id="username" name="username" value="<?= h($detail['username']) ?>" maxlength="30" required>
                            </div>
                            <div class="form-group">
                                <label for="email">Email</label>
                                <input type="email" id="email" name="email" value="<?= h($detail['email']) ?>" maxlength="255" required>
                            </div>
                            <div class="form-group full">
                                <label for="address">Address</label>
                                <textarea id="address" name="address" maxlength="255" required><?= h($detail['address']) ?></textarea>
                            </div>
                            <div class="form-group">
                                <label for="role">Role</label>
                                <select id="role" name="role">
                                    <option value="user" <?= $detail['role'] === 'user' ? 'selected' : '' ?>>User</option>
                                    <option value="admin" <?= $detail['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="new_password">Reset Password</label>
                                <input type="password" id="new_password" name="new_password" autocomplete="new-password" placeholder="Leave blank to keep">
                            </div>
                        </div>
                        <p class="muted" style="margin-bottom:15px;">Member since <?= h(date('F d, Y', strtotime($detail['created_at']))) ?> · User ID <?= (int)$detail['id'] ?></p>
                        <button type="submit" class="btn btn-primary">SAVE PROFILE</button>
                    </form>
                </div>

                <div class="stats" style="grid-template-columns:1fr 1fr;">
                    <div class="stat-card"><b>ORDERS</b><span><?= count($orders) ?></span></div>
                    <div class="stat-card"><b>TOTAL SPENT</b><span><?= peso($spent) ?></span></div>
                </div>
            </div>

            <div class="card">
                <h2><i class="fas fa-clock-rotate-left"></i>Order History</h2>
                <div class="table-wrap">
                    <table class="data">
                        <thead><tr><th>ORDER ID</th><th>DATE</th><th>PAYMENT</th><th>STATUS</th><th class="num">TOTAL</th></tr></thead>
                        <tbody>
                            <?php if (!$orders): ?><tr><td colspan="5" class="muted">This user has no orders yet.</td></tr><?php endif; ?>
                            <?php foreach ($orders as $o): ?>
                                <tr>
                                    <td><a href="admin-orders.php?q=<?= h(urlencode($o['order_code'])) ?>"><b><?= h($o['order_code']) ?></b></a></td>
                                    <td><?= h(fmt_date($o['created_at'])) ?></td>
                                    <td><?= h(payment_label($o['payment_method'])) ?> <span class="badge badge-<?= h($o['payment_status']) ?>"><?= h($o['payment_status']) ?></span></td>
                                    <td><span class="badge badge-<?= h($o['order_status']) ?>"><?= h(ucfirst($o['order_status'])) ?></span></td>
                                    <td class="num"><?= peso($o['total_amount']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

<?php else:
    $q = trim($_GET['q'] ?? '');
    $users = $q !== ''
        ? db_all('SELECT u.*, (SELECT COUNT(*) FROM orders o WHERE o.user_id = u.id) AS order_count FROM users u
                  WHERE u.username LIKE ? OR u.email LIKE ? ORDER BY u.role, u.id', 'ss', ["%$q%", "%$q%"])
        : db_all('SELECT u.*, (SELECT COUNT(*) FROM orders o WHERE o.user_id = u.id) AS order_count FROM users u ORDER BY u.role, u.id');
?>
            <?php if (isset($_GET['user'])): ?>
                <div class="error-list">That user could not be found.</div>
            <?php endif; ?>

            <div class="card">
                <h2><i class="fas fa-user-shield"></i>Add New Admin</h2>
                <form method="POST" action="admin-users.php" autocomplete="off">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="add_admin">
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="a_username">Username</label>
                            <input type="text" id="a_username" name="username" maxlength="30" required>
                        </div>
                        <div class="form-group">
                            <label for="a_email">Email</label>
                            <input type="email" id="a_email" name="email" maxlength="255" required>
                        </div>
                        <div class="form-group">
                            <label for="a_address">Address (optional)</label>
                            <input type="text" id="a_address" name="address" maxlength="255">
                        </div>
                        <div class="form-group">
                            <label for="a_password">Password</label>
                            <input type="password" id="a_password" name="password" autocomplete="new-password" required>
                            <p class="form-hint">8+ characters, with a letter and a number.</p>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">CREATE ADMIN</button>
                </form>
            </div>

            <div class="card">
                <div class="toolbar">
                    <h2 style="margin:0;"><i class="fas fa-users"></i>All Users (<?= count($users) ?>)</h2>
                    <form method="GET" action="admin-users.php">
                        <input type="text" name="q" value="<?= h($q) ?>" placeholder="Search username or email">
                        <button class="btn btn-outline btn-sm" type="submit">SEARCH</button>
                    </form>
                </div>

                <div class="table-wrap">
                    <table class="data">
                        <thead><tr><th>ID</th><th>USER</th><th>ADDRESS</th><th>ROLE</th><th class="num">ORDERS</th><th>JOINED</th><th>ACTIONS</th></tr></thead>
                        <tbody>
                            <?php foreach ($users as $u): ?>
                                <tr>
                                    <td><?= (int)$u['id'] ?></td>
                                    <td><b><?= h($u['username']) ?></b><br><span class="muted"><?= h($u['email']) ?></span></td>
                                    <td><?= h($u['address']) ?></td>
                                    <td><span class="badge badge-<?= h($u['role']) ?>"><?= h($u['role']) ?></span></td>
                                    <td class="num"><?= (int)$u['order_count'] ?></td>
                                    <td><?= h(date('M d, Y', strtotime($u['created_at']))) ?></td>
                                    <td>
                                        <div class="row-actions">
                                            <a class="btn btn-outline btn-sm" href="admin-users.php?user=<?= (int)$u['id'] ?>">VIEW / EDIT</a>
                                            <?php if ((int)$u['id'] !== $me): ?>
                                                <form method="POST" action="admin-users.php" onsubmit="return confirm('Delete this user? Their orders will stay in the history.');">
                                                    <?= csrf_field() ?>
                                                    <input type="hidden" name="action" value="delete_user">
                                                    <input type="hidden" name="user_id" value="<?= (int)$u['id'] ?>">
                                                    <button class="btn btn-danger btn-sm" type="submit">DELETE</button>
                                                </form>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
<?php endif; ?>

        </div>
    </main>

<?php include 'partials/site_footer.php'; ?>
</body>

</html>
