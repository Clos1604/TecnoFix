<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../controllers/AuthController.php';

$response = AuthController::logout();
if (empty($response['success'])) {
	http_response_code(400);
}
echo json_encode($response);
exit;
