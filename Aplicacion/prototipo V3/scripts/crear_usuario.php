<?php
/**
 * ============================================================================
 * TECNOFIX - GESTOR DE USUARIOS POR CONSOLA (CLI / VISUAL STUDIO CODE)
 * ============================================================================
 * Este script SOLO puede ejecutarse desde la terminal / consola de Visual Studio Code.
 * Prohibido su acceso vía servidor web / navegador (HTTP).
 *
 * Uso interactivo:
 *   php crear_usuario.php
 *
 * Uso directo por argumentos:
 *   php crear_usuario.php <email> <password> <rol> [nombre] [debe_cambiar_pass:0|1]
 *   Ejemplo:
 *   php crear_usuario.php admin@tecnofix.com Admin123456* Administrador "Admin TecnoFix" 0
 * ============================================================================
 */

// 1. RESTRICCIÓN DE SEGURIDAD ESTRICTA: SOLO CLI
if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    header('Content-Type: text/plain; charset=utf-8');
    echo "=====================================================================\n";
    echo "ACCESO DENEGADO (HTTP 403)\n";
    echo "Este script es una herramienta interna de administración y SOLO puede\n";
    echo "ejecutarse desde la terminal / consola (CLI) en Visual Studio Code.\n";
    echo "=====================================================================\n";
    exit(1);
}

// 2. CARGA DE CONEXIÓN A BASE DE DATOS
$conexionPath = __DIR__ . '/../config/conexion.php';
if (!file_exists($conexionPath)) {
    $conexionPath = __DIR__ . '/Aplicacion/prototipo V3/config/conexion.php';
}

if (!file_exists($conexionPath)) {
    fwrite(STDERR, "[ERROR] No se encontró el archivo de conexión en: $conexionPath\n");
    exit(1);
}

require_once $conexionPath;

$db = getDBConnection();
if (!$db instanceof PDO) {
    fwrite(STDERR, "\n[ERROR CRÍTICO] No fue posible conectar con la base de datos MySQL.\n");
    fwrite(STDERR, "Detalles de conexión actuales:\n");
    fwrite(STDERR, "  Host: " . DB_HOST . ":" . DB_PORT . "\n");
    fwrite(STDERR, "  Base de datos: " . DB_NAME . "\n");
    fwrite(STDERR, "  Usuario: " . DB_USER . "\n\n");
    fwrite(STDERR, "Verifique que:\n");
    fwrite(STDERR, "  1. El servicio MySQL (o XAMPP MySQL) esté iniciado y en ejecución.\n");
    fwrite(STDERR, "  2. El archivo .env en la raíz del proyecto tenga las credenciales correctas (DB_USER, DB_PASS).\n\n");
    exit(1);
}

// Helper para leer entrada de terminal
function prompt($mensaje, $default = '') {
    $textoPrompt = $default !== '' ? "$mensaje [$default]: " : "$mensaje: ";
    echo $textoPrompt;
    $input = trim(fgets(STDIN));
    return $input === '' ? $default : $input;
}

// Helper para crear o actualizar usuario
function insertarOUsuario(PDO $db, $nombre, $email, $password, $rol, $debeCambiarPass = 0) {
    $rolesValidos = ['Administrador', 'Tecnico', 'Recepcion'];
    if (!in_array($rol, $rolesValidos, true)) {
        return ['exito' => false, 'mensaje' => "Rol inválido ('$rol'). Debe ser: Administrador, Tecnico o Recepcion."];
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return ['exito' => false, 'mensaje' => "El formato del correo ('$email') no es válido."];
    }

    if (strlen($password) < 6) {
        return ['exito' => false, 'mensaje' => "La contraseña debe tener al menos 6 caracteres."];
    }

    $hash = password_hash($password, PASSWORD_BCRYPT);
    $debeCambiar = $debeCambiarPass ? 1 : 0;

    // Verificar si ya existe
    $stmtFind = $db->prepare("SELECT id_usuario FROM usuarios WHERE email = :email");
    $stmtFind->execute([':email' => $email]);
    $existente = $stmtFind->fetch(PDO::FETCH_ASSOC);

    if ($existente) {
        // Actualizar clave y datos
        $stmtUpdate = $db->prepare(
            "UPDATE usuarios 
             SET nombre = :nombre, password_hash = :hash, rol = :rol, estado = 'Activo', debe_cambiar_pass = :debe 
             WHERE email = :email"
        );
        $stmtUpdate->execute([
            ':nombre' => $nombre,
            ':hash'   => $hash,
            ':rol'    => $rol,
            ':debe'   => $debeCambiar,
            ':email'  => $email,
        ]);
        return [
            'exito' => true,
            'accion' => 'actualizado',
            'id_usuario' => $existente['id_usuario'],
            'email' => $email,
            'rol' => $rol,
            'debe_cambiar_pass' => $debeCambiar
        ];
    } else {
        // Insertar nuevo
        $stmtInsert = $db->prepare(
            "INSERT INTO usuarios (nombre, email, password_hash, rol, estado, debe_cambiar_pass) 
             VALUES (:nombre, :email, :hash, :rol, 'Activo', :debe)"
        );
        $stmtInsert->execute([
            ':nombre' => $nombre,
            ':email'  => $email,
            ':hash'   => $hash,
            ':rol'    => $rol,
            ':debe'   => $debeCambiar,
        ]);
        return [
            'exito' => true,
            'accion' => 'creado',
            'id_usuario' => $db->lastInsertId(),
            'email' => $email,
            'rol' => $rol,
            'debe_cambiar_pass' => $debeCambiar
        ];
    }
}

// Helper para listar usuarios
function listarUsuarios(PDO $db) {
    $stmt = $db->query("SELECT id_usuario, nombre, email, rol, estado, debe_cambiar_pass, fecha_creacion FROM usuarios ORDER BY id_usuario ASC");
    $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "\n========================================================================================\n";
    echo " USUARIOS REGISTRADOS EN LA BASE DE DATOS (Total: " . count($usuarios) . ")\n";
    echo "========================================================================================\n";
    printf("%-5s | %-25s | %-30s | %-14s | %-8s | %-12s\n", "ID", "NOMBRE", "EMAIL", "ROL", "ESTADO", "CAMBIO CLAVE");
    echo "----------------------------------------------------------------------------------------\n";

    if (empty($usuarios)) {
        echo "  (No hay usuarios registrados en la base de datos actualmente)\n";
    } else {
        foreach ($usuarios as $u) {
            $cambio = ($u['debe_cambiar_pass'] == 1) ? 'Pendiente' : 'Al día';
            printf(
                "%-5d | %-25.25s | %-30.30s | %-14s | %-8s | %-12s\n",
                $u['id_usuario'],
                $u['nombre'],
                $u['email'],
                $u['rol'],
                $u['estado'],
                $cambio
            );
        }
    }
    echo "========================================================================================\n\n";
}

// -----------------------------------------------------------------------------
// 3. MODO POR ARGUMENTOS DE LÍNEA DE COMANDOS
// -----------------------------------------------------------------------------
if ($argc >= 4) {
    $email = $argv[1];
    $password = $argv[2];
    $rol = ucfirst(strtolower($argv[3]));
    $nombre = $argv[4] ?? 'Usuario ' . $rol;
    $debeCambiar = isset($argv[5]) ? intval($argv[5]) : 0;

    echo "\n[INFO] Procesando registro vía parámetros...\n";
    $res = insertarOUsuario($db, $nombre, $email, $password, $rol, $debeCambiar);

    if ($res['exito']) {
        echo "[EXITO] Usuario " . $res['accion'] . " correctamente (ID: {$res['id_usuario']})\n";
        echo "  Email: {$res['email']}\n";
        echo "  Rol: {$res['rol']}\n";
        echo "  Exige cambio de contraseña: " . ($res['debe_cambiar_pass'] ? 'SI' : 'NO') . "\n\n";
        exit(0);
    } else {
        fwrite(STDERR, "[ERROR] " . $res['mensaje'] . "\n\n");
        exit(1);
    }
}

// -----------------------------------------------------------------------------
// 4. MODO INTERACTIVO (MENÚ DE CONSOLA PARA VISUAL STUDIO CODE)
// -----------------------------------------------------------------------------
while (true) {
    echo "\n";
    echo "==============================================================\n";
    echo "    🔧 TECNOFIX - GESTOR DE USUARIOS (VS CODE CLI)           \n";
    echo "==============================================================\n";
    echo "  [1] Crear un nuevo usuario personalizado\n";
    echo "  [2] Inicializar cuentas predeterminadas de TecnoFix\n";
    echo "      (Admin, Técnico Senior, Recepción)\n";
    echo "  [3] Listar todos los usuarios en la base de datos\n";
    echo "  [4] Cambiar contraseña de un usuario existente\n";
    echo "  [5] Salir\n";
    echo "==============================================================\n";

    $opcion = prompt("Seleccione una opción (1-5)");

    switch ($opcion) {
        case '1':
            echo "\n--- CREAR USUARIO PERSONALIZADO ---\n";
            $nombre = prompt("Nombre completo del usuario");
            if ($nombre === '') {
                echo "[ERROR] El nombre no puede estar vacío.\n";
                break;
            }

            $email = prompt("Correo electrónico / Usuario");
            if ($email === '') {
                echo "[ERROR] El correo no puede estar vacío.\n";
                break;
            }

            echo "Seleccione el rol:\n";
            echo "  1) Administrador\n";
            echo "  2) Tecnico\n";
            echo "  3) Recepcion\n";
            $rolOpc = prompt("Opción de rol", "1");
            $rol = match ($rolOpc) {
                '2' => 'Tecnico',
                '3' => 'Recepcion',
                default => 'Administrador',
            };

            $password = prompt("Contraseña deseada (o presione Enter para generar una aleatoria)");
            if ($password === '') {
                $password = bin2hex(random_bytes(6)) . 'A1*';
                echo "[INFO] Contraseña generada automáticamente: $password\n";
            }

            $exigirCambio = prompt("¿Obligar a cambiar la contraseña en su primer login? (s/n)", "n");
            $debeCambiar = strtolower($exigirCambio) === 's' ? 1 : 0;

            $res = insertarOUsuario($db, $nombre, $email, $password, $rol, $debeCambiar);
            if ($res['exito']) {
                echo "\n✅ ¡USUARIO " . strtoupper($res['accion']) . " CON ÉXITO!\n";
                echo "   ID: {$res['id_usuario']}\n";
                echo "   Nombre: $nombre\n";
                echo "   Email: $email\n";
                echo "   Contraseña: $password\n";
                echo "   Rol: $rol\n";
                echo "   Cambio de contraseña pendiente: " . ($debeCambiar ? 'SI' : 'NO') . "\n";
            } else {
                echo "\n❌ ERROR: " . $res['mensaje'] . "\n";
            }
            break;

        case '2':
            echo "\n--- INICIALIZAR USUARIOS PREDETERMINADOS DE TECNOFIX ---\n";
            echo "Se configurarán los 3 roles principales del sistema.\n";
            $claveComun = prompt("Ingrese la contraseña que desea para estas cuentas", "TecnoFix2026*");
            $forzarCambio = prompt("¿Exigir cambio de clave en el primer login? (s/n)", "n");
            $debeCambiar = strtolower($forzarCambio) === 's' ? 1 : 0;

            $cuentasDefault = [
                [
                    'nombre' => 'Administrador TecnoFix',
                    'email'  => 'admin@tecnofix.com',
                    'rol'    => 'Administrador'
                ],
                [
                    'nombre' => 'Carlos Ruiz (Técnico Senior)',
                    'email'  => 'carlos.tecnico@tecnofix.com',
                    'rol'    => 'Tecnico'
                ],
                [
                    'nombre' => 'María López (Recepción)',
                    'email'  => 'maria.recepcion@tecnofix.com',
                    'rol'    => 'Recepcion'
                ]
            ];

            echo "\nProcesando cuentas...\n";
            foreach ($cuentasDefault as $c) {
                $r = insertarOUsuario($db, $c['nombre'], $c['email'], $claveComun, $c['rol'], $debeCambiar);
                if ($r['exito']) {
                    echo "  ✓ [{$r['accion']}] {$c['email']} ({$c['rol']})\n";
                } else {
                    echo "  ✗ [error] {$c['email']}: {$r['mensaje']}\n";
                }
            }

            echo "\n✅ ¡Listo! Puedes ingresar al sistema en el navegador con:\n";
            echo "   Usuario: admin@tecnofix.com\n";
            echo "   Contraseña: $claveComun\n";
            break;

        case '3':
            listarUsuarios($db);
            break;

        case '4':
            echo "\n--- CAMBIAR CONTRASEÑA DE USUARIO EXISTENTE ---\n";
            $email = prompt("Correo del usuario a modificar");
            $stmt = $db->prepare("SELECT id_usuario, nombre FROM usuarios WHERE email = :email");
            $stmt->execute([':email' => $email]);
            $u = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$u) {
                echo "[ERROR] No se encontró ningún usuario con el correo '$email'.\n";
                break;
            }

            $nuevaClave = prompt("Nueva contraseña para '{$u['nombre']}'");
            if (strlen($nuevaClave) < 6) {
                echo "[ERROR] La contraseña debe tener al menos 6 caracteres.\n";
                break;
            }

            $exigir = prompt("¿Obligar a cambiar la contraseña en su próximo inicio? (s/n)", "n");
            $debe = strtolower($exigir) === 's' ? 1 : 0;

            $hash = password_hash($nuevaClave, PASSWORD_BCRYPT);
            $stmtUpd = $db->prepare("UPDATE usuarios SET password_hash = :hash, debe_cambiar_pass = :debe WHERE id_usuario = :id");
            $stmtUpd->execute([
                ':hash' => $hash,
                ':debe' => $debe,
                ':id'   => $u['id_usuario']
            ]);

            echo "\n✅ ¡Contraseña actualizada exitosamente para {$u['nombre']} ({$email})!\n";
            break;

        case '5':
            echo "\nSaliendo del gestor de usuarios. ¡Hasta pronto!\n\n";
            exit(0);

        default:
            echo "\n[AVISO] Opción no válida. Ingrese un número del 1 al 5.\n";
            break;
    }
}
