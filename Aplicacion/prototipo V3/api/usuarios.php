<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../controllers/AuthController.php';

$action = $_GET['action'] ?? $_POST['action'] ?? 'listar';

if ($action === 'crear') {
    echo json_encode(AuthController::crearUsuario());
} elseif ($action === 'cambiar_password') {
    echo json_encode(AuthController::cambiarPasswordInicial());
} else {
    $usuarios = AuthController::listarUsuarios();
    echo json_encode(['success' => true, 'data' => $usuarios]);
}
exit;
