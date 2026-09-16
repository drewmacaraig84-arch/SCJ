<?php
/**
 * Seeder Script for True Thesis Titles (2014 - 2026)
 * School of Criminal Justice Education (SCJE) Information System
 */

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/cache.php';

$pdo = DB::getConnection();
$driver = DB::getDriver();
echo "Active Database Driver: " . strtoupper($driver) . "\n";

$jsonPath = __DIR__ . '/theses_data.json';
if (!file_exists($jsonPath)) {
    die("Error: theses_data.json not found at {$jsonPath}\n");
}

$theses = json_decode(file_get_contents($jsonPath), true);
if (!is_array($theses) || empty($theses)) {
    die("Error: Invalid or empty theses data in JSON file.\n");
}

echo "Found " . count($theses) . " thesis records to import.\n";

// Clear existing research table
$pdo->exec("DELETE FROM research");
if ($driver === 'sqlite') {
    $pdo->exec("DELETE FROM sqlite_sequence WHERE name = 'research'");
}

$stmt = $pdo->prepare("INSERT INTO research (id, author_name, research_title, month_year, category, abstract, keywords, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");

$inserted = 0;
$pdo->beginTransaction();
try {
    foreach ($theses as $t) {
        $stmt->execute([
            $t['id'],
            $t['author_name'],
            $t['research_title'],
            $t['month_year'],
            $t['category'],
            $t['abstract'],
            $t['keywords'],
            $t['status'] ?? 'Published'
        ]);
        $inserted++;
    }
    $pdo->commit();
    echo "Successfully inserted {$inserted} thesis records into 'research' table.\n";
} catch (Exception $e) {
    $pdo->rollBack();
    die("Seeding failed: " . $e->getMessage() . "\n");
}

// Invalidate all caches
Cache::flush();
echo "All application caches successfully invalidated.\n";

// Verification
$totalCount = $pdo->query("SELECT COUNT(*) FROM research")->fetchColumn();
echo "Total verified records in database: {$totalCount}\n";
