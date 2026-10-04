<?php
/**
 * TECNOFIX - CONEXIÓN A LA BASE DE DATOS
 * Implementación con PDO para MySQL (XAMPP / MariaDB)
 */

define('DB_HOST', 'localhost');
define('DB_PORT', '3306');
define('DB_NAME', 'tecnofix_db');
define('DB_USER', 'root');
define('DB_PASS', 'Claudio16*');

class Database {
    private static $instance = null;
    private $pdo;
    private $isMock = false;

    private function __construct() {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8mb4";
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];
            $this->pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            // Si la BD no está creada aún o MySQL está apagado, activamos modo fallback/mock para desarrollo continuo
            $this->isMock = true;
            $this->pdo = null;
        }
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection() {
        return $this->pdo;
    }

    public function isMockMode() {
        return $this->isMock;
    }
}

// Función helper global de conexión
function getDBConnection() {
    return Database::getInstance()->getConnection();
}
