<?php
/**
 * CONTROLADOR: AuthController
 * Maneja el flujo de Autenticación, Inicio de Sesión y Cierre de Sesión
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

            // Validaciones de entrada
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

                return ['success' => true, 'user' => $user, 'message' => 'Acceso concedido. Redirigiendo...'];
            } else {
                return ['success' => false, 'message' => 'Credenciales inválidas. Compruebe su usuario o contraseña.'];
            }
        }
        return ['success' => false, 'message' => 'Método no permitido.'];
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
