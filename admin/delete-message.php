<?php
require_once 'includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: messages.php');
    exit;
}

$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
$token = $_POST['csrf_token'] ?? '';

if (!$id || !verifyToken($token)) {
    flashMessage('danger', 'Erreur de sécurité.');
    header('Location: messages.php');
    exit;
}

try {
    $stmt = $db->prepare("DELETE FROM contacts WHERE id = ?");
    $stmt->execute([$id]);
    flashMessage('success', 'Message supprimé.');
} catch (PDOException $e) {
    flashMessage('danger', 'Erreur suppression.');
}

header('Location: messages.php');
exit;
