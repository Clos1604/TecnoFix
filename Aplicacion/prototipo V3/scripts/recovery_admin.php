<?php
/**
 * Script de recuperación de emergencia para el Administrador de TecnoFix.
 * Permite restaurar el acceso cuando la tabla 'usuarios' está vacía.
 * Solo ejecutable por CLI en entorno local.
 */

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    fwrite(STDERR, "Acceso denegado: solo ejecutable por consola CLI.\n");
    exit(1);
}

if (getenv('RECOVERY_ENVIRONMENT') !== 'local') {
    fwrite(STDERR, "Error: Debe definir RECOVERY_ENVIRONMENT=local.\n");
    exit(1);
}

$host = getenv('DB_HOST') ?: 'localhost';
if (!in_array($host, ['localhost', '127.0.0.1', '::1'], true)) {
    fwrite(STDERR, "Error: Solo se permite recuperación en bases de datos locales.\n");
    exit(1);
}

require_once __DIR__ . '/../config/conexion.php';
$db = getDBConnection();
if (!$db instanceof PDO) {
    fwrite(STDERR, "Error: No se pudo conectar a la base de datos MySQL.\n");
    exit(1);
}

$authFile = getenv('RECOVERY_AUTH_FILE');
$authToken = getenv('RECOVERY_AUTH_TOKEN');
if (!empty($authFile) && !empty($authToken)) {
    if (!is_file($authFile) || trim((string)file_get_contents($authFile)) !== $authToken) {
        fwrite(STDERR, "Error: Token de autorización inválido o archivo no encontrado.\n");
        exit(1);
    }
}

$name = getenv('RECOVERY_ADMIN_NAME') ?: 'Administrador TecnoFix';
$email = getenv('RECOVERY_ADMIN_EMAIL') ?: 'admin@tecnofix.com';
$password = getenv('RECOVERY_ADMIN_PASSWORD');

if (empty($password) || strlen($password) < 12) {
    fwrite(STDERR, "Error: RECOVERY_ADMIN_PASSWORD debe tener al menos 12 caracteres.\n");
    exit(1);
}

try {
    $db->beginTransaction();
    $count = (int)$db->query("SELECT COUNT(*) FROM usuarios")->fetchColumn();
    if ($count > 0) {
        $db->rollBack();
        fwrite(STDERR, "Aviso: Ya existen usuarios en la base de datos. Use el gestor crear_usuario.php.\n");
        exit(0);
    }

    $hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $db->prepare("INSERT INTO usuarios (nombre, email, password_hash, rol, estado, debe_cambiar_pass) VALUES (:nombre, :email, :hash, 'Administrador', 'Activo', 0)");
    $stmt->execute([
        ':nombre' => $name,
        ':email'  => $email,
        ':hash'   => $hash
    ]);
    $db->commit();

    if (!empty($authFile) && is_file($authFile)) {
        @unlink($authFile);
    }

    echo "Administrador recuperado exitosamente con correo: $email\n";
    exit(0);
} catch (Throwable $e) {
    if ($db->inTransaction()) {
        $db->rollBack();
    }
    fwrite(STDERR, "Error durante la inserción: " . $e->getMessage() . "\n");
    exit(1);
}
