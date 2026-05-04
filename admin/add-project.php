<?php
require_once 'includes/auth.php';

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? '';
    if (!verifyToken($token)) {
        die('Token invalide. Rechargez la page.');
    }

    // --- Données texte ---
    $title = trim($_POST['title'] ?? '');
    $slug  = trim($_POST['slug'] ?? '');
    $description = $_POST['description'] ?? '';
    $short_description = trim($_POST['short_description'] ?? '');
    $technologies = trim($_POST['technologies'] ?? '');
    
    $github_url = filter_input(INPUT_POST, 'github_url', FILTER_VALIDATE_URL);
    $github_url = ($github_url !== false) ? $github_url : null;
    
    $demo_url = filter_input(INPUT_POST, 'demo_url', FILTER_VALIDATE_URL);
    $demo_url = ($demo_url !== false) ? $demo_url : null;

    // --- Validation ---
    if (empty($title) || empty($slug)) {
        $errors[] = "Le titre et le slug sont obligatoires.";
    }

    // Vérifier slug unique
    $check = $db->prepare("SELECT id FROM projects WHERE slug = ?");
    $check->execute([$slug]);
    if ($check->fetch()) {
        $errors[] = "Ce slug existe déjà.";
    }

    if (empty($errors)) {
        $hero_filename = null;
        $uploadDir = __DIR__ . '/../public/images/projects/';

        // 1. Hero image
        if (!empty($_FILES['hero_image']['tmp_name']) && $_FILES['hero_image']['error'] === UPLOAD_ERR_OK) {
            $ext = strtolower(pathinfo($_FILES['hero_image']['name'], PATHINFO_EXTENSION));
            $allowed = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
            if (in_array($ext, $allowed)) {
                $hero_filename = 'hero_' . bin2hex(random_bytes(4)) . '.' . $ext;
                move_uploaded_file($_FILES['hero_image']['tmp_name'], $uploadDir . $hero_filename);
            }
        }

        // 2. Insertion projet
        $stmt = $db->prepare("
            INSERT INTO projects 
            (title, slug, description, short_description, technologies, github_url, demo_url, image, created_at) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ");
        $stmt->execute([
            $title, $slug, $description, $short_description, 
            $technologies, $github_url, $demo_url, $hero_filename
        ]);

        $project_id = $db->lastInsertId();

        // 3. Images de galerie (NOUVEAU)
        if (!empty($_FILES['gallery']['tmp_name'][0])) {
            foreach ($_FILES['gallery']['tmp_name'] as $index => $tmpName) {
                if ($_FILES['gallery']['error'][$index] === UPLOAD_ERR_OK) {
                    $ext = strtolower(pathinfo($_FILES['gallery']['name'][$index], PATHINFO_EXTENSION));
                    $allowed = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
                    if (in_array($ext, $allowed)) {
                        $filename = 'gallery_' . $project_id . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
                        if (move_uploaded_file($tmpName, $uploadDir . $filename)) {
                            // Adapter le nom de ta table/colonnes si besoin
                            $stmtImg = $db->prepare("
                                INSERT INTO project_images (project_id, image_path, alt_text, display_order) 
                                VALUES (?, ?, ?, 1)
                            ");
                            $stmtImg->execute([$project_id, $filename, '']);
                        }
                    }
                }cd c:/
            }
        }

        flashMessage('success', 'Projet et galerie créés avec succès !');
        header('Location: dashboard.php');
        exit;
    }
}

$csrf = generateToken();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Ajouter un projet</title>
    <style>
        body { font-family: Arial; max-width: 700px; margin: 0 auto; padding: 20px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; font-weight: bold; margin-bottom: 5px; }
        input, textarea, select { width: 100%; padding: 8px; }
        .btn { background: #28a745; color: white; padding: 10px 20px; border: none; cursor: pointer; }
        .error { color: #dc3545; background: #f8d7da; padding: 10px; border-radius: 4px; margin-bottom: 15px; }
        small { color: #666; }
    </style>
</head>
<body>

    <h1>➕ Ajouter un projet</h1>
    <a href="dashboard.php">← Retour</a>

    <?php if (!empty($errors)): ?>
        <div class="error"><?= implode('<br>', array_map('htmlspecialchars', $errors)) ?></div>
    <?php endif; ?>

    <!-- ATTENTION : enctype="multipart/form-data" est OBLIGATOIRE -->
    <form method="POST" action="" enctype="multipart/form-data">
        <input type="hidden" name="csrf_token" value="<?= $csrf ?>">

        <div class="form-group">
            <label>Titre</label>
            <input type="text" name="title" required>
        </div>

        <div class="form-group">
            <label>Slug (unique, sans espace)</label>
            <input type="text" name="slug" required placeholder="mon-super-projet">
        </div>

        <div class="form-group">
            <label>Description courte</label>
            <input type="text" name="short_description">
        </div>

        <div class="form-group">
            <label>Description complète</label>
            <textarea name="description" rows="5"></textarea>
        </div>

        <div class="form-group">
            <label>Technologies</label>
            <input type="text" name="technologies" placeholder="PHP, MySQL, JS...">
        </div>

        <div class="form-group">
            <label>URL GitHub</label>
            <input type="url" name="github_url">
        </div>

        <div class="form-group">
            <label>URL Démo</label>
            <input type="url" name="demo_url">
        </div>

        <div class="form-group">
            <label>Image principale (Hero)</label>
            <input type="file" name="hero_image" accept="image/*">
        </div>

        <!-- NOUVEAU CHAMP GALERIE -->
        <div class="form-group">
            <label>Images de la galerie</label>
            <input type="file" name="gallery[]" multiple accept="image/*">
            <small>Maintenez Ctrl pour sélectionner plusieurs images</small>
        </div>

        <button type="submit" class="btn">Créer le projet</button>
    </form>

</body>
</html>
