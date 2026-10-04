<?php
require_once __DIR__ . '/../Aplicacion/prototipo V3/config/conexion.php';
require_once __DIR__ . '/../Aplicacion/prototipo V3/models/Usuario.php';
require_once __DIR__ . '/../Aplicacion/prototipo V3/models/Orden.php';

echo "=== PROBANDO CONEXION Y MODELOS EN MYSQL ===\n";

// Test Usuario Login
$user = Usuario::login('admin@tecnofix.com', '1234');
if ($user) {
    echo "LOGIN_EXITOSO: Bienvenido " . $user['nombre'] . " (" . $user['rol'] . ")\n";
} else {
    echo "LOGIN_FALLIDO\n";
}

// Test Ordenes GetAll
$ordenes = Orden::getAll();
echo "TOTAL_ORDENES_EN_MYSQL: " . count($ordenes) . "\n";
foreach ($ordenes as $o) {
    echo "  - [" . $o['codigo_orden'] . "] " . $o['cliente_nombre'] . " | " . $o['equipo_info'] . " | Estado: " . $o['estado'] . "\n";
}
