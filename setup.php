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
        );"
    ];

    foreach ($tables as $name => $sql) {
        $pdo->exec($sql);
        $results[] = "Table `{$name}` verified/created.";
    }

    // 2. Seed Default Users
    $checkUsers = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
    if ($checkUsers == 0) {
        $defaultPassword = password_hash(env('DEFAULT_ADMIN_PASSWORD', 'password123'), PASSWORD_BCRYPT);
        $stmt = $pdo->prepare("INSERT INTO users (id_number, name, email, password, role) VALUES (?, ?, ?, ?, ?)");
        
        $stmt->execute([
            env('DEFAULT_ADMIN_ID', 'ADMIN-001'),
            env('DEFAULT_ADMIN_NAME', 'Dean Office Admin'),
            env('DEFAULT_ADMIN_EMAIL', 'admin@dwcc-scje.edu.ph'),
            $defaultPassword,
            'admin'
        ]);

        $stmt->execute([
            'FAC-2024-001',
            'Prof. Joan Mae A. Gayacan, RCrim.',
            'j.gayacan@dwcc-scje.edu.ph',
            $defaultPassword,
            'faculty'
        ]);

        $stmt->execute([
            '2024-10045',
            'Mark Justin S. Reyes',
            'mreyes@student.dwcc-scje.edu.ph',
            $defaultPassword,
            'student'
        ]);

        $results[] = "Seeded 3 user accounts (Admin: ADMIN-001, Faculty: FAC-2024-001, Student: 2024-10045).";
    }

    // 3. Seed Research Papers (Matching 16 Categories + Sketch Columns)
    $checkResearch = $pdo->query("SELECT COUNT(*) FROM research")->fetchColumn();
    if ($checkResearch == 0) {
        $stmt = $pdo->prepare("INSERT INTO research (author_name, research_title, month_year, category, abstract, keywords) VALUES (?, ?, ?, ?, ?, ?)");
        
        $researches = [
            [
                'Joan Mae A. Gayacan, RCrim.',
                'Comparative Analysis of Altered Signatures and Handwriting Characteristics Among Selected Criminology Students',
                'March 2025',
                'Questioned Document Examination',
                'This empirical study investigates the distinct microscopic traits and stroke cadence identifiable in simulated questioned handwriting specimens, establishing automated baseline criteria for forensic document examiners.',
                'Questioned Documents, Handwriting Analysis, Forensics, Signatures'
            ],
            [
                'Dr. Anthony S. Morales, RCrim., CSP',
                'Ballistic Striation Patterns on Fired Cartridge Cases Using High-Resolution Comparison Microscopy',
                'January 2025',
                'Forensic Ballistics',
                'An investigation into firing pin impressions and breech face markings produced by standard issue 9mm semi-automatic pistols in humid maritime tropical environments.',
                'Ballistics, Comparison Microscope, Firearms Identification, Striations'
            ],
            [
                'Carlos D. Mendoza, RCrim.',
                'Efficacy of Cyanoacrylate Fuming versus Ninhydrin on Latent Fingerprints on Non-Porous Surfaces',
                'December 2024',
                'Criminalistics',
                'A rigorous comparative assessment of latent ridge development methodologies on metallic, glass, and varnished wooden crime scene exhibits.',
                'Fingerprints, Dactyloscopy, Criminalistics, Latent Prints'
            ],
            [
                'Atty. Clarissa T. Mendoza & J. Delos Santos',
                'Procedural Integrity and Admissibility of Digital Evidence in Cybercrime Prosecutions in Calapan City',
                'November 2024',
                'Cybercrime',
                'Examines the chain of custody protocols followed by local law enforcement officers when seizing and extracting forensic digital data from mobile smartphones and computing terminals.',
                'Cybercrime, Digital Forensics, Chain of Custody, Rules on Electronic Evidence'
            ],
            [
                'Capt. Eduardo P. Ramos, PN (Ret.)',
                'Community-Oriented Policing Strategies (COPS) and Crime Rate Reduction in Oriental Mindoro',
                'October 2024',
                'Police Administration',
                'Evaluates patrol response time, police visibility, and citizen trust indexes across urban and rural barangays following the deployment of community assistance desks.',
                'Police Administration, Community Policing, Crime Prevention, PNP'
            ],
            [
                'Dr. Roberto M. Dela Cruz & F. Gutierrez',
                'Socio-Economic Determinants and Intervention Outcomes Among Children in Conflict with the Law (CICL)',
                'September 2024',
                'Juvenile Delinquency',
                'A longitudinal case evaluation of restorative justice diversions and rehabilitation frameworks implemented by local juvenile custodial facilities.',
                'Juvenile Delinquency, Restorative Justice, CICL, Youth Rehabilitation'
            ],
            [
                'Engr. Mark Daniel Bautista & K. Villanueva',
                'Spectrophotometric Analysis of Gunshot Residue (GSR) on Textile Fabrics at Varying Distances',
                'August 2024',
                'Forensic Science',
                'Establishes muzzle-to-target distance estimation thresholds by quantifying lead, barium, and antimony particulates through atomic absorption spectrophotometry.',
                'GSR, Forensic Chemistry, Gunshot Residue, Distance Estimation'
            ],
            [
                'Prof. Veronica L. Santos, RCrim.',
                'Standardization of Forensic Crime Scene Photography Under Extreme Low-Light Conditions',
                'June 2024',
                'Forensic Photography',
                'Guidelines and optical camera configurations for evidentiary photographic documentation during nighttime crime scene operations.',
                'Forensic Photography, Crime Scene, Lighting, Evidentiary Value'
            ],
            [
                'Gerald N. Navarro, RCrim.',
                'Physiological Baseline Deviations During Computerized Polygraph Examinations of Traumatized Victims',
                'May 2024',
                'Victimology',
                'Assesses autonomic nervous system responses, galvanic skin resistance, and cardio-sphygmograph spikes in traumatized interviewees.',
                'Polygraph, Victimology, Lie Detection, Autonomic Response'
            ],
            [
                'Prof. Arnold T. Castillo, RCrim.',
                'Modern Custodial Management and Congestion Mitigation in Provincial Jail Facilities',
                'April 2024',
                'Corrections',
                'Structural assessment of inmate classification, healthcare delivery, and rehabilitation programs within provincial correctional institutes.',
                'Corrections, Penology, Jail Management, Inmate Welfare'
            ],
            [
                'Kristine Mae Solis, RCrim.',
                'Barangay Peacekeeping Action Teams (BPATs) and Localized Conflict Resolution Frameworks',
                'February 2024',
                'Community-Based Studies',
                'Measures the effectiveness of community-based frontline responders in de-escalating domestic conflicts and reporting criminal incidents.',
                'Community Safety, BPATs, Barangay Justice, Conflict Resolution'
            ],
            [
                'Mark Justin S. Reyes & B. Tan',
                'Perceived Safety and Victimization Fears Among Nighttime Commuters in Commercial Centers',
                'January 2024',
                'Public Safety',
                'Survey-based assessment analyzing urban lighting, CCTV surveillance coverage, and pedestrian security perceptions.',
                'Public Safety, Urban Planning, Crime Prevention, Fear of Crime'
            ]
        ];

        foreach ($researches as $r) {
            $stmt->execute($r);
        }
        $results[] = "Seeded " . count($researches) . " Criminological Research papers.";
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
                'Dr. Roberto M. Dela Cruz, Ph.D. Crim., CSP',
                'OIC - Dean, School of Criminal Justice Education',
                'dean',
                'dean.scje@dwcc-scje.edu.ph',
                'Criminological Theories, Criminal Justice Administration, Security Management',
                'Restorative Justice, Institutional Leadership, Public Safety Metrics',
                'Office of the Dean, 2nd Floor SCJE Building',
                'assets/images/faculty_dean.jpg',
                1
            ],
            // Second Level: Chairs and Coordinators
            [
                'Prof. Joan Mae A. Gayacan, RCrim., MS Crim.',
                'Program Chairperson, Department of Criminology',
                'chair',
                'j.gayacan@dwcc-scje.edu.ph',
                'Questioned Document Examination, Forensic Photography',
                'Forensic Science, Criminal Justice, Indigenous Peoples\' Rights',
                'Criminology Faculty Office, Room 204',
                'assets/images/faculty_gayacan.jpg',
                2
            ],
            [
                'Dr. Anthony S. Morales, RCrim., CSP',
                'Forensic Science & Ballistics Coordinator',
                'coordinator',
                'a.morales@dwcc-scje.edu.ph',
                'Forensic Ballistics, Firearms Identification, Toolmark Analysis',
                'Striation Microscopy, Ballistic Databases, Gunpowder Chemistry',
                'Ballistics Laboratory Annex, Room 108',
                'assets/images/faculty_morales.jpg',
                3
            ],
            [
                'Engr. Mark Daniel Bautista, MSFS',
                'Laboratory Custodian & Technical Officer',
                'custodian',
                'm.bautista@dwcc-scje.edu.ph',
                'Forensic Instrumentation, Chemical Safety, Dactyloscopy Systems',
                'Forensic Laboratory Protocols, Quality Assurance, Reagent Stability',
                'Central Criminology Laboratory Office, Room 101',
                'assets/images/faculty_bautista.jpg',
                4
            ],
            // Third Level: Professors & Subject Matter Experts
            [
                'Atty. Clarissa T. Mendoza, LL.B., RCrim.',
                'Professor of Criminal Law & Evidence',
                'faculty',
                'c.mendoza@dwcc-scje.edu.ph',
                'Criminal Law Book 1 & 2, Criminal Procedure, Court Testimony',
                'Rules on Electronic Evidence, Constitutional Rights, Prosecutorial Discretion',
                'Faculty Hall, Room 205',
                'assets/images/faculty_mendoza.jpg',
                5
            ],
            [
                'Capt. Eduardo P. Ramos, PN (Ret.), MS Crim.',
                'Associate Professor - Law Enforcement Administration',
                'faculty',
                'e.ramos@dwcc-scje.edu.ph',
                'Police Patrol Operations, Industrial Security, Crisis Incident Command',
                'Community-Oriented Policing, Maritime Law Enforcement, Counter-Terrorism',
                'Faculty Hall, Room 206',
                'assets/images/faculty_ramos.jpg',
                6
            ],
            [
                'Prof. Veronica L. Santos, RCrim.',
                'Assistant Professor - Criminalistics',
                'faculty',
                'v.santos@dwcc-scje.edu.ph',
                'Dactyloscopy, Personal Identification, Forensic Photography',
                'Latent Ridge Quality, Friction Ridge Development, Biometrics',
                'Criminalistics Lab Prep Room',
                'assets/images/faculty_santos.jpg',
                7
            ],
            [
                'Prof. Gerald N. Navarro, RCrim.',
                'Instructor - Crime Scene Investigation & Polygraphy',
                'faculty',
                'g.navarro@dwcc-scje.edu.ph',
                'Crime Scene Processing, Physical Evidence Handling, Lie Detection',
                'Mock Crime Scene Pedagogy, Psychophysiological Stress Response',
                'CSI Simulation Facility',
                'assets/images/faculty_navarro.jpg',
                8
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
            echo "  Admin ID Number : " . env('DEFAULT_ADMIN_ID', 'ADMIN-001') . "\n";
            echo "  Password        : " . env('DEFAULT_ADMIN_PASSWORD', 'password123') . "\n";
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
            echo '<p>Default Admin: <strong>' . htmlspecialchars(env('DEFAULT_ADMIN_ID', 'ADMIN-001')) . '</strong> / Password: <strong>' . htmlspecialchars(env('DEFAULT_ADMIN_PASSWORD', 'password123')) . '</strong></p>';
            echo '<a href="' . base_url() . '">Go to Website Homepage &rarr;</a>';
            echo '</div></body></html>';
        }
    } catch (Exception $e) {
        die("Setup error: " . $e->getMessage());
    }
}
