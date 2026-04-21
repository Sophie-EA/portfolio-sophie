<?php
require_once 'includes/auth.php';
require_once '../config/db.php';

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupération des données
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $technologies = trim($_POST['technologies'] ?? '');
    $github_url = trim($_POST['github_url'] ?? '');
    $demo_url = trim($_POST['demo_url'] ?? '');
    
    // Validation
    if (empty($title)) $errors[] = "Le titre est obligatoire";
    if (empty($description)) $errors[] = "La description est obligatoire";
    
    // Gestion de l'image
    $image_name = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $filename = $_FILES['image']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        if (!in_array($ext, $allowed)) {
            $errors[] = "Format d'image non autorisé (jpg, png, gif, webp uniquement)";
        } elseif ($_FILES['image']['size'] > 2 * 1024 * 1024) { // 2Mo max
            $errors[] = "L'image est trop lourde (max 2Mo)";
        } else {
            // Nom sécurisé : timestamp + nom original nettoyé
            $image_name = time() . '_' . preg_replace('/[^a-zA-Z0-9.-]/', '_', $filename);
            $upload_dir = '../public/images/';
            
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            
            move_uploaded_file($_FILES['image']['tmp_name'], $upload_dir . $image_name);
        }
    }
    
    // Si pas d'erreurs, insertion
    if (empty($errors)) {
        $stmt = $db->prepare("INSERT INTO projects (title, description, technologies, image, github_url, demo_url) 
                              VALUES (?, ?, ?, ?, ?, ?)");
        
        if ($stmt->execute([$title, $description, $technologies, $image_name, $github_url, $demo_url])) {
            $_SESSION['message'] = "Projet ajouté avec succès !";
            header('Location: dashboard.php');
            exit;
        } else {
            $errors[] = "Erreur lors de l'ajout en base de données";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Ajouter un projet</title>
    <style>
        body { font-family: Arial; max-width: 700px; margin: 0 auto; padding: 20px; }
        .error { background: #f8d7da; color: #721c24; padding: 10px; margin-bottom: 15px; border-radius: 4px; }
        .error ul { margin: 0; }
        label { display: block; margin-top: 15px; font-weight: bold; }
        input, textarea { width: 100%; padding: 8px; margin-top: 5px; border: 1px solid #ddd; border-radius: 4px; }
        textarea { min-height: 150px; font-family: inherit; }
        button { margin-top: 20px; padding: 10px 20px; background: #28a745; color: white; border: none; cursor: pointer; border-radius: 4px; }
        .back { color: #666; text-decoration: none; }
    </style>
</head>
<body>
    <a href="dashboard.php" class="back">← Retour au dashboard</a>
    <h1>Ajouter un projet</h1>
    
    <?php if (!empty($errors)): ?>
        <div class="error">
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?= htmlspecialchars($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>
    
    <form method="POST" enctype="multipart/form-data">
        <label>Titre du projet *</label>
        <input type="text" name="title" required value="<?= htmlspecialchars($_POST['title'] ?? '') ?>">
        
        <label>Description *</label>
        <textarea name="description" required placeholder="Décris ton projet, ton rôle, les défis..."><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
        <small>Utilise des retours à la ligne pour structurer ton texte</small>
        
        <label>Technologies utilisées</label>
        <input type="text" name="technologies" placeholder="Ex: Figma, HTML, CSS, JavaScript" value="<?= htmlspecialchars($_POST['technologies'] ?? '') ?>">
        
        <label>Image du projet</label>
        <input type="file" name="image" accept="image/*">
        <small>Format : jpg, png, gif, webf (max 2Mo)</small>
        
        <label>URL GitHub (optionnel)</label>
        <input type="url" name="github_url" placeholder="https://github.com/..." value="<?= htmlspecialchars($_POST['github_url'] ?? '') ?>">
        
        <label>URL Démo / Figma (optionnel)</label>
        <input type="url" name="demo_url" placeholder="https://www.figma.com/..." value="<?= htmlspecialchars($_POST['demo_url'] ?? '') ?>">
        
        <button type="submit">Ajouter le projet</button>
    </form>
</body>
</html>
