<?php
/**
 * Laboratory Equipment API (Search, Filter, CRUD)
 * School of Criminal Justice Education (SCJE) Information System
 */

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/middleware/SecurityHeadersMiddleware.php';
require_once __DIR__ . '/../includes/middleware/CsrfMiddleware.php';
require_once __DIR__ . '/../includes/middleware/AuthMiddleware.php';
require_once __DIR__ . '/../includes/middleware/RoleMiddleware.php';
require_once __DIR__ . '/../includes/cache.php';

SecurityHeadersMiddleware::handle();
$pdo = get_db();
$method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');

// GET: Fetch / Search Laboratory Equipment
if ($method === 'GET') {
    $q = trim($_GET['q'] ?? '');
    $lab = trim($_GET['lab'] ?? '');

    if (empty($q) && (empty($lab) || $lab === 'all')) {
        $data = Cache::remember('all_equipment', 3600, function() use ($pdo) {
            return $pdo->query("SELECT * FROM equipment ORDER BY id ASC")->fetchAll();
        });
    } else {
        $sql = "SELECT * FROM equipment WHERE 1=1";
        $params = [];

        if (!empty($q)) {
            $sql .= " AND (equipment_code LIKE ? OR equipment_name LIKE ? OR brand LIKE ? OR model LIKE ? OR current_location LIKE ?)";
            $wildcard = "%{$q}%";
            $params[] = $wildcard;
            $params[] = $wildcard;
            $params[] = $wildcard;
            $params[] = $wildcard;
            $params[] = $wildcard;
        }

        if (!empty($lab) && $lab !== 'all') {
            $sql .= " AND laboratory_category = ?";
            $params[] = $lab;
        }

        $sql .= " ORDER BY id ASC";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $data = $stmt->fetchAll();
    }

    echo json_encode(['status' => 'success', 'data' => $data]);
    exit;
}

// POST: Add new Equipment (Admin only)
if ($method === 'POST') {
    RoleMiddleware::handle(['admin'], true);
    CsrfMiddleware::handle(true);

    $code = trim($_POST['equipment_code'] ?? '');
    $name = trim($_POST['equipment_name'] ?? '');
    $brand = trim($_POST['brand'] ?? '');
    $model = trim($_POST['model'] ?? '');
    $location = trim($_POST['current_location'] ?? '');
    $labCat = trim($_POST['laboratory_category'] ?? 'Criminalistics Laboratory');
    $status = trim($_POST['status'] ?? 'Available');
    $serial = trim($_POST['serial_number'] ?? '');
    $desc = trim($_POST['description'] ?? '');

    if (empty($code) || empty($name) || empty($brand) || empty($model) || empty($location)) {
        http_response_code(422);
        echo json_encode(['status' => 'error', 'message' => 'Please provide equipment code, name, brand, model, and location.']);
        exit;
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO equipment (equipment_code, equipment_name, brand, model, current_location, laboratory_category, status, serial_number, description) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$code, $name, $brand, $model, $location, $labCat, $status, $serial, $desc]);
        Cache::flush();

        echo json_encode(['status' => 'success', 'message' => 'Equipment added successfully.', 'id' => $pdo->lastInsertId()]);
    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Error: ' . $e->getMessage()]);
    }
    exit;
}

// DELETE: Delete Equipment (Admin only)
if ($method === 'DELETE') {
    RoleMiddleware::handle(['admin'], true);
    CsrfMiddleware::handle(true);

    $id = intval($_GET['id'] ?? 0);
    if ($id <= 0) {
        http_response_code(422);
        echo json_encode(['status' => 'error', 'message' => 'Valid ID is required.']);
        exit;
    }

    $stmt = $pdo->prepare("DELETE FROM equipment WHERE id = ?");
    $stmt->execute([$id]);
    Cache::flush();

    echo json_encode(['status' => 'success', 'message' => 'Equipment record deleted.']);
    exit;
}


http_response_code(405);
echo json_encode(['status' => 'error', 'message' => 'Method Not Allowed']);
