<?php
require_once 'functions.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: addtocart.php');
    exit;
}
csrf_check();

$uid    = (int)$_SESSION['user_id'];
$action = $_POST['action'] ?? '';
$pid    = (int)($_POST['product_id'] ?? 0);
$qty    = (int)($_POST['quantity'] ?? 1);

$product = db_one('SELECT * FROM products WHERE productId = ?', 'i', [$pid]);
if (!$product) {
    flash('error', 'That product could not be found.');
    header('Location: first.php');
    exit;
}
$stock = (int)$product['productStock'];

switch ($action) {

    case 'add':
    case 'buy':
        if ($stock <= 0) {
            flash('error', $product['productName'] . ' is out of stock.');
            header('Location: product.php?id=' . $pid);
            exit;
        }
        $qty = max(1, $qty);
        if ($qty > $stock) {
            $qty = $stock;
            flash('info', 'Only ' . $stock . ' in stock, so the quantity was adjusted.');
        }

        if ($action === 'buy') {
            $_SESSION['buy_now'] = ['product_id' => $pid, 'quantity' => $qty];
            header('Location: checkout.php?mode=buy');
            exit;
        }

        db_exec(
            'INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)
             ON DUPLICATE KEY UPDATE quantity = LEAST(quantity + VALUES(quantity), ?)',
            'iiii',
            [$uid, $pid, $qty, $stock]
        );
        flash('success', $product['productName'] . ' was added to your cart.');
        header('Location: product.php?id=' . $pid);
        exit;

    case 'update':
        if ($qty <= 0) {
            db_exec('DELETE FROM cart WHERE user_id = ? AND product_id = ?', 'ii', [$uid, $pid]);
            flash('success', 'Item removed from your cart.');
        } else {
            if ($qty > $stock) {
                $qty = max(1, $stock);
                flash('info', 'Only ' . $stock . ' available for ' . $product['productName'] . '.');
            }
            db_exec('UPDATE cart SET quantity = ? WHERE user_id = ? AND product_id = ?', 'iii', [$qty, $uid, $pid]);
            flash('success', 'Cart updated.');
        }
        break;

    case 'remove':
        db_exec('DELETE FROM cart WHERE user_id = ? AND product_id = ?', 'ii', [$uid, $pid]);
        flash('success', 'Item removed from your cart.');
        break;
}

header('Location: addtocart.php');
exit;
