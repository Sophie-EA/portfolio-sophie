<?php
require_once 'includes/auth.php';

$errors = [];
// $success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? '';
    if (!verifyToken($token)) {
        die('Token invalide. Rechargez la page.');
    }

    // --- Données texte ---
    $title             = trim($_POST['title'] ?? '');
    $slug              = trim($_POST['slug'] ?? '');
    $description       = $_POST['description'] ?? '';
    $short_description = trim($_POST['short_description'] ?? '');
    $technologies      = trim($_POST['technologies'] ?? '');
    $project_date      = $_POST['project_date'] ?? null;
    $has_custom_assets = isset($_POST['has_custom_assets']) ? 1 : 0;

    $github_url = filter_input(INPUT_POST, 'github_url', FILTER_VALIDATE_URL);
    $github_url = ($github_url !== false && $github_url !== null) ? $github_url : null;

    $demo_url = filter_input(INPUT_POST, 'demo_url', FILTER_VALIDATE_URL);
    $demo_url = ($demo_url !== false && $demo_url !== null) ? $demo_url : null;

    // --- Validation ---
    if (empty($title) || empty($slug)) {
        $errors[] = "Le titre et le slug sont obligatoires.";
    }
    if (empty($project_date)) {
        $errors[] = "La date de réalisation est obligatoire.";
    }
    if (empty($description)) {
        $errors[] = "La description est obligatoire.";
    }

    // Vérifier slug unique
    $check = $db->prepare("SELECT id FROM projects WHERE slug = ?");
    $check->execute([$slug]);
    if ($check->fetch()) {
        $errors[] = "Ce slug existe déjà.";
    }

    // --- Upload Hero (obligatoire en création) ---
    $hero_filename = null;
    $uploadDir = __DIR__ . '/../public/images/projects/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    if (!empty($_FILES['hero_image']['tmp_name']) && $_FILES['hero_image']['error'] === UPLOAD_ERR_OK) {
        $imageInfo = getimagesize($_FILES['hero_image']['tmp_name']);
        if ($imageInfo === false) {
            $errors[] = "Le fichier hero n'est pas une image valide.";
        } else {
            $ext = strtolower(pathinfo($_FILES['hero_image']['name'], PATHINFO_EXTENSION));
            $allowed = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
            if (in_array($ext, $allowed)) {
                $hero_filename = 'hero_' . bin2hex(random_bytes(4)) . '.' . $ext;
                if (!move_uploaded_file($_FILES['hero_image']['tmp_name'], $uploadDir . $hero_filename)) {
                    $errors[] = "Erreur lors de l'enregistrement de l'image hero.";
                    $hero_filename = null;
                }
            } else {
                $errors[] = "Format image hero non autorisé (jpg, png, webp, gif).";
            }
        }
    } else {
        $errors[] = "L'image hero est obligatoire.";
    }

    // --- Si tout est bon, on insère ---
    if (empty($errors)) {
        $stmt = $db->prepare("
            INSERT INTO projects 
            (title, slug, description, short_description, technologies, github_url, demo_url, image, project_date, has_custom_assets) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $title,
            $slug,
            $description,
            $short_description,
            $technologies,
            $github_url,
            $demo_url,
            $hero_filename,
            $project_date,
            $has_custom_assets
        ]);

        $project_id = $db->lastInsertId();

        // --- Images de galerie (optionnelles) ---
        if (!empty($_FILES['gallery']['tmp_name'][0])) {
            foreach ($_FILES['gallery']['tmp_name'] as $index => $tmpName) {
                if ($_FILES['gallery']['error'][$index] !== UPLOAD_ERR_OK || empty($tmpName)) {
                    continue;
                }
                
                $ext = strtolower(pathinfo($_FILES['gallery']['name'][$index], PATHINFO_EXTENSION));
                $allowed = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
                
                if (in_array($ext, $allowed)) {
                    $filename = 'gallery_' . $project_id . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
                    if (move_uploaded_file($tmpName, $uploadDir . $filename)) {
                        $stmtImg = $db->prepare("
                            INSERT INTO project_images (project_id, image_path, alt_text, display_order) 
                            VALUES (?, ?, ?, 1)
                        ");
                        $stmtImg->execute([$project_id, $filename, '']);
                    }
                }
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

        <div class="form-group">
            <label>
            <input type="checkbox" name="has_custom_assets" value="1">
                Projet avec assets custom (jeu/interaction spécifique)
            </label>
            <small>Coche si le projet a besoin de son propre CSS/JS (ex: Sokoban)</small>
        </div>

        <div class="form-group">
            <label>Date de réalisation du projet</label>
            <input type="date" name="project_date" required>
        </div>

        <!-- GALERIE -->
        <div class="form-group">
            <label>Images de la galerie</label>
            <input type="file" name="gallery[]" multiple accept="image/*">
            <small>Maintenez Ctrl pour sélectionner plusieurs images</small>
        </div>

        <button type="submit" class="btn">Créer le projet</button>
    </form>

</body>
</html>
