<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../controllers/AuthController.php';

$action = $_GET['action'] ?? $_POST['action'] ?? 'listar';

if ($action === 'crear') {
    echo json_encode(AuthController::crearUsuario());
} elseif ($action === 'cambiar_password') {
    echo json_encode(AuthController::cambiarPasswordInicial());
} else {
    if (!AuthController::isAuthenticated() || AuthController::isPasswordChangePending()) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Debe iniciar sesión.']);
        exit;
    }
    if (($_SESSION['user_rol'] ?? '') !== 'Administrador') {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'No tiene permisos para consultar usuarios.']);
        exit;
    }
    $usuarios = AuthController::listarUsuarios();
    echo json_encode(['success' => true, 'data' => $usuarios]);
}
exit;
