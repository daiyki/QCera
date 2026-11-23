<?php
/* Shared helpers for every page. Include with: require_once 'functions.php'; */

if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

require_once __DIR__ . '/db.php';
date_default_timezone_set('Asia/Manila');

const CATEGORIES     = ['Keyboard', 'Mouse', 'Monitor', 'PC Component'];
const ORDER_STATUSES = ['pending', 'processing', 'shipped', 'delivered', 'cancelled'];
const TRACK_STEPS    = ['pending', 'processing', 'shipped', 'delivered'];

/* ---------- output / formatting ---------- */
function h($v): string
{
    return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
}

function peso($n): string
{
    return '₱ ' . number_format((float)$n, 2);
}

function fmt_date($ts): string
{
    return date('M d, Y h:i A', strtotime((string)$ts));
}

function payment_label(string $m): string
{
    return $m === 'online' ? 'Online Payment' : 'Cash on Delivery';
}

/* ---------- database helpers (always prepared statements) ---------- */
function db_run(string $sql, string $types = '', array $params = [])
{
    global $conn;
    $st = mysqli_prepare($conn, $sql);
    if ($types !== '') {
        mysqli_stmt_bind_param($st, $types, ...$params);
    }
    mysqli_stmt_execute($st);
    return $st;
}

function db_all(string $sql, string $types = '', array $params = []): array
{
    $st  = db_run($sql, $types, $params);
    $res = mysqli_stmt_get_result($st);
    $rows = $res ? mysqli_fetch_all($res, MYSQLI_ASSOC) : [];
    mysqli_stmt_close($st);
    return $rows;
}

function db_one(string $sql, string $types = '', array $params = []): ?array
{
    $rows = db_all($sql, $types, $params);
    return $rows[0] ?? null;
}

function db_exec(string $sql, string $types = '', array $params = []): int
{
    $st = db_run($sql, $types, $params);
    $n  = mysqli_stmt_affected_rows($st);
    mysqli_stmt_close($st);
    return $n;
}

/* ---------- CSRF ---------- */
function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf" value="' . h(csrf_token()) . '">';
}

function csrf_check(): void
{
    $t = $_POST['csrf'] ?? '';
    if (!is_string($t) || !hash_equals($_SESSION['csrf'] ?? '', $t)) {
        http_response_code(403);
        exit('Invalid or expired request. Please go back, refresh the page and try again.');
    }
}

/* ---------- flash messages ---------- */
function flash(string $type, string $msg): void
{
    $_SESSION['flash'][] = ['t' => $type, 'm' => $msg];
}

function flash_html(): string
{
    if (empty($_SESSION['flash'])) {
        return '';
    }
    $colors = [
        'success' => 'background:#e6ffed;color:#23834b;',
        'error'   => 'background:#ffe6e6;color:#d63031;',
        'info'    => 'background:#f1f1f1;color:#444;',
    ];
    $out = '';
    foreach ($_SESSION['flash'] as $f) {
        $c = $colors[$f['t']] ?? $colors['info'];
        $out .= '<div role="alert" style="' . $c . 'padding:12px 16px;border-radius:8px;margin:0 0 20px;'
              . "font-size:14px;font-family:'Segoe UI',Tahoma,Geneva,Verdana,sans-serif;\">" . h($f['m']) . '</div>';
    }
    unset($_SESSION['flash']);
    return $out;
}

/* ---------- auth ---------- */
function is_logged_in(): bool
{
    return !empty($_SESSION['loggedin']) && $_SESSION['loggedin'] === true && !empty($_SESSION['user_id']);
}

$isLoggedIn = is_logged_in();

function require_login(): void
{
    if (!is_logged_in()) {
        $target = basename($_SERVER['PHP_SELF']);
        if (!empty($_SERVER['QUERY_STRING'])) {
            $target .= '?' . $_SERVER['QUERY_STRING'];
        }
        header('Location: login.php?redirect=' . urlencode($target));
        exit;
    }

    // keep session in sync with the database (role / profile changes, deleted accounts)
    $u = db_one('SELECT id, username, email, role FROM users WHERE id = ?', 'i', [(int)$_SESSION['user_id']]);
    if (!$u) {
        $_SESSION = [];
        session_destroy();
        header('Location: login.php');
        exit;
    }
    $_SESSION['username'] = $u['username'];
    $_SESSION['email']    = $u['email'];
    $_SESSION['role']     = $u['role'];
}

function require_admin(): void
{
    require_login();
    if (($_SESSION['role'] ?? '') !== 'admin') {
        http_response_code(403);
        exit('Access denied. This area is for administrators only.');
    }
}

function account_nav(): array
{
    return [
        ['first.php', 'Go Back to Home', 'HOME'],
        ['first.php#latest', 'Products', 'SHOP'],
        ['first.php#about', 'About Us', 'ABOUT'],
        ['#foot', 'Contact Us', 'CONTACT'],
    ];
}

/* ---------- validation ---------- */
function valid_username(string $u): bool
{
    return (bool)preg_match('/^[A-Za-z0-9_. -]{3,30}$/', $u);
}

function valid_password(string $p): bool
{
    return strlen($p) >= 8 && strlen($p) <= 72 && preg_match('/[A-Za-z]/', $p) && preg_match('/\d/', $p);
}

const PASSWORD_RULE = 'Password must be 8-72 characters and contain at least one letter and one number.';

/* ---------- cart / checkout ---------- */
function get_cart_items(int $uid): array
{
    return db_all(
        'SELECT p.*, c.quantity AS qty FROM cart c JOIN products p ON p.productId = c.product_id
         WHERE c.user_id = ? ORDER BY c.added_at, c.id',
        'i',
        [$uid]
    );
}

function get_checkout_items(string $mode, int $uid): array
{
    if ($mode === 'buy') {
        $b = $_SESSION['buy_now'] ?? null;
        if (!$b) {
            return [];
        }
        $p = db_one('SELECT * FROM products WHERE productId = ?', 'i', [(int)$b['product_id']]);
        if (!$p) {
            return [];
        }
        $p['qty'] = max(1, (int)$b['quantity']);
        return [$p];
    }
    return get_cart_items($uid);
}

function generate_order_code(): string
{
    return 'QC-' . date('ymd') . '-' . strtoupper(bin2hex(random_bytes(3)));
}

function get_items_for_orders(array $orders): array
{
    if (!$orders) {
        return [];
    }
    $ids = array_map('intval', array_column($orders, 'id'));
    $ph  = implode(',', array_fill(0, count($ids), '?'));
    $rows = db_all("SELECT * FROM order_items WHERE order_id IN ($ph) ORDER BY id", str_repeat('i', count($ids)), $ids);
    $by = [];
    foreach ($rows as $r) {
        $by[$r['order_id']][] = $r;
    }
    return $by;
}

/**
 * Changes an order's status safely.
 *  - customers ($onlyUser set): may only cancel their OWN order while it is still "pending"
 *  - admins: may move an order forward (pending > processing > shipped > delivered) or cancel it
 * Cancelling returns the stock. Returns '' on success or an error message.
 */
function set_order_status(int $oid, string $new, ?int $onlyUser = null): string
{
    global $conn;
    if (!in_array($new, ORDER_STATUSES, true)) {
        return 'Invalid status.';
    }

    mysqli_begin_transaction($conn);
    try {
        $o = db_one('SELECT * FROM orders WHERE id = ? FOR UPDATE', 'i', [$oid]);
        if (!$o || ($onlyUser !== null && (int)$o['user_id'] !== $onlyUser)) {
            mysqli_rollback($conn);
            return 'Order not found.';
        }
        $cur = $o['order_status'];

        if ($onlyUser !== null) {
            if ($new !== 'cancelled') {
                mysqli_rollback($conn);
                return 'You can only cancel your order.';
            }
            if ($cur !== 'pending') {
                mysqli_rollback($conn);
                return 'This order is already being processed and can no longer be cancelled.';
            }
        } else {
            if (in_array($cur, ['delivered', 'cancelled'], true)) {
                mysqli_rollback($conn);
                return 'Delivered or cancelled orders can no longer be changed.';
            }
            if ($new !== 'cancelled' && array_search($new, ORDER_STATUSES, true) <= array_search($cur, ORDER_STATUSES, true)) {
                mysqli_rollback($conn);
                return 'Orders can only move forward: pending > processing > shipped > delivered.';
            }
        }

        $pay = $o['payment_status'];

        if ($new === 'cancelled') {
            $items = db_all('SELECT product_id, quantity FROM order_items WHERE order_id = ?', 'i', [$oid]);
            foreach ($items as $it) {
                if ($it['product_id'] !== null) {
                    db_exec(
                        'UPDATE products SET productStock = productStock + ? WHERE productId = ?',
                        'ii',
                        [(int)$it['quantity'], (int)$it['product_id']]
                    );
                }
            }
            if ($pay === 'paid') {
                $pay = 'refunded';
            }
        }
        if ($new === 'delivered' && $o['payment_method'] === 'cod') {
            $pay = 'paid';
        }

        db_exec('UPDATE orders SET order_status = ?, payment_status = ? WHERE id = ?', 'ssi', [$new, $pay, $oid]);
        mysqli_commit($conn);
        return '';
    } catch (Throwable $e) {
        mysqli_rollback($conn);
        error_log('set_order_status: ' . $e->getMessage());
        return 'Something went wrong. Please try again.';
    }
}

/* ---------- product images (admin uploads) ---------- */
function save_product_image(array $file, string &$err): ?string
{
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return null;
    }
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $err = 'The image could not be uploaded (error code ' . (int)$file['error'] . '). Check upload_max_filesize in php.ini.';
        return null;
    }
    if ($file['size'] > 5 * 1024 * 1024) {
        $err = 'Image is too large. Maximum size is 5 MB.';
        return null;
    }
    $info = @getimagesize($file['tmp_name']);
    $map  = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp', 'image/gif' => 'gif'];
    if (!$info || !isset($map[$info['mime']])) {
        $err = 'Only JPG, PNG, WEBP or GIF images are allowed.';
        return null;
    }
    $dir = __DIR__ . '/imgvid/products';
    if (!is_dir($dir) && !mkdir($dir, 0755, true)) {
        $err = 'Could not create the imgvid/products folder. Please create it manually.';
        return null;
    }
    $name = bin2hex(random_bytes(8)) . '.' . $map[$info['mime']];
    if (!move_uploaded_file($file['tmp_name'], $dir . '/' . $name)) {
        $err = 'Could not save the uploaded image. Check folder permissions.';
        return null;
    }
    return 'imgvid/products/' . $name;
}

function delete_product_image(string $path): void
{
    // only ever delete files that were uploaded through the admin page
    if (strpos($path, 'imgvid/products/') === 0) {
        $f = __DIR__ . '/imgvid/products/' . basename($path);
        if (is_file($f)) {
            @unlink($f);
        }
    }
}
