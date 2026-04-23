<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?= $title ?? 'Portfolio Sophie El Asry' ?></title>
    <link rel="stylesheet" href="/public/CSS/style.css" />
</head>
<body>
    <!-- Header -->
    
    <?php include __DIR__ . '/partials/header.php'; ?>
    
    <main>
        <?= $content ?? '' ?>
    </main>
    
    <!-- Footer -->
    <?php include __DIR__ . '/partials/footer.php'; ?>
    
    <script src="/public/JS/index.js"></script>
</body>
</html>
