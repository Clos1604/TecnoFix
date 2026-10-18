<?php

require_once __DIR__ . '/../Aplicacion/prototipo V3/config/conexion.php';
require_once __DIR__ . '/../Aplicacion/prototipo V3/models/Usuario.php';

function check($condition, $message) {
    if (!$condition) {
        fwrite(STDERR, "FAIL: $message\n");
        exit(1);
    }
}

$db = getDBConnection();
check($db instanceof PDO, 'Database connection is available.');

$accounts = [
    ['admin@tecnofix.com', 'DEMO_ADMIN_PASSWORD', 'Administrador'],
    ['carlos.tecnico@tecnofix.com', 'DEMO_SENIOR_TECH_PASSWORD', 'Tecnico'],
    ['maria.recepcion@tecnofix.com', 'DEMO_RECEPTION_PASSWORD', 'Recepcion'],
    ['pedro.nuevo@tecnofix.com', 'DEMO_NEW_TECH_PASSWORD', 'Tecnico'],
];

$find = $db->prepare('SELECT password_hash, rol, debe_cambiar_pass FROM usuarios WHERE email = :email');
foreach ($accounts as [$email, $passwordVariable, $role]) {
    $password = getenv($passwordVariable);
    check(is_string($password) && $password !== '', "Temporary password is available for $email.");

    $find->execute([':email' => $email]);
    $record = $find->fetch();
    check($record !== false, "Demo account exists for $email.");
    check(password_verify($password, $record['password_hash']), "Stored bcrypt hash verifies for $email.");
    check((int) $record['debe_cambiar_pass'] === 1, "Password change is required for $email.");

    $user = Usuario::login($email, $password);
    check($user !== false && $user['rol'] === $role, "Login succeeds with expected role for $email.");
    check(Usuario::login($email, $password . '-invalid') === false, "Wrong password is rejected for $email.");
}

$counts = [
    'clientes' => 3,
    'equipos' => 3,
    'ordenes_servicio' => 3,
    'historial_ordenes' => 2,
];
foreach ($counts as $table => $expected) {
    $actual = (int) $db->query("SELECT COUNT(*) FROM `$table`")->fetchColumn();
    check($actual === $expected, "Expected demo rows exist in $table.");
}

$brokenForeignKeys = (int) $db->query(
    'SELECT COUNT(*) FROM ordenes_servicio o '
    . 'LEFT JOIN clientes c ON c.id_cliente = o.id_cliente '
    . 'LEFT JOIN equipos e ON e.id_equipo = o.id_equipo '
    . 'WHERE c.id_cliente IS NULL OR e.id_equipo IS NULL'
)->fetchColumn();
check($brokenForeignKeys === 0, 'Demo orders have valid client and equipment relationships.');

$brokenHistoryUsers = (int) $db->query(
    'SELECT COUNT(*) FROM historial_ordenes h '
    . 'LEFT JOIN ordenes_servicio o ON o.id_orden = h.id_orden '
    . 'LEFT JOIN usuarios u ON u.id_usuario = h.id_usuario '
    . 'WHERE o.id_orden IS NULL OR u.id_usuario IS NULL'
)->fetchColumn();
check($brokenHistoryUsers === 0, 'Demo history has valid order and user relationships.');

echo "Authentication, bcrypt verification, temporary-password flags, and demo relations passed.\n";