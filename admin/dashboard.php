<?php
require_once 'includes/auth.php';
require_once '../config/db.php';

// Récupération des projets
$stmt = $db->query("SELECT id, title, created_at FROM projects ORDER BY created_at DESC");
$projects = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Message flash
$message = $_SESSION['message'] ?? '';
unset($_SESSION['message']);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Admin - Dashboard</title>
    <style>
        body { font-family: Arial; max-width: 900px; margin: 0 auto; padding: 20px; }
        .success { background: #d4edda; color: #155724; padding: 10px; margin-bottom: 20px; border-radius: 4px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #f2f2f2; }
        .btn { padding: 6px 12px; text-decoration: none; color: white; border-radius: 4px; font-size: 14px; }
        .btn-add { background: #28a745; }
        .btn-edit { background: #ffc107; color: #000; }
        .btn-delete { background: #dc3545; }
        .logout { float: right; color: #666; }
    </style>
</head>
<body>
    <a href="../logout.php" class="logout">Déconnexion</a>
    <h1>Dashboard Admin</h1>
    <p>Bienvenue <?= htmlspecialchars($_SESSION['admin_name']) ?> !</p>
    
    <?php if ($message): ?>
        <div class="success"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>
    
    <a href="add-project.php" class="btn btn-add">+ Ajouter un projet</a>
    
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
                <td><?= date('d/m/Y', strtotime($project['created_at'])) ?></td>
                <td>
                    <a href="edit-project.php?id=<?= $project['id'] ?>" class="btn btn-edit">Modifier</a>
                    <a href="delete-project.php?id=<?= $project['id'] ?>" 
                       class="btn btn-delete" 
                       onclick="return confirm('Supprimer ce projet ?')">Supprimer</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>
