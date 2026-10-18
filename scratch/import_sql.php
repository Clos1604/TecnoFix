<?php
try {
    $host = getenv('DB_HOST') ?: 'localhost';
    $port = getenv('DB_PORT') ?: '3306';
    $name = getenv('DB_NAME') ?: 'tecnofix_db';
    $user = getenv('DB_USER');
    if ($user === false || $user === '') {
        throw new RuntimeException('Configure DB_USER in the environment.');
    }
    $password = getenv('DB_PASS') ?: '';
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$name;charset=utf8mb4", $user, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    
    $sql = file_get_contents(__DIR__ . '/../Aplicacion/prototipo V3/database/tecnofix_db.sql');
    
    // Execute SQL queries
    $pdo->exec($sql);
    echo "IMPORTACION_SQL_EXITOSA_EN_MYSQL\n";

    // Verify tables created
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    echo "TABLAS_CREADAS: " . implode(", ", $tables) . "\n";

} catch (Throwable $e) {
    echo "ERROR_IMPORTACION: " . $e->getMessage() . "\n";
}
