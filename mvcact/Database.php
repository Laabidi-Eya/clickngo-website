<?php


class Database {
    private $host = "localhost";
    private $username = "root";
    private $password = "";
    private $dbname = "click'n'go";
    private $conn;
    private static $instance = null;

    public function connect() {
        try {
            // Check if connection already exists
            if ($this->conn instanceof PDO) {
                return $this->conn;
            }
            
            // Create new connection
            $this->conn = new PDO(
                "mysql:host=$this->host;dbname=$this->dbname;charset=utf8mb4", 
                $this->username, 
                $this->password,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                    PDO::ATTR_PERSISTENT => true
                ]
            );
            
            // Test connection
            $this->conn->query("SELECT 1");
            
            return $this->conn;
        } catch (PDOException $e) {
            error_log("Erreur de connexion à la base de données: " . $e->getMessage());
            throw new PDOException("Erreur de connexion à la base de données: " . $e->getMessage());
        }
    }
    
    // Singleton pattern to ensure only one database connection is created
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
}
?>
