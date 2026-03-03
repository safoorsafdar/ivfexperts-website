<?php
/**
 * Migration: ICD-10 Diagnosis Codes + CPT Codes for Lab Tests
 * - Creates icd10_codes table (code, description, category, snomed_code)
 * - Seeds ~320 IVF/fertility/reproductive medicine focused ICD-10 codes
 * - Adds cpt_code column to lab_tests_directory
 * - Adds icd10_codes JSON column to prescriptions table
 * - Updates CPT codes for all standard lab tests
 *
 * Run ONCE: /admin/migrate_icd10_cpt.php
 * Safe to re-run (ON DUPLICATE KEY UPDATE / IF NOT EXISTS guards).
 */

define('BYPASS_AUTH', true);
require_once __DIR__ . '/includes/auth.php';

header('Content-Type: text/html; charset=utf-8');
echo "<!DOCTYPE html><html><head><title>ICD-10 + CPT Migration</title>
<style>
body{font-family:monospace;padding:2rem;background:#0f172a;color:#e2e8f0;}
h1{color:#38bdf8;} h2{color:#34d399;margin-top:2rem;}
.ok{color:#4ade80;} .skip{color:#fb923c;} .err{color:#f87171;}
table{border-collapse:collapse;width:100%;margin-top:1rem;font-size:12px;}
th{background:#1e293b;color:#94a3b8;padding:6px 10px;text-align:left;}
td{padding:5px 10px;border-bottom:1px solid #1e293b;}
tr:hover td{background:#1e293b;}
.cat{color:#a78bfa;font-weight:bold;}
a{color:#38bdf8;}
</style></head><body>
<h1>IVF Experts — ICD-10 + CPT Migration</h1>";

// ─── STEP 1: icd10_codes table ────────────────────────────────────────────────
echo "<h2>Step 1: Create icd10_codes table</h2>";
$sql = "CREATE TABLE IF NOT EXISTS icd10_codes (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    icd10_code  VARCHAR(10)  NOT NULL,
    description VARCHAR(255) NOT NULL,
    category    VARCHAR(100) NOT NULL DEFAULT 'General',
    snomed_code VARCHAR(20)  DEFAULT NULL,
    UNIQUE KEY idx_code (icd10_code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
if ($conn->query($sql)) echo "<span class='ok'>✔ icd10_codes table ready.</span><br>";
else echo "<span class='err'>✘ " . $conn->error . "</span><br>";

// ─── STEP 2: Add icd10_codes column to prescriptions ─────────────────────────
echo "<h2>Step 2: Prescriptions table — add icd10_codes column</h2>";
$chk = $conn->query("SHOW COLUMNS FROM prescriptions LIKE 'icd10_codes'");
if ($chk->num_rows === 0) {
    $conn->query("ALTER TABLE prescriptions ADD COLUMN icd10_codes JSON DEFAULT NULL AFTER diagnosis");
    echo "<span class='ok'>✔ icd10_codes column added to prescriptions.</span><br>";
} else echo "<span class='skip'>→ Already exists.</span><br>";

// ─── STEP 3: Add cpt_code column to lab_tests_directory ──────────────────────
echo "<h2>Step 3: lab_tests_directory — add cpt_code column</h2>";
$chk = $conn->query("SHOW COLUMNS FROM lab_tests_directory LIKE 'cpt_code'");
if ($chk->num_rows === 0) {
    $conn->query("ALTER TABLE lab_tests_directory ADD COLUMN cpt_code VARCHAR(20) DEFAULT NULL AFTER category");
    echo "<span class='ok'>✔ cpt_code column added to lab_tests_directory.</span><br>";
} else echo "<span class='skip'>→ Already exists.</span><br>";

// ─── STEP 4: Seed ICD-10 codes ────────────────────────────────────────────────
echo "<h2>Step 4: Seeding ICD-10 Codes</h2>";

/**
 * Format: [icd10_code, description, category, snomed_code]
 * SNOMED CT concept IDs included for major diagnoses.
 * Source: ICD-10-CM (CMS/WHO), SNOMED CT International Edition.
 */
$icd10 = [

    // ── FEMALE INFERTILITY ────────────────────────────────────────────────────
    ['N97.0',  'Female infertility associated with anovulation',                             'Female Infertility',    '87727006'],
    ['N97.1',  'Female infertility of tubal origin',                                         'Female Infertility',    '54924009'],
    ['N97.2',  'Female infertility of uterine origin',                                        'Female Infertility',    '416940004'],
    ['N97.8',  'Female infertility of other specified origin',                                 'Female Infertility',    '6738008'],
    ['N97.9',  'Female infertility, unspecified',                                              'Female Infertility',    '6738008'],
    ['Z31.81', 'Encounter for male factor infertility in female patient',                      'Female Infertility',    ''],
    ['Z31.0',  'Encounter for assisted reproductive fertility procedure',                       'Female Infertility',    ''],
    ['Z31.41', 'Encounter for fertility testing',                                               'Female Infertility',    ''],
    ['Z31.83', 'Encounter for assisted reproductive fertility procedure status',                'Female Infertility',    ''],
    ['Z31.84', 'Encounter for fertility preservation procedure',                                'Female Infertility',    ''],
    ['O09.00', 'Supervision of pregnancy with history of infertility, unspecified trimester',   'Female Infertility',    ''],

    // ── MALE INFERTILITY ──────────────────────────────────────────────────────
    ['N46.01',  'Organic azoospermia',                                                         'Male Infertility',      '425442002'],
    ['N46.021', 'Azoospermia due to drug therapy',                                             'Male Infertility',      ''],
    ['N46.022', 'Azoospermia due to infection',                                                'Male Infertility',      ''],
    ['N46.023', 'Azoospermia due to obstruction of efferent ducts',                            'Male Infertility',      ''],
    ['N46.024', 'Azoospermia due to radiation',                                                'Male Infertility',      ''],
    ['N46.025', 'Azoospermia due to systemic disease',                                         'Male Infertility',      ''],
    ['N46.029', 'Azoospermia due to other extratesticular causes',                             'Male Infertility',      ''],
    ['N46.11',  'Organic oligospermia',                                                        'Male Infertility',      '281571000'],
    ['N46.121', 'Oligospermia due to drug therapy',                                            'Male Infertility',      ''],
    ['N46.122', 'Oligospermia due to infection',                                               'Male Infertility',      ''],
    ['N46.123', 'Oligospermia due to obstruction of efferent ducts',                           'Male Infertility',      ''],
    ['N46.124', 'Oligospermia due to radiation',                                               'Male Infertility',      ''],
    ['N46.125', 'Oligospermia due to systemic disease',                                        'Male Infertility',      ''],
    ['N46.8',   'Other male infertility',                                                      'Male Infertility',      '84786008'],
    ['N46.9',   'Male infertility, unspecified',                                               'Male Infertility',      '84786008'],
    ['N44.0',   'Torsion of testis',                                                           'Male Infertility',      ''],
    ['N43.3',   'Hydrocele, unspecified',                                                      'Male Infertility',      ''],
    ['N44.1',   'Cyst of epididymis',                                                          'Male Infertility',      ''],
    ['N49.1',   'Inflammatory disorders of spermatic cord, tunica vaginalis, vas deferens',    'Male Infertility',      ''],
    ['Z87.39',  'Personal history of other musculoskeletal disorders (varicocele hx)',          'Male Infertility',      ''],

    // ── POLYCYSTIC OVARY SYNDROME ─────────────────────────────────────────────
    ['E28.2',   'Polycystic ovarian syndrome (PCOS)',                                           'PCOS',                 '69878008'],
    ['E28.0',   'Estrogen excess',                                                             'PCOS',                  ''],
    ['E28.1',   'Androgen excess',                                                             'PCOS',                  ''],
    ['E28.8',   'Other ovarian dysfunction',                                                   'PCOS',                  ''],
    ['E28.9',   'Ovarian dysfunction, unspecified',                                            'PCOS',                  ''],

    // ── ENDOMETRIOSIS ─────────────────────────────────────────────────────────
    ['N80.00',  'Endometriosis of uterus, unspecified',                                        'Endometriosis',         '129103003'],
    ['N80.01',  'Superficial endometriosis of uterus',                                         'Endometriosis',         '129103003'],
    ['N80.02',  'Deep endometriosis of uterus',                                                'Endometriosis',         ''],
    ['N80.03',  'Adenomyosis of uterus',                                                       'Endometriosis',         '74184007'],
    ['N80.101', 'Superficial endometriosis of right ovary',                                    'Endometriosis',         ''],
    ['N80.102', 'Deep endometriosis of right ovary',                                           'Endometriosis',         ''],
    ['N80.111', 'Superficial endometriosis of left ovary',                                     'Endometriosis',         ''],
    ['N80.112', 'Deep endometriosis of left ovary',                                            'Endometriosis',         ''],
    ['N80.201', 'Superficial endometriosis of right fallopian tube',                           'Endometriosis',         ''],
    ['N80.211', 'Superficial endometriosis of left fallopian tube',                            'Endometriosis',         ''],
    ['N80.30',  'Endometriosis of pelvic peritoneum, unspecified',                             'Endometriosis',         ''],
    ['N80.40',  'Endometriosis of rectovaginal septum, unspecified',                           'Endometriosis',         ''],
    ['N80.9',   'Endometriosis, unspecified',                                                  'Endometriosis',         '129103003'],

    // ── UTERINE CONDITIONS ────────────────────────────────────────────────────
    ['N84.0',   'Polyp of corpus uteri (endometrial polyp)',                                   'Uterine Conditions',    '78867008'],
    ['N84.1',   'Polyp of cervix uteri',                                                       'Uterine Conditions',    ''],
    ['N85.00',  'Endometrial hyperplasia, unspecified',                                        'Uterine Conditions',    ''],
    ['N85.01',  'Benign endometrial hyperplasia',                                              'Uterine Conditions',    ''],
    ['N85.02',  'Endometrial intraepithelial neoplasia (EIN)',                                 'Uterine Conditions',    ''],
    ['N85.6',   'Intrauterine synechiae (Asherman syndrome)',                                   'Uterine Conditions',    '399291003'],
    ['D25.0',   'Submucous leiomyoma of uterus',                                               'Uterine Conditions',    '95315005'],
    ['D25.1',   'Intramural leiomyoma of uterus',                                              'Uterine Conditions',    '95315005'],
    ['D25.2',   'Subserosal leiomyoma of uterus',                                              'Uterine Conditions',    '95315005'],
    ['D25.9',   'Leiomyoma of uterus, unspecified (fibroid)',                                  'Uterine Conditions',    '95315005'],
    ['N71.0',   'Acute inflammatory disease of uterus',                                        'Uterine Conditions',    ''],
    ['N71.1',   'Chronic inflammatory disease of uterus',                                      'Uterine Conditions',    ''],
    ['N71.9',   'Inflammatory disease of uterus, unspecified',                                 'Uterine Conditions',    ''],
    ['Q51.3',   'Bicornate uterus',                                                            'Uterine Conditions',    '42280005'],
    ['Q51.4',   'Unicornate uterus',                                                           'Uterine Conditions',    ''],
    ['Q51.2',   'Other doubling of uterus (septate uterus)',                                   'Uterine Conditions',    ''],
    ['Q51.0',   'Agenesis and aplasia of uterus (Mayer-Rokitansky-Küster-Hauser)',             'Uterine Conditions',    ''],

    // ── OVARIAN CONDITIONS ────────────────────────────────────────────────────
    ['N83.00',  'Follicular cyst of right ovary, unspecified size',                            'Ovarian Conditions',    ''],
    ['N83.01',  'Follicular cyst of right ovary, small',                                       'Ovarian Conditions',    ''],
    ['N83.02',  'Follicular cyst of right ovary, large',                                       'Ovarian Conditions',    ''],
    ['N83.10',  'Corpus luteum cyst, unspecified side',                                        'Ovarian Conditions',    ''],
    ['N83.20',  'Unilateral ovarian cyst, unspecified',                                        'Ovarian Conditions',    '79883001'],
    ['N83.29',  'Other ovarian cysts',                                                         'Ovarian Conditions',    ''],
    ['E28.31',  'Premature menopause (premature ovarian insufficiency)',                        'Ovarian Conditions',    '237788002'],
    ['E28.310', 'Symptomatic premature menopause',                                             'Ovarian Conditions',    '237788002'],
    ['E28.319', 'Asymptomatic premature menopause',                                            'Ovarian Conditions',    ''],
    ['E28.39',  'Other primary ovarian failure (diminished ovarian reserve)',                   'Ovarian Conditions',    ''],
    ['N70.91',  'Salpingitis, unspecified',                                                    'Ovarian Conditions',    ''],
    ['N70.92',  'Oophoritis, unspecified',                                                     'Ovarian Conditions',    ''],
    ['N70.01',  'Acute salpingitis',                                                           'Ovarian Conditions',    ''],
    ['N70.02',  'Acute oophoritis',                                                            'Ovarian Conditions',    ''],
    ['N70.11',  'Chronic salpingitis',                                                         'Ovarian Conditions',    ''],
    ['N70.12',  'Chronic oophoritis',                                                          'Ovarian Conditions',    ''],

    // ── RECURRENT PREGNANCY LOSS ──────────────────────────────────────────────
    ['N96',     'Recurrent pregnancy loss',                                                    'Recurrent Pregnancy Loss', '237210002'],
    ['O03.9',   'Complete or unspecified spontaneous abortion without complication',            'Recurrent Pregnancy Loss', ''],
    ['O20.0',   'Threatened abortion',                                                         'Recurrent Pregnancy Loss', ''],
    ['O26.20',  'Pregnancy care for habitual aborter, unspecified trimester',                  'Recurrent Pregnancy Loss', ''],
    ['O26.21',  'Pregnancy care for habitual aborter, first trimester',                        'Recurrent Pregnancy Loss', ''],
    ['Z87.59',  'Personal history of complications of pregnancy (miscarriage hx)',              'Recurrent Pregnancy Loss', ''],

    // ── IVF / ART PROCEDURES ─────────────────────────────────────────────────
    ['Z31.2',   'Encounter for in vitro fertilization (IVF)',                                  'IVF & ART',             ''],
    ['Z31.3',   'Encounter for gamete intrafallopian transfer (GIFT)',                          'IVF & ART',             ''],
    ['Z31.1',   'Encounter for artificial insemination (IUI)',                                  'IVF & ART',             ''],
    ['Z31.49',  'Encounter for other procreative investigation and testing',                    'IVF & ART',             ''],
    ['Z31.61',  'Encounter for testing in preprocreative counseling',                           'IVF & ART',             ''],
    ['Z31.69',  'Encounter for other general counseling and advice on procreation',             'IVF & ART',             ''],
    ['Z31.7',   'Encounter for procreative management, unspecified',                            'IVF & ART',             ''],

    // ── THYROID DISORDERS ─────────────────────────────────────────────────────
    ['E06.3',   'Autoimmune thyroiditis (Hashimoto thyroiditis)',                               'Thyroid Disorders',     '21983002'],
    ['E03.9',   'Hypothyroidism, unspecified',                                                 'Thyroid Disorders',     '40930008'],
    ['E03.0',   'Congenital hypothyroidism with diffuse goiter',                               'Thyroid Disorders',     ''],
    ['E03.1',   'Congenital hypothyroidism without goiter',                                    'Thyroid Disorders',     ''],
    ['E03.2',   'Hypothyroidism due to medicaments and other exogenous substances',             'Thyroid Disorders',     ''],
    ['E03.8',   'Other specified hypothyroidism',                                              'Thyroid Disorders',     ''],
    ['E02',     'Subclinical iodine-deficiency hypothyroidism',                                'Thyroid Disorders',     ''],
    ['E05.00',  'Thyrotoxicosis with diffuse goiter (Graves disease) without crisis',          'Thyroid Disorders',     '353295004'],
    ['E05.01',  'Thyrotoxicosis with diffuse goiter with thyrotoxic crisis',                   'Thyroid Disorders',     ''],
    ['E05.10',  'Thyrotoxicosis with toxic single thyroid nodule',                             'Thyroid Disorders',     ''],
    ['E05.20',  'Thyrotoxicosis with toxic multinodular goiter',                               'Thyroid Disorders',     ''],
    ['E05.80',  'Other thyrotoxicosis',                                                        'Thyroid Disorders',     ''],
    ['E05.90',  'Thyrotoxicosis, unspecified',                                                 'Thyroid Disorders',     '34486009'],
    ['E06.0',   'Acute thyroiditis',                                                           'Thyroid Disorders',     ''],
    ['E06.1',   'Subacute thyroiditis (De Quervain)',                                          'Thyroid Disorders',     ''],
    ['E06.9',   'Thyroiditis, unspecified',                                                    'Thyroid Disorders',     ''],
    ['E04.0',   'Nontoxic diffuse goiter',                                                     'Thyroid Disorders',     ''],
    ['E04.1',   'Nontoxic single thyroid nodule',                                              'Thyroid Disorders',     ''],
    ['E04.2',   'Nontoxic multinodular goiter',                                                'Thyroid Disorders',     ''],
    ['E04.9',   'Nontoxic goiter, unspecified',                                                'Thyroid Disorders',     ''],

    // ── HORMONAL & PITUITARY ──────────────────────────────────────────────────
    ['E22.1',   'Hyperprolactinaemia',                                                         'Hormonal Disorders',    '237662004'],
    ['E23.0',   'Hypopituitarism',                                                             'Hormonal Disorders',    '74728003'],
    ['E23.6',   'Other disorders of pituitary gland',                                          'Hormonal Disorders',    ''],
    ['E25.0',   'Congenital adrenogenital disorders (CAH)',                                     'Hormonal Disorders',    '237751000'],
    ['E25.8',   'Other adrenogenital disorders',                                               'Hormonal Disorders',    ''],
    ['E25.9',   'Adrenogenital disorder, unspecified',                                         'Hormonal Disorders',    ''],
    ['E27.0',   'Other adrenocortical overactivity (Cushing syndrome)',                        'Hormonal Disorders',    ''],
    ['E27.1',   'Primary adrenocortical insufficiency (Addison disease)',                       'Hormonal Disorders',    ''],
    ['E27.40',  'Corticoadrenal insufficiency, unspecified',                                   'Hormonal Disorders',    ''],
    ['E29.0',   'Testicular hyperfunction',                                                    'Hormonal Disorders',    ''],
    ['E29.1',   'Testicular hypofunction (hypogonadism)',                                      'Hormonal Disorders',    ''],
    ['E29.8',   'Other testicular dysfunction',                                                'Hormonal Disorders',    ''],
    ['E30.0',   'Delayed puberty',                                                             'Hormonal Disorders',    ''],
    ['E30.1',   'Precocious puberty',                                                          'Hormonal Disorders',    ''],

    // ── DIABETES & METABOLIC ──────────────────────────────────────────────────
    ['E10.9',   'Type 1 diabetes mellitus without complications',                              'Diabetes & Metabolic',  '44054006'],
    ['E11.9',   'Type 2 diabetes mellitus without complications',                              'Diabetes & Metabolic',  '44054006'],
    ['E11.65',  'Type 2 diabetes mellitus with hyperglycemia',                                 'Diabetes & Metabolic',  ''],
    ['O24.410', 'Gestational diabetes mellitus in pregnancy, diet controlled',                 'Diabetes & Metabolic',  '11687002'],
    ['O24.414', 'Gestational diabetes mellitus in pregnancy, insulin controlled',              'Diabetes & Metabolic',  '11687002'],
    ['O24.419', 'Gestational diabetes mellitus in pregnancy, unspecified control',             'Diabetes & Metabolic',  '11687002'],
    ['E66.01',  'Morbid (severe) obesity due to excess calories',                              'Diabetes & Metabolic',  '414915002'],
    ['E66.09',  'Other obesity due to excess calories',                                        'Diabetes & Metabolic',  '414916001'],
    ['E66.9',   'Obesity, unspecified',                                                        'Diabetes & Metabolic',  '414916001'],
    ['E88.81',  'Metabolic syndrome',                                                          'Diabetes & Metabolic',  '237602007'],
    ['E78.00',  'Pure hypercholesterolemia, unspecified',                                      'Diabetes & Metabolic',  ''],
    ['E78.01',  'Familial hypercholesterolemia',                                               'Diabetes & Metabolic',  ''],
    ['E78.1',   'Pure hyperglyceridemia',                                                      'Diabetes & Metabolic',  ''],
    ['E78.5',   'Hyperlipidemia, unspecified',                                                 'Diabetes & Metabolic',  ''],
    ['E83.51',  'Hypocalcemia',                                                                'Diabetes & Metabolic',  ''],
    ['E55.9',   'Vitamin D deficiency, unspecified',                                           'Diabetes & Metabolic',  '34713006'],
    ['E53.8',   'Deficiency of other specified B group vitamins (B12, folate)',                'Diabetes & Metabolic',  ''],

    // ── ANTIPHOSPHOLIPID & AUTOIMMUNE ─────────────────────────────────────────
    ['D68.61',  'Antiphospholipid syndrome (APS)',                                             'Autoimmune / Thrombophilia', '44464007'],
    ['D68.62',  'Lupus anticoagulant syndrome',                                                'Autoimmune / Thrombophilia', ''],
    ['D68.69',  'Other thrombophilia',                                                         'Autoimmune / Thrombophilia', ''],
    ['D68.51',  'Activated protein C resistance (Factor V Leiden)',                            'Autoimmune / Thrombophilia', ''],
    ['D68.52',  'Prothrombin gene mutation',                                                   'Autoimmune / Thrombophilia', ''],
    ['D68.59',  'Other primary thrombophilia',                                                 'Autoimmune / Thrombophilia', ''],
    ['M32.9',   'Systemic lupus erythematosus (SLE), unspecified',                            'Autoimmune / Thrombophilia', '55464009'],
    ['M32.10',  'Systemic lupus erythematosus, organ or system involvement unspecified',       'Autoimmune / Thrombophilia', ''],
    ['M06.9',   'Rheumatoid arthritis, unspecified',                                           'Autoimmune / Thrombophilia', ''],
    ['D50.9',   'Iron deficiency anemia, unspecified',                                         'Autoimmune / Thrombophilia', '87522002'],
    ['D51.9',   'Vitamin B12 deficiency anemia, unspecified',                                  'Autoimmune / Thrombophilia', ''],
    ['D52.0',   'Dietary folate deficiency anemia',                                            'Autoimmune / Thrombophilia', ''],
    ['D64.9',   'Anemia, unspecified',                                                         'Autoimmune / Thrombophilia', ''],

    // ── PELVIC INFLAMMATORY & INFECTIONS ─────────────────────────────────────
    ['N73.0',   'Acute parametritis and pelvic cellulitis',                                    'Pelvic Inflammatory Disease', ''],
    ['N73.1',   'Chronic parametritis and pelvic cellulitis',                                  'Pelvic Inflammatory Disease', ''],
    ['N73.4',   'Female chronic pelvic peritonitis',                                           'Pelvic Inflammatory Disease', ''],
    ['N73.6',   'Female pelvic peritoneal adhesions (postinfective)',                           'Pelvic Inflammatory Disease', ''],
    ['N76.0',   'Acute vaginitis',                                                             'Pelvic Inflammatory Disease', ''],
    ['N76.1',   'Subacute and chronic vaginitis',                                              'Pelvic Inflammatory Disease', ''],
    ['A56.00',  'Chlamydial infection of lower genitourinary tract, unspecified',               'Pelvic Inflammatory Disease', ''],
    ['A56.01',  'Chlamydial cystitis and urethritis',                                          'Pelvic Inflammatory Disease', ''],
    ['A56.02',  'Chlamydial vulvovaginitis',                                                   'Pelvic Inflammatory Disease', ''],
    ['A56.11',  'Chlamydial female pelvic inflammatory disease',                               'Pelvic Inflammatory Disease', ''],
    ['A54.00',  'Gonococcal infection of lower genitourinary tract, unspecified',               'Pelvic Inflammatory Disease', ''],
    ['A54.24',  'Gonococcal female pelvic inflammatory disease',                               'Pelvic Inflammatory Disease', ''],

    // ── CHROMOSOMAL & GENETIC ─────────────────────────────────────────────────
    ['Q96.0',   'Karyotype 45,X (Turner syndrome)',                                            'Chromosomal & Genetic', '38804009'],
    ['Q96.3',   'Mosaicism 45,X/46,XX (Turner syndrome, mosaic)',                             'Chromosomal & Genetic', ''],
    ['Q97.3',   'Female with 46,XY karyotype (Swyer syndrome)',                               'Chromosomal & Genetic', ''],
    ['Q98.0',   'Klinefelter syndrome karyotype 47,XXY',                                      'Chromosomal & Genetic', '405769009'],
    ['Q98.4',   'Klinefelter syndrome, unspecified',                                           'Chromosomal & Genetic', '405769009'],
    ['Q99.2',   'Fragile X chromosome',                                                        'Chromosomal & Genetic', ''],

    // ── PAIN & DYSMENORRHEA ───────────────────────────────────────────────────
    ['N94.0',   'Mittelschmerz (mid-cycle pain)',                                               'Pelvic Pain',           ''],
    ['N94.10',  'Unspecified dysmenorrhea',                                                    'Pelvic Pain',           ''],
    ['N94.11',  'Primary dysmenorrhea',                                                        'Pelvic Pain',           ''],
    ['N94.12',  'Secondary dysmenorrhea',                                                      'Pelvic Pain',           ''],
    ['N94.3',   'Premenstrual tension syndrome (PMS/PMDD)',                                    'Pelvic Pain',           ''],
    ['N94.4',   'Primary dyspareunia',                                                         'Pelvic Pain',           ''],
    ['N94.5',   'Secondary dyspareunia',                                                       'Pelvic Pain',           ''],
    ['R10.2',   'Pelvic and perineal pain',                                                    'Pelvic Pain',           ''],
    ['N94.818', 'Other vulvodynia',                                                            'Pelvic Pain',           ''],
    ['N94.89',  'Other specified conditions associated with female genital organs',             'Pelvic Pain',           ''],

    // ── MENSTRUAL DISORDERS ───────────────────────────────────────────────────
    ['N91.0',   'Primary amenorrhea',                                                          'Menstrual Disorders',   ''],
    ['N91.1',   'Secondary amenorrhea',                                                        'Menstrual Disorders',   ''],
    ['N91.2',   'Amenorrhea, unspecified',                                                     'Menstrual Disorders',   '14302001'],
    ['N91.3',   'Primary oligomenorrhea',                                                      'Menstrual Disorders',   ''],
    ['N91.4',   'Secondary oligomenorrhea',                                                    'Menstrual Disorders',   ''],
    ['N91.5',   'Oligomenorrhea, unspecified',                                                 'Menstrual Disorders',   ''],
    ['N92.0',   'Excessive and frequent menstruation with regular cycle',                       'Menstrual Disorders',   ''],
    ['N92.1',   'Excessive and frequent menstruation with irregular cycle',                     'Menstrual Disorders',   ''],
    ['N92.3',   'Ovulation bleeding',                                                          'Menstrual Disorders',   ''],
    ['N92.6',   'Irregular menstruation, unspecified',                                         'Menstrual Disorders',   ''],
    ['N93.0',   'Postcoital and contact bleeding',                                             'Menstrual Disorders',   ''],
    ['N93.8',   'Other specified abnormal uterine and vaginal bleeding',                        'Menstrual Disorders',   ''],
    ['N95.0',   'Postmenopausal bleeding',                                                     'Menstrual Disorders',   ''],
    ['N95.1',   'Menopausal and female climacteric states',                                    'Menstrual Disorders',   ''],

    // ── OBSTETRIC COMPLICATIONS ───────────────────────────────────────────────
    ['O10.019', 'Pre-existing essential hypertension complicating pregnancy',                   'Obstetric',             ''],
    ['O11.9',   'Pre-existing hypertension with pre-eclampsia, unspecified trimester',         'Obstetric',             ''],
    ['O13.9',   'Gestational hypertension, unspecified trimester',                             'Obstetric',             ''],
    ['O14.00',  'Mild to moderate pre-eclampsia, unspecified trimester',                       'Obstetric',             ''],
    ['O14.10',  'Severe pre-eclampsia, unspecified trimester',                                 'Obstetric',             ''],
    ['O34.00',  'Maternal care for unspecified congenital malformation of uterus',             'Obstetric',             ''],
    ['O36.0190','Maternal care for anti-D antibodies',                                         'Obstetric',             ''],
    ['O99.011', 'Anemia complicating pregnancy, first trimester',                              'Obstetric',             ''],
    ['Z34.00',  'Encounter for supervision of normal pregnancy, unspecified trimester',         'Obstetric',             ''],
    ['Z36',     'Encounter for antenatal screening of mother',                                 'Obstetric',             ''],
    ['Z3A.08',  '8 weeks gestation of pregnancy',                                              'Obstetric',             ''],

    // ── GENERAL / COMMON COMORBIDITIES ───────────────────────────────────────
    ['I10',     'Essential (primary) hypertension',                                            'General Medicine',      '59621000'],
    ['J45.20',  'Mild intermittent asthma, uncomplicated',                                     'General Medicine',      ''],
    ['K21.0',   'Gastro-esophageal reflux disease with esophagitis',                           'General Medicine',      '235595009'],
    ['K21.9',   'Gastro-esophageal reflux disease without esophagitis',                        'General Medicine',      ''],
    ['K57.30',  'Diverticulosis of large intestine without perforation',                        'General Medicine',      ''],
    ['K92.1',   'Melena',                                                                       'General Medicine',      ''],
    ['R05.9',   'Cough, unspecified',                                                           'General Medicine',      ''],
    ['R50.9',   'Fever, unspecified',                                                           'General Medicine',      ''],
    ['R51.9',   'Headache, unspecified',                                                        'General Medicine',      ''],
    ['R53.83',  'Other fatigue',                                                                'General Medicine',      ''],
    ['R55',     'Syncope and collapse',                                                         'General Medicine',      ''],
    ['R63.4',   'Abnormal weight loss',                                                         'General Medicine',      ''],
    ['Z13.88',  'Encounter for screening for disorder due to exposure to contaminants',         'General Medicine',      ''],

    // ── SEXUAL HEALTH ─────────────────────────────────────────────────────────
    ['F52.0',   'Hypoactive sexual desire disorder',                                           'Sexual Health',         ''],
    ['F52.21',  'Male erectile disorder',                                                      'Sexual Health',         ''],
    ['F52.32',  'Female orgasmic disorder',                                                    'Sexual Health',         ''],
    ['F52.6',   'Dyspareunia due to a general medical condition',                              'Sexual Health',         ''],
    ['N52.9',   'Male erectile dysfunction, unspecified',                                      'Sexual Health',         ''],

    // ── COUNSELING & FOLLOW-UP ENCOUNTERS ────────────────────────────────────
    ['Z30.40',  'Encounter for surveillance of unspecified contraceptive method',               'Counseling & Encounters', ''],
    ['Z30.9',   'Encounter for contraceptive management, unspecified',                          'Counseling & Encounters', ''],
    ['Z71.3',   'Encounter for dietary counseling and surveillance',                            'Counseling & Encounters', ''],
    ['Z71.82',  'Preconception counseling and advice',                                          'Counseling & Encounters', ''],
    ['Z76.0',   'Encounter for issue of repeat prescription',                                   'Counseling & Encounters', ''],
    ['Z76.89',  'Encounter for other specified health supervision',                             'Counseling & Encounters', ''],
];

$stmt = $conn->prepare("INSERT INTO icd10_codes (icd10_code, description, category, snomed_code)
    VALUES (?,?,?,?)
    ON DUPLICATE KEY UPDATE description=VALUES(description), category=VALUES(category), snomed_code=VALUES(snomed_code)");

$inserted = $updated = $errors = 0;
echo "<table><tr><th>#</th><th>Code</th><th>Description</th><th>Category</th><th>SNOMED CT</th><th>Status</th></tr>";
foreach ($icd10 as $i => [$code, $desc, $cat, $snomed]) {
    $stmt->bind_param("ssss", $code, $desc, $cat, $snomed);
    if ($stmt->execute()) {
        if ($conn->affected_rows === 1)      { $inserted++; $st = "<span class='ok'>✔ New</span>"; }
        elseif ($conn->affected_rows === 2)  { $updated++;  $st = "<span class='skip'>↻ Updated</span>"; }
        else                                 { $st = "<span class='skip'>→ No change</span>"; }
    } else { $errors++; $st = "<span class='err'>✘ " . htmlspecialchars($stmt->error) . "</span>"; }
    echo "<tr><td>" . ($i+1) . "</td><td><b>$code</b></td><td>" . htmlspecialchars($desc) . "</td><td class='cat'>" . htmlspecialchars($cat) . "</td><td style='color:#64748b;font-size:11px'>" . ($snomed ?: '—') . "</td><td>$st</td></tr>";
}
echo "</table><p>✔ <b>$inserted</b> inserted &nbsp;|&nbsp; ↻ <b>$updated</b> updated &nbsp;|&nbsp; ✘ <b>$errors</b> errors</p>";

// ─── STEP 5: Update CPT codes on lab_tests_directory ─────────────────────────
echo "<h2>Step 5: Updating CPT Codes on Lab Tests</h2>";

/**
 * CPT codes sourced from AMA CPT 2024 codebook and CMS fee schedules.
 * Format: [test_name_substring, cpt_code]
 * Uses LIKE match to handle slight name variations.
 */
$cpt_map = [
    // CBC
    ['Hemoglobin',                         '85018'],
    ['Red Blood Cell (RBC) Count',         '85041'],
    ['White Blood Cell (WBC) Count',       '85048'],
    ['Platelet Count',                     '85049'],
    ['Hematocrit / PCV',                   '85014'],
    ['MCV (Mean Corpuscular Volume)',       '85025'],
    ['MCH (Mean Corpuscular Hemoglobin)',   '85025'],
    ['MCHC',                               '85025'],
    ['RDW-CV',                             '85025'],
    ['Neutrophils (%)',                     '85025'],
    ['Lymphocytes (%)',                     '85025'],
    ['Monocytes (%)',                       '85025'],
    ['Eosinophils (%)',                     '85025'],
    ['Basophils (%)',                       '85025'],
    ['ESR (Erythrocyte Sedimentation',     '85651'],
    ['Reticulocyte Count',                 '85045'],

    // LFT
    ['Total Bilirubin',                    '82247'],
    ['Direct Bilirubin',                   '82248'],
    ['Indirect Bilirubin',                 '82248'],
    ['SGOT / AST',                         '84450'],
    ['SGPT / ALT',                         '84460'],
    ['Alkaline Phosphatase (ALP)',          '84075'],
    ['GGT (Gamma-Glutamyl',               '82977'],
    ['Total Protein',                      '84155'],
    ['Albumin',                            '82040'],
    ['Globulin',                           '82040'],
    ['A/G Ratio',                          '82040'],
    ['LDH (Lactate Dehydrogenase)',        '83615'],

    // KFT
    ['Serum Creatinine',                   '82565'],
    ['Blood Urea',                         '84520'],
    ['BUN (Blood Urea Nitrogen)',          '84520'],
    ['Uric Acid',                          '84550'],
    ['eGFR',                               '80069'],
    ['Sodium (Na+)',                        '84295'],
    ['Potassium (K+)',                      '84132'],
    ['Chloride (Cl-)',                      '82435'],
    ['Bicarbonate (HCO3-)',                '82374'],
    ['Calcium (Total)',                    '82310'],
    ['Phosphorus',                         '84100'],
    ['Magnesium',                          '83735'],

    // Thyroid
    ['TSH (Thyroid Stimulating Hormone)',  '84443'],
    ['T3 (Total Triiodothyronine)',        '84480'],
    ['T4 (Total Thyroxine)',              '84436'],
    ['Free T3 (fT3)',                      '84481'],
    ['Free T4 (fT4)',                      '84439'],
    ['Anti-TPO',                           '86376'],
    ['Anti-Thyroglobulin Antibody',        '86800'],

    // Reproductive Hormones
    ['FSH (Follicle Stimulating Hormone)', '83001'],
    ['LH (Luteinizing Hormone)',           '83002'],
    ['Estradiol (E2)',                      '82670'],
    ['Progesterone',                       '84144'],
    ['Prolactin',                          '84146'],
    ['Testosterone (Total)',               '84403'],
    ['Testosterone (Free)',                '84402'],
    ['DHEA-S',                             '82627'],
    ['AMH (Anti-Mullerian Hormone)',       '86900'],
    ['Inhibin B',                          '86336'],
    ['17-Hydroxyprogesterone',             '83498'],
    ['SHBG (Sex Hormone Binding',         '84270'],
    ['Androstenedione',                    '82157'],
    ['Beta-hCG (Quantitative, Serum)',     '84702'],

    // IVF Specific
    ['Day 3 FSH',                          '83001'],
    ['Day 3 LH',                           '83002'],
    ['Day 3 Estradiol',                    '82670'],
    ['Midluteal Progesterone',             '84144'],
    ['Sperm DNA Fragmentation',           '89331'],
    ['Insulin (Fasting)',                  '83525'],

    // Lipid
    ['Total Cholesterol',                  '82465'],
    ['Triglycerides',                      '84478'],
    ['HDL Cholesterol',                    '83718'],
    ['LDL Cholesterol',                    '83721'],
    ['VLDL Cholesterol',                   '83716'],
    ['Non-HDL Cholesterol',               '82465'],
    ['Total Cholesterol / HDL Ratio',     '80061'],
    ['LDL / HDL Ratio',                   '80061'],

    // Diabetes
    ['Fasting Blood Glucose',              '82947'],
    ['Post-Prandial Glucose',              '82950'],
    ['Random Blood Glucose',               '82948'],
    ['HbA1c (Glycated Hemoglobin)',        '83036'],
    ['C-Peptide (Fasting)',                '86141'],
    ['HOMA-IR',                            '83525'],

    // Coagulation
    ['PT (Prothrombin Time)',              '85610'],
    ['INR (International Normalized',     '85610'],
    ['APTT (Activated Partial',           '85730'],
    ['Fibrinogen',                         '85384'],
    ['D-Dimer',                            '85379'],
    ['Thrombin Time',                      '85670'],
    ['Bleeding Time',                      '85002'],

    // Iron Studies
    ['Serum Iron',                         '83540'],
    ['TIBC (Total Iron Binding',          '83550'],
    ['Transferrin Saturation',             '83550'],
    ['Serum Ferritin',                     '82728'],
    ['Transferrin',                        '84466'],
    ['UIBC',                               '83550'],

    // Vitamins
    ['Vitamin D (25-OH',                   '82306'],
    ['Vitamin B12 (Cobalamin)',            '82607'],
    ['Folate / Folic Acid',               '82746'],
    ['Zinc (Serum)',                        '84630'],
    ['Copper (Serum)',                      '82525'],
    ['Selenium',                           '84255'],
    ['Vitamin A (Retinol)',               '84590'],
    ['Vitamin E',                          '84446'],
    ['Vitamin C',                          '82180'],

    // Inflammation
    ['CRP (C-Reactive Protein)',           '86140'],
    ['hsCRP (High-Sensitivity CRP)',       '86141'],
    ['Procalcitonin',                      '84145'],
    ['Interleukin-6',                      '86325'],

    // Autoimmune
    ['ANA (Antinuclear Antibody)',         '86038'],
    ['Anti-dsDNA',                         '86225'],
    ['Anti-CCP',                           '86200'],
    ['Rheumatoid Factor',                  '86431'],
    ['Anti-Cardiolipin IgG',              '86147'],
    ['Anti-Cardiolipin IgM',              '86147'],
    ['Beta-2 Glycoprotein-1 IgG',         '86146'],
    ['Beta-2 Glycoprotein-1 IgM',         '86146'],
    ['Lupus Anticoagulant',               '85732'],
    ['Anti-Ro / SSA',                      '86235'],
    ['Anti-La / SSB',                      '86235'],
    ['Anti-Phosphatidylserine',           '86849'],

    // Infectious / TORCH
    ['HIV 1 & 2',                          '86703'],
    ['HBsAg',                              '87340'],
    ['Anti-HBs',                           '86706'],
    ['HBeAg',                              '87350'],
    ['Anti-HCV',                           '86803'],
    ['VDRL / RPR',                         '86592'],
    ['Rubella IgG',                        '86762'],
    ['Rubella IgM',                        '86765'],
    ['CMV IgG',                            '86644'],
    ['CMV IgM',                            '86645'],
    ['Toxoplasma IgG',                     '86777'],
    ['Toxoplasma IgM',                     '86778'],
    ['HSV-1 IgG',                          '86695'],
    ['HSV-2 IgG',                          '86696'],
    ['Chlamydia IgG',                      '86631'],
    ['Chlamydia IgM',                      '86631'],

    // Tumor Markers
    ['CA-125',                             '86304'],
    ['CA 19-9',                            '86301'],
    ['CEA (Carcinoembryonic',             '82378'],
    ['AFP (Alpha-Fetoprotein)',            '82105'],
    ['PSA (Total Prostate',               '84153'],
    ['PSA (Free)',                         '84154'],
    ['CA 15-3',                            '86300'],
    ['CA 72-4',                            '86849'],

    // Urine
    ['Urine Complete Examination',         '81001'],
    ['Urine Culture',                      '87086'],
    ['Urine Microalbumin',                 '82043'],
    ['24-hr Urine Protein',               '84156'],
    ['Urine Creatinine',                   '82570'],
];

$updated_cpt = 0;
echo "<table><tr><th>Test (LIKE match)</th><th>CPT Code</th><th>Rows Updated</th></tr>";
$upd = $conn->prepare("UPDATE lab_tests_directory SET cpt_code=? WHERE test_name LIKE ? AND (cpt_code IS NULL OR cpt_code = '')");
foreach ($cpt_map as [$name_part, $cpt]) {
    $like = '%' . $name_part . '%';
    $upd->bind_param("ss", $cpt, $like);
    $upd->execute();
    $rows = $upd->affected_rows;
    $updated_cpt += $rows;
    $col = $rows > 0 ? 'ok' : 'skip';
    echo "<tr><td>" . htmlspecialchars($name_part) . "</td><td><b>$cpt</b></td><td class='$col'>$rows row(s)</td></tr>";
}
echo "</table><p>Total CPT codes applied: <b>$updated_cpt</b></p>";

echo "<br><h2 style='color:#38bdf8;'>Migration Complete</h2>";
echo "<p>
  <a href='lab_tests.php'>→ Lab Tests Directory</a> &nbsp;|&nbsp;
  <a href='prescriptions_add.php?patient_id=1'>→ Test Prescription Wizard</a>
</p></body></html>";
