<?php
require_once 'config/db.php';
require_once 'includes/Template.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: /index.php');
    exit;
}

$id = (int) $_GET['id'];
$stmt = $db->prepare("SELECT * FROM projects WHERE id = ?");
$stmt->execute([$id]);
$project = $stmt->fetch();

if (!$project) {
    header('Location: /index.php');
    exit;
}

$template = new Template();

$template->section('title');
echo htmlspecialchars($project['title']) . " - Portfolio";
$template->endSection();

$template->section('content');
?>

<article class="projet-detail">
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
                <a href="<?= htmlspecialchars($project['demo_url']) ?>" target="_blank" class="btn">Démo</a>
            <?php endif; ?>
        </div>
    </div>
    
    <a href="/index.php#projects" class="retour">← Retour aux projets</a>
</article>

<?php
$template->endSection();
$template->render();
