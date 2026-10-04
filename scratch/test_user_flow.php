<?php
require_once __DIR__ . '/../Aplicacion/prototipo V3/config/conexion.php';
require_once __DIR__ . '/../Aplicacion/prototipo V3/models/Usuario.php';

echo "=== PRUEBA DE FLUJO: CREACIÓN DE USUARIO Y CAMBIO OBLIGATORIO DE PASSWORD ===\n";

// 1. Crear nuevo usuario como Admin
$resCrear = Usuario::crearUsuario("Técnico Prueba", "tecnico.prueba@tecnofix.com", "Tecnico");
echo "1. RESULTADO_CREAR: " . ($resCrear['exito'] ? 'EXITO' : 'ERROR') . " | Clave por defecto: " . ($resCrear['default_password'] ?? '') . "\n";

// 2. Login con clave por defecto
$userLogin1 = Usuario::login("tecnico.prueba@tecnofix.com", "tecnico123");
if ($userLogin1) {
    echo "2. LOGIN_TEMPORAL_EXITOSO: Debe cambiar pass = " . $userLogin1['debe_cambiar_pass'] . "\n";
} else {
    echo "2. LOGIN_TEMPORAL_FALLIDO\n";
}

// 3. Cambiar clave obligatoria
$idUser = $userLogin1['id_usuario'];
$resCambio = Usuario::cambiarPassword($idUser, "ClaveSegura2026!");
echo "3. CAMBIO_PASS_RESULTADO: " . ($resCambio['exito'] ? 'EXITO' : 'ERROR') . "\n";

// 4. Intentar login con clave antigua (debe fallar)
$userLoginOld = Usuario::login("tecnico.prueba@tecnofix.com", "tecnico123");
echo "4. LOGIN_CLAVE_VIEJA: " . ($userLoginOld ? 'ACCESO_PERMITIDO (ERROR)' : 'RECHAZADO (CORRECTO)') . "\n";

// 5. Login con clave nueva (debe ser exitoso y debe_cambiar_pass == 0)
$userLogin2 = Usuario::login("tecnico.prueba@tecnofix.com", "ClaveSegura2026!");
if ($userLogin2) {
    echo "5. LOGIN_CLAVE_NUEVA_EXITOSO: Debe cambiar pass = " . $userLogin2['debe_cambiar_pass'] . "\n";
} else {
    echo "5. LOGIN_CLAVE_NUEVA_FALLIDO\n";
}
