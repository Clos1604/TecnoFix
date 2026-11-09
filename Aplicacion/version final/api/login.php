<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../controllers/AuthController.php';

$response = AuthController::processLogin();
echo json_encode($response);
exit;
