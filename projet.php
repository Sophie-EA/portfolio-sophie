<?php
require_once 'config/db.php';
require_once 'includes/Template.php';

// Récupération du projet
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$slug = isset($_GET['slug']) ? $_GET['slug'] : '';

if ($id) {
    $stmt = $db->prepare("SELECT * FROM projects WHERE id = ?");
    $stmt->execute([$id]);
} elseif ($slug) {
    $stmt = $db->prepare("SELECT * FROM projects WHERE slug = ?");
    $stmt->execute([$slug]);
} else {
    header('Location: /index.php');
    exit;
}

$project = $stmt->fetch();

if (!$project) {
    header('Location: /index.php');
    exit;
}

$template = new Template(__DIR__ . '/templates/layout.php');

$template->section('title');
echo htmlspecialchars($project['title']) . " - Portfolio";
$template->endSection();

// CSS spécifique si projet avec assets custom
$template->section('extra_css');
if ($project['has_custom_assets']) {
    $project_path = "/public/projects/{$project['slug']}/css/game.css";
    if (file_exists(__DIR__ . $project_path)) {
        echo '<link rel="stylesheet" href="' . $project_path . '">';
    }
}
$template->endSection();

$template->section('content');

// Si projet standard (texte + image)
if (!$project['has_custom_assets'] || $project['slug'] === null) {
    ?>
    <article class="projet-detail standard">
        <h1><?= htmlspecialchars($project['title']) ?></h1>
        
        <img src="/public/images/<?= htmlspecialchars($project['image']) ?>" 
             alt="<?= htmlspecialchars($project['title']) ?>" />
        
        <div class="description">
            <?= nl2br(htmlspecialchars($project['description'])) ?>
        </div>
        
        <div class="meta">
            <p><strong>Technologies :</strong> <?= htmlspecialchars($project['technologies']) ?></p>
            <div class="links">
                <?php if (!empty($project['github_url'])): ?>
                    <a href="<?= htmlspecialchars($project['github_url']) ?>" target="_blank" class="btn">GitHub</a>
                <?php endif; ?>
                <?php if (!empty($project['demo_url'])): ?>
                    <a href="<?= htmlspecialchars($project['demo_url']) ?>" target="_blank" class="btn">Démo externe</a>
                <?php endif; ?>
            </div>
        </div>
    </article>
    <?php
} 
// Si projet avec template custom (comme Sokoban)
else {
    // Inclusion dynamique du template spécifique
    $custom_template = __DIR__ . "/templates/projects/{$project['slug']}.php";
    
    if (file_exists($custom_template)) {
        // Extrait les variables pour le template inclus
        extract(['project' => $project, 'db' => $db]);
        include $custom_template;
    } else {
        // Fallback si template manquant
        echo "<p>Template spécifique en cours de développement...</p>";
    }
}
?>

<a href="/index.php#projects" class="retour">← Retour aux projets</a>

<?php
$template->endSection();

// JS spécifique en fin de page
$template->section('extra_js');
if ($project['has_custom_assets']) {
    $js_path = "/public/projects/{$project['slug']}/js/game.js";
    if (file_exists(__DIR__ . $js_path)) {
        echo '<script src="' . $js_path . '"></script>';
    }
}
$template->endSection();

$template->render();
?>
