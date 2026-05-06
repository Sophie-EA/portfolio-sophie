<?php
require_once 'includes/auth.php';

// Récupération des projets
$stmt = $db->prepare("SELECT id, title, project_date FROM projects ORDER BY project_date DESC");
$stmt->execute();
$projects = $stmt->fetchAll(PDO::FETCH_ASSOC);

$csrf = generateToken();
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Admin - Dashboard</title>
    <style>
        body { font-family: Arial; max-width: 900px; margin: 0 auto; padding: 20px; }
        .success { background: #d4edda; color: #155724; padding: 10px; margin-bottom: 20px; border-radius: 4px; }
        .warning { background: #fff3cd; color: #856404; padding: 10px; margin-bottom: 20px; border-radius: 4px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #f2f2f2; }
        .btn { padding: 6px 12px; text-decoration: none; color: white; border-radius: 4px; font-size: 14px; border: none; cursor: pointer; }
        .btn-add { background: #28a745; }
        .btn-edit { background: #ffc107; color: #000; }
        .btn-delete { background: #dc3545; }
        .logout { float: right; color: #666; }
    </style>
</head>
<body>
    <a href="../logout.php" class="logout">Déconnexion</a>
    <h1>Dashboard Admin</h1>
    <p>Bienvenue <?= htmlspecialchars($_SESSION['admin_name'] ?? 'Admin') ?> !</p>

    <?php if ($flash): ?>
        <div class="<?= $flash['type'] ?>"><?= htmlspecialchars($flash['message']) ?></div>
    <?php endif; ?>

    <a href="add-project.php" class="btn btn-add">+ Ajouter un projet</a>
    <a href="messages.php" class="btn" style="background:#17a2b8; margin-left:10px;">
        📬 Messages (<?= $db->query("SELECT COUNT(*) FROM contacts")->fetchColumn() ?>)
    </a>

    <table>
        <thead>
            <tr>
                <th>Titre</th>
                <th>Date de création</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($projects as $project): ?>
            <tr>
                <td><?= htmlspecialchars($project['title']) ?></td>
                <td><?= date('d/m/Y', strtotime($project['project_date'])) ?></td>
                <td>
                    <a href="edit-project.php?id=<?= $project['id'] ?>" class="btn btn-edit">Modifier</a>

                    <form method="POST" action="delete-project.php" style="display:inline;" 
                          onsubmit="return confirm('Supprimer définitivement ce projet et ses images ?');">
                        <input type="hidden" name="id" value="<?= $project['id'] ?>">
                        <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
                        <button type="submit" class="btn btn-delete">Supprimer</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>
