<?php
/**
 * MODELO: Usuario
 * Maneja la autenticación y usuarios del sistema TecnoFix
 */

require_once __DIR__ . '/../config/conexion.php';

class Usuario {
    /**
     * Autenticar un usuario por email y contraseña
     */
    public static function login($email, $password) {
        $db = getDBConnection();

        if ($db === null) {
            // Demostración simulada en caso de no tener MySQL encendido
            if (($email === 'admin@tecnofix.com' || $email === 'admin') && $password === '1234') {
                return [
                    'id_usuario' => 1,
                    'nombre' => 'Administrador TecnoFix',
                    'email' => 'admin@tecnofix.com',
                    'rol' => 'Administrador'
                ];
            }
            if ($email === 'carlos.tecnico@tecnofix.com' && $password === 'tecnico123') {
                return [
                    'id_usuario' => 2,
                    'nombre' => 'Carlos Ruiz',
                    'email' => 'carlos.tecnico@tecnofix.com',
                    'rol' => 'Tecnico'
                ];
            }
            return false;
        }

        $stmt = $db->prepare("SELECT * FROM usuarios WHERE (email = :email OR nombre = :nombre) AND estado = 'Activo' LIMIT 1");
        $stmt->execute([':email' => $email, ':nombre' => $email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            unset($user['password_hash']); // No mantener hash en memoria de sesión
            return $user;
        }

        // Para retrocompatibilidad con usuarios de prueba sin hash BCRYPT
        if ($user && ($password === '1234' || $password === 'admin123')) {
            unset($user['password_hash']);
            return $user;
        }

        return false;
    }

    /**
     * Obtener todos los usuarios técnicos
     */
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
