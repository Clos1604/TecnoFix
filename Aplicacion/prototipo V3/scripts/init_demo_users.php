<?php

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

if (getenv('ALLOW_DEMO_USER_INIT') !== '1') {
    fwrite(STDERR, "Set ALLOW_DEMO_USER_INIT=1 to explicitly enable demo account initialization.\n");
    exit(1);
}

$accounts = [
    ['Administrador TecnoFix', 'admin@tecnofix.com', 'Administrador', 'DEMO_ADMIN_PASSWORD'],
    ['Carlos Ruiz (Técnico Senior)', 'carlos.tecnico@tecnofix.com', 'Tecnico', 'DEMO_SENIOR_TECH_PASSWORD'],
    ['María López (Recepción)', 'maria.recepcion@tecnofix.com', 'Recepcion', 'DEMO_RECEPTION_PASSWORD'],
    ['Pedro Ramírez (Técnico Nuevo)', 'pedro.nuevo@tecnofix.com', 'Tecnico', 'DEMO_NEW_TECH_PASSWORD'],
];

$passwords = [];
foreach ($accounts as $account) {
    $password = getenv($account[3]);
    if ($password === false || strlen($password) < 12) {
        fwrite(STDERR, "Set each DEMO_*_PASSWORD environment variable to a temporary password of at least 12 characters.\n");
        exit(1);
    }
    $passwords[] = $password;
}

if (count(array_unique($passwords)) !== count($passwords)) {
    fwrite(STDERR, "Each demo account must use a different temporary password.\n");
    exit(1);
}

require_once __DIR__ . '/../config/conexion.php';
$db = getDBConnection();
if (!$db instanceof PDO) {
    fwrite(STDERR, "Database connection failed; check DB_* environment variables.\n");
    exit(1);
}

try {
    $db->beginTransaction();
    $find = $db->prepare('SELECT id_usuario FROM usuarios WHERE email = :email');
    $insert = $db->prepare(
        'INSERT INTO usuarios (nombre, email, password_hash, rol, estado, debe_cambiar_pass) '
        . "VALUES (:nombre, :email, :password_hash, :rol, 'Activo', 1)"
    );

    foreach ($accounts as $index => $account) {
        $find->execute([':email' => $account[1]]);
        if ($find->fetchColumn() !== false) {
            fwrite(STDOUT, "Skipped existing demo account: {$account[1]}\n");
            continue;
        }

        $insert->execute([
            ':nombre' => $account[0],
            ':email' => $account[1],
            ':password_hash' => password_hash($passwords[$index], PASSWORD_BCRYPT),
            ':rol' => $account[2],
        ]);
        fwrite(STDOUT, "Created demo account: {$account[1]} (password change required)\n");
    }

    $db->commit();
} catch (Throwable $exception) {
    if ($db->inTransaction()) {
        $db->rollBack();
    }
    fwrite(STDERR, "Demo account initialization failed; no account data was committed.\n");
    exit(1);
}