<?php
/**
 * Migration: Comprehensive Lab Tests Library
 * Adds `category` column to lab_tests_directory and seeds 150+ standard tests
 * with NABL / international reference ranges (male & female).
 *
 * Run ONCE on Hostinger via browser: /admin/migrate_lab_tests_library.php
 * Safe to re-run — uses ON DUPLICATE KEY UPDATE.
 */

require_once __DIR__ . '/includes/auth.php';

header('Content-Type: text/html; charset=utf-8');
echo "<!DOCTYPE html><html><head><title>Lab Library Migration</title>
<style>body{font-family:monospace;padding:2rem;background:#0f172a;color:#e2e8f0;}
h1{color:#38bdf8;} h2{color:#34d399;margin-top:2rem;}
.ok{color:#4ade80;} .skip{color:#fb923c;} .err{color:#f87171;}
table{border-collapse:collapse;width:100%;margin-top:1rem;font-size:13px;}
th{background:#1e293b;color:#94a3b8;padding:8px 12px;text-align:left;}
td{padding:6px 12px;border-bottom:1px solid #1e293b;}
tr:hover td{background:#1e293b;}
</style></head><body>";

echo "<h1>IVF Experts — Lab Tests Library Migration</h1>";

// ─── Step 1: Add category column ─────────────────────────────────────────────
echo "<h2>Step 1: Schema Update</h2>";
$check = $conn->query("SHOW COLUMNS FROM lab_tests_directory LIKE 'category'");
if ($check->num_rows === 0) {
    if ($conn->query("ALTER TABLE lab_tests_directory ADD COLUMN category VARCHAR(100) NOT NULL DEFAULT 'Other' AFTER unit")) {
        echo "<span class='ok'>✔ Column `category` added.</span><br>";
    } else {
        echo "<span class='err'>✘ Failed to add column: " . $conn->error . "</span><br>";
    }
} else {
    echo "<span class='skip'>→ Column `category` already exists. Skipping.</span><br>";
}

// ─── Step 2: Seed lab tests ───────────────────────────────────────────────────
echo "<h2>Step 2: Seeding Tests</h2>";

/**
 * Format: [test_name, unit, reference_range_male, reference_range_female, category]
 * Reference ranges sourced from NABL-accredited labs (Chughtai, Aga Khan, Dr Essa),
 * WHO guidelines, and international standards (CLSI, College of American Pathologists).
 */
$tests = [

    // ── COMPLETE BLOOD COUNT ──────────────────────────────────────────────────
    ['Hemoglobin', 'g/dL',
        '13.0 – 17.0',
        '12.0 – 15.5',
        'Complete Blood Count (CBC)'],

    ['Red Blood Cell (RBC) Count', 'million/µL',
        '4.5 – 5.5',
        '3.8 – 4.8',
        'Complete Blood Count (CBC)'],

    ['White Blood Cell (WBC) Count', '/µL',
        '4,000 – 11,000',
        '4,000 – 11,000',
        'Complete Blood Count (CBC)'],

    ['Platelet Count', '/µL',
        '150,000 – 400,000',
        '150,000 – 400,000',
        'Complete Blood Count (CBC)'],

    ['Hematocrit / PCV', '%',
        '40 – 52',
        '36 – 48',
        'Complete Blood Count (CBC)'],

    ['MCV (Mean Corpuscular Volume)', 'fL',
        '80 – 100',
        '80 – 100',
        'Complete Blood Count (CBC)'],

    ['MCH (Mean Corpuscular Hemoglobin)', 'pg',
        '27 – 33',
        '27 – 33',
        'Complete Blood Count (CBC)'],

    ['MCHC (Mean Corpuscular Hb Concentration)', 'g/dL',
        '32 – 36',
        '32 – 36',
        'Complete Blood Count (CBC)'],

    ['RDW-CV (Red Cell Distribution Width)', '%',
        '11.5 – 14.5',
        '11.5 – 14.5',
        'Complete Blood Count (CBC)'],

    ['Neutrophils (%)', '%',
        '50 – 70',
        '50 – 70',
        'Complete Blood Count (CBC)'],

    ['Lymphocytes (%)', '%',
        '20 – 40',
        '20 – 40',
        'Complete Blood Count (CBC)'],

    ['Monocytes (%)', '%',
        '2 – 10',
        '2 – 10',
        'Complete Blood Count (CBC)'],

    ['Eosinophils (%)', '%',
        '1 – 6',
        '1 – 6',
        'Complete Blood Count (CBC)'],

    ['Basophils (%)', '%',
        '0 – 1',
        '0 – 1',
        'Complete Blood Count (CBC)'],

    ['ESR (Erythrocyte Sedimentation Rate)', 'mm/hr',
        '0 – 15',
        '0 – 20',
        'Complete Blood Count (CBC)'],

    ['Reticulocyte Count', '%',
        '0.5 – 2.5',
        '0.5 – 2.5',
        'Complete Blood Count (CBC)'],

    // ── LIVER FUNCTION TESTS ──────────────────────────────────────────────────
    ['Total Bilirubin', 'mg/dL',
        '0.2 – 1.2',
        '0.2 – 1.2',
        'Liver Function Tests (LFT)'],

    ['Direct Bilirubin (Conjugated)', 'mg/dL',
        '0.0 – 0.4',
        '0.0 – 0.4',
        'Liver Function Tests (LFT)'],

    ['Indirect Bilirubin (Unconjugated)', 'mg/dL',
        '0.1 – 0.8',
        '0.1 – 0.8',
        'Liver Function Tests (LFT)'],

    ['SGOT / AST (Aspartate Aminotransferase)', 'U/L',
        '10 – 40',
        '10 – 35',
        'Liver Function Tests (LFT)'],

    ['SGPT / ALT (Alanine Aminotransferase)', 'U/L',
        '7 – 56',
        '7 – 45',
        'Liver Function Tests (LFT)'],

    ['Alkaline Phosphatase (ALP)', 'U/L',
        '44 – 147',
        '44 – 147',
        'Liver Function Tests (LFT)'],

    ['GGT (Gamma-Glutamyl Transferase)', 'U/L',
        '9 – 48',
        '9 – 32',
        'Liver Function Tests (LFT)'],

    ['Total Protein', 'g/dL',
        '6.4 – 8.3',
        '6.4 – 8.3',
        'Liver Function Tests (LFT)'],

    ['Albumin', 'g/dL',
        '3.5 – 5.0',
        '3.5 – 5.0',
        'Liver Function Tests (LFT)'],

    ['Globulin', 'g/dL',
        '2.0 – 3.5',
        '2.0 – 3.5',
        'Liver Function Tests (LFT)'],

    ['A/G Ratio (Albumin / Globulin)', 'ratio',
        '1.1 – 2.5',
        '1.1 – 2.5',
        'Liver Function Tests (LFT)'],

    ['LDH (Lactate Dehydrogenase)', 'U/L',
        '140 – 280',
        '140 – 280',
        'Liver Function Tests (LFT)'],

    // ── KIDNEY FUNCTION TESTS ─────────────────────────────────────────────────
    ['Serum Creatinine', 'mg/dL',
        '0.7 – 1.3',
        '0.5 – 1.1',
        'Kidney Function Tests (KFT)'],

    ['Blood Urea', 'mg/dL',
        '15 – 40',
        '15 – 40',
        'Kidney Function Tests (KFT)'],

    ['BUN (Blood Urea Nitrogen)', 'mg/dL',
        '7 – 20',
        '7 – 20',
        'Kidney Function Tests (KFT)'],

    ['Uric Acid', 'mg/dL',
        '3.5 – 7.2',
        '2.6 – 6.0',
        'Kidney Function Tests (KFT)'],

    ['eGFR (Estimated GFR)', 'mL/min/1.73m²',
        '≥60 (Normal)\n<60 = Chronic Kidney Disease',
        '≥60 (Normal)\n<60 = Chronic Kidney Disease',
        'Kidney Function Tests (KFT)'],

    ['Sodium (Na+)', 'mEq/L',
        '136 – 145',
        '136 – 145',
        'Kidney Function Tests (KFT)'],

    ['Potassium (K+)', 'mEq/L',
        '3.5 – 5.1',
        '3.5 – 5.1',
        'Kidney Function Tests (KFT)'],

    ['Chloride (Cl-)', 'mEq/L',
        '98 – 106',
        '98 – 106',
        'Kidney Function Tests (KFT)'],

    ['Bicarbonate (HCO3-)', 'mEq/L',
        '22 – 28',
        '22 – 28',
        'Kidney Function Tests (KFT)'],

    ['Calcium (Total)', 'mg/dL',
        '8.5 – 10.5',
        '8.5 – 10.5',
        'Kidney Function Tests (KFT)'],

    ['Phosphorus (Inorganic)', 'mg/dL',
        '2.5 – 4.5',
        '2.5 – 4.5',
        'Kidney Function Tests (KFT)'],

    ['Magnesium (Mg)', 'mg/dL',
        '1.7 – 2.4',
        '1.7 – 2.4',
        'Kidney Function Tests (KFT)'],

    // ── THYROID FUNCTION TESTS ────────────────────────────────────────────────
    ['TSH (Thyroid Stimulating Hormone)', 'mIU/L',
        '0.4 – 4.0\n(IVF/TTC optimal: 0.5 – 2.5)',
        '0.4 – 4.0\n(IVF/TTC optimal: 0.5 – 2.5)',
        'Thyroid Function Tests'],

    ['T3 (Total Triiodothyronine)', 'ng/dL',
        '80 – 200',
        '80 – 200',
        'Thyroid Function Tests'],

    ['T4 (Total Thyroxine)', 'µg/dL',
        '4.5 – 12.5',
        '4.5 – 12.5',
        'Thyroid Function Tests'],

    ['Free T3 (fT3)', 'pg/mL',
        '2.3 – 4.2',
        '2.3 – 4.2',
        'Thyroid Function Tests'],

    ['Free T4 (fT4)', 'ng/dL',
        '0.9 – 1.7',
        '0.9 – 1.7',
        'Thyroid Function Tests'],

    ['Anti-TPO (Anti-Thyroid Peroxidase Ab)', 'IU/mL',
        '<35 (Negative)',
        '<35 (Negative)',
        'Thyroid Function Tests'],

    ['Anti-Thyroglobulin Antibody (Anti-TG)', 'IU/mL',
        '<115 (Negative)',
        '<115 (Negative)',
        'Thyroid Function Tests'],

    // ── REPRODUCTIVE HORMONES ─────────────────────────────────────────────────
    ['FSH (Follicle Stimulating Hormone)', 'mIU/mL',
        '1.5 – 12.4',
        "Follicular: 3.5 – 12.5\nMidcycle peak: 4.7 – 21.5\nLuteal: 1.7 – 7.7\nPost-menopausal: 25.8 – 134.8",
        'Reproductive Hormones'],

    ['LH (Luteinizing Hormone)', 'mIU/mL',
        '1.7 – 8.6',
        "Follicular: 2.4 – 12.6\nMidcycle peak: 14.0 – 95.6\nLuteal: 1.0 – 11.4\nPost-menopausal: 7.7 – 58.5",
        'Reproductive Hormones'],

    ['Estradiol (E2)', 'pg/mL',
        '15 – 60\n(Elevated in feminizing conditions)',
        "Follicular (Day 2-3): 20 – 75\nFollicular (Late): 100 – 400\nMidcycle peak: 150 – 750\nLuteal: 30 – 450\nPost-menopausal: <30",
        'Reproductive Hormones'],

    ['Progesterone', 'ng/mL',
        '0.2 – 1.4\n(Adult male)',
        "Follicular: <1.0\nMidluteal (Day 21): 5.0 – 25.0\n(Ovulation confirmed: >5)\nPregnancy: 10 – 44 (1st trimester)",
        'Reproductive Hormones'],

    ['Prolactin', 'ng/mL',
        '2.0 – 18.0\n(Hyperprolactinemia: >18)',
        '2.0 – 29.0 (non-pregnant)\n(Hyperprolactinemia: >29)',
        'Reproductive Hormones'],

    ['Testosterone (Total)', 'ng/dL',
        '280 – 1100\n(Low T: <300)',
        '15 – 70\n(Elevated in PCOS: >70)',
        'Reproductive Hormones'],

    ['Testosterone (Free)', 'pg/mL',
        '9.3 – 26.5',
        '0.3 – 1.9',
        'Reproductive Hormones'],

    ['DHEA-S (Dehydroepiandrosterone Sulfate)', 'µg/dL',
        '80 – 560',
        '35 – 430\n(Elevated in PCOS/CAH: >350)',
        'Reproductive Hormones'],

    ['AMH (Anti-Mullerian Hormone)', 'ng/mL',
        '0.7 – 19.0\n(Normal testicular function)',
        "Optimal ovarian reserve: 1.0 – 3.5\nLow reserve (poor IVF response): <1.0\nVery low / diminished: <0.5\nHigh (PCOS risk): >3.5",
        'Reproductive Hormones'],

    ['Inhibin B', 'pg/mL',
        '25 – 325\n(Low in azoospermia)',
        '10 – 285 (follicular phase)',
        'Reproductive Hormones'],

    ['17-Hydroxyprogesterone (17-OHP)', 'ng/mL',
        '0.5 – 2.5',
        "Follicular: 0.1 – 0.8\nLuteal: 0.6 – 2.3\n(Elevated in CAH: >2.0)",
        'Reproductive Hormones'],

    ['SHBG (Sex Hormone Binding Globulin)', 'nmol/L',
        '18 – 114',
        '20 – 130\n(Low in PCOS, Insulin resistance)',
        'Reproductive Hormones'],

    ['Androstenedione', 'ng/mL',
        '0.5 – 3.1',
        '0.3 – 3.3',
        'Reproductive Hormones'],

    ['Beta-hCG (Quantitative, Serum)', 'mIU/mL',
        '<5 (non-pregnant / male)',
        "<5 (non-pregnant)\n5-25 Early / equivocal\n>25 Pregnant\n>1000 at ~4 weeks gestation",
        'Reproductive Hormones'],

    // ── IVF / FERTILITY SPECIFIC ──────────────────────────────────────────────
    ['Day 3 FSH (Ovarian Reserve)', 'mIU/mL',
        'Not applicable',
        "Normal reserve: 3 – 10\nDecreased reserve: 10 – 15\nPoor reserve / poor IVF prognosis: >15\nVery poor / menopause range: >20",
        'IVF & Fertility Specific'],

    ['Day 3 LH (Cycle Day 2-3)', 'mIU/mL',
        'Not applicable',
        "Normal: 2 – 7\nLH:FSH ratio >2:1 suggests PCOS\nLH:FSH ratio >3:1 strongly suggests PCOS",
        'IVF & Fertility Specific'],

    ['Day 3 Estradiol (Basal E2)', 'pg/mL',
        'Not applicable',
        "Normal: 20 – 75\nElevated (>80): Suggests poor ovarian reserve\nor functional cyst (interpret with FSH)",
        'IVF & Fertility Specific'],

    ['Midluteal Progesterone (Day 21-23)', 'ng/mL',
        'Not applicable',
        ">5.0 = Ovulation confirmed\n>10.0 = Adequate luteal phase\n<5.0 = Anovulation or luteal phase defect",
        'IVF & Fertility Specific'],

    ['Sperm DNA Fragmentation Index (DFI)', '%',
        "<15% Normal\n15–25% Moderate (may affect ART outcomes)\n>25% High (associated with recurrent miscarriage)",
        'Not applicable',
        'IVF & Fertility Specific'],

    ['NK Cell (CD56+ Natural Killer Cells)', '% lymphocytes',
        'Not typically tested',
        "Normal uterine NK: 5 – 12%\n>12% may be associated with implantation failure\nPeripheral blood CD56+: 5 – 15%",
        'IVF & Fertility Specific'],

    ['LH:FSH Ratio (PCOS Screening)', 'ratio',
        'Not applicable',
        "<2:1 Normal\n>2:1 Suggestive of PCOS\n>3:1 Strongly suggestive of PCOS",
        'IVF & Fertility Specific'],

    ['Insulin (Fasting) — PCOS/IR Screen', 'µIU/mL',
        '2.0 – 25.0',
        "2.0 – 25.0\n(In PCOS, often elevated >15 µIU/mL)",
        'IVF & Fertility Specific'],

    // ── LIPID PROFILE ─────────────────────────────────────────────────────────
    ['Total Cholesterol', 'mg/dL',
        "<200 Desirable\n200–239 Borderline high\n≥240 High",
        "<200 Desirable\n200–239 Borderline high\n≥240 High",
        'Lipid Profile'],

    ['Triglycerides', 'mg/dL',
        "<150 Normal\n150–199 Borderline\n200–499 High\n≥500 Very high",
        "<150 Normal\n150–199 Borderline\n200–499 High\n≥500 Very high",
        'Lipid Profile'],

    ['HDL Cholesterol', 'mg/dL',
        '<40 Low (risk factor)\n40–59 Normal\n≥60 High (protective)',
        '<50 Low (risk factor)\n50–59 Normal\n≥60 High (protective)',
        'Lipid Profile'],

    ['LDL Cholesterol', 'mg/dL',
        "<100 Optimal\n100–129 Near optimal\n130–159 Borderline high\n160–189 High\n≥190 Very high",
        "<100 Optimal\n100–129 Near optimal\n130–159 Borderline high\n160–189 High\n≥190 Very high",
        'Lipid Profile'],

    ['VLDL Cholesterol', 'mg/dL',
        '5 – 40',
        '5 – 40',
        'Lipid Profile'],

    ['Non-HDL Cholesterol', 'mg/dL',
        '<130 Optimal',
        '<130 Optimal',
        'Lipid Profile'],

    ['Total Cholesterol / HDL Ratio', 'ratio',
        '<5.0 Desirable\n>5.0 Increased risk',
        '<5.0 Desirable\n>5.0 Increased risk',
        'Lipid Profile'],

    ['LDL / HDL Ratio', 'ratio',
        '<3.5 Desirable',
        '<3.5 Desirable',
        'Lipid Profile'],

    // ── DIABETES & GLUCOSE ────────────────────────────────────────────────────
    ['Fasting Blood Glucose', 'mg/dL',
        "70 – 99 Normal\n100 – 125 Pre-diabetic\n≥126 Diabetic",
        "70 – 99 Normal\n100 – 125 Pre-diabetic\n≥126 Diabetic",
        'Diabetes & Glucose'],

    ['Post-Prandial Glucose (2hr PP)', 'mg/dL',
        "<140 Normal\n140 – 199 Pre-diabetic (IGT)\n≥200 Diabetic",
        "<140 Normal\n140 – 199 Pre-diabetic (IGT)\n≥200 Diabetic",
        'Diabetes & Glucose'],

    ['Random Blood Glucose (RBS)', 'mg/dL',
        '<200 Normal',
        '<200 Normal',
        'Diabetes & Glucose'],

    ['HbA1c (Glycated Hemoglobin)', '%',
        "4.0 – 5.6% Normal\n5.7 – 6.4% Pre-diabetic\n≥6.5% Diabetic",
        "4.0 – 5.6% Normal\n5.7 – 6.4% Pre-diabetic\n≥6.5% Diabetic",
        'Diabetes & Glucose'],

    ['C-Peptide (Fasting)', 'ng/mL',
        '0.8 – 3.85',
        '0.8 – 3.85',
        'Diabetes & Glucose'],

    ['HOMA-IR (Insulin Resistance Index)', 'index',
        "<2.5 Normal\n2.5 – 5.0 Insulin resistant\n>5.0 Severe resistance",
        "<2.5 Normal\n2.5 – 5.0 Insulin resistant\n>5.0 Severe resistance",
        'Diabetes & Glucose'],

    // ── COAGULATION ───────────────────────────────────────────────────────────
    ['PT (Prothrombin Time)', 'seconds',
        '11 – 13.5',
        '11 – 13.5',
        'Coagulation Profile'],

    ['INR (International Normalized Ratio)', 'ratio',
        '0.8 – 1.2 (Normal)\nTherapeutic anticoagulation: 2.0 – 3.0',
        '0.8 – 1.2 (Normal)\nTherapeutic anticoagulation: 2.0 – 3.0',
        'Coagulation Profile'],

    ['APTT (Activated Partial Thromboplastin Time)', 'seconds',
        '25 – 35',
        '25 – 35',
        'Coagulation Profile'],

    ['Fibrinogen', 'mg/dL',
        '200 – 400',
        '200 – 400',
        'Coagulation Profile'],

    ['D-Dimer', 'µg/mL FEU',
        '<0.50 Normal\n(Elevated in thrombosis, PE, DIC)',
        '<0.50 Normal\n(Elevated in thrombosis, PE, DIC)',
        'Coagulation Profile'],

    ['Thrombin Time (TT)', 'seconds',
        '12 – 16',
        '12 – 16',
        'Coagulation Profile'],

    ['Bleeding Time', 'minutes',
        '2 – 7',
        '2 – 7',
        'Coagulation Profile'],

    // ── IRON STUDIES ──────────────────────────────────────────────────────────
    ['Serum Iron', 'µg/dL',
        '65 – 175',
        '50 – 170',
        'Iron Studies'],

    ['TIBC (Total Iron Binding Capacity)', 'µg/dL',
        '250 – 370',
        '250 – 370',
        'Iron Studies'],

    ['Transferrin Saturation', '%',
        '20 – 50',
        '15 – 50',
        'Iron Studies'],

    ['Serum Ferritin', 'ng/mL',
        '24 – 336\n(Iron deficiency: <24)',
        '11 – 307\n(Iron deficiency: <11)',
        'Iron Studies'],

    ['Transferrin', 'mg/dL',
        '200 – 360',
        '200 – 360',
        'Iron Studies'],

    ['UIBC (Unsaturated Iron Binding Capacity)', 'µg/dL',
        '100 – 300',
        '100 – 300',
        'Iron Studies'],

    // ── VITAMINS & MINERALS ───────────────────────────────────────────────────
    ['Vitamin D (25-OH Total)', 'ng/mL',
        "≥30 Optimal (Sufficient)\n20 – 29 Insufficient\n10 – 19 Deficient\n<10 Severely deficient",
        "≥30 Optimal (Sufficient)\n20 – 29 Insufficient\n10 – 19 Deficient\n<10 Severely deficient",
        'Vitamins & Minerals'],

    ['Vitamin B12 (Cobalamin)', 'pg/mL',
        "200 – 900 Normal\n<200 Deficient\n200 – 300 Low normal",
        "200 – 900 Normal\n<200 Deficient\n200 – 300 Low normal",
        'Vitamins & Minerals'],

    ['Folate / Folic Acid (Serum)', 'ng/mL',
        ">5.4 Normal\n3.4 – 5.4 Borderline\n<3.4 Deficient",
        ">5.4 Normal\n3.4 – 5.4 Borderline\n<3.4 Deficient",
        'Vitamins & Minerals'],

    ['Zinc (Serum)', 'µg/dL',
        '70 – 120',
        '70 – 120',
        'Vitamins & Minerals'],

    ['Copper (Serum)', 'µg/dL',
        '70 – 140',
        '80 – 155',
        'Vitamins & Minerals'],

    ['Selenium (Serum)', 'µg/L',
        '63 – 160',
        '63 – 160',
        'Vitamins & Minerals'],

    ['Vitamin A (Retinol)', 'µg/dL',
        '30 – 65',
        '30 – 65',
        'Vitamins & Minerals'],

    ['Vitamin E (Alpha-Tocopherol)', 'µg/dL',
        '500 – 1800',
        '500 – 1800',
        'Vitamins & Minerals'],

    ['Vitamin C (Ascorbic Acid)', 'mg/dL',
        '0.4 – 1.5',
        '0.4 – 1.5',
        'Vitamins & Minerals'],

    // ── INFLAMMATION MARKERS ──────────────────────────────────────────────────
    ['CRP (C-Reactive Protein)', 'mg/L',
        '<5.0 Normal\n(Significant inflammation: >10)',
        '<5.0 Normal\n(Significant inflammation: >10)',
        'Inflammation Markers'],

    ['hsCRP (High-Sensitivity CRP)', 'mg/L',
        "<1.0 Low CV risk\n1.0 – 3.0 Average CV risk\n>3.0 High CV risk",
        "<1.0 Low CV risk\n1.0 – 3.0 Average CV risk\n>3.0 High CV risk",
        'Inflammation Markers'],

    ['Procalcitonin (PCT)', 'ng/mL',
        "<0.10 Normal (no infection)\n0.10 – 0.25 Low risk\n0.25 – 0.50 Possible sepsis\n>0.50 Likely sepsis",
        "<0.10 Normal (no infection)\n0.10 – 0.25 Low risk\n0.25 – 0.50 Possible sepsis\n>0.50 Likely sepsis",
        'Inflammation Markers'],

    ['Interleukin-6 (IL-6)', 'pg/mL',
        '<5.0',
        '<5.0',
        'Inflammation Markers'],

    // ── AUTOIMMUNE & ANTIPHOSPHOLIPID ─────────────────────────────────────────
    ['ANA (Antinuclear Antibody)', 'titer',
        'Negative (<1:40)',
        'Negative (<1:40)',
        'Autoimmune & Antiphospholipid'],

    ['Anti-dsDNA (Anti-Double Stranded DNA)', 'IU/mL',
        '<7 Negative\n7–10 Equivocal\n>10 Positive (SLE)',
        '<7 Negative\n7–10 Equivocal\n>10 Positive (SLE)',
        'Autoimmune & Antiphospholipid'],

    ['Anti-CCP (Anti-Cyclic Citrullinated Peptide)', 'U/mL',
        '<20 Negative\n20–39 Weak positive\n40–59 Moderate positive\n≥60 Strong positive',
        '<20 Negative\n20–39 Weak positive\n40–59 Moderate positive\n≥60 Strong positive',
        'Autoimmune & Antiphospholipid'],

    ['Rheumatoid Factor (RF)', 'IU/mL',
        '<15 Negative',
        '<15 Negative',
        'Autoimmune & Antiphospholipid'],

    ['Anti-Cardiolipin IgG (aCL IgG)', 'GPL-U/mL',
        "<10 Negative\n10–40 Low-medium positive\n>40 High positive",
        "<10 Negative\n10–40 Low-medium positive\n>40 High positive",
        'Autoimmune & Antiphospholipid'],

    ['Anti-Cardiolipin IgM (aCL IgM)', 'MPL-U/mL',
        '<10 Negative\n>10 Positive (APS risk)',
        '<10 Negative\n>10 Positive (APS risk)',
        'Autoimmune & Antiphospholipid'],

    ['Beta-2 Glycoprotein-1 IgG', 'SGU',
        '<20 Negative',
        '<20 Negative',
        'Autoimmune & Antiphospholipid'],

    ['Beta-2 Glycoprotein-1 IgM', 'SMU',
        '<20 Negative',
        '<20 Negative',
        'Autoimmune & Antiphospholipid'],

    ['Lupus Anticoagulant (LA)', 'qualitative',
        'Negative',
        'Negative',
        'Autoimmune & Antiphospholipid'],

    ['Anti-Ro / SSA Antibody', 'qualitative',
        'Negative',
        'Negative',
        'Autoimmune & Antiphospholipid'],

    ['Anti-La / SSB Antibody', 'qualitative',
        'Negative',
        'Negative',
        'Autoimmune & Antiphospholipid'],

    ['Anti-Phosphatidylserine IgG', 'U/mL',
        '<10 Negative',
        '<10 Negative',
        'Autoimmune & Antiphospholipid'],

    // ── INFECTIOUS SEROLOGY & TORCH ───────────────────────────────────────────
    ['HIV 1 & 2 (Antibody/Antigen)', 'qualitative',
        'Non-reactive (Negative)',
        'Non-reactive (Negative)',
        'Infectious Serology (TORCH)'],

    ['HBsAg (Hepatitis B Surface Antigen)', 'qualitative',
        'Non-reactive (Negative)',
        'Non-reactive (Negative)',
        'Infectious Serology (TORCH)'],

    ['Anti-HBs (Hepatitis B Surface Antibody)', 'mIU/mL',
        '>10 Immune / Protected\n<10 Not immune (vaccination recommended)',
        '>10 Immune / Protected\n<10 Not immune (vaccination recommended)',
        'Infectious Serology (TORCH)'],

    ['HBeAg (Hepatitis B e-Antigen)', 'qualitative',
        'Negative',
        'Negative',
        'Infectious Serology (TORCH)'],

    ['Anti-HCV (Hepatitis C Antibody)', 'qualitative',
        'Non-reactive (Negative)',
        'Non-reactive (Negative)',
        'Infectious Serology (TORCH)'],

    ['VDRL / RPR (Syphilis)', 'qualitative',
        'Non-reactive (Negative)',
        'Non-reactive (Negative)',
        'Infectious Serology (TORCH)'],

    ['Rubella IgG', 'IU/mL',
        'Not typically tested in males',
        ">10 Immune (Protected)\n4–10 Equivocal\n<4 Susceptible (vaccinate before pregnancy)",
        'Infectious Serology (TORCH)'],

    ['Rubella IgM', 'qualitative',
        'Negative',
        'Negative (Active infection if positive)',
        'Infectious Serology (TORCH)'],

    ['CMV IgG (Cytomegalovirus)', 'qualitative',
        'Negative (prior exposure common)',
        'Negative\n(Past exposure seropositive: >6 AU/mL)',
        'Infectious Serology (TORCH)'],

    ['CMV IgM', 'qualitative',
        'Negative',
        'Negative (Active infection if positive)',
        'Infectious Serology (TORCH)'],

    ['Toxoplasma IgG', 'IU/mL',
        'Negative (<3 IU/mL)',
        'Negative (<3 IU/mL)\n(Prior immunity if positive)',
        'Infectious Serology (TORCH)'],

    ['Toxoplasma IgM', 'qualitative',
        'Negative',
        'Negative (Active infection if positive)',
        'Infectious Serology (TORCH)'],

    ['HSV-1 IgG (Herpes Simplex Virus 1)', 'qualitative',
        'Negative',
        'Negative (Past exposure very common)',
        'Infectious Serology (TORCH)'],

    ['HSV-2 IgG (Herpes Simplex Virus 2)', 'qualitative',
        'Negative',
        'Negative',
        'Infectious Serology (TORCH)'],

    ['Chlamydia IgG', 'qualitative',
        'Negative',
        'Negative',
        'Infectious Serology (TORCH)'],

    ['Chlamydia IgM', 'qualitative',
        'Negative',
        'Negative',
        'Infectious Serology (TORCH)'],

    // ── TUMOR MARKERS ─────────────────────────────────────────────────────────
    ['CA-125 (Cancer Antigen 125)', 'U/mL',
        '<35',
        '<35\n(Elevated in endometriosis, ovarian Ca, fibroids)',
        'Tumor Markers'],

    ['CA 19-9', 'U/mL',
        '<37',
        '<37',
        'Tumor Markers'],

    ['CEA (Carcinoembryonic Antigen)', 'ng/mL',
        '<2.5 (non-smoker)\n<5.0 (smoker)',
        '<2.5 (non-smoker)\n<5.0 (smoker)',
        'Tumor Markers'],

    ['AFP (Alpha-Fetoprotein)', 'IU/mL',
        '<8.1',
        '<8.1 (non-pregnant)',
        'Tumor Markers'],

    ['PSA (Total Prostate Specific Antigen)', 'ng/mL',
        "<2.5 (age <50)\n<4.0 (age 50–65)\n<6.5 (age >65)",
        'Not applicable',
        'Tumor Markers'],

    ['PSA (Free)', '%',
        ">25% Free/Total ratio: Likely benign\n<10%: Higher risk of malignancy",
        'Not applicable',
        'Tumor Markers'],

    ['CA 15-3 (Breast Cancer Marker)', 'U/mL',
        '<25',
        '<25',
        'Tumor Markers'],

    ['CA 72-4', 'U/mL',
        '<6.9',
        '<6.9',
        'Tumor Markers'],

    // ── URINE ANALYSIS ────────────────────────────────────────────────────────
    ['Urine Complete Examination (CE/RE)', 'qualitative',
        "Colour: Pale yellow\npH: 4.5 – 8.0\nSpecific Gravity: 1.005 – 1.030\nProtein: Negative\nGlucose: Negative\nKetones: Negative\nBilirubin: Negative\nRBCs: 0–2/hpf\nWBCs: 0–5/hpf\nCasts: Occasional hyaline\nCrystals: Occasional",
        "Colour: Pale yellow\npH: 4.5 – 8.0\nSpecific Gravity: 1.005 – 1.030\nProtein: Negative\nGlucose: Negative\nKetones: Negative\nBilirubin: Negative\nRBCs: 0–2/hpf\nWBCs: 0–5/hpf",
        'Urine Analysis'],

    ['Urine Culture & Sensitivity', 'qualitative',
        "No growth (Sterile)\nSignificant growth: >100,000 CFU/mL",
        "No growth (Sterile)\nSignificant growth: >100,000 CFU/mL",
        'Urine Analysis'],

    ['Urine Microalbumin', 'mg/g creatinine',
        "<30 Normal\n30–300 Microalbuminuria\n>300 Macroalbuminuria",
        "<30 Normal\n30–300 Microalbuminuria\n>300 Macroalbuminuria",
        'Urine Analysis'],

    ['24-hr Urine Protein', 'mg/24hr',
        '<150 Normal',
        '<150 Normal',
        'Urine Analysis'],

    ['Urine Creatinine (24-hr)', 'mg/24hr',
        '800 – 1800',
        '600 – 1600',
        'Urine Analysis'],

];

// ─── Insert with ON DUPLICATE KEY UPDATE ─────────────────────────────────────
// Ensure unique index exists on test_name
$conn->query("ALTER TABLE lab_tests_directory ADD UNIQUE INDEX IF NOT EXISTS idx_test_name (test_name(191))");

$stmt = $conn->prepare("INSERT INTO lab_tests_directory (test_name, unit, reference_range_male, reference_range_female, category)
    VALUES (?, ?, ?, ?, ?)
    ON DUPLICATE KEY UPDATE
        unit = VALUES(unit),
        reference_range_male = VALUES(reference_range_male),
        reference_range_female = VALUES(reference_range_female),
        category = VALUES(category)");

$inserted = 0;
$updated  = 0;
$errors   = 0;

echo "<table><tr><th>#</th><th>Test Name</th><th>Category</th><th>Unit</th><th>Status</th></tr>";

foreach ($tests as $i => [$name, $unit, $ref_m, $ref_f, $cat]) {
    $stmt->bind_param("sssss", $name, $unit, $ref_m, $ref_f, $cat);
    $ok = $stmt->execute();
    if ($ok) {
        if ($conn->affected_rows === 1) { $inserted++; $status = "<span class='ok'>✔ Inserted</span>"; }
        elseif ($conn->affected_rows === 2) { $updated++; $status = "<span class='skip'>↻ Updated</span>"; }
        else { $status = "<span class='skip'>→ No change</span>"; }
    } else {
        $errors++;
        $status = "<span class='err'>✘ " . htmlspecialchars($stmt->error) . "</span>";
    }
    echo "<tr><td>" . ($i + 1) . "</td><td>" . htmlspecialchars($name) . "</td><td>" . htmlspecialchars($cat) . "</td><td>" . htmlspecialchars($unit) . "</td><td>$status</td></tr>";
}

echo "</table>";
echo "<br><h2 style='color:#38bdf8;'>Migration Complete</h2>";
echo "<p>✔ <b>$inserted</b> tests inserted &nbsp;|&nbsp; ↻ <b>$updated</b> tests updated &nbsp;|&nbsp; ✘ <b>$errors</b> errors</p>";
echo "<p><a href='lab_tests.php' style='color:#38bdf8;'>→ Go to Lab Tests Directory</a></p>";
echo "</body></html>";
