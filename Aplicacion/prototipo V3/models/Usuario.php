<?php
/**
 * MODELO: Usuario
 * Maneja la autenticación, creación de usuarios por Admin y cambio de contraseña
 */

require_once __DIR__ . '/../config/conexion.php';

class Usuario {

    /**
     * Autenticar un usuario por email/nombre y contraseña
     */
    public static function login($email, $password) {
        $db = getDBConnection();

        if ($db === null) {
            // Demostración simulada
            if (($email === 'admin@tecnofix.com' || $email === 'admin') && $password === '1234') {
                return [
                    'id_usuario' => 1,
                    'nombre' => 'Administrador TecnoFix',
                    'email' => 'admin@tecnofix.com',
                    'rol' => 'Administrador',
                    'debe_cambiar_pass' => 0
                ];
            }
            if ($email === 'pedro.nuevo@tecnofix.com' && $password === 'tecnico123') {
                return [
                    'id_usuario' => 4,
                    'nombre' => 'Pedro Ramírez',
                    'email' => 'pedro.nuevo@tecnofix.com',
                    'rol' => 'Tecnico',
                    'debe_cambiar_pass' => 1
                ];
            }
            return false;
        }

        $stmt = $db->prepare("SELECT * FROM usuarios WHERE (email = :email OR nombre = :nombre) AND estado = 'Activo' LIMIT 1");
        $stmt->execute([':email' => $email, ':nombre' => $email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            unset($user['password_hash']);
            $user['debe_cambiar_pass'] = intval($user['debe_cambiar_pass'] ?? 0);
            return $user;
        }

        // Retrocompatibilidad exclusiva para usuario demo 'admin' / '1234'
        if ($user && $user['id_usuario'] == 1 && $password === '1234') {
            unset($user['password_hash']);
            $user['debe_cambiar_pass'] = intval($user['debe_cambiar_pass'] ?? 0);
            return $user;
        }

        return false;
    }

    /**
     * Crear un nuevo usuario por parte del Administrador
     * Asigna contraseña por defecto: rol + "123" (Ej: tecnico123)
     * Marca debe_cambiar_pass = 1
     */
    public static function crearUsuario($nombre, $email, $rol) {
        $db = getDBConnection();

        $rolClean = strtolower(trim($rol));
        if ($rolClean === 'técnico') $rolClean = 'tecnico';
        if ($rolClean === 'recepción') $rolClean = 'recepcion';

        $defaultPassword = $rolClean . '123';
        $passwordHash = password_hash($defaultPassword, PASSWORD_BCRYPT);

        if ($db === null) {
            return [
                'exito' => true,
                'default_password' => $defaultPassword,
                'mensaje' => "Usuario $nombre creado exitosamente. Contraseña temporal: $defaultPassword"
            ];
        }

        try {
            $stmtCheck = $db->prepare("SELECT id_usuario FROM usuarios WHERE email = :email");
            $stmtCheck->execute([':email' => $email]);
            if ($stmtCheck->fetch()) {
                return ['exito' => false, 'error' => 'Ya existe un usuario registrado con este correo electrónico.'];
            }

            $stmt = $db->prepare("INSERT INTO usuarios (nombre, email, password_hash, rol, estado, debe_cambiar_pass) 
                                  VALUES (:nombre, :email, :pass, :rol, 'Activo', 1)");
            $stmt->execute([
                ':nombre' => $nombre,
                ':email' => $email,
                ':pass' => $passwordHash,
                ':rol' => $rol
            ]);

            return [
                'exito' => true,
                'id_usuario' => $db->lastInsertId(),
                'default_password' => $defaultPassword,
                'mensaje' => "Usuario registrado con éxito. Contraseña por defecto asignada: '$defaultPassword'"
            ];
        } catch (Exception $e) {
            return ['exito' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Cambiar la contraseña del usuario en su primer inicio de sesión
     */
    public static function cambiarPassword($idUsuario, $nuevaPassword) {
        $db = getDBConnection();
        $passwordHash = password_hash($nuevaPassword, PASSWORD_BCRYPT);

        if ($db === null) {
            return ['exito' => true, 'mensaje' => 'Contraseña actualizada correctamente (Simulación).'];
        }

        try {
            $stmt = $db->prepare("UPDATE usuarios SET password_hash = :pass, debe_cambiar_pass = 0 WHERE id_usuario = :id");
            $stmt->execute([
                ':pass' => $passwordHash,
                ':id' => $idUsuario
            ]);
            return ['exito' => true, 'mensaje' => 'Tu contraseña ha sido actualizada con éxito.'];
        } catch (Exception $e) {
            return ['exito' => false, 'error' => $e->getMessage()];
        }
    }

    public static function getAll() {
        $db = getDBConnection();
        if ($db === null) {
            return [
                ['id_usuario' => 1, 'nombre' => 'Administrador TecnoFix', 'email' => 'admin@tecnofix.com', 'rol' => 'Administrador', 'estado' => 'Activo', 'debe_cambiar_pass' => 0],
                ['id_usuario' => 2, 'nombre' => 'Carlos Ruiz', 'email' => 'carlos.tecnico@tecnofix.com', 'rol' => 'Tecnico', 'estado' => 'Activo', 'debe_cambiar_pass' => 0],
                ['id_usuario' => 3, 'nombre' => 'María López', 'email' => 'maria.recepcion@tecnofix.com', 'rol' => 'Recepcion', 'estado' => 'Activo', 'debe_cambiar_pass' => 0],
                ['id_usuario' => 4, 'nombre' => 'Pedro Ramírez', 'email' => 'pedro.nuevo@tecnofix.com', 'rol' => 'Tecnico', 'estado' => 'Activo', 'debe_cambiar_pass' => 1]
            ];
        }

        $stmt = $db->query("SELECT id_usuario, nombre, email, rol, estado, debe_cambiar_pass, fecha_creacion FROM usuarios ORDER BY id_usuario DESC");
        return $stmt->fetchAll();
    }

    public static function getTecnicos() {
        $db = getDBConnection();
        if ($db === null) {
            return [
                ['id_usuario' => 2, 'nombre' => 'Carlos Ruiz (Técnico Senior)', 'rol' => 'Tecnico']
            ];
        }

        $stmt = $db->query("SELECT id_usuario, nombre, email, rol FROM usuarios WHERE rol IN ('Tecnico', 'Administrador') AND estado = 'Activo'");
        return $stmt->fetchAll();
    }
}
