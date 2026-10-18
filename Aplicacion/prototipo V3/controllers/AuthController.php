<?php
/**
 * CONTROLADOR: AuthController
 * Maneja la Autenticación, Creación de Usuarios por Admin y Cambio de Contraseña Inicial
 */

require_once __DIR__ . '/../config/session.php';
startSecureSession();

require_once __DIR__ . '/../models/Usuario.php';

class AuthController {

    public static function processLogin() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!self::isCsrfValid()) {
                return ['success' => false, 'message' => 'La sesión del formulario expiró. Recargue la página.'];
            }

            $username = trim(self::postString('username'));
            $password = self::postString('password');

            if (empty($username) || empty($password)) {
                return ['success' => false, 'message' => 'Por favor complete todos los campos de acceso.'];
            }

            $user = Usuario::login($username, $password);

            if ($user) {
                session_regenerate_id(true);
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
            if (!self::isAuthenticated() || ($_SESSION['user_rol'] ?? null) !== 'Administrador') {
                return ['success' => false, 'message' => 'Solo un Administrador tiene permisos para crear usuarios.'];
            }
            if (!self::isCsrfValid()) {
                return ['success' => false, 'message' => 'La sesión del formulario expiró. Recargue la página.'];
            }

            $nombre = trim(self::postString('nombre'));
            $email = trim(self::postString('email'));
            $rol = trim(self::postString('rol', 'Recepcion'));

            if (empty($nombre) || empty($email) || empty($rol)) {
                return ['success' => false, 'message' => 'Por favor complete todos los campos obligatorios del usuario.'];
            }
            if (strlen($nombre) > 100 || strlen($email) > 120 || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return ['success' => false, 'message' => 'El nombre o el correo electrónico no son válidos.'];
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
            if (!self::isAuthenticated() || !self::isCsrfValid()) {
                return ['success' => false, 'message' => 'Sesión no válida para cambio de contraseña.'];
            }

            $userId = intval($_SESSION['user_id'] ?? 0);
            $nuevaPassword = self::postString('nueva_password');
            $confirmarPassword = self::postString('confirmar_password');

            if ($userId <= 0) {
                return ['success' => false, 'message' => 'Sesión no válida para cambio de contraseña.'];
            }

            if (strlen($nuevaPassword) < 12) {
                return ['success' => false, 'message' => 'La nueva contraseña debe tener al menos 12 caracteres.'];
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
        if (!self::isAuthenticated() || self::isPasswordChangePending() || ($_SESSION['user_rol'] ?? '') !== 'Administrador') {
            return ['success' => false, 'message' => 'Debe iniciar sesión para consultar usuarios.'];
        }
        return Usuario::getAll();
    }

    public static function logout() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !self::isCsrfValid()) {
            return ['success' => false, 'message' => 'Solicitud no válida.'];
        }
        session_unset();
        session_destroy();
        return ['success' => true, 'message' => 'Sesión cerrada correctamente.'];
    }

    public static function isAuthenticated() {
        return !empty($_SESSION['usuario_logged']) && $_SESSION['usuario_logged'] === true;
    }

    public static function isPasswordChangePending() {
        return !empty($_SESSION['debe_cambiar_pass']);
    }

    public static function isCsrfValid() {
        $submittedToken = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        $sessionToken = $_SESSION['csrf_token'] ?? '';
        return $submittedToken !== '' && $sessionToken !== '' && hash_equals($sessionToken, $submittedToken);
    }

    private static function postString($key, $default = '') {
        $value = $_POST[$key] ?? $default;
        return is_string($value) ? $value : $default;
    }
}
