<?php
/**
 * Seeder for DWCC Enrolled Criminology Students (SY 2026-2027)
 */
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/cache.php';

$pdo = get_db();
$driver = DB::getDriver();
echo "Active database driver: {$driver}\n";

// 1. Ensure columns exist in MySQL and SQLite
function ensureColumns($db, $isSqlite = false) {
    $cols = [];
    if ($isSqlite) {
        $stmt = $db->query("PRAGMA table_info(users)");
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $cols[] = strtolower($row['name']);
        }
    } else {
        $stmt = $db->query("SHOW COLUMNS FROM users");
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $cols[] = strtolower($row['Field']);
        }
    }

    if (!in_array('course', $cols)) {
        echo "Adding 'course' column...\n";
        $db->exec("ALTER TABLE users ADD COLUMN course VARCHAR(50) NULL");
    }
    if (!in_array('year_level', $cols)) {
        echo "Adding 'year_level' column...\n";
        $db->exec("ALTER TABLE users ADD COLUMN year_level INT NULL");
    }
    if (!in_array('gender', $cols)) {
        echo "Adding 'gender' column...\n";
        $db->exec("ALTER TABLE users ADD COLUMN gender VARCHAR(20) NULL");
    }
}

ensureColumns($pdo, $driver === 'sqlite');

// Also ensure SQLite file has these columns if MySQL is primary
$sqlitePath = __DIR__ . '/scj.sqlite';
$sqlitePdo = null;
if (file_exists($sqlitePath)) {
    try {
        $sqlitePdo = new PDO('sqlite:' . $sqlitePath);
        $sqlitePdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        ensureColumns($sqlitePdo, true);
    } catch (Exception $e) {
        echo "SQLite column check note: " . $e->getMessage() . "\n";
    }
}

// 2. Read and parse students_raw.txt
$lines = file(__DIR__ . '/students_raw.txt', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
echo "Total raw lines read: " . count($lines) . "\n";

$students = [];
$pattern = '/^\s*(\d+)\s+(\d+)\s+(.+?)\s+(BS\s+CRIM\s+\d+)\s+(\d+)\s+(MALE|FEMALE)\s*$/i';

foreach ($lines as $line) {
    $line = trim($line);
    if (empty($line)) continue;

    if (preg_match($pattern, $line, $matches)) {
        $seq = (int)$matches[1];
        $idNumber = trim($matches[2]);
        $nameRaw = trim($matches[3]);
        // Normalize name: fix space before comma e.g. "ACLAN , JUSTIN" -> "ACLAN, JUSTIN"
        $name = preg_replace('/\s+,\s*/', ', ', $nameRaw);
        $course = trim($matches[4]);
        $yearLevel = (int)$matches[5];
        $gender = strtoupper(trim($matches[6]));

        // Extract Last Name (everything before the first comma)
        $parts = explode(',', $name);
        $lastName = trim($parts[0]);

        // Default password: Last Name + ID Number (e.g. ABAG51955)
        $rawPassword = $lastName . $idNumber;
        $hashedPassword = password_hash($rawPassword, PASSWORD_BCRYPT);
        $email = $idNumber . '@dwcc.edu.ph';

        $students[] = [
            'seq' => $seq,
            'id_number' => $idNumber,
            'name' => $name,
            'last_name' => $lastName,
            'course' => $course,
            'year_level' => $yearLevel,
            'gender' => $gender,
            'password_raw' => $rawPassword,
            'password_hash' => $hashedPassword,
            'email' => $email
        ];
    } else {
        echo "FAILED TO PARSE LINE: [{$line}]\n";
    }
}

echo "Successfully parsed " . count($students) . " students.\n";

if (count($students) !== 302) {
    echo "Warning: Expected 302 students, but parsed " . count($students) . ".\n";
}

// 3. Upsert into MySQL
$insertStmt = $pdo->prepare("
    INSERT INTO users (id_number, name, email, password, role, course, year_level, gender)
    VALUES (:id_number, :name, :email, :password, 'student', :course, :year_level, :gender)
    ON DUPLICATE KEY UPDATE
        name = VALUES(name),
        password = VALUES(password),
        course = VALUES(course),
        year_level = VALUES(year_level),
        gender = VALUES(gender)
");

$sqliteInsert = null;
if ($sqlitePdo) {
    $sqliteInsert = $sqlitePdo->prepare("
        INSERT INTO users (id_number, name, email, password, role, course, year_level, gender)
        VALUES (:id_number, :name, :email, :password, 'student', :course, :year_level, :gender)
        ON CONFLICT(id_number) DO UPDATE SET
            name = excluded.name,
            password = excluded.password,
            course = excluded.course,
            year_level = excluded.year_level,
            gender = excluded.gender
    ");
}

$count = 0;
foreach ($students as $s) {
    $params = [
        ':id_number' => $s['id_number'],
        ':name' => $s['name'],
        ':email' => $s['email'],
        ':password' => $s['password_hash'],
        ':course' => $s['course'],
        ':year_level' => $s['year_level'],
        ':gender' => $s['gender']
    ];

    try {
        $insertStmt->execute($params);
        if ($sqliteInsert) {
            $sqliteInsert->execute($params);
        }
        $count++;
    } catch (Exception $e) {
        echo "Error saving student {$s['id_number']} ({$s['name']}): " . $e->getMessage() . "\n";
    }
}

echo "Seeded {$count} students successfully into database!\n";
Cache::flush();
echo "Cache cleared.\n";
