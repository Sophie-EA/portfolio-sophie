<?php
class Template {
    private string $layoutPath;
    private array $sections = [];
    private ?string $currentSection = null;

    public function __construct(string $layoutPath = __DIR__ . '/../templates/layout.php') {
        if (!file_exists($layoutPath)) {
            throw new Exception("Layout introuvable : $layoutPath");
        }
        $this->layoutPath = $layoutPath;
    }

    public function section(string $name): void {
        if ($this->currentSection !== null) {
            throw new Exception("La section '$this->currentSection' est déjà ouverte. Ferme-la avant d'en ouvrir une nouvelle.");
        }
        $this->currentSection = $name;
        ob_start();
    }

    public function endSection(): void {
        if ($this->currentSection !== null) {
            $this->sections[$this->currentSection] = ob_get_clean();
            $this->currentSection = null;
        }
    }

    public function getSection(string $name): string {
        return $this->sections[$name] ?? '';
    }

    public function render(): void {
        if ($this->currentSection !== null) {
            throw new Exception("La section '$this->currentSection' n'a pas été fermée avec endSection()");
        }

        extract($this->sections);
        include $this->layoutPath;
    }
}

