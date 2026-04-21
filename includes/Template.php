<?php
class Template {
    private string $layoutPath;
    private array $sections = [];
    private string $currentSection = '';

    public function __construct(string $layoutPath = __DIR__ . '/../templates/layout.php') {
        // Vérifie que le fichier existe, sinon lève une erreur claire
        if (!file_exists($layoutPath)) {
            throw new Exception("Layout introuvable : $layoutPath");
        }
        $this->layoutPath = $layoutPath;
    }

    // Démarre une section (ex: title, content)
    public function section(string $name): void {
        if ($this->currentSection !== '') {
            throw new Exception("La section '$this->currentSection' est déjà ouverte. Ferme-la avant d'en ouvrir une nouvelle.");
        }
        $this->currentSection = $name;
        ob_start();
    }

    // Termine une section
    public function endSection(): void {
        if ($this->currentSection === '') {
            throw new Exception('Aucune section ouverte à fermer');
        }
        $this->sections[$this->currentSection] = ob_get_clean();
        $this->currentSection = '';
    }

    // Récupère le contenu d'une section (utilisé dans le layout)
    public function getSection(string $name): string {
        return $this->sections[$name] ?? '';
    }

    // Rend le layout final avec les sections
    public function render(array $data = []): void {
        // Vérifie qu'il n'y a pas de section encore ouverte
        if ($this->currentSection !== '') {
            throw new Exception("La section '$this->currentSection' n'a pas été fermée avec endSection()");
        }

        // Extrait les données pour les rendre disponibles dans le layout
        extract($data);
        // Ajoute les sections aux données
        extract($this->sections);
        
        include $this->layoutPath;
    }
}
