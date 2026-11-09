<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../controllers/AuthController.php';
require_once __DIR__ . '/../controllers/OrdenesController.php';

if (!AuthController::isAuthenticated() || AuthController::isPasswordChangePending()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Debe iniciar sesión.']);
    exit;
}

if (!in_array($_SESSION['user_rol'] ?? '', ['Administrador', 'Recepcion', 'Tecnico'], true)) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'No tiene permisos para consultar órdenes.']);
    exit;
}

$action = $_GET['action'] ?? $_POST['action'] ?? 'listar';

if ($action === 'crear') {
    echo json_encode(OrdenesController::crear());
} elseif ($action === 'cambiar_estado') {
    echo json_encode(OrdenesController::actualizarEstado());
} else {
    $filtro = $_GET['estado'] ?? null;
    $busq = $_GET['q'] ?? null;
    $ordenes = OrdenesController::index($filtro, $busq);
    echo json_encode(['success' => true, 'data' => $ordenes]);
}
exit;
