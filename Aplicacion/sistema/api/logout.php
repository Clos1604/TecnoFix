<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../controllers/AuthController.php';

$response = AuthController::logout();
echo json_encode($response);
exit;
