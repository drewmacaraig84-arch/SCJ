<?php
/**
 * Materials & Chemicals API (Search, Filter, CRUD)
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

// GET: Fetch / Search Materials & Chemicals
if ($method === 'GET') {
    $q = trim($_GET['q'] ?? '');
    $loc = trim($_GET['loc'] ?? '');
    $status = trim($_GET['status'] ?? '');

    if (empty($q) && (empty($loc) || $loc === 'all') && (empty($status) || $status === 'all')) {
        $data = Cache::remember('all_materials', 3600, function() use ($pdo) {
            return $pdo->query("SELECT * FROM materials_chemicals ORDER BY id ASC")->fetchAll();
        });
    } else {
        $sql = "SELECT * FROM materials_chemicals WHERE 1=1";
        $params = [];

        if (!empty($q)) {
            $sql .= " AND (item_code LIKE ? OR item_name LIKE ? OR brand LIKE ? OR location LIKE ? OR person_accountable LIKE ?)";
            $wildcard = "%{$q}%";
            $params[] = $wildcard;
            $params[] = $wildcard;
            $params[] = $wildcard;
            $params[] = $wildcard;
            $params[] = $wildcard;
        }

        if (!empty($loc) && $loc !== 'all') {
            $sql .= " AND location = ?";
            $params[] = $loc;
        }

        if (!empty($status) && $status !== 'all') {
            $sql .= " AND status = ?";
            $params[] = $status;
        }

        $sql .= " ORDER BY id ASC";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $data = $stmt->fetchAll();
    }

    echo json_encode(['status' => 'success', 'data' => $data]);
    exit;
}

// POST: Add new Material/Chemical (Admin only)
if ($method === 'POST') {
    RoleMiddleware::handle(['admin'], true);
    CsrfMiddleware::handle(true);

    $code = trim($_POST['item_code'] ?? '');
    $name = trim($_POST['item_name'] ?? '');
    $qty = trim($_POST['qty'] ?? '1');
    $unit = trim($_POST['unit'] ?? 'N/A');
    $brand = trim($_POST['brand'] ?? 'N/A');
    $location = trim($_POST['location'] ?? 'Crime Laboratory');
    $status = trim($_POST['status'] ?? 'Good Condition');
    $person = trim($_POST['person_accountable'] ?? 'Sir Jom');

    if (empty($code) || empty($name)) {
        http_response_code(422);
        echo json_encode(['status' => 'error', 'message' => 'Item code and item name are required.']);
        exit;
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO materials_chemicals (item_code, qty, unit, item_name, person_accountable, brand, status, location) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$code, $qty, $unit, $name, $person, $brand, $status, $location]);
        Cache::flush();

        echo json_encode(['status' => 'success', 'message' => 'Material/Chemical added successfully.', 'id' => $pdo->lastInsertId()]);
    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Error: ' . $e->getMessage()]);
    }
    exit;
}

// DELETE: Delete Material/Chemical (Admin only)
if ($method === 'DELETE') {
    RoleMiddleware::handle(['admin'], true);
    CsrfMiddleware::handle(true);

    $id = intval($_GET['id'] ?? 0);
    if ($id <= 0) {
        http_response_code(422);
        echo json_encode(['status' => 'error', 'message' => 'Valid ID is required.']);
        exit;
    }

    $stmt = $pdo->prepare("DELETE FROM materials_chemicals WHERE id = ?");
    $stmt->execute([$id]);
    Cache::flush();

    echo json_encode(['status' => 'success', 'message' => 'Material record deleted.']);
    exit;
}

http_response_code(405);
echo json_encode(['status' => 'error', 'message' => 'Method Not Allowed']);
