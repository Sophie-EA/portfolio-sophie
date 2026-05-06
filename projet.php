<?php
declare(strict_types=1);

require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/Template.php';

// ---------- 1. Récupération du projet ----------
$slug = $_GET['slug'] ?? null;

if (empty($slug)) {
    header('Location: /index.php#projets');
    exit;
}

$stmt = $db->prepare("SELECT * FROM projects WHERE slug = ?");
$stmt->execute([$slug]);
$project = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$project) {
    header('Location: /index.php#projets');
    exit;
}

// ---------- 2. Images de la galerie ----------
$stmtImages = $db->prepare("
    SELECT image_path, alt_text 
    FROM project_images 
    WHERE project_id = ? 
    ORDER BY display_order ASC
");
$stmtImages->execute([$project['id']]); 
$galleryImages = $stmtImages->fetchAll(PDO::FETCH_ASSOC);

// ---------- 3. Template Engine ----------
$template = new Template(__DIR__ . '/templates/layout.php');

// Title
$template->section('title');
echo htmlspecialchars($project['title']) . " - Portfolio";
$template->endSection();

// CSS spécifique projet
$template->section('extra_css');
$isCustom = !empty($project['has_custom_assets']);

if ($isCustom) {
    $cssDisk = __DIR__ . "/public/projects/{$project['slug']}/css/game.css";
    if (file_exists($cssDisk)) {
        echo '<link rel="stylesheet" href="/public/projects/' . urlencode($project['slug']) . '/css/game.css">';
    }
}
$template->endSection();

// ---------- 4. Contenu principal ----------
$template->section('content');

if ($isCustom) {
    $customTemplate = __DIR__ . "/templates/projects/{$project['slug']}.php";
    if (file_exists($customTemplate)) {
        include $customTemplate;
    } else {
        echo '<p>Template spécifique en cours de développement...</p>';
        echo '<a href="/index.php#projets" class="btn-retour">← Retour aux projets</a>';
    }
} else {
    include __DIR__ . '/templates/projects/standard.php';
}

if (!empty($galleryImages)) {
    ?>
    <div id="lightbox" class="lightbox">
        <span class="lightbox-close">&times;</span>
        <img class="lightbox-img" src="" alt="">
        <div class="lightbox-caption"></div>
    </div>
    <?php
}

$template->endSection();

// ---------- 5. JS ----------
$template->section('extra_js');

if ($isCustom) {
    $jsDisk = __DIR__ . "/public/projects/{$project['slug']}/js/game.js";
    if (file_exists($jsDisk)) {
        echo '<script src="/public/projects/' . urlencode($project['slug']) . '/js/game.js"></script>';
    }
}

if (!empty($galleryImages)) {
    echo '<script src="/public/js/galerie.js"></script>';
}

$template->endSection();

// ---------- 6. Rendu ----------
$template->render();
