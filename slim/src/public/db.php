<?php 

//use PDO;

class Database {
    private $host = 'mysql';
    private $db = 'aeropuertos';
    private $user = 'alumno'; // Cambia con tu usuario de MySQL
    private $pass = 'alumno'; // Cambia con tu contraseña de MySQL
    private $charset = 'utf8mb4';

    public $pdo = null;

    public function connect() {
        if ($this->pdo === null) {
            $dsn = "mysql:host=$this->host;dbname=$this->db;charset=$this->charset";
            try {
                $this->pdo = new PDO($dsn, $this->user, $this->pass);
                $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch (PDOException $e) {
                echo "<p>Connection failed: " . $e->getMessage() . "</p>";
            }
        }
        return $this->pdo;
    }
}