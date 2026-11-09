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

        return false;
    }

    /**
     * Crear un nuevo usuario por parte del Administrador
    * Asigna una contraseña temporal aleatoria y exige cambiarla.
     */
    public static function crearUsuario($nombre, $email, $rol) {
        $db = getDBConnection();

        if (!in_array($rol, ['Administrador', 'Tecnico', 'Recepcion'], true)) {
            return ['exito' => false, 'error' => 'El rol seleccionado no es válido.'];
        }

        $defaultPassword = bin2hex(random_bytes(12));
        $passwordHash = password_hash($defaultPassword, PASSWORD_BCRYPT);

        if ($db === null) {
            return ['exito' => false, 'error' => 'No hay conexión disponible con la base de datos.'];
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
                'mensaje' => 'Usuario registrado con una contraseña temporal. Deberá cambiarla al iniciar sesión.'
            ];
        } catch (Throwable $exception) {
            return ['exito' => false, 'error' => 'No se pudo crear el usuario. Verifique los datos e inténtelo de nuevo.'];
        }
    }

    /**
     * Cambiar la contraseña del usuario en su primer inicio de sesión
     */
    public static function cambiarPassword($idUsuario, $nuevaPassword) {
        $db = getDBConnection();
        $passwordHash = password_hash($nuevaPassword, PASSWORD_BCRYPT);

        if ($db === null) {
            return ['exito' => false, 'error' => 'No hay conexión disponible con la base de datos.'];
        }

        try {
            $stmt = $db->prepare("UPDATE usuarios SET password_hash = :pass, debe_cambiar_pass = 0 WHERE id_usuario = :id");
            $stmt->execute([
                ':pass' => $passwordHash,
                ':id' => $idUsuario
            ]);
            return ['exito' => true, 'mensaje' => 'Tu contraseña ha sido actualizada con éxito.'];
        } catch (Throwable $exception) {
            return ['exito' => false, 'error' => 'No se pudo actualizar la contraseña.'];
        }
    }

    public static function getAll() {
        $db = getDBConnection();
        if ($db === null) {
            return [];
        }

        $stmt = $db->query("SELECT id_usuario, nombre, email, rol, estado, debe_cambiar_pass, fecha_creacion FROM usuarios ORDER BY id_usuario DESC");
        return $stmt->fetchAll();
    }

    public static function getTecnicos() {
        $db = getDBConnection();
        if ($db === null) {
            return [];
        }

        $stmt = $db->query("SELECT id_usuario, nombre, email, rol FROM usuarios WHERE rol IN ('Tecnico', 'Administrador') AND estado = 'Activo'");
        return $stmt->fetchAll();
    }
}
