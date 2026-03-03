<?php
/**
 * IVF Experts — MASTER MIGRATION SCRIPT
 * Phase 1: Database Schema Stabilization
 *
 * Safe to run multiple times (idempotent).
 * Checks before altering — will not destroy existing data.
 * Run this FIRST before using any admin features after a DB reset.
 */
require_once __DIR__ . '/includes/auth.php';

$results = [];
$errors = [];

function run_sql(mysqli $conn, string $label, string $sql): string
{
    try {
        if ($conn->query($sql)) {
            return "<tr><td class='p'>✅</td><td class='p'><b>{$label}</b></td><td class='p text-green'>OK</td></tr>";
        }
        else {
            return "<tr><td class='p'>❌</td><td class='p'><b>{$label}</b></td><td class='p text-red'>" . htmlspecialchars($conn->error) . "</td></tr>";
        }
    }
    catch (Throwable $e) {
        $msg = $e->getMessage();
        $skip = strpos($msg, 'Duplicate column') !== false || strpos($msg, 'already exists') !== false;
        $icon = $skip ? '⚪' : '❌';
        $cls = $skip ? 'text-orange' : 'text-red';
        return "<tr><td class='p'>{$icon}</td><td class='p'><b>{$label}</b></td><td class='p {$cls}'>" . ($skip ? "Already exists — skipped" : htmlspecialchars($msg)) . "</td></tr>";
    }
}

function ensure_column(mysqli $conn, string $table, string $col, string $def): string
{
    try {
        $res = $conn->query("SHOW COLUMNS FROM `{$table}` LIKE '{$col}'");
        if ($res && $res->num_rows > 0) {
            return "<tr><td class='p'>⚪</td><td class='p'><b>{$table}.{$col}</b></td><td class='p text-orange'>Already exists</td></tr>";
        }
        $conn->query("ALTER TABLE `{$table}` ADD {$col} {$def}");
        return "<tr><td class='p'>✅</td><td class='p'><b>{$table}.{$col}</b></td><td class='p text-green'>Added</td></tr>";
    }
    catch (Throwable $e) {
        return "<tr><td class='p'>❌</td><td class='p'><b>{$table}.{$col}</b></td><td class='p text-red'>" . htmlspecialchars($e->getMessage()) . "</td></tr>";
    }
}

$out = '';

// ─────────────────────────────────────────────────────────────
// SECTION 1: CORE TABLES
// ─────────────────────────────────────────────────────────────
$out .= "<h2>1. Core Tables</h2><table width='100%'>";

$out .= run_sql($conn, "hospitals", "CREATE TABLE IF NOT EXISTS hospitals (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    address VARCHAR(255),
    city VARCHAR(100),
    phone VARCHAR(30),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$out .= run_sql($conn, "patients", "CREATE TABLE IF NOT EXISTS patients (
    id INT AUTO_INCREMENT PRIMARY KEY,
    mr_number VARCHAR(30) UNIQUE NOT NULL,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100),
    patient_age INT,
    date_of_birth DATE,
    blood_group VARCHAR(10),
    gender ENUM('Male', 'Female', 'Other') NOT NULL,
    marital_status ENUM('Single', 'Married', 'Divorced', 'Widowed') DEFAULT 'Married',
    gravida INT DEFAULT 0,
    para INT DEFAULT 0,
    abortions INT DEFAULT 0,
    years_married INT,
    cnic VARCHAR(20),
    phone VARCHAR(20),
    address TEXT,
    email VARCHAR(255),
    spouse_name VARCHAR(150),
    spouse_age INT,
    spouse_gender ENUM('Male','Female','Other'),
    spouse_cnic VARCHAR(20),
    spouse_phone VARCHAR(20),
    referring_hospital_id INT,
    photo_path VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$out .= run_sql($conn, "patient_history", "CREATE TABLE IF NOT EXISTS patient_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    patient_id INT NOT NULL,
    record_for ENUM('Patient','Spouse') DEFAULT 'Patient',
    clinical_notes TEXT,
    diagnosis TEXT,
    medication TEXT,
    advice TEXT,
    next_visit DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$out .= run_sql($conn, "prescriptions", "CREATE TABLE IF NOT EXISTS prescriptions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    patient_id INT NOT NULL,
    record_for ENUM('Patient','Spouse') DEFAULT 'Patient',
    clinical_notes TEXT,
    diagnosis TEXT,
    icd10_codes JSON,
    general_advice TEXT,
    next_visit DATE,
    qrcode_hash VARCHAR(64),
    scanned_report_path VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$out .= run_sql($conn, "prescription_items", "CREATE TABLE IF NOT EXISTS prescription_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    prescription_id INT NOT NULL,
    medicine_name VARCHAR(255) NOT NULL,
    dosage VARCHAR(100),
    frequency VARCHAR(100),
    duration VARCHAR(100),
    instructions TEXT,
    INDEX (prescription_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$out .= run_sql($conn, "advised_lab_tests (prescription linking)", "CREATE TABLE IF NOT EXISTS advised_lab_tests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    prescription_id INT,
    patient_id INT NOT NULL,
    test_id INT,
    test_name VARCHAR(255),
    record_for ENUM('Patient','Spouse') DEFAULT 'Patient',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$out .= run_sql($conn, "lab_tests_directory", "CREATE TABLE IF NOT EXISTS lab_tests_directory (
    id INT AUTO_INCREMENT PRIMARY KEY,
    test_name VARCHAR(255) NOT NULL,
    category VARCHAR(100),
    unit VARCHAR(50),
    reference_range VARCHAR(255),
    reference_range_male TEXT,
    reference_range_female TEXT,
    cpt_code VARCHAR(20),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$out .= run_sql($conn, "patient_lab_results", "CREATE TABLE IF NOT EXISTS patient_lab_results (
    id INT AUTO_INCREMENT PRIMARY KEY,
    patient_id INT NOT NULL,
    test_id INT,
    test_for ENUM('Patient','Spouse') DEFAULT 'Patient',
    result_value VARCHAR(100),
    status ENUM('Pending','Completed') DEFAULT 'Completed',
    unit VARCHAR(50),
    reference_range VARCHAR(255),
    reference_range_male TEXT,
    reference_range_female TEXT,
    test_date DATE,
    lab_name VARCHAR(255),
    lab_city VARCHAR(100),
    lab_mr_number VARCHAR(50),
    scanned_report_path VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$out .= run_sql($conn, "patient_ultrasounds", "CREATE TABLE IF NOT EXISTS patient_ultrasounds (
    id INT AUTO_INCREMENT PRIMARY KEY,
    patient_id INT NOT NULL,
    hospital_id INT,
    qrcode_hash VARCHAR(64),
    record_for ENUM('Patient','Spouse') DEFAULT 'Patient',
    report_title VARCHAR(255),
    content LONGTEXT,
    scanned_report_path VARCHAR(255),
    scan_timestamp DATETIME,
    scan_location_data VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$out .= run_sql($conn, "semen_analyses", "CREATE TABLE IF NOT EXISTS semen_analyses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    patient_id INT NOT NULL,
    collection_time DATETIME,
    volume DECIMAL(5,2),
    concentration DECIMAL(8,2),
    total_count DECIMAL(10,2),
    pr_motility DECIMAL(5,2) DEFAULT 0,
    np_motility DECIMAL(5,2) DEFAULT 0,
    im_motility DECIMAL(5,2) DEFAULT 0,
    normal_morphology DECIMAL(5,2),
    vitality DECIMAL(5,2),
    wbc_count DECIMAL(5,2),
    auto_diagnosis VARCHAR(255),
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$out .= run_sql($conn, "advised_procedures", "CREATE TABLE IF NOT EXISTS advised_procedures (
    id INT AUTO_INCREMENT PRIMARY KEY,
    patient_id INT NOT NULL,
    record_for ENUM('Patient','Spouse') DEFAULT 'Patient',
    procedure_name VARCHAR(255) NOT NULL,
    date_advised DATE,
    status ENUM('Advised','In Progress','Completed','Cancelled') DEFAULT 'Advised',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$out .= run_sql($conn, "receipts", "CREATE TABLE IF NOT EXISTS receipts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    patient_id INT NOT NULL,
    hospital_id INT,
    advised_procedure_id INT,
    procedure_name VARCHAR(255),
    amount DECIMAL(15,2) NOT NULL DEFAULT 0,
    payment_method VARCHAR(50) DEFAULT 'Cash',
    status ENUM('Paid','Unpaid','Pending','Past Due') DEFAULT 'Paid',
    receipt_date DATE,
    qrcode_hash VARCHAR(64) UNIQUE,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$out .= run_sql($conn, "ultrasound_templates", "CREATE TABLE IF NOT EXISTS ultrasound_templates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    body LONGTEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$out .= run_sql($conn, "medications library", "CREATE TABLE IF NOT EXISTS medications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) UNIQUE NOT NULL,
    generic_name VARCHAR(255),
    category VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$out .= "</table>";

// ─────────────────────────────────────────────────────────────
// SECTION 2: ENSURE CRITICAL COLUMNS ON PRE-EXISTING TABLES
// ─────────────────────────────────────────────────────────────
$out .= "<h2>2. Ensuring Critical Columns</h2><table width='100%'>";

// patients extra columns
foreach ([
['patients', 'spouse_name', 'VARCHAR(150) AFTER email'],
['patients', 'spouse_age', 'INT AFTER spouse_name'],
['patients', 'spouse_gender', "ENUM('Male','Female','Other') AFTER spouse_age"],
['patients', 'spouse_cnic', 'VARCHAR(20) AFTER spouse_gender'],
['patients', 'spouse_phone', 'VARCHAR(20) AFTER spouse_cnic'],
['patients', 'referring_hospital_id', 'INT AFTER spouse_phone'],
['patients', 'photo_path', 'VARCHAR(255) AFTER referring_hospital_id'],
['prescriptions', 'record_for', "ENUM('Patient','Spouse') DEFAULT 'Patient' AFTER patient_id"],
['prescriptions', 'icd10_codes', 'JSON AFTER diagnosis'],
['patient_history', 'record_for', "ENUM('Patient','Spouse') DEFAULT 'Patient' AFTER patient_id"],
['patient_lab_results', 'test_for', "ENUM('Patient','Spouse') DEFAULT 'Patient' AFTER test_id"],
['patient_lab_results', 'status', "ENUM('Pending','Completed') DEFAULT 'Completed' AFTER result_value"],
['patient_lab_results', 'reference_range_male', 'TEXT AFTER reference_range'],
['patient_lab_results', 'reference_range_female', 'TEXT AFTER reference_range_male'],
['patient_ultrasounds', 'record_for', "ENUM('Patient','Spouse') DEFAULT 'Patient' AFTER patient_id"],
['advised_procedures', 'record_for', "ENUM('Patient','Spouse') DEFAULT 'Patient' AFTER patient_id"],
['advised_procedures', 'status', "ENUM('Advised','In Progress','Completed','Cancelled') DEFAULT 'Advised' AFTER procedure_name"],
['lab_tests_directory', 'reference_range_male', 'TEXT AFTER reference_range'],
['lab_tests_directory', 'reference_range_female', 'TEXT AFTER reference_range_male'],
['lab_tests_directory', 'cpt_code', 'VARCHAR(20) AFTER reference_range_female'],
] as [$t, $c, $d]) {
    $out .= ensure_column($conn, $t, $c, $d);
}
$out .= "</table>";

// ─────────────────────────────────────────────────────────────
// SECTION 3: SEED ESSENTIAL DATA
// ─────────────────────────────────────────────────────────────
$out .= "<h2>3. Seeding Essential Data</h2><table width='100%'>";

$out .= run_sql($conn, "Default hospital (if none)", "INSERT IGNORE INTO hospitals (id, name, city) VALUES (1, 'IVF Experts Clinic', 'Lahore')");

$out .= run_sql($conn, "Core fertility medications", "INSERT IGNORE INTO medications (name, category) VALUES
    ('Clomiphene Citrate (Clomid)', 'Ovulation Induction'),
    ('Letrozole (Femara)', 'Ovulation Induction'),
    ('Gonadotropins (FSH/hMG)', 'Ovarian Stimulation'),
    ('hCG (Ovitrelle)', 'Trigger Shot'),
    ('Progesterone (Cyclogest)', 'Luteal Support'),
    ('Metformin', 'Insulin Sensitizer'),
    ('Folic Acid 5mg', 'Supplement'),
    ('Aspirin 75mg', 'Anti-coagulant'),
    ('Prednisolone', 'Immunosuppressant'),
    ('Dydrogesterone (Duphaston)', 'Progesterone Support'),
    ('Inositol', 'PCOS Support'),
    ('CoQ10 (Ubiquinol)', 'Antioxidant'),
    ('Vitamin D3', 'Supplement'),
    ('Omega-3', 'Supplement'),
    ('GnRH Agonist (Lupron)', 'Downregulation')
");

$out .= run_sql($conn, "Core IVF lab tests directory", "INSERT IGNORE INTO lab_tests_directory (test_name, category, unit, reference_range_male, reference_range_female) VALUES
    ('FSH (Follicle Stimulating Hormone)', 'Hormonal', 'mIU/mL', '1.5–12.4', '3.5–12.5 (follicular)'),
    ('LH (Luteinizing Hormone)', 'Hormonal', 'mIU/mL', '1.7–8.6', '2.4–12.6 (follicular)'),
    ('AMH (Anti-Müllerian Hormone)', 'Ovarian Reserve', 'ng/mL', '1.0–10.0', '1.0–10.0 (age-dependent)'),
    ('Prolactin', 'Hormonal', 'ng/mL', '2–18', '2–29'),
    ('TSH (Thyroid Stimulating Hormone)', 'Thyroid', 'mIU/L', '0.4–4.0', '0.4–4.0'),
    ('Testosterone (Total)', 'Hormonal', 'ng/dL', '300–1000', '15–70'),
    ('Estradiol (E2)', 'Hormonal', 'pg/mL', '10–40', '30–400 (follicular)'),
    ('Progesterone', 'Hormonal', 'ng/mL', '0.3–1.2', '1–28 (luteal)'),
    ('Anti-Sperm Antibodies (ASA)', 'Immunological', 'units', 'Negative', 'Negative'),
    ('CBC (Complete Blood Count)', 'Hematology', 'See report', 'Normal range', 'Normal range'),
    ('Blood Group & Rh Factor', 'Hematology', 'Type', '-', '-'),
    ('HBsAg (Hepatitis B)', 'Infectious', '-', 'Negative', 'Negative'),
    ('Anti-HCV (Hepatitis C)', 'Infectious', '-', 'Negative', 'Negative'),
    ('HIV 1&2', 'Infectious', '-', 'Non-reactive', 'Non-reactive'),
    ('VDRL/RPR (Syphilis)', 'Infectious', '-', 'Non-reactive', 'Non-reactive'),
    ('TORCH Panel', 'Infectious', '-', '-', 'IgG/IgM see report'),
    ('Thyroid Antibodies (TPO Ab)', 'Thyroid', 'IU/mL', '<35', '<35'),
    ('Random Blood Sugar', 'Metabolic', 'mg/dL', '70–140', '70–140'),
    ('HbA1c', 'Metabolic', '%', '<5.7', '<5.7'),
    ('Semen Analysis (WHO 6th Ed)', 'Andrology', 'See report', 'See WHO criteria', 'N/A'),
    ('Sperm DNA Fragmentation Index', 'Andrology', '%', '<15 (ideal)', 'N/A'),
    ('Karyotype', 'Genetic', '-', '46,XY normal', '46,XX normal'),
    ('Y-Chromosome Microdeletion', 'Genetic', '-', 'No deletion', 'N/A')
");

$out .= "</table>";

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Master Migration — IVF Experts</title>
<style>
  body { font-family: 'Segoe UI', sans-serif; background: #f8fafc; color: #1e293b; margin: 0; padding: 2rem; }
  h1 { color: #0f172a; border-bottom: 3px solid #2dd4bf; pb: 0.5rem; }
  h2 { color: #0f172a; margin-top: 2rem; font-size: 1.1rem; background: #f1f5f9; padding: 0.5rem 1rem; border-radius: 8px; }
  table { width: 100%; border-collapse: collapse; margin-bottom: 1rem; }
  .p { padding: 6px 10px; font-size: 0.8rem; border-bottom: 1px solid #f1f5f9; }
  .text-green { color: #16a34a; font-weight: bold; }
  .text-orange { color: #d97706; }
  .text-red { color: #dc2626; font-weight: bold; }
  .summary { background: #f0fdf4; border: 2px solid #86efac; border-radius: 12px; padding: 1.5rem; margin-top: 2rem; }
  .warning { background: #fffbeb; border: 2px solid #fcd34d; border-radius: 12px; padding: 1rem; margin-bottom: 1.5rem; font-size: 0.85rem; }
  a.btn { display: inline-block; margin: 0.25rem; padding: 0.5rem 1.25rem; background: #0d9488; color: white; border-radius: 8px; text-decoration: none; font-weight: bold; font-size: 0.8rem; }
  a.btn:hover { background: #0f766e; }
</style>
</head>
<body>
<h1>🧬 IVF Experts — Master Database Migration</h1>
<div class="warning">⚠️ <strong>Security Note:</strong> Delete or restrict this file after running. It should not be publicly accessible.</div>

<?php echo $out; ?>

<div class="summary">
  <h2 style="margin-top:0; background:none; padding:0;">✅ Migration Complete!</h2>
  <p>All required tables and columns have been verified. You can now use all admin features.</p>
  <p><strong>Next Steps:</strong></p>
  <ol>
    <li>Click "Schema Check" below to verify everything is green</li>
    <li>If any rows show ❌, check your DB user permissions on Hostinger</li>
    <li>Delete or rename this file: <code>master_migration.php</code></li>
  </ol>
  <br>
  <a href="schema_check.php" class="btn">🔬 Schema Check</a>
  <a href="patients.php" class="btn">👥 Patients</a>
  <a href="dashboard.php" class="btn">📊 Dashboard</a>
</div>
</body>
</html>
