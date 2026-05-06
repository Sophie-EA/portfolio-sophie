<?php
// /admin/index.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Pas connecté ? → vers la page de login à la racine (AVEC le slash)
if (!isset($_SESSION['admin_id'])) {
    header('Location: /login.php');
    exit;
}

// Connecté ? → directement au dashboard
header('Location: /admin/dashboard.php');
exit;

