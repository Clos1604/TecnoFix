<?php
try {
    $pdo = new PDO("mysql:host=localhost;port=3306", "root", "Claudio16*");
    echo "CONEXION_MYSQL_EXITOSA\n";
    
    // Check if tecnofix_db exists or create it
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `tecnofix_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");
    echo "BASE_DE_DATOS_TECNOFIX_DB_LISTA\n";
} catch (PDOException $e) {
    echo "ERROR_CONEXION: " . $e->getMessage() . "\n";
}
