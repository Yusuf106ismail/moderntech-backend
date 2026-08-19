<?php
require_once __DIR__ . '/helpers/response.php';
require_once __DIR__ . '/controllers/AuthController.php';
require_once __DIR__ . '/controllers/EmployeeController.php';
require_once __DIR__ . '/controllers/TimeOffController.php';

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];

// Parse URL parameters for ID matching (e.g. /api/employees/1)
$parts = explode('/', trim($uri, '/'));
$resource = $parts[1] ?? null;
$id = $parts[2] ?? null;

if ($method === 'OPTIONS') {
    sendResponse(200, ["status" => "success"]);
}

switch ($resource) {
    case 'login':
        if ($method === 'POST') (new AuthController())->login();
        break;

    case 'logout':
        if ($method === 'POST') (new AuthController())->logout();
        break;

    case 'employees':
        $emp = new EmployeeController();
        if ($method === 'GET' && !$id) $emp->getAll();
        elseif ($method === 'GET' && $id) $emp->getOne($id);
        elseif ($method === 'POST') $emp->create();
        elseif ($method === 'PUT' && $id) $emp->update($id);
        elseif ($method === 'DELETE' && $id) $emp->delete($id);
        break;

    case 'time-off':
        $timeOff = new TimeOffController();
        if ($method === 'GET') $timeOff->getAll();
        elseif ($method === 'POST') $timeOff->create();
        elseif ($method === 'PUT' && $id) $timeOff->updateStatus($id);
        break;

    default:
        sendResponse(404, ["status" => "error", "message" => "Endpoint not found"]);
        break;
}
