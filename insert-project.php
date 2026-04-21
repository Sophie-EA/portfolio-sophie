<?php
require_once 'config/db.php';

// RebootTech
$data = [
    'title' => 'RebootTech - Marketplace de matériel reconditionné',
    'description' => "Conception UX/UI d'une plateforme e-commerce de matériel informatique reconditionné. \n\nMéthodologie Design Thinking appliquée à un persona réaliste (Julien Dubois, freelance 28 ans) : Empathy Map, User Journey complète depuis la recherche Google jusqu'à la commande. \n\nProjet réalisé en binôme avec répartition des tâches par expertise : j'ai piloté la recherche utilisateur (Empathy Map, wireframes desktop, architecture de l'information) tandis que mon co-équipier gérait l'adaptation mobile et les fiches produits détaillées. \n\nDéfi principal : rassurer sur la qualité du matériel d'occasion tout en valorisant l'engagement écologique sans surcharger l'interface.",
    'technologies' => 'Figma, UX Research, Design Thinking, Empathy Mapping, Wireframing, Prototyping, Travail collaboratif',
    'image' => 'reboottech-hero.jpg',
    'github_url' => '',
    'demo_url' => 'https://www.figma.com/proto/...' // Mets ton vrai lien ici
];

$stmt = $db->prepare("INSERT INTO projects (title, description, technologies, image, github_url, demo_url) 
                      VALUES (:title, :description, :technologies, :image, :github, :demo)");

try {
    $stmt->execute($data);
    echo "✅ RebootTech ajouté avec succès ! ID : " . $db->lastInsertId();
} catch (PDOException $e) {
    echo "❌ Erreur : " . $e->getMessage();
}
?>
