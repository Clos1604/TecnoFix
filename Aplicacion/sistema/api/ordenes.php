<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../controllers/OrdenesController.php';

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
