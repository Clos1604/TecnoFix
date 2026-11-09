<?php
/**
 * Acceso directo para ejecutar el gestor de usuarios desde la raíz del proyecto.
 * Ejemplo de uso en Visual Studio Code:
 *   php crear_usuario.php
 */

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    die("Acceso denegado: este script solo puede ejecutarse desde la consola (CLI).\n");
}

require_once __DIR__ . '/Aplicacion/prototipo V3/scripts/crear_usuario.php';
