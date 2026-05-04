<?php
class ConfigDB {
    private string $host = "127.0.0.1";
    private string $db = "Portfolio";
    private string $user = "root";
    private string $mdp = "root";
    private string $charset = "utf8mb4";
    private string $port = '3306';

    public function __construct(?string $file = null) {
        if ($file !== null && file_exists($file)) {
            $this->parseConf($file);
        }
    }

    private function parseConf(string $file): void {
        if (!file_exists($file)) {
            throw new Exception("Fichier de configuration introuvable : $file");
        }

        $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            // Ignore les commentaires (lignes commençant par # ou ;)
            if (preg_match('/^[#;]/', trim($line))) {
                continue;
            }

            // Séparation clé=valeur
            $parts = explode('=', $line, 2);
            if (count($parts) === 2) {
                $key = trim($parts[0]);
                $value = trim($parts[1]);
                // Supprime les guillemets simples/doubles si présents
                $value = trim($value, '"\'');
                if (property_exists($this, $key)) {
                    $this->$key = $value;
                }
            }
        }
    }

    // Getters
    public function getHost(): string { return $this->host; }
    public function getPort(): string { return $this->port; }
    public function getDB(): string { return $this->db; }
    public function getUser(): string { return $this->user; }
    public function getMdp(): string { return $this->mdp; }
    public function getCharset(): string { return $this->charset; }
}
// Création de la connexion PDO globale
try {
    $configFile = __DIR__ . '/database.conf';
    $config = new ConfigDB($configFile);
    
    $dsn = "mysql:host={$config->getHost()};port={$config->getPort()};dbname={$config->getDB()};charset={$config->getCharset()}";
    
    $db = new PDO($dsn, $config->getUser(), $config->getMdp(), [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false
    ]);
} catch (Exception $e) {
    die("Erreur de configuration : " . $e->getMessage());
} catch (PDOException $e) {
    die("Erreur de connexion BDD : " . $e->getMessage());
}
