<?php
// expects: $css (array of stylesheet names in /styles), optional $pageTitle
$css = $css ?? [];
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title><?= h($pageTitle ?? 'QCera') ?></title>

<?php foreach ($css as $cssFile): ?>
    <link rel="stylesheet" href="styles/<?= h($cssFile) ?>.css">
<?php endforeach; ?>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.3.1/css/all.min.css">
    <link rel="stylesheet" href="styles/responsive.css">
</head>

<body>
