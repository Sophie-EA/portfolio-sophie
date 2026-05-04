<?php
require_once 'includes/auth.php';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    flashMessage('danger', 'ID invalide.');
    header('Location: dashboard.php');
    exit;
}

// --- Récupération du projet ---
$stmt = $db->prepare("SELECT * FROM projects WHERE id = ?");
$stmt->execute([$id]);
$project = $stmt->fetch(); // ou $stmt->fetch() selon ton wrapper DB

if (!$project) {
    flashMessage('danger', 'Projet introuvable.');
    header('Location: dashboard.php');
    exit;
}

// --- Récupération galerie ---
$stmtGal = $db->prepare("SELECT * FROM project_images WHERE project_id = ? ORDER BY display_order, id");
$stmtGal->execute([$id]);
$galleryImages = $stmtGal->fetchAll();

$csrf = generateToken();
$uploadDir = __DIR__ . '/../public/images/projects/';
$uploadUrl = '../public/images/projects/';

// --- TRAITEMENT POST ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!verifyToken($_POST['csrf_token'] ?? '')) {
        die('Token invalide. Rechargez la page.');
    }

    $title = trim($_POST['title'] ?? '');
    $slug = trim($_POST['slug'] ?? '');
    $short_description = trim($_POST['short_description'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $technologies = trim($_POST['technologies'] ?? '');
    $github_url = filter_input(INPUT_POST, 'github_url', FILTER_VALIDATE_URL);
    $github_url = ($github_url !== false && $github_url !== null) ? $github_url : null;
    $demo_url = filter_input(INPUT_POST, 'demo_url', FILTER_VALIDATE_URL);
    $demo_url = ($demo_url !== false && $demo_url !== null) ? $demo_url : null;
    $has_custom_assets = isset($_POST['has_custom_assets']) ? 1 : 0;
    $imagePath = $project['image']; // on garde l'ancienne par défaut

    // Validation
    if (empty($title) || empty($description)) {
        flashMessage('warning', 'Titre et description obligatoires.');
    } elseif (empty($slug) || !preg_match('/^[a-z0-9\-]+$/', $slug)) {
        flashMessage('warning', 'Slug invalide (minuscules, chiffres, tirets uniquement).');
    } else {
        // Vérifier unicité du slug (hors projet actuel)
        $check = $db->prepare("SELECT id FROM projects WHERE slug = ? AND id != ?");
        $check->execute([$slug, $id]);
        if ($check->fetch()) {
            flashMessage('warning', 'Ce slug est déjà utilisé par un autre projet.');
        } else {

            // --- UPLOAD IMAGE HERO ---
            if (!empty($_FILES['hero_image']['tmp_name']) && $_FILES['hero_image']['error'] === UPLOAD_ERR_OK) {
                $ext = strtolower(pathinfo($_FILES['hero_image']['name'], PATHINFO_EXTENSION));
                $allowed = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
                if (in_array($ext, $allowed)) {
                    $newName = 'hero_' . $id . '_' . uniqid() . '.' . $ext;
                    if (!is_dir($uploadDir)) {
                        mkdir($uploadDir, 0755, true);
                    }
                    if (move_uploaded_file($_FILES['hero_image']['tmp_name'], $uploadDir . $newName)) {
                        // Supprimer l'ancienne hero si elle existe
                        if (!empty($project['image']) && file_exists($uploadDir . $project['image'])) {
                            unlink($uploadDir . $project['image']);
                        }
                        $imagePath = $newName;
                    }
                } else {
                    flashMessage('warning', 'Format image hero non autorisé (jpg, png, webp, gif).');
                }
            }

            // --- UPDATE TABLE PROJECTS ---
            $sql = "UPDATE projects SET 
                        title = ?, slug = ?, short_description = ?, description = ?, 
                        technologies = ?, github_url = ?, demo_url = ?, image = ?, 
                        has_custom_assets = ?
                    WHERE id = ?";
            $stmt = $db->prepare($sql);
            $stmt->execute([
                $title, $slug, $short_description, $description,
                $technologies, $github_url, $demo_url, $imagePath,
                $has_custom_assets, $id
            ]);

            // --- GESTION GALERIE EXISTANTE ---
            // Mise à jour alt et ordre
            if (!empty($_POST['gallery_meta'])) {
                foreach ($_POST['gallery_meta'] as $imgId => $meta) {
                    $alt = trim($meta['alt'] ?? '');
                    $order = filter_var($meta['order'] ?? 0, FILTER_VALIDATE_INT);
                    $upd = $db->prepare("UPDATE project_images SET alt_text = ?, display_order = ? WHERE id = ? AND project_id = ?");
                    $upd->execute([$alt, $order, $imgId, $id]);
                }
            }

            // Suppression images cochées
            if (!empty($_POST['delete_gallery'])) {
                foreach ($_POST['delete_gallery'] as $delId) {
                    $delId = (int)$delId;
                    $find = $db->prepare("SELECT image_path FROM project_images WHERE id = ? AND project_id = ?");
                    $find->execute([$delId, $id]);
                    $found = $find->fetch();
                    if ($found && file_exists($uploadDir . $found['image_path'])) {
                        unlink($uploadDir . $found['image_path']);
                    }
                    $db->prepare("DELETE FROM project_images WHERE id = ? AND project_id = ?")->execute([$delId, $id]);
                }
            }

            // --- AJOUT NOUVELLES IMAGES GALERIE ---
            if (!empty($_FILES['new_gallery']['tmp_name'][0])) {
                foreach ($_FILES['new_gallery']['tmp_name'] as $key => $tmpName) {
                    if ($_FILES['new_gallery']['error'][$key] !== UPLOAD_ERR_OK) continue;
                    $ext = strtolower(pathinfo($_FILES['new_gallery']['name'][$key], PATHINFO_EXTENSION));
                    if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif'])) {
                        $newName = 'gallery_' . $id . '_' . uniqid() . '.' . $ext;
                        if (move_uploaded_file($tmpName, $uploadDir . $newName)) {
                            $ins = $db->prepare("INSERT INTO project_images (project_id, image_path, alt_text, display_order) VALUES (?, ?, ?, ?)");
                            $ins->execute([$id, $newName, '', 0]);
                        }
                    }
                }
            }

            flashMessage('success', 'Projet mis à jour avec succès.');
            header("Location: edit-project.php?id=$id");
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Modifier - <?= htmlspecialchars($project['title']) ?></title>
    <style>
        body { font-family: Arial; max-width: 900px; margin: 0 auto; padding: 20px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; font-weight: bold; margin-bottom: 5px; }
        input[type="text"], input[type="url"], textarea, select { width: 100%; padding: 8px; box-sizing: border-box; }
        textarea { min-height: 120px; }
        .current-img { max-width: 150px; max-height: 100px; border: 1px solid #ddd; margin: 5px 0; }
        .gallery-row { display: flex; align-items: center; gap: 10px; margin-bottom: 10px; padding: 8px; border: 1px solid #eee; }
        .gallery-row img { width: 80px; height: 60px; object-fit: cover; }
        .btn { padding: 10px 20px; background: #007bff; color: white; border: none; cursor: pointer; border-radius: 4px; }
        .success, .warning { padding: 10px; margin-bottom: 15px; border-radius: 4px; }
        .success { background: #d4edda; color: #155724; }
        .warning { background: #fff3cd; color: #856404; }
    </style>
</head>
<body>

<h1>Modifier le projet</h1>

<?php $flash = $_SESSION['flash'] ?? null; unset($_SESSION['flash']); ?>
<?php if ($flash): ?>
    <div class="<?= $flash['type'] ?>"><?= htmlspecialchars($flash['message']) ?></div>
<?php endif; ?>

<form method="POST" enctype="multipart/form-data">
    <input type="hidden" name="csrf_token" value="<?= $csrf ?>">

    <div class="form-group">
        <label>Titre</label>
        <input type="text" name="title" value="<?= htmlspecialchars((string)($_POST['title'] ?? $project['title'] ?? '')) ?>" required>
    </div>

    <div class="form-group">
        <label>Slug (URL)</label>
        <input type="text" name="slug" value="<?= htmlspecialchars((string)($_POST['slug'] ?? $project['slug'] ?? '')) ?>" required pattern="[a-z0-9\-]+">
    </div>

    <div class="form-group">
        <label>Description courte</label>
        <input type="text" name="short_description" value="<?= htmlspecialchars((string)($_POST['short_description'] ?? $project['short_description'] ?? '')) ?>">
    </div>

    <div class="form-group">
        <label>Description complète</label>
        <textarea name="description" required><?= htmlspecialchars((string)($_POST['description'] ?? $project['description'] ?? '')) ?></textarea>
    </div>

    <div class="form-group">
        <label>Technologies (séparées par virgule)</label>
        <input type="text" name="technologies" value="<?= htmlspecialchars((string)($_POST['technologies'] ?? $project['technologies'] ?? '')) ?>">
    </div>

    <div class="form-group">
        <label>URL GitHub</label>
        <input type="url" name="github_url" value="<?= htmlspecialchars((string)($_POST['github_url'] ?? $project['github_url'] ?? '')) ?>">
    </div>

    <div class="form-group">
        <label>URL Démo</label>
        <input type="url" name="demo_url" value="<?= htmlspecialchars((string)($_POST['demo_url'] ?? $project['demo_url'] ?? '')) ?>">
    </div>

    <div class="form-group">
        <label>
            <input type="checkbox" name="has_custom_assets" value="1" <?= (isset($_POST['has_custom_assets']) ? ($_POST['has_custom_assets'] ? 'checked' : '') : ($project['has_custom_assets'] ? 'checked' : '')) ?>>
            Projet avec assets personnalisés (ex: Sokoban)
        </label>
    </div>

    <!-- IMAGE HERO -->
    <div class="form-group">
        <label>Image principale (Hero + Card)</label>
        <?php if (!empty($project['image'])): ?>
            <div><img src="<?= $uploadUrl . htmlspecialchars($project['image']) ?>" class="current-img" alt="Hero actuelle"></div>
        <?php endif; ?>
        <input type="file" name="hero_image" accept="image/*">
        <small>Laisse vide pour conserver l'image actuelle.</small>
    </div>

    <!-- GALERIE EXISTANTE -->
    <div class="form-group">
        <label>Galerie existante</label>
        <?php if ($galleryImages): ?>
            <?php foreach ($galleryImages as $img): ?>
            <div class="gallery-row">
                <img src="<?= $uploadUrl . htmlspecialchars((string)$img['image_path']) ?>" alt="">
                <div style="flex:1">
                    <label>Alt text</label>
                    <input type="text" name="gallery_meta[<?= $img['id'] ?>][alt]" value="<?= htmlspecialchars($img['alt_text']) ?>">
                    <label>Ordre</label>
                    <input type="number" name="gallery_meta[<?= $img['id'] ?>][order]" value="<?= (int)$img['display_order'] ?>" style="width:60px">
                </div>
                <label style="display:flex;align-items:center;gap:5px;">
                    <input type="checkbox" name="delete_gallery[]" value="<?= $img['id'] ?>"> Supprimer
                </label>
            </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p><em>Aucune image en galerie.</em></p>
        <?php endif; ?>
    </div>

    <!-- NOUVELLES IMAGES -->
    <div class="form-group">
        <label>Ajouter des images à la galerie</label>
        <input type="file" name="new_gallery[]" multiple accept="image/*">
    </div>

    <button type="submit" class="btn">💾 Enregistrer les modifications</button>
    <a href="dashboard.php" style="margin-left:10px;">← Retour</a>
</form>

</body>
</html>
