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
            date_acquired DATE NULL,
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

    // 4. Seed Laboratory Equipment (Matching 5 Lab Areas + Sketch Columns)
    $checkEquip = $pdo->query("SELECT COUNT(*) FROM equipment")->fetchColumn();
    if ($checkEquip == 0) {
        $stmt = $pdo->prepare("INSERT INTO equipment (equipment_code, equipment_name, brand, model, current_location, laboratory_category, status, serial_number, description) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");

        $equipment = [
            [
                'EQ-BAL-001',
                'Comparison Microscope with Digital Imaging',
                'Leica',
                'FS4000 M',
                'Forensic Ballistics Laboratory',
                'Forensic Ballistics',
                'Available',
                'LCA-FS-98421',
                'High-precision motorized bridge comparison microscope with dual split-field optical system for matching fired bullets and cartridge cases.'
            ],
            [
                'EQ-BAL-002',
                'Bullet Recovery Water Tank System',
                'Sirchie',
                'BRT-500',
                'Forensic Ballistics Laboratory',
                'Forensic Ballistics',
                'Available',
                'SRC-WT-2041',
                'Heavy-duty stainless steel water recovery tank with deceleration baffles for collecting non-deformed test-fired test bullets.'
            ],
            [
                'EQ-CRIM-001',
                'Automated Cyanoacrylate Fuming Chamber',
                'Air Science',
                'Safefume 36',
                'Criminalistics Laboratory',
                'Criminalistics Laboratory',
                'Available',
                'ASC-SF-1104',
                'Controlled humidity and temperature chamber for developing latent fingerprint ridge details on non-porous physical evidence.'
            ],
            [
                'EQ-CRIM-002',
                'Forensic Optical Comparator / Video Spectral Comparator',
                'Foster+Freeman',
                'VSC40',
                'Questioned Documents Room',
                'Criminalistics Laboratory',
                'Available',
                'FF-VSC-3921',
                'Multi-spectral illumination unit with UV, infrared, and coaxial light sources for examining questioned signatures, paper fiber, and ink differentiations.'
            ],
            [
                'EQ-CRIM-003',
                'Stereomicroscope with Polarized Light Stage',
                'Olympus',
                'SZ61',
                'Criminalistics Laboratory',
                'Criminalistics Laboratory',
                'Available',
                'OLY-SZ-5509',
                'Used for macroscopic inspection of hair, fiber, soil samples, toolmarks, and microscopic physical trace items.'
            ],
            [
                'EQ-CSI-001',
                'Alternate Light Source (ALS) Crime Scene Forensic Kit',
                'Sirchie',
                'MegaMAXX MK-II',
                'Crime Scene Investigation Laboratory',
                'Crime Scene Investigation Laboratory',
                'Available',
                'SRC-MM-4421',
                'Multi-wavelength LED forensic torch kit (UV, Blue, Green, Cyan) for identifying biological fluids, latent prints, and gunshot residue.'
            ],
            [
                'EQ-CSI-002',
                'Crime Scene Forensic Photography Kit with Macro Ring Flash',
                'Nikon',
                'D7500 Forensic FX',
                'Crime Scene Investigation Laboratory',
                'Crime Scene Investigation Laboratory',
                'Available',
                'NK-FX-8812',
                'DSLR camera kit outfitted with 60mm micro-lens, polarized filters, scale calibrations, and forensic photographic scales.'
            ],
            [
                'EQ-CSI-003',
                'Electrostatic Dust Print Lifter (EDPL)',
                'Sirchie',
                'ESP900',
                'Crime Scene Investigation Laboratory',
                'Crime Scene Investigation Laboratory',
                'Available',
                'SRC-ED-7712',
                'High-voltage electrostatic charge generator for lifting latent dust impressions from floors, carpets, paper, and upholstery.'
            ],
            [
                'EQ-FS-001',
                'UV-Vis Double Beam Spectrophotometer',
                'Shimadzu',
                'UV-1900i',
                'Forensic Science Laboratory',
                'Forensic Science Laboratory',
                'Available',
                'SHM-UV-6632',
                'Analytical spectrophotometer for quantitative measurement of drug analytes, toxicology blood-alcohol concentration, and forensic chemical reagents.'
            ],
            [
                'EQ-FS-002',
                'Ductless Forensic Chemical Fume Hood',
                'Labconco',
                'Purifier 32',
                'Forensic Science Laboratory',
                'Forensic Science Laboratory',
                'Available',
                'LBC-FH-1290',
                'Equipped with carbon-activated filters for safely preparing caustic chemical reagents, Kastle-Meyer, and luminol solutions.'
            ],
            [
                'EQ-SPEC-001',
                'Computerized 6-Channel Polygraph System',
                'Lafayette',
                'LX6-00',
                'Polygraph Examination Suite',
                'Other Specialized Areas',
                'Available',
                'LFY-LX-88301',
                'State-of-the-art computerized polygraph with thoracic and abdominal pneumographs, skin resistance EDA sensors, blood pressure cuff, and plethysmograph.'
            ],
            [
                'EQ-SPEC-002',
                'Forensic Digital Extraction & Triage Hardware Workstation',
                'Cellebrite',
                'UFED Touch2',
                'Digital Forensics Center',
                'Other Specialized Areas',
                'Available',
                'CLB-UF-4009',
                'Dedicated hardware device for forensically acquiring logical and physical mobile extractions from suspect cellular devices with integrity hashing.'
            ],
            [
                'EQ-BAL-003',
                'Electronic Trigger Pull Gauge',
                'Lyman',
                'Digital Trigger Spec',
                'Forensic Ballistics Laboratory',
                'Forensic Ballistics',
                'In Use',
                'LYM-TP-552',
                'Precision sensor for measuring the trigger release resistance and pull force of suspect firearms in kilograms and pounds.'
            ],
            [
                'EQ-CRIM-004',
                'Direct Inking Dactyloscopic Fingerprint Station',
                'Sirchie',
                'Master Print-60',
                'Dactyloscopy Laboratory',
                'Criminalistics Laboratory',
                'Available',
                'SRC-MP-9003',
                'Stainless steel fingerprint table with porelon ceramic roller, glass ink plate, and tenprint cardholder clamps.'
            ]
        ];

        foreach ($equipment as $eq) {
            $stmt->execute($eq);
        }
        $results[] = "Seeded " . count($equipment) . " Laboratory Equipment records.";
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
                'A leading center of excellence in criminology and criminal justice education, recognized for academic rigor, ethical public safety leadership, and advanced forensic innovation in the region and beyond.'
            ],
            [
                'mission',
                'MISSION',
                'To provide quality education, cutting-edge empirical research, and rigorous professional training for a safer, peaceful, and just society through competent, disciplined, and morally upright graduates.'
            ],
            [
                'goals',
                'GOALS',
                'To produce highly competent, ethical, and service-oriented criminology professionals equipped with scientific investigative skills, respect for human rights, and readiness for national and international law enforcement agencies.'
            ],
            [
                'history',
                'HISTORY OF SCJ',
                'The School of Criminal Justice Education (SCJE) at Divine Word College of Calapan (DWCC) was established in response to the growing national imperative for professionally trained, scientifically grounded, and value-laden criminologists. 

Founded with a commitment to academic excellence and moral formation, the School has grown from a humble department into a recognized premier institution in Oriental Mindoro and the MIMAROPA region. Over decades of dedicated service, SCJE has produced top-ranking licensed criminologists who now lead prominent positions in the Philippine National Police (PNP), Bureau of Jail Management and Penology (BJMP), Bureau of Fire Protection (BFP), National Bureau of Investigation (NBI), Philippine Drug Enforcement Agency (PDEA), Armed Forces of the Philippines (AFP), and corporate security domains.

Today, the School boasts state-of-the-art specialized laboratories—including the Forensic Ballistics Laboratory, Criminalistics and Dactyloscopy Suite, Crime Scene Investigation Simulation Facility, and Computerized Polygraph Examination Suite—advancing the boundaries of scientific crime investigation and criminal justice research.'
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
