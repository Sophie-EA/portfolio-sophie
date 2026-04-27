<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?= $title ?? 'Portfolio Sophie El Asry' ?></title>

    <!-- 1. Base (variables + reset indispensables) -->
    <link rel="stylesheet" href="/public/css/base.css">
    
    <!-- 2. Composants (cards, boutons) -->
    <link rel="stylesheet" href="/public/css/components.css">
    
    <!-- 3. Sections spécifiques -->
    <link rel="stylesheet" href="/public/css/sections.css">
    
    <!-- 4. Responsive en dernier pour écraser les règles -->
    <link rel="stylesheet" href="/public/css/responsive.css">

        <!-- Injection CSS spécifique à la page -->
    <?= $extra_css ?? '' ?>
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
    <?= $extra_js ?? '' ?>
</body>
</html>
