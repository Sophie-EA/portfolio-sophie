<?php
class ConfigDB {
    private string $host;
    private string $db;
    private string $user;
    private string $mdp;
    private string $charset;
    private string $port;

    public function __construct(string $file) {
        if (!file_exists($file)) {
            throw new Exception("Fichier de configuration requis et introuvable : $file");
        }

        // Valeurs par défaut universelles (pas de credentials)
        $this->charset = "utf8mb4";
        $this->port = "3306";

        $this->parseConf($file);

        // Vérification : on s'assure que les valeurs critiques ont bien été lues
        if (empty($this->host) || empty($this->db) || empty($this->user) || empty($this->mdp)) {
            throw new Exception("Le fichier $file est incomplet (host, db, user, mdp requis).");
        }
    }

    private function parseConf(string $file): void {
        $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line) || preg_match('/^[#;]/', $line)) {
                continue;
            }

            $parts = explode('=', $line, 2);
            if (count($parts) === 2) {
                $key = trim($parts[0]);
                $value = trim($parts[1], ' "\'');
                
                if (property_exists($this, $key)) {
                    $this->$key = $value;
                }
            }
        }
    }

    // Getters...
    public function getHost(): string { return $this->host; }
    public function getPort(): string { return $this->port; }
    public function getDB(): string { return $this->db; }
    public function getUser(): string { return $this->user; }
    public function getMdp(): string { return $this->mdp; }
    public function getCharset(): string { return $this->charset; }
}

// --- Utilisation strictement liée au fichier ---
try {
    $configFile = __DIR__ . '/database.conf';
    $config = new ConfigDB($configFile); // Échouera si le fichier manque ou est vide
    
    $dsn = sprintf(
        "mysql:host=%s;port=%s;dbname=%s;charset=%s",
        $config->getHost(),
        $config->getPort(),
        $config->getDB(),
        $config->getCharset()
    );

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
?>