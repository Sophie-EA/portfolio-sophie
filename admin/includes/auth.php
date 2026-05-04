<?php
// 1. Démarrer la session UNE SEULE FOIS (évite les "headers already sent")
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// 2. Services transverses 
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/helpers.php';

// 3. connexion admin
if (!isset($_SESSION['admin_id'])) {
    header('Location: /login.php');
    exit;
}
?>
