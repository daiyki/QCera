<?php
require_once 'functions.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $action = $_POST['action'] ?? '';

    /* ----- quick stock changes ----- */
    if ($action === 'stock') {
        $pid  = (int)($_POST['product_id'] ?? 0);
        $mode = $_POST['mode'] ?? 'set';
        $val  = filter_var($_POST['value'] ?? '', FILTER_VALIDATE_INT);
        if ($val === false || $val < 0 || $val > 100000) {
            flash('error', 'Please enter a whole number between 0 and 100000.');
        } elseif ($mode === 'add') {
            db_exec('UPDATE products SET productStock = productStock + ? WHERE productId = ?', 'ii', [$val, $pid]);
            flash('success', 'Added ' . $val . ' to the stock.');
        } else {
            db_exec('UPDATE products SET productStock = ? WHERE productId = ?', 'ii', [$val, $pid]);
            flash('success', 'Stock updated.');
        }
        header('Location: admin-products.php');
        exit;
    }

    /* ----- delete ----- */
    if ($action === 'delete') {
        $pid = (int)($_POST['product_id'] ?? 0);
        $p = db_one('SELECT productImg FROM products WHERE productId = ?', 'i', [$pid]);
        if ($p) {
            db_exec('DELETE FROM products WHERE productId = ?', 'i', [$pid]);   // cart rows cascade, order history keeps its snapshot
            delete_product_image($p['productImg']);
            flash('success', 'Product deleted.');
        }
        header('Location: admin-products.php');
        exit;
    }

    /* ----- add / edit ----- */
    if ($action === 'save') {
        $pid   = (int)($_POST['product_id'] ?? 0);
        $name  = trim($_POST['name'] ?? '');
        $cat   = $_POST['category'] ?? '';
        $price = filter_var($_POST['price'] ?? '', FILTER_VALIDATE_INT);
        $stock = filter_var($_POST['stock'] ?? '', FILTER_VALIDATE_INT);
        $desc  = trim($_POST['description'] ?? '');
        $errors = [];

        if ($name === '' || mb_strlen($name) > 255) $errors[] = 'Product name is required (max 255 characters).';
        if (!in_array($cat, CATEGORIES, true))        $errors[] = 'Please choose a valid category.';
        if ($price === false || $price < 1)           $errors[] = 'Price must be a whole number of at least 1.';
        if ($stock === false || $stock < 0)           $errors[] = 'Stock must be 0 or more.';
        if ($desc === '' || mb_strlen($desc) > 2000)  $errors[] = 'Description is required (max 2000 characters).';

        $old = $pid ? db_one('SELECT * FROM products WHERE productId = ?', 'i', [$pid]) : null;
        if ($pid && !$old) $errors[] = 'Product not found.';

        $imgErr = '';
        $newImg = save_product_image($_FILES['image'] ?? ['error' => UPLOAD_ERR_NO_FILE], $imgErr);
        if ($imgErr !== '') $errors[] = $imgErr;
        if (!$pid && $newImg === null && !$errors) $errors[] = 'Please upload a product image.';

        if ($errors) {
            if ($newImg) delete_product_image($newImg);
            $_SESSION['product_form'] = ['id' => $pid, 'name' => $name, 'category' => $cat, 'price' => $_POST['price'] ?? '',
                                         'stock' => $_POST['stock'] ?? '', 'description' => $desc];
            flash('error', implode(' ', $errors));
            header('Location: admin-products.php' . ($pid ? '?edit=' . $pid : '') . '#form');
            exit;
        }

        if ($pid) {
            $img = $newImg ?? $old['productImg'];
            db_exec(
                'UPDATE products SET productName=?, productCategory=?, productPrice=?, productImg=?, productDescription=?, productStock=? WHERE productId=?',
                'ssissii',
                [$name, $cat, $price, $img, $desc, $stock, $pid]
            );
            if ($newImg) delete_product_image($old['productImg']);
            flash('success', 'Product updated.');
        } else {
            db_exec(
                'INSERT INTO products (productName, productCategory, productPrice, productImg, productDescription, productStock) VALUES (?,?,?,?,?,?)',
                'ssissi',
                [$name, $cat, $price, $newImg, $desc, $stock]
            );
            flash('success', 'Product added.');
        }
        header('Location: admin-products.php');
        exit;
    }
}

$edit = null;
if (isset($_GET['edit'])) {
    $edit = db_one('SELECT * FROM products WHERE productId = ?', 'i', [(int)$_GET['edit']]);
}
$f = ['id' => 0, 'name' => '', 'category' => 'Keyboard', 'price' => '', 'stock' => '', 'description' => ''];
if ($edit) {
    $f = ['id' => $edit['productId'], 'name' => $edit['productName'], 'category' => $edit['productCategory'], 'price' => $edit['productPrice'],
          'stock' => $edit['productStock'], 'description' => $edit['productDescription']];
}
if (!empty($_SESSION['product_form'])) {   // re-fill the form after a validation error
    $f = array_merge($f, $_SESSION['product_form']);
    unset($_SESSION['product_form']);
}

$q = trim($_GET['q'] ?? '');
$list = $q !== ''
    ? db_all('SELECT * FROM products WHERE productName LIKE ? OR productCategory LIKE ? ORDER BY productCategory, productPrice', 'ss', ['%' . $q . '%', '%' . $q . '%'])
    : db_all('SELECT * FROM products ORDER BY productCategory, productPrice');

$adminActive = 'products';
$pageTitle = 'QCera | Products & Stock';
include 'partials/admin_top.php';
?>

    <main class="admin-page">
        <div class="admin-wrap">

            <div class="page-head">
                <h1>PRODUCTS &amp; <span>STOCK</span></h1>
                <p>Add products, upload pictures and adjust stock</p>
            </div>

            <?= flash_html() ?>

            <div class="card" id="form">
                <h2><i class="fas fa-<?= $f['id'] ? 'pen' : 'plus' ?>"></i><?= $f['id'] ? 'Edit Product' : 'Add New Product' ?></h2>

                <form method="POST" action="admin-products.php" enctype="multipart/form-data">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="save">
                    <input type="hidden" name="product_id" value="<?= (int)$f['id'] ?>">

                    <div class="form-grid">
                        <div class="form-group">
                            <label for="name">Product Name</label>
                            <input type="text" id="name" name="name" value="<?= h($f['name']) ?>" maxlength="255" required>
                        </div>
                        <div class="form-group">
                            <label for="category">Category</label>
                            <select id="category" name="category">
                                <?php foreach (CATEGORIES as $c): ?>
                                    <option value="<?= h($c) ?>" <?= $f['category'] === $c ? 'selected' : '' ?>><?= h($c) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="price">Price (₱)</label>
                            <input type="number" id="price" name="price" value="<?= h($f['price']) ?>" min="1" step="1" required>
                        </div>
                        <div class="form-group">
                            <label for="stock">Stock</label>
                            <input type="number" id="stock" name="stock" value="<?= h($f['stock']) ?>" min="0" step="1" required>
                        </div>
                        <div class="form-group full">
                            <label for="description">Description</label>
                            <textarea id="description" name="description" maxlength="2000" required><?= h($f['description']) ?></textarea>
                        </div>
                        <div class="form-group full">
                            <label for="image">Product Picture <?= $f['id'] ? '(leave empty to keep the current one)' : '' ?></label>
                            <input type="file" id="image" name="image" accept="image/jpeg,image/png,image/webp,image/gif" <?= $f['id'] ? '' : 'required' ?>>
                            <p class="form-hint">JPG, PNG, WEBP or GIF, up to 5 MB. Saved automatically into <b>imgvid/products/</b>.</p>
                            <?php if ($edit): ?><img src="<?= h($edit['productImg']) ?>" alt="" class="thumb" style="margin-top:8px;"><?php endif; ?>
                        </div>
                    </div>

                    <div class="row-actions">
                        <button type="submit" class="btn btn-primary"><?= $f['id'] ? 'SAVE CHANGES' : 'ADD PRODUCT' ?></button>
                        <?php if ($f['id']): ?><a href="admin-products.php" class="btn btn-outline">CANCEL</a><?php endif; ?>
                    </div>
                </form>
            </div>

            <div class="card">
                <div class="toolbar">
                    <h2 style="margin:0;"><i class="fas fa-boxes-stacked"></i>All Products (<?= count($list) ?>)</h2>
                    <form method="GET" action="admin-products.php">
                        <input type="text" name="q" value="<?= h($q) ?>" placeholder="Search name or category">
                        <button class="btn btn-outline btn-sm" type="submit">SEARCH</button>
                    </form>
                </div>

                <div class="table-wrap">
                    <table class="data">
                        <thead>
                            <tr><th></th><th>PRODUCT</th><th class="num">PRICE</th><th>STOCK</th><th>ADJUST STOCK</th><th>ACTIONS</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ($list as $p): $s = (int)$p['productStock']; ?>
                                <tr>
                                    <td><img class="thumb" src="<?= h($p['productImg']) ?>" alt=""></td>
                                    <td><b><?= h($p['productName']) ?></b><br><span class="muted"><?= h($p['productCategory']) ?> · ID <?= (int)$p['productId'] ?></span></td>
                                    <td class="num"><?= peso($p['productPrice']) ?></td>
                                    <td>
                                        <b><?= $s ?></b><br>
                                        <span class="badge <?= $s <= 0 ? 'badge-out' : ($s <= 5 ? 'badge-low' : 'badge-ok') ?>"><?= $s <= 0 ? 'Out of stock' : ($s <= 5 ? 'Low' : 'In stock') ?></span>
                                    </td>
                                    <td>
                                        <form method="POST" action="admin-products.php" class="stock-form">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="action" value="stock">
                                            <input type="hidden" name="mode" value="set">
                                            <input type="hidden" name="product_id" value="<?= (int)$p['productId'] ?>">
                                            <input type="number" name="value" value="<?= $s ?>" min="0" aria-label="Set stock">
                                            <button class="btn btn-outline btn-sm" type="submit">SET</button>
                                        </form>
                                        <form method="POST" action="admin-products.php" class="stock-form">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="action" value="stock">
                                            <input type="hidden" name="mode" value="add">
                                            <input type="hidden" name="product_id" value="<?= (int)$p['productId'] ?>">
                                            <input type="number" name="value" value="10" min="0" aria-label="Add stock">
                                            <button class="btn btn-primary btn-sm" type="submit">+ ADD</button>
                                        </form>
                                    </td>
                                    <td>
                                        <div class="row-actions">
                                            <a class="btn btn-outline btn-sm" href="admin-products.php?edit=<?= (int)$p['productId'] ?>#form">EDIT</a>
                                            <form method="POST" action="admin-products.php" onsubmit="return confirm('Delete this product? Past orders keep their history.');">
                                                <?= csrf_field() ?>
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="product_id" value="<?= (int)$p['productId'] ?>">
                                                <button class="btn btn-danger btn-sm" type="submit">DELETE</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </main>

<?php include 'partials/site_footer.php'; ?>
</body>

</html>
