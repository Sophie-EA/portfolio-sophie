<?php
declare(strict_types=1);

require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/Template.php';

// ---------- 1. Récupération du projet ----------

$slug = $_GET['slug'] ?? null;
$id   = isset($_GET['id']) ? (int)$_GET['id'] : null;

if (!$slug && !$id) {
    header('Location: /');
    exit;
}

if ($slug) {
    $stmt = $db->prepare("SELECT * FROM projects WHERE slug = ?");
    $stmt->execute([$slug]);
} else {
    $stmt = $db->prepare("SELECT * FROM projects WHERE id = ?");
    $stmt->execute([$id]);
}

$project = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$project) {
    http_response_code(404);
    echo "Projet non trouvé.";
    exit;
}

// ---------- 2. Routage vers le bon template ----------



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
if (!empty($project['has_custom_assets']) && !empty($project['slug'])) {
    $css_path = "/public/projects/{$project['slug']}/css/game.css";
    if (file_exists(__DIR__ . $css_path)) {
        echo '<link rel="stylesheet" href="' . $css_path . '">';
    }
}
$template->endSection();

// ---------- 4. Contenu principal ----------
$template->section('content');

$isCustom = !empty($project['has_custom_assets']) && !empty($project['slug']);

if ($isCustom) {
    // Template spécifique (Sokoban, etc.)
    $custom_template = __DIR__ . "/templates/projects/{$project['slug']}.php";
    if (file_exists($custom_template)) {
        include $custom_template;
    } else {
        echo '<p>Template spécifique en cours de développement...</p>';
        echo '<a href="/index.php#projects" class="btn-retour">← Retour aux projets</a>';
    }
} else {
    // Template standard (RebootTech, Portfolio...)
    include __DIR__ . '/templates/projects/standard.php';
}

// Lightbox (nécessaire pour tous les projets qui ont une galerie ou des images cliquables)
?>
<div id="lightbox" class="lightbox">
    <span class="lightbox-close">&times;</span>
    <img class="lightbox-img" src="" alt="">
    <div class="lightbox-caption"></div>
</div>

<?php
$template->endSection();

// ---------- 5. JS en fin de page ----------
$template->section('extra_js');
if ($isCustom) {
    $js_path = "/public/projects/{$project['slug']}/js/game.js";
    if (file_exists(__DIR__ . $js_path)) {
        echo '<script src="' . $js_path . '"></script>';
    }
} else {
    // Lightbox + effets galerie pour les projets standards
    echo '<script src="/public/js/galerie.js"></script>';
}
$template->endSection();

// ---------- 6. Rendu ----------
$template->render();
