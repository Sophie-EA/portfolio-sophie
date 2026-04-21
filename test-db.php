<?php
require_once 'config/db.php';

// Test
$stmt = $db->query("SELECT 1 as test");
$result = $stmt->fetch();
echo "Connexion OK ! Test : " . $result['test'];
?>
