<?php
require_once __DIR__ . '/../config/db.php';

if (!isset($db) || !($db instanceof PDO)) {
    die('Erreur critique : connexion BDD non disponible');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /index.php#contact');
    exit;
}

$name    = trim($_POST['name'] ?? '');
$email   = trim($_POST['email'] ?? '');
$subject = trim($_POST['subject'] ?? 'Non précisé');
$message = trim($_POST['message'] ?? '');

if (empty($name) || empty($email) || empty($message)) {
    header('Location: /index.php?contact=error&reason=empty#contact');
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header('Location: /index.php?contact=error&reason=email#contact');
    exit;
}

try {
    $sql = "INSERT INTO contacts (name, email, subject, message, created_at) 
            VALUES (:name, :email, :subject, :message, NOW())";

    $stmt = $db->prepare($sql);
    $result = $stmt->execute([
        ':name'    => $name,
        ':email'   => $email,
        ':subject' => $subject,
        ':message' => $message
    ]);

    if (!$result) {
        header('Location: /index.php?contact=error&reason=db#contact');
        exit;
    }

    header('Location: /index.php?contact=success#contact');
    exit;

} catch (PDOException $e) {
    error_log("Erreur contact : " . $e->getMessage());
    header('Location: /index.php?contact=error&reason=db#contact');
    exit;
}
