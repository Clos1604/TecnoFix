<?php
try {
    $pdo = new PDO("mysql:host=localhost;dbname=tecnofix_db;charset=utf8mb4", "root", "Claudio16*", [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    
    $sql = file_get_contents(__DIR__ . '/../Aplicacion/sistema/database/tecnofix_db.sql');
    
    // Execute SQL queries
    $pdo->exec($sql);
    echo "IMPORTACION_SQL_EXITOSA_EN_MYSQL\n";

    // Verify tables created
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    echo "TABLAS_CREADAS: " . implode(", ", $tables) . "\n";

} catch (PDOException $e) {
    echo "ERROR_IMPORTACION: " . $e->getMessage() . "\n";
}
