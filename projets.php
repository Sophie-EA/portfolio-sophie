<?php
require_once 'config/db.php';

$query = $db->query("SELECT * FROM projects ORDER BY created_at DESC");
$projects = $query->fetchAll(PDO::FETCH_ASSOC);
?>

<h1>Mes projets</h1>

<div class="projects">
<?php foreach ($projects as $project): ?>
    <article>
        <h2><?= htmlspecialchars($project['title']) ?></h2>
        <p><?= htmlspecialchars($project['technologies']) ?></p>

        <a href="projet.php?id=<?= $project['id'] ?>">
            Voir le projet
        </a>
    </article>
<?php endforeach; ?>
</div>
