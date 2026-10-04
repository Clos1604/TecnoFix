<?php
/**
 * CONTROLADOR: AuthController
 * Maneja la Autenticación, Creación de Usuarios por Admin y Cambio de Contraseña Inicial
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../models/Usuario.php';

class AuthController {

    public static function processLogin() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = trim($_POST['username'] ?? '');
            $password = $_POST['password'] ?? '';

            if (empty($username) || empty($password)) {
                return ['success' => false, 'message' => 'Por favor complete todos los campos de acceso.'];
            }

            $user = Usuario::login($username, $password);

            if ($user) {
                $_SESSION['usuario_logged'] = true;
                $_SESSION['user_id'] = $user['id_usuario'];
                $_SESSION['user_nombre'] = $user['nombre'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_rol'] = $user['rol'];
                $_SESSION['debe_cambiar_pass'] = intval($user['debe_cambiar_pass'] ?? 0);

                return [
                    'success' => true,
                    'user' => $user,
                    'debe_cambiar_pass' => $_SESSION['debe_cambiar_pass'],
                    'message' => 'Acceso concedido. Redirigiendo...'
                ];
            } else {
                return ['success' => false, 'message' => 'Credenciales inválidas. Compruebe su usuario o contraseña.'];
            }
        }
        return ['success' => false, 'message' => 'Método no permitido.'];
    }

    public static function crearUsuario() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Verificar si el usuario actual es Administrador
            $userRol = $_SESSION['user_rol'] ?? 'Administrador';
            if ($userRol !== 'Administrador') {
                return ['success' => false, 'message' => 'Solo un Administrador tiene permisos para crear usuarios.'];
            }

            $nombre = trim($_POST['nombre'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $rol = trim($_POST['rol'] ?? 'Recepcion');

            if (empty($nombre) || empty($email) || empty($rol)) {
                return ['success' => false, 'message' => 'Por favor complete todos los campos obligatorios del usuario.'];
            }

            $res = Usuario::crearUsuario($nombre, $email, $rol);
            if (!empty($res['exito'])) {
                return [
                    'success' => true,
                    'default_password' => $res['default_password'],
                    'message' => $res['mensaje']
                ];
            } else {
                return ['success' => false, 'message' => $res['error'] ?? 'Error al crear usuario.'];
            }
        }
        return ['success' => false, 'message' => 'Petición inválida.'];
    }

    public static function cambiarPasswordInicial() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $userId = $_SESSION['user_id'] ?? intval($_POST['user_id'] ?? 0);
            $nuevaPassword = $_POST['nueva_password'] ?? '';
            $confirmarPassword = $_POST['confirmar_password'] ?? '';

            if ($userId <= 0) {
                return ['success' => false, 'message' => 'Sesión no válida para cambio de contraseña.'];
            }

            if (strlen($nuevaPassword) < 6) {
                return ['success' => false, 'message' => 'La nueva contraseña debe tener al menos 6 caracteres.'];
            }

            if ($nuevaPassword !== $confirmarPassword) {
                return ['success' => false, 'message' => 'Las contraseñas ingresadas no coinciden.'];
            }

            $res = Usuario::cambiarPassword($userId, $nuevaPassword);

            if (!empty($res['exito'])) {
                $_SESSION['debe_cambiar_pass'] = 0;
                return ['success' => true, 'message' => 'Contraseña actualizada exitosamente. Ya puedes acceder al sistema.'];
            } else {
                return ['success' => false, 'message' => $res['error'] ?? 'No se pudo actualizar la contraseña.'];
            }
        }
        return ['success' => false, 'message' => 'Petición inválida.'];
    }

    public static function listarUsuarios() {
        return Usuario::getAll();
    }

    public static function logout() {
        session_unset();
        session_destroy();
        return ['success' => true, 'message' => 'Sesión cerrada correctamente.'];
    }

    public static function isAuthenticated() {
        return !empty($_SESSION['usuario_logged']) && $_SESSION['usuario_logged'] === true;
    }
}
