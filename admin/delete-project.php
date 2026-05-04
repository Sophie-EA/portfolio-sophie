<?php
require_once 'includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    flashMessage('warning', 'Action non autorisée.');
    header('Location: dashboard.php');
    exit;
}

if (!verifyToken($_POST['csrf_token'] ?? '')) {
    flashMessage('danger', 'Session invalide. Rechargez la page.');
    header('Location: dashboard.php');
    exit;
}

$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    flashMessage('warning', 'ID invalide.');
    header('Location: dashboard.php');
    exit;
}

// Chemins
$baseDir = __DIR__ . '/../public/images/projects/';

// --- 1. Récupération des images à supprimer physiquement ---
$stmt = $db->prepare("SELECT image FROM projects WHERE id = ?");
$stmt->execute([$id]);
$project = $stmt->fetch();

if (!$project) {
    flashMessage('warning', 'Projet introuvable.');
    header('Location: dashboard.php');
    exit;
}

// Suppression image hero
if (!empty($project['image']) && file_exists($baseDir . $project['image'])) {
    unlink($baseDir . $project['image']);
}

// Suppression images galerie
$galleryStmt = $db->prepare("SELECT image_path FROM project_images WHERE project_id = ?");
$galleryStmt->execute([$id]);
while ($img = $galleryStmt->fetch()) {
    if (!empty($img['image_path']) && file_exists($baseDir . $img['image_path'])) {
        unlink($baseDir . $img['image_path']);
    }
}

// --- 2. Suppression en DB (la FK ON DELETE CASCADE supprime project_images automatiquement) ---
$db->prepare("DELETE FROM projects WHERE id = ?")->execute([$id]);

flashMessage('success', 'Projet et images supprimés avec succès.');
header('Location: dashboard.php');
exit;
