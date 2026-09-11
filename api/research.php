<?php
/**
 * Research API (Search, Filter, CRUD)
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

// GET: Fetch / Search Researches
if ($method === 'GET') {
    $q = trim($_GET['q'] ?? '');
    $cat = trim($_GET['category'] ?? '');

    if (empty($q) && (empty($cat) || $cat === 'all')) {
        $data = Cache::remember('all_researches', 3600, function() use ($pdo) {
            return $pdo->query("SELECT * FROM research ORDER BY id DESC")->fetchAll();
        });
    } else {
        $sql = "SELECT * FROM research WHERE 1=1";
        $params = [];

        if (!empty($q)) {
            $sql .= " AND (author_name LIKE ? OR research_title LIKE ? OR keywords LIKE ?)";
            $wildcard = "%{$q}%";
            $params[] = $wildcard;
            $params[] = $wildcard;
            $params[] = $wildcard;
        }

        if (!empty($cat) && $cat !== 'all') {
            $sql .= " AND category = ?";
            $params[] = $cat;
        }

        $sql .= " ORDER BY id DESC";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $data = $stmt->fetchAll();
    }

    echo json_encode(['status' => 'success', 'data' => $data]);
    exit;
}

// POST: Add new Research (Admin & Faculty only)
if ($method === 'POST') {
    RoleMiddleware::handle(['admin', 'faculty'], true);
    CsrfMiddleware::handle(true);

    $author = trim($_POST['author_name'] ?? '');
    $title = trim($_POST['research_title'] ?? '');
    $monthYear = trim($_POST['month_year'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $abstract = trim($_POST['abstract'] ?? '');
    $keywords = trim($_POST['keywords'] ?? '');

    if (empty($author) || empty($title) || empty($monthYear) || empty($category)) {
        http_response_code(422);
        echo json_encode(['status' => 'error', 'message' => 'Please provide author, title, date, and category.']);
        exit;
    }

    $stmt = $pdo->prepare("INSERT INTO research (author_name, research_title, month_year, category, abstract, keywords) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$author, $title, $monthYear, $category, $abstract, $keywords]);
    Cache::flush();

    echo json_encode(['status' => 'success', 'message' => 'Research paper created successfully.', 'id' => $pdo->lastInsertId()]);
    exit;
}

// DELETE: Delete Research (Admin only)
if ($method === 'DELETE') {
    RoleMiddleware::handle(['admin'], true);
    CsrfMiddleware::handle(true);

    $id = intval($_GET['id'] ?? 0);
    if ($id <= 0) {
        http_response_code(422);
        echo json_encode(['status' => 'error', 'message' => 'Valid ID is required.']);
        exit;
    }

    $stmt = $pdo->prepare("DELETE FROM research WHERE id = ?");
    $stmt->execute([$id]);
    Cache::flush();

    echo json_encode(['status' => 'success', 'message' => 'Research record removed.']);
    exit;
}


http_response_code(405);
echo json_encode(['status' => 'error', 'message' => 'Method Not Allowed']);
