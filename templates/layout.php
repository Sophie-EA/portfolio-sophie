<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?= $title ?? 'Portfolio Sophie El Asry' ?></title>
    <link rel="stylesheet" href="/public/css/base.css">
    <link rel="stylesheet" href="/public/css/components.css">
    <link rel="stylesheet" href="/public/css/sections.css">
    <link rel="stylesheet" href="/public/css/responsive.css">

    <?= $extra_css ?? '' ?>
</head>
<body>
    <?php include __DIR__ . '/partials/header.php'; ?>
    
    <main>
        <?= $content ?? '' ?>
    </main>

    <?php include __DIR__ . '/partials/footer.php'; ?>
    <script src="/public/JS/index.js"></script>
    <?= $extra_js ?? '' ?>
</body>
</html>
