<?php
/**
 * Database Setup & Realistic Criminological Seeder
 * School of Criminal Justice Education (SCJE) Information System
 */

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';

function init_database(PDO $pdo, string $driver): array {
    $results = [];

    // SQLite vs MySQL data type variations
    $autoInc = ($driver === 'sqlite') ? 'INTEGER PRIMARY KEY AUTOINCREMENT' : 'INT AUTO_INCREMENT PRIMARY KEY';
    $nowDefault = ($driver === 'sqlite') ? "DATETIME DEFAULT CURRENT_TIMESTAMP" : "DATETIME DEFAULT CURRENT_TIMESTAMP";

    // 1. Create Tables
    $tables = [
        "users" => "CREATE TABLE IF NOT EXISTS users (
            id {$autoInc},
            id_number VARCHAR(50) NOT NULL UNIQUE,
            name VARCHAR(150) NOT NULL,
            email VARCHAR(150) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL,
            role VARCHAR(30) NOT NULL DEFAULT 'student',
            created_at {$nowDefault}
        );",

        "research" => "CREATE TABLE IF NOT EXISTS research (
            id {$autoInc},
            author_name VARCHAR(150) NOT NULL,
            research_title VARCHAR(300) NOT NULL,
            month_year VARCHAR(50) NOT NULL,
            category VARCHAR(100) NOT NULL,
            abstract TEXT NULL,
            keywords VARCHAR(255) NULL,
            status VARCHAR(50) DEFAULT 'Published',
            created_at {$nowDefault}
        );",

        "equipment" => "CREATE TABLE IF NOT EXISTS equipment (
            id {$autoInc},
            equipment_code VARCHAR(50) NOT NULL UNIQUE,
            equipment_name VARCHAR(150) NOT NULL,
            brand VARCHAR(100) NOT NULL,
            model VARCHAR(100) NOT NULL,
            current_location VARCHAR(150) NOT NULL,
            laboratory_category VARCHAR(100) NOT NULL,
            status VARCHAR(50) NOT NULL DEFAULT 'Available',
            serial_number VARCHAR(100) NULL,
            description TEXT NULL,
            person_accountable VARCHAR(150) NULL DEFAULT 'Sir Jom',
            date_acquired DATE NULL,
            created_at {$nowDefault}
        );",

        "materials_chemicals" => "CREATE TABLE IF NOT EXISTS materials_chemicals (
            id {$autoInc},
            item_code VARCHAR(50) NOT NULL UNIQUE,
            qty VARCHAR(50) NULL,
            unit VARCHAR(50) NULL,
            item_name VARCHAR(200) NOT NULL,
            person_accountable VARCHAR(150) NULL DEFAULT 'Sir Jom',
            brand VARCHAR(100) NULL,
            status VARCHAR(50) NOT NULL DEFAULT 'Good Condition',
            location VARCHAR(150) NOT NULL DEFAULT 'Crime Laboratory',
            created_at {$nowDefault}
        );",

        "faculty" => "CREATE TABLE IF NOT EXISTS faculty (
            id {$autoInc},
            name VARCHAR(150) NOT NULL,
            position VARCHAR(150) NOT NULL,
            role_level VARCHAR(50) NOT NULL DEFAULT 'faculty',
            email VARCHAR(150) NULL,
            specialization VARCHAR(255) NULL,
            research_interests VARCHAR(255) NULL,
            office_location VARCHAR(150) NULL,
            photo_url VARCHAR(255) NULL,
            order_index INT NOT NULL DEFAULT 0,
            created_at {$nowDefault}
        );",

        "contact_messages" => "CREATE TABLE IF NOT EXISTS contact_messages (
            id {$autoInc},
            name VARCHAR(150) NOT NULL,
            email VARCHAR(150) NOT NULL,
            subject VARCHAR(200) NOT NULL,
            message TEXT NOT NULL,
            status VARCHAR(30) NOT NULL DEFAULT 'unread',
            created_at {$nowDefault}
        );",

        "site_content" => "CREATE TABLE IF NOT EXISTS site_content (
            id {$autoInc},
            section_key VARCHAR(50) NOT NULL UNIQUE,
            title VARCHAR(200) NOT NULL,
            content TEXT NOT NULL,
            updated_at {$nowDefault}
        );",

        "roles" => "CREATE TABLE IF NOT EXISTS roles (
            id {$autoInc},
            role_key VARCHAR(50) NOT NULL UNIQUE,
            role_name VARCHAR(100) NOT NULL,
            description VARCHAR(255) NULL,
            is_system TINYINT DEFAULT 0,
            created_at {$nowDefault}
        );",

        "crash_logs" => "CREATE TABLE IF NOT EXISTS crash_logs (
            id {$autoInc},
            level VARCHAR(20) NOT NULL DEFAULT 'ERROR',
            message TEXT NOT NULL,
            file VARCHAR(255) NULL,
            line INT NULL,
            trace TEXT NULL,
            url VARCHAR(500) NULL,
            method VARCHAR(10) NULL,
            ip_address VARCHAR(45) NULL,
            user_id VARCHAR(50) NULL,
            user_role VARCHAR(50) NULL,
            resolved TINYINT DEFAULT 0,
            created_at {$nowDefault}
        );"
    ];

    foreach ($tables as $name => $sql) {
        $pdo->exec($sql);
        $results[] = "Table `{$name}` verified/created.";
    }

    // 2. Seed Default Roles
    $checkRoles = $pdo->query("SELECT COUNT(*) FROM roles")->fetchColumn();
    if ($checkRoles == 0) {
        $roleStmt = $pdo->prepare("INSERT INTO roles (role_key, role_name, description, is_system) VALUES (?, ?, ?, 1)");
        $defaultRoles = [
            ['super_admin', 'Super Administrator', 'Complete system authority, health diagnostics, crash logs, and role management.'],
            ['admin', 'Administrator', 'Full access to researches, equipment, chemicals, faculty, messages, and site content.'],
            ['faculty', 'Faculty / Instructor', 'Academic research submission, faculty portal access, and curriculum records.'],
            ['student', 'Student Member', 'Student research portal, laboratory guidelines, and public criminology records.']
        ];
        foreach ($defaultRoles as $r) {
            $roleStmt->execute($r);
        }
        $results[] = "Seeded default system roles (Super Admin, Admin, Faculty, Student).";
    }

    // 3. Seed Default Users & Super Admin (Drew / 49543)
    $checkUsers = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
    if ($checkUsers == 0) {
        $superAdminPassword = password_hash('49543', PASSWORD_BCRYPT);
        $userPassword = password_hash('password123', PASSWORD_BCRYPT);
        $stmt = $pdo->prepare("INSERT INTO users (id_number, name, email, password, role) VALUES (?, ?, ?, ?, ?)");
        
        $stmt->execute([
            'Drew',
            'Drew',
            'drew@dwcc-scje.edu.ph',
            $superAdminPassword,
            'super_admin'
        ]);

        $stmt->execute([
            'FAC-2024-001',
            'Prof. Joan Mae A. Gayacan, RCrim.',
            'j.gayacan@dwcc-scje.edu.ph',
            $userPassword,
            'faculty'
        ]);

        $stmt->execute([
            '2024-10045',
            'Mark Justin S. Reyes',
            'mreyes@student.dwcc-scje.edu.ph',
            $userPassword,
            'student'
        ]);

        $results[] = "Seeded 3 user accounts (Super Admin: Drew, Faculty: FAC-2024-001, Student: 2024-10045).";
    } else {
        // Ensure Drew exists and is Super Admin with password 49543
        $drewCheck = $pdo->prepare("SELECT id FROM users WHERE LOWER(id_number) = 'drew' OR LOWER(name) = 'drew'");
        $drewCheck->execute();
        $drewUser = $drewCheck->fetch();
        $drewPassHash = password_hash('49543', PASSWORD_BCRYPT);
        if ($drewUser) {
            $updateDrew = $pdo->prepare("UPDATE users SET role = 'super_admin', password = ? WHERE id = ?");
            $updateDrew->execute([$drewPassHash, $drewUser['id']]);
            $results[] = "Updated Drew to Super Admin with updated credentials.";
        } else {
            $insertDrew = $pdo->prepare("INSERT INTO users (id_number, name, email, password, role) VALUES (?, ?, ?, ?, ?)");
            $insertDrew->execute(['Drew', 'Drew', 'drew@dwcc-scje.edu.ph', $drewPassHash, 'super_admin']);
            $results[] = "Created Super Admin Drew with credentials.";
        }
    }


    // 3. Seed Research Papers (230 True Theses 2014-2026)
    $checkResearch = $pdo->query("SELECT COUNT(*) FROM research")->fetchColumn();
    if ($checkResearch < 200) {
        // Clear out any old dummy data if upgrading
        $pdo->exec("DELETE FROM research");
        if ($driver === 'sqlite') {
            $pdo->exec("DELETE FROM sqlite_sequence WHERE name = 'research'");
        }

        $jsonThesesPath = __DIR__ . '/database/theses_data.json';
        if (file_exists($jsonThesesPath)) {
            $thesesData = json_decode(file_get_contents($jsonThesesPath), true);
            if (!empty($thesesData)) {
                $stmt = $pdo->prepare("INSERT INTO research (id, author_name, research_title, month_year, category, abstract, keywords, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                foreach ($thesesData as $r) {
                    $stmt->execute([
                        $r['id'],
                        $r['author_name'],
                        $r['research_title'],
                        $r['month_year'],
                        $r['category'],
                        $r['abstract'],
                        $r['keywords'],
                        $r['status'] ?? 'Published'
                    ]);
                }
                $results[] = "Seeded " . count($thesesData) . " True Criminological Research theses (2014-2026).";
            }
        }
    }

    // 4. Seed Laboratory Equipment (Official AY 2025-2026 Inventory: 18 Items)
    $checkEquip = $pdo->query("SELECT COUNT(*) FROM equipment")->fetchColumn();
    if ($checkEquip == 0) {
        $stmt = $pdo->prepare("INSERT INTO equipment (equipment_code, equipment_name, brand, model, current_location, laboratory_category, status, serial_number, description, person_accountable) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

        $equipment = [
            [
                'CLE-001',
                'Chainomatic Analytical Balance',
                'Keroy',
                'N/A',
                'Crime Lab',
                'Criminalistics Laboratory',
                'Good Condition',
                'N/A',
                'Official SCJE laboratory equipment assigned to Crime Lab. Accountable officer: Sir Jom.',
                'Sir Jom'
            ],
            [
                'CLE-002',
                'Chainomatic Analytical Balance',
                'Keroy',
                'N/A',
                'Crime Lab',
                'Criminalistics Laboratory',
                'Good Condition',
                'N/A',
                'Official SCJE laboratory equipment assigned to Crime Lab. Accountable officer: Sir Jom.',
                'Sir Jom'
            ],
            [
                'CLE-003',
                'Chainomatic Analytical Balance',
                'Keroy',
                'N/A',
                'Crime Lab',
                'Criminalistics Laboratory',
                'Good Condition',
                'N/A',
                'Official SCJE laboratory equipment assigned to Crime Lab. Accountable officer: Sir Jom.',
                'Sir Jom'
            ],
            [
                'CLE-004',
                'Digital Analytical Balance',
                'Kern',
                'N/A',
                'Crime Lab',
                'Criminalistics Laboratory',
                'Good Condition',
                'N/A',
                'Official SCJE laboratory equipment assigned to Crime Lab. Accountable officer: Sir Jom.',
                'Sir Jom'
            ],
            [
                'CLE-005',
                'Triple Beam Balance',
                'Lark',
                'Mb-2610',
                'Crime Lab',
                'Criminalistics Laboratory',
                'Good Condition',
                'N/A',
                'Official SCJE laboratory equipment assigned to Crime Lab. Accountable officer: Sir Jom.',
                'Sir Jom'
            ],
            [
                'CLE-006',
                'Triple Beam Balance',
                'Lark',
                'Mb-2610',
                'Crime Lab',
                'Criminalistics Laboratory',
                'Good Condition',
                'N/A',
                'Official SCJE laboratory equipment assigned to Crime Lab. Accountable officer: Sir Jom.',
                'Sir Jom'
            ],
            [
                'CLE-007',
                'Questioned Document Kit',
                'Sirchie',
                'N/A',
                'Crime Lab',
                'Criminalistics Laboratory',
                'Good Condition',
                'N/A',
                'Official SCJE laboratory equipment assigned to Crime Lab. Accountable officer: Sir Jom.',
                'Sir Jom'
            ],
            [
                'FPE-001',
                'Profile Projector',
                'Radical',
                'Rpp-250',
                'Forensic Photography Room',
                'Forensic Science Laboratory',
                'Good Condition',
                'N/A',
                'Official SCJE laboratory equipment assigned to Forensic Photography Room. Accountable officer: Sir Jom.',
                'Sir Jom'
            ],
            [
                'FPE-002',
                'Comparison Microscope',
                'Radical',
                'N/A',
                'Forensic Photography Room',
                'Forensic Science Laboratory',
                'Out Of Service',
                'N/A',
                'Official SCJE laboratory equipment assigned to Forensic Photography Room. Accountable officer: Sir Jom.',
                'Sir Jom'
            ],
            [
                'FPE-003',
                'Binocular Microscope',
                'Radical',
                'N/A',
                'Forensic Photography Room',
                'Forensic Science Laboratory',
                'Out Of Service',
                'N/A',
                'Official SCJE laboratory equipment assigned to Forensic Photography Room. Accountable officer: Sir Jom.',
                'Sir Jom'
            ],
            [
                'FPE-004',
                'Tripod',
                'Davis And Standford',
                'Provista',
                'Forensic Photography Room',
                'Forensic Science Laboratory',
                'Good Condition',
                '751813',
                'Official SCJE laboratory equipment assigned to Forensic Photography Room. Accountable officer: Sir Jom.',
                'Sir Jom'
            ],
            [
                'FPE-005',
                'Tripod',
                'Vivitar',
                'Vtr',
                'Forensic Photography Room',
                'Forensic Science Laboratory',
                'Good Condition',
                '003005',
                'Official SCJE laboratory equipment assigned to Forensic Photography Room. Accountable officer: Sir Jom.',
                'Sir Jom'
            ],
            [
                'FPE-006',
                'Color Enlarger',
                'Lpl',
                'C 7700 Pro',
                'Forensic Photography Room',
                'Forensic Science Laboratory',
                'Good Condition',
                'N/A',
                'Official SCJE laboratory equipment assigned to Forensic Photography Room. Accountable officer: Sir Jom.',
                'Sir Jom'
            ],
            [
                'FPE-007',
                'Photo Enlarger',
                'Kaiser',
                'Vc 60',
                'Forensic Photography Room',
                'Forensic Science Laboratory',
                'Out Of Service',
                'N/A',
                'Official SCJE laboratory equipment assigned to Forensic Photography Room. Accountable officer: Sir Jom.',
                'Sir Jom'
            ],
            [
                'FPE-008',
                'Photo Cutter',
                'N/A',
                'N/A',
                'Forensic Photography Room',
                'Forensic Science Laboratory',
                'Good Condition',
                'N/A',
                'Official SCJE laboratory equipment assigned to Forensic Photography Room. Accountable officer: Sir Jom.',
                'Sir Jom'
            ],
            [
                'FE-001',
                'Fingerprint Kit',
                'Samsonite',
                'N/A',
                'Fingerprint Room',
                'Criminalistics Laboratory',
                'Good Condition',
                'N/A',
                'Official SCJE laboratory equipment assigned to Fingerprint Room. Accountable officer: Sir Jom.',
                'Sir Jom'
            ],
            [
                'PE-001',
                'Polygraph Machine (Analog Type)',
                'N/A',
                'N/A',
                'Polygraphy Room',
                'Other Specialized Areas',
                'Good Condition',
                'N/A',
                'Official SCJE laboratory equipment assigned to Polygraphy Room. Accountable officer: Sir Jom.',
                'Sir Jom'
            ],
            [
                'PE-002',
                'Digital Polygraph Machine',
                'Stoelting',
                'N/A',
                'Dean’s Office',
                'Other Specialized Areas',
                'Brandnew',
                'N/A',
                'Official SCJE laboratory equipment assigned to Dean’s Office. Accountable officer: Sir Jom.',
                'Sir Jom'
            ]
        ];

        foreach ($equipment as $eq) {
            $stmt->execute($eq);
        }
        $results[] = "Seeded " . count($equipment) . " Laboratory Equipment records (AY 2025-2026).";
    }

    // 4b. Seed Materials & Chemicals Inventory (Official AY 2025-2026: 64 Items)
    $checkMat = $pdo->query("SELECT COUNT(*) FROM materials_chemicals")->fetchColumn();
    if ($checkMat == 0) {
        $stmt = $pdo->prepare("INSERT INTO materials_chemicals (item_code, qty, unit, item_name, person_accountable, brand, status, location) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");

        $materials = [
            [
                'MC-001',
                '4',
                '500 Ml',
                'Erlenmeyer Flask',
                'Sir Jom',
                'Pyrex',
                'Good Condition',
                'Crime Laboratory'
            ],
            [
                'MC-002',
                '6',
                '300 Ml',
                'Erlenmeyer Flask',
                'Sir Jom',
                'Pyrex',
                'Good Condition',
                'Crime Laboratory'
            ],
            [
                'MC-003',
                '4',
                '250 Ml',
                'Erlenmeyer Flask',
                'Sir Jom',
                'Pyrex',
                'Good Condition',
                'Crime Laboratory'
            ],
            [
                'MC-004',
                '4',
                '125 Ml',
                'Erlenmeyer Flask',
                'Sir Jom',
                'Pyrex',
                'Good Condition',
                'Crime Laboratory'
            ],
            [
                'MC-005',
                '2',
                '600 Ml',
                'Beaker',
                'Sir Jom',
                'Pyrex',
                'Good Condition',
                'Crime Laboratory'
            ],
            [
                'MC-006',
                '10',
                '500 Ml',
                'Beaker',
                'Sir Jom',
                'Pyrex',
                'Good Condition',
                'Crime Laboratory'
            ],
            [
                'MC-007',
                '9',
                '250 Ml',
                'Beaker',
                'Sir Jom',
                'Pyrex',
                'Good Condition',
                'Crime Laboratory'
            ],
            [
                'MC-008',
                '4',
                '75 Ml',
                'Funnel',
                'Sir Jom',
                'N/A',
                'Good Condition',
                'Crime Laboratory'
            ],
            [
                'MC-009',
                '32',
                'N/A',
                'Test Tube',
                'Sir Jom',
                'N/A',
                'Good Condition',
                'Crime Laboratory'
            ],
            [
                'MC-010',
                '4',
                'N/A',
                'Test Tube Rack',
                'Sir Jom',
                'N/A',
                'Good Condition',
                'Crime Laboratory'
            ],
            [
                'MC-011',
                '5',
                '100 Ml',
                'Graduated Cylinder',
                'Sir Jom',
                'Bonex',
                'Good Condition',
                'Crime Laboratory'
            ],
            [
                'MC-012',
                '3',
                '25 Ml',
                'Graduated Cylinder',
                'Sir Jom',
                'Tek',
                'Good Condition',
                'Crime Laboratory'
            ],
            [
                'MC-013',
                '72',
                'N/A',
                'Microscope Slides',
                'Sir Jom',
                'Top Care',
                'Brand New',
                'Crime Laboratory'
            ],
            [
                'MC-014',
                '7',
                '250 Ml',
                'Glass Alcohol Lamp',
                'Sir Jom',
                'N/A',
                'Good Condition',
                'Crime Laboratory'
            ],
            [
                'MC-015',
                '13',
                'N/A',
                'Eye Protector',
                'Sir Jom',
                'N/A',
                'Good Condition',
                'Crime Laboratory'
            ],
            [
                'MC-016',
                '4',
                'N/A',
                'Steering Rod',
                'Sir Jom',
                'N/A',
                'Good Condition',
                'Crime Laboratory'
            ],
            [
                'MC-017',
                '2',
                'N/A',
                'Pipette',
                'Sir Jom',
                'N/A',
                'Good Condition',
                'Crime Laboratory'
            ],
            [
                'MC-018',
                '13',
                '50 Ml',
                'Glass Burette',
                'Sir Jom',
                'N/A',
                'Good Condition',
                'Crime Laboratory'
            ],
            [
                'MC-019',
                '10',
                'N/A',
                'Dropper',
                'Sir Jom',
                'N/A',
                'Good Condition',
                'Crime Laboratory'
            ],
            [
                'MC-020',
                '1',
                'N/A',
                'Reagent Glass Bottle',
                'Sir Jom',
                'N/A',
                'Good Condition',
                'Crime Laboratory'
            ],
            [
                'MC-021',
                '10',
                'N/A',
                'Scalpel',
                'Sir Jom',
                'N/A',
                'Good Condition',
                'Crime Laboratory'
            ],
            [
                'MC-022',
                '5',
                'N/A',
                'Watch Glass',
                'Sir Jom',
                'N/A',
                'Good Condition',
                'Crime Laboratory'
            ],
            [
                'MC-023',
                '2',
                'N/A',
                'Mortar And Pestle',
                'Sir Jom',
                'N/A',
                'Good Condition',
                'Crime Laboratory'
            ],
            [
                'MC-024',
                '4',
                'N/A',
                'Evaporating Dish',
                'Sir Jom',
                'N/A',
                'Good Condition',
                'Crime Laboratory'
            ],
            [
                'MC-025',
                '5',
                'N/A',
                'Porcelain',
                'Sir Jom',
                'N/A',
                'Good Condition',
                'Crime Laboratory'
            ],
            [
                'MC-026',
                '7',
                'N/A',
                'Bunsen Burner',
                'Sir Jom',
                'N/A',
                'Good Condition',
                'Crime Laboratory'
            ],
            [
                'MC-027',
                '6',
                'N/A',
                'Crucible Tongs',
                'Sir Jom',
                'N/A',
                'Good Condition',
                'Crime Laboratory'
            ],
            [
                'MC-028',
                '3',
                'N/A',
                'Magnifying Glass',
                'Sir Jom',
                'N/A',
                'Brand New',
                'Crime Laboratory'
            ],
            [
                'MC-029',
                '45',
                'N/A',
                'Magnifying Glass',
                'Sir Jom',
                'N/A',
                'Good Condition',
                'Fingerprint Room'
            ],
            [
                'MC-030',
                '2',
                'N/A',
                'Forceps',
                'Sir Jom',
                'N/A',
                'Good Condition',
                'Fingerprint Room'
            ],
            [
                'MC-031',
                '27',
                'N/A',
                'Fingerprint Brush',
                'Sir Jom',
                'N/A',
                'Good Condition',
                'Fingerprint Room'
            ],
            [
                'MC-032',
                '6',
                'N/A',
                'Ink Roller',
                'Sir Jom',
                'N/A',
                'Good Condition',
                'Fingerprint Room'
            ],
            [
                'MC-033',
                '6',
                'N/A',
                'Horshoe Fingerprint Lense',
                'Sir Jom',
                'N/A',
                'Good Condition',
                'Fingerprint Room'
            ],
            [
                'MC-034',
                '5',
                'N/A',
                'Ink Slab',
                'Sir Jom',
                'N/A',
                'Good Condition',
                'Fingerprint Room'
            ],
            [
                'MC-035',
                '4',
                'N/A',
                'Ink Slab',
                'Sir Jom',
                'N/A',
                'Brand New',
                'Fingerprint Room'
            ],
            [
                'MC-036',
                '7',
                'N/A',
                'Fingerprint Card Holder',
                'Sir Jom',
                'Sirchie',
                'Good Condition',
                'Fingerprint Room'
            ],
            [
                'MC-037',
                '4',
                'N/A',
                'Fingerprint Card Holder',
                'Sir Jom',
                'Sirchie',
                'Brand New',
                'Fingerprint Room'
            ],
            [
                'MC-038',
                '2',
                '59 Ml',
                'Latent Fingerprint Powder (Silk Black)',
                'Sir Jom',
                'Sirchie',
                'Brandnew',
                'Fingerprint Room'
            ],
            [
                'MC-039',
                '4',
                '59 Ml',
                'Latent Fingerprint Powder (Silk Black)',
                'Sir Jom',
                'Sirchie',
                'Good Condition',
                'Fingerprint Room'
            ],
            [
                'MC-040',
                '4',
                '59 Ml',
                'Latent Fingerprint Powder (White)',
                'Sir Jom',
                'Sirchie',
                'Good Condition',
                'Fingerprint Room'
            ],
            [
                'MC-041',
                '1',
                '30 Ml',
                'Latent Fingerprint Powder (Silk Black)',
                'Sir Jom',
                'Sirchie',
                'Good Condition',
                'Fingerprint Room'
            ],
            [
                'MC-042',
                '1',
                '30 Ml',
                'Latent Fingerprint Powder (White)',
                'Sir Jom',
                'Sirchie',
                'Good Condition',
                'Fingerprint Room'
            ],
            [
                'MC-043',
                '8',
                '1.5',
                'Fingerprint Lifting Tape (Transparent)',
                'Sir Jom',
                'Sirchie',
                'Good Condition',
                'Fingerprint Room'
            ],
            [
                'MC-044',
                '3',
                'N/A',
                'Ballpen',
                'Sir Jom',
                'N/A',
                'Good Condition',
                'Fingerprint Room'
            ],
            [
                'MC-045',
                '4',
                'N/A',
                'Forceps',
                'Sir Jom',
                'N/A',
                'Brand New',
                'Fingerprint Room'
            ],
            [
                'MC-046',
                '2',
                '15 Cm',
                'Caliper',
                'Sir Jom',
                'Vernie',
                'Brand New',
                'Crime Laboratory'
            ],
            [
                'MC-047',
                '2',
                '15 Cm',
                'Caliper',
                'Sir Jom',
                'N/A',
                'Good Condition',
                'Crime Laboratory'
            ],
            [
                'MC-048',
                '1',
                '15 Cm',
                'Caliper',
                'Sir Jom',
                'Mitutoyo',
                'Good Condition',
                'Crime Laboratory'
            ],
            [
                'MC-049',
                '12',
                'M-Xl',
                'Laboratory Gown',
                'Sir Jom',
                'N/A',
                'Good Condition',
                'Crime Laboratory'
            ],
            [
                'MC-050',
                '1',
                'N/A',
                'Typewriting Protractor',
                'Sir Jom',
                'Sirchie',
                'Brand New',
                'Crime Laboratory'
            ],
            [
                'MC-051',
                '3',
                'N/A',
                'Typewriting Protractor',
                'Sir Jom',
                'Sirchie',
                'Good Condition',
                'Crime Laboratory'
            ],
            [
                'MC-052',
                '2',
                'N/A',
                'Space Test Plate',
                'Sir Jom',
                'Sirchie',
                'Brand New',
                'Crime Laboratory'
            ],
            [
                'MC-053',
                '1',
                'N/A',
                'Space Test Plate',
                'Sir Jom',
                'Sirchie',
                'Good Condition',
                'Crime Laboratory'
            ],
            [
                'MC-054',
                '4',
                'N/A',
                'Handwriting Comparison Test Plate',
                'Sir Jom',
                'Sirchie',
                'Brand New',
                'Crime Laboratory'
            ],
            [
                'MC-055',
                '5',
                'N/A',
                'Handwriting Comparision Test Plate',
                'Sir Jom',
                'Sirchie',
                'Good Condition',
                'Crime Laboratory'
            ],
            [
                'MC-056',
                '7',
                'N/A',
                'Magnifying Comparator',
                'Sir Jom',
                'Finescale',
                'Good Condition',
                'Crime Laboratory'
            ],
            [
                'MC-057',
                '1',
                'N/A',
                'Comparator Stock Pocket Model',
                'Sir Jom',
                'Finescale',
                'Good Condition',
                'Crime Laboratory'
            ],
            [
                'MC-058',
                '4',
                'N/A',
                'Paraffin Wax',
                'Sir Jom',
                'N/A',
                'Good Condition',
                'Consultation Room'
            ],
            [
                'MC-059',
                '1',
                'N/A',
                'Plaster Of Paris',
                'Sir Jom',
                'N/A',
                'Good Condition',
                'Consultation Room'
            ],
            [
                'MC-060',
                '4',
                'N/A',
                'Uv Mini Light',
                'Sir Jom',
                'Sirchie',
                'Out Of Service',
                'Crime Laboratory'
            ],
            [
                'MC-061',
                '4',
                'N/A',
                'Black & White Negative Film',
                'Sir Jom',
                'Kodak',
                'Good Condition',
                'Forensic Photography'
            ],
            [
                'MC-062',
                '1',
                'N/A',
                'Funnel',
                'Sir Jom',
                'N/A',
                'Good Condition',
                'Forensic Photography'
            ],
            [
                'MC-063',
                '1',
                'N/A',
                'Reagent Bottle',
                'Sir Jom',
                'N/A',
                'Good Condition',
                'Forensic Photography'
            ],
            [
                'MC-064',
                '1',
                'N/A',
                'Timer',
                'Sir Jom',
                'N/A',
                'Good Condition',
                'Forensic Photography'
            ]
        ];

        foreach ($materials as $m) {
            $stmt->execute($m);
        }
        $results[] = "Seeded " . count($materials) . " Materials & Chemicals records (AY 2025-2026).";
    }

    // 5. Seed Faculty Directory (Matching Sketch Org Chart with OIC Dean at Top)
    $checkFaculty = $pdo->query("SELECT COUNT(*) FROM faculty")->fetchColumn();
    if ($checkFaculty == 0) {
        $stmt = $pdo->prepare("INSERT INTO faculty (name, position, role_level, email, specialization, research_interests, office_location, photo_url, order_index) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");

        $faculty = [
            // Top Level: OIC - DEAN, SCJ
            [
                'Sacha Pollyne M. Austria, RCrim., MSCJ',
                'Officer-In-Charge, School of Criminal Justice Education',
                'dean',
                's.austria@dwcc-scje.edu.ph',
                'Criminological Education & Program Coordination',
                'Program Coordinator (3 years) • NSTP-ROTC Coordinator (4 years)',
                'Office of the Dean, 2nd Floor SCJE Building',
                'assets/images/faculty_dean.png',
                1
            ],
            // Second Level: Chairs and Coordinators
            [
                'Joan Mae A. Gayacan, RCrim.',
                'Program Chairperson, Department of Criminology',
                'chair',
                'j.gayacan@dwcc-scje.edu.ph',
                'Questioned Document Examination (QDE), Forensic Photography',
                'Department Adviser • Certified Criminalistic Specialist (QDE)',
                'Criminology Faculty Office, Room 204',
                'assets/images/faculty_gayacan.png',
                2
            ],
            [
                'Dr. Janenovelle A. Cuenca, RCrim., Ph.D.',
                'Criminology Internship Adviser & Graduate Faculty',
                'coordinator',
                'j.cuenca@dwcc-scje.edu.ph',
                'Criminal Justice Administration, Internship Supervision, Criminological Research',
                'Provincial Chancellor (PCAP Reg. IV) • PNP MIMAROPA RSSC Member • Internship Adviser',
                'Criminology Faculty Hall, Room 203',
                'assets/images/faculty_cuenca.png',
                3
            ],
            [
                'Jomari R. Vencio, RCrim.',
                'Laboratory Custodian',
                'custodian',
                'j.vencio@dwcc-scje.edu.ph',
                'Forensic Instrumentation, Chemical Safety, Evidence Custody',
                'Certified Criminalistic Specialist (Laboratory Custodian) • Research Area Committee Member',
                'Central Criminology Laboratory Office, Room 101',
                'assets/images/faculty_vencio.png',
                4
            ],
            // Instructional Faculty (Full-Time)
            [
                'Quennie F. Lovendino, RCrim.',
                'Full-Time Faculty - Criminology',
                'faculty',
                'q.lovendino@dwcc-scje.edu.ph',
                'Criminological Theories, Law Enforcement Administration',
                'Region 4 Achiever (LEC) • Full-Time Faculty',
                'Faculty Hall, 2nd Floor SCJE Building',
                'assets/images/faculty_lovendino.png',
                5
            ],
            [
                'Lyra B. Caoli, RCrim.',
                'Instructor - Polygraphy & Deception Detection',
                'faculty',
                'l.caoli@dwcc-scje.edu.ph',
                'Digital Polygraphy, Lie Detection Technique, Deception Detection',
                'Mastering Digital Polygraph Technique • Advanced Training for Accurate Deception Detection',
                'Polygraph Suite & Examination Room, SCJE Bldg',
                'assets/images/faculty_caoli.png',
                6
            ],
            // Part-Time Faculty & Professional Lecturers
            [
                'Atty. Zyreen B. Cataquis, J.D.',
                'Part-Time Faculty - Administrative & Public Law',
                'faculty',
                'z.cataquis@dwcc-scje.edu.ph',
                'Local Governance, Administrative Law, Civil Service, Government Procurement, Performance Management',
                'Juris Doctor (BatStateU) • MPA Units (DWCC) • Administrative Officer IV (PGOM)',
                'Faculty Hall, 2nd Floor SCJE Building',
                'assets/images/faculty_cataquis.png',
                7
            ],
            [
                'Atty. Jake Magsisi, J.D.',
                'Part-Time Faculty - Criminal Law & Legal Procedure',
                'faculty',
                'j.magsisi@dwcc-scje.edu.ph',
                'Criminal Law, Civil & Administrative Cases, Special Civil Actions, Election and Labor Law',
                'Juris Doctor (BatStateU) • BS Information Technology (DWCC) • Attorney III (Provincial Legal Office)',
                'Faculty Hall, 2nd Floor SCJE Building',
                'assets/images/faculty_magsisi.png',
                8
            ],
            [
                'Atty. Marc Paolo C. Cusi, J.D.',
                'Part-Time Faculty - Criminal Justice & Legal Studies',
                'faculty',
                'm.cusi@dwcc-scje.edu.ph',
                'Criminal Procedure, Legal Counseling, Provincial Legal Services',
                'Juris Doctor (Non-Thesis) • Provincial Legal Office (PGOM)',
                'Faculty Hall, 2nd Floor SCJE Building',
                'assets/images/faculty_cusi.png',
                9
            ],
            [
                'Jasmin M. Bagon, LPT',
                'Part-Time Faculty - Technical Report Writing',
                'faculty',
                'j.bagon@dwcc-scje.edu.ph',
                'Technical Report Writing, Language and Literacy Education, Corporate Training',
                'BA English Studies (UP Diliman) • MA Language Literacy (UP Open University) • Client Acquisition and Training Manager',
                'Faculty Hall, 2nd Floor SCJE Building',
                'assets/images/faculty_bagon.png',
                10
            ],
            [
                'Hazel Joys B. Jamilla, LPT',
                'Part-Time Faculty - General Science',
                'faculty',
                'h.jamilla@dwcc-scje.edu.ph',
                'General Science, Science Education, Environmental Science',
                'MaEd Science Education (30 units) • Learning Area Coordinator • YES-O Adviser • SHS Faculty',
                'Faculty Hall, 2nd Floor SCJE Building',
                'assets/images/faculty_jamilla.png',
                11
            ],
            [
                'Rommel M. Casiple, Ret. PNP',
                'Part-Time Faculty - Marksmanship & Defensive Tactics',
                'faculty',
                'r.casiple@dwcc-scje.edu.ph',
                'Fundamentals of Martial Arts, Fundamentals of Marksmanship, Gun Safety, Pekiti Tirsia Kali',
                'Ret. PNP (Training Service / Maritime Group) • Security Agent II / Deputy CSU (PGSO) • Pekiti Tirsia Kali Instructor',
                'Tactical Defense Room & Range, SCJE Bldg',
                'assets/images/faculty_casiple.png',
                12
            ],
            [
                'Annie A. Amuguis, LPT',
                'Part-Time Faculty - First Aid & Water Safety',
                'faculty',
                'a.amuguis@dwcc-scje.edu.ph',
                'First Aid and Water Safety Course, Emergency Response, Sports Coordination',
                'Faculty, School of Arts and Sciences • DWCC Sports Coordinator',
                'Faculty Hall / Gymnasium Office, SCJE',
                'assets/images/faculty_amuguis.png',
                13
            ],
            [
                'Jessica Mae T. Decillo, LPT, MPA',
                'Part-Time Faculty - Criminological Research 1',
                'faculty',
                'j.decillo@dwcc-scje.edu.ph',
                'Criminological Research 1 Course, Thesis Advisory, Public Administration',
                'Master in Public Administration (MPA) • Faculty, School of Arts and Sciences',
                'Faculty Hall, 2nd Floor SCJE Building',
                'assets/images/faculty_decillo.png',
                14
            ]
        ];

        foreach ($faculty as $f) {
            $stmt->execute($f);
        }
        $results[] = "Seeded " . count($faculty) . " Faculty Members (Dean, Chairs, Coordinators, Professors).";
    }

    // 6. Seed Site Content (Vision, Mission, Goals, History)
    $checkContent = $pdo->query("SELECT COUNT(*) FROM site_content")->fetchColumn();
    if ($checkContent == 0) {
        $stmt = $pdo->prepare("INSERT INTO site_content (section_key, title, content) VALUES (?, ?, ?)");

        $contents = [
            [
                'vision',
                'VISION',
                'To become the center and primer in the pursuit of quality instruction in the field of Criminal Justice in the entire Mindoro Region.'
            ],
            [
                'mission',
                'MISSION',
                'To produce professionally competent and morally upright graduates equipped with contemporary and functional knowledge and skills in the field of law enforcement administration, crime detection and investigation, correctional administration, criminal sociology and forensic science.'
            ],
            [
                'goals',
                'GOALS',
                "• Foster the value of god-fearing, social responsibility, self-sacrifice and discipline;\n• Provide students with theoretical, technical, practical and actual knowledge relative to criminology profession; and\n• Prepare students for careers in any agencies under the Philippine Criminal Justice System"
            ],
            [
                'history',
                'HISTORY OF SCJ',
                "The Criminology academic program at Divine Word College of Calapan (DWCC) was first offered in 2009 under the Liberal Arts Department, headed by Mr. Dennis S. Alcaraz.\n\nIn 2012, recognizing the rapid growth and distinct professional identity of the discipline, it was separated and placed under an independent department called the Criminology Department, headed by Ms. Janenovelle A. Cuenca.\n\nThe year 2013 marked a series of historic milestones: Ms. Janenovelle A. Cuenca was appointed as one of the members of the Regional Selection and Screening Committee for applicants of the PNP in MIMAROPA. In that same year, the DWCC Criminology Department offered its own in-house and formal review for the licensure board examination. When the first batch of graduates took the Criminology Board Examination, they set an extraordinary precedent with a 100% passing rate. Ever since, the department has maintained a yearly board examination passing rate far exceeding the national average, on various occasions becoming the number one school of criminology in the province and one among the best in the entire region, while actively hosting and participating in international and local criminology seminars.\n\nIn 2019, the department was formally renamed the School of Criminal Justice (SCJ) in compliance with the mandate of the Commission on Higher Education (CHED), opening opportunities for the future offering of allied courses. Today, more than 90% of SCJ graduates are employed in stable jobs across law enforcement agencies, primarily the Philippine National Police (PNP), and the School continues to expand with a dramatic increase in student population every school year."
            ]
        ];

        foreach ($contents as $c) {
            $stmt->execute($c);
        }
        $results[] = "Seeded Site Content (Vision, Mission, Goals, History of SCJ).";
    }

    return $results;
}

// Auto-run if executed directly via CLI or Web
if (php_sapi_name() === 'cli' || basename($_SERVER['SCRIPT_NAME'] ?? '') === 'setup.php') {
    try {
        $pdo = get_db();
        $driver = DB::getDriver();
        $logs = init_database($pdo, $driver);

        if (php_sapi_name() === 'cli') {
            echo "=====================================================\n";
            echo "  DWCC - School of Criminal Justice Education (SCJE) \n";
            echo "  Database Setup Completed Successfully!             \n";
            echo "  Active Engine: " . strtoupper($driver) . "\n";
            echo "=====================================================\n";
            foreach ($logs as $log) {
                echo "  [OK] {$log}\n";
            }
            echo "\nDefault Credentials:\n";
            echo "  Admin ID Number : " . env('DEFAULT_ADMIN_ID', 'Drew') . "\n";
            echo "  Password        : " . env('DEFAULT_ADMIN_PASSWORD', 'admin') . "\n";
            echo "=====================================================\n";
        } else {
            echo '<!DOCTYPE html><html><head><title>Database Setup - SCJE</title><style>body{font-family:Segoe UI,sans-serif;background:#0A192F;color:#E2E8F0;padding:40px;}h1{color:#38BDF8;}li{margin:8px 0;}.card{background:#1E293B;padding:24px;border-radius:12px;max-width:650px;margin:auto;box-shadow:0 10px 25px rgba(0,0,0,0.5);}a{display:inline-block;margin-top:20px;padding:10px 20px;background:#2563EB;color:#fff;text-decoration:none;border-radius:6px;font-weight:bold;}</style></head><body>';
            echo '<div class="card">';
            echo '<h1>Database Setup Completed!</h1>';
            echo '<p>Active Database Engine: <strong>' . strtoupper($driver) . '</strong></p>';
            echo '<ul>';
            foreach ($logs as $log) {
                echo "<li>{$log}</li>";
            }
            echo '</ul>';
            echo '<p>Default Admin: <strong>' . htmlspecialchars(env('DEFAULT_ADMIN_ID', 'Drew')) . '</strong> / Password: <strong>' . htmlspecialchars(env('DEFAULT_ADMIN_PASSWORD', 'admin')) . '</strong></p>';
            echo '<a href="' . base_url() . '">Go to Website Homepage &rarr;</a>';
            echo '</div></body></html>';
        }
    } catch (Exception $e) {
        die("Setup error: " . $e->getMessage());
    }
}
