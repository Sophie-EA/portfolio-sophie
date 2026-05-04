<?php
require_once 'includes/auth.php';

// Récupération
$stmt = $db->prepare("SELECT * FROM contacts ORDER BY created_at DESC");
$stmt->execute();
$messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

$csrf = generateToken();
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Messages reçus</title>
    <style>
        body { font-family: Arial; max-width: 1000px; margin: 0 auto; padding: 20px; }
        .success { background: #d4edda; color: #155724; padding: 10px; border-radius: 4px; margin-bottom: 15px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 10px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #f2f2f2; }
        .btn-delete { background: #dc3545; color: white; border: none; padding: 4px 10px; border-radius: 3px; cursor: pointer; }
        .msg-preview { max-width: 300px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .back { color: #666; text-decoration: none; }
    </style>
</head>
<body>
    <a href="dashboard.php" class="back">← Retour au Dashboard</a>
    <h1>📬 Messages de contact (<?= count($messages) ?>)</h1>

    <?php if ($flash): ?>
        <div class="success"><?= htmlspecialchars($flash['message']) ?></div>
    <?php endif; ?>

    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Nom</th>
                <th>Email</th>
                <th>Sujet</th>
                <th>Message</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($messages) === 0): ?>
                <tr><td colspan="6"><em>Aucun message pour l'instant.</em></td></tr>
            <?php else: ?>
                <?php foreach ($messages as $msg): ?>
                <tr>
                    <td><?= date('d/m/Y H:i', strtotime($msg['created_at'])) ?></td>
                    <td><?= htmlspecialchars((string)$msg['name']) ?></td>
                    <td><a href="mailto:<?= htmlspecialchars((string)$msg['email']) ?>"><?= htmlspecialchars((string)$msg['email']) ?></a></td>
                    <td><?= htmlspecialchars((string)$msg['subject']) ?></td>
                    <td class="msg-preview" title="<?= htmlspecialchars((string)$msg['message']) ?>">
                        <?= htmlspecialchars((string)$msg['message']) ?>
                    </td>
                    <td>
                        <form method="POST" action="delete-message.php" style="display:inline;" onsubmit="return confirm('Supprimer ce message ?')">
                            <input type="hidden" name="id" value="<?= (int)$msg['id'] ?>">
                            <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
                            <button type="submit" class="btn-delete">Supprimer</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</body>
</html>
