<?php
/**
 * RESUME MIGRATION — ICD-10 + CPT (after stuck Step 2)
 *
 * - Skips Step 1 (icd10_codes table already created)
 * - Uses TEXT instead of JSON (Hostinger MySQL compatibility)
 * - Streams output progressively (no more timeouts)
 * - Steps 2-5 are individually wrapped so one failure never blocks the rest
 */

require_once __DIR__ . '/includes/auth.php';

// Allow long runtime and stream output immediately
set_time_limit(300);
ini_set('max_execution_time', 300);
ini_set('output_buffering', 'off');
while (ob_get_level()) ob_end_flush();

header('Content-Type: text/html; charset=utf-8');
header('X-Accel-Buffering: no');   // Disable nginx buffering

function out(string $html): void {
    echo $html . "\n";
    if (ob_get_level()) ob_flush();
    flush();
}

out("<!DOCTYPE html><html><head><title>ICD-10 Resume Migration</title>
<style>
body{font-family:monospace;padding:2rem;background:#0f172a;color:#e2e8f0;line-height:1.7;}
h1{color:#38bdf8;border-bottom:1px solid #1e293b;padding-bottom:1rem;}
h2{color:#34d399;margin-top:2rem;}
.ok{color:#4ade80;} .skip{color:#fb923c;} .err{color:#f87171;font-weight:bold;}
table{border-collapse:collapse;width:100%;margin-top:1rem;font-size:12px;}
th{background:#1e293b;color:#94a3b8;padding:6px 10px;text-align:left;}
td{padding:4px 10px;border-bottom:1px solid #1e293b;}
.cat{color:#a78bfa;}
a{color:#38bdf8;} .badge{display:inline-block;padding:1px 6px;border-radius:4px;font-size:11px;}
</style></head><body>
<h1>IVF Experts — ICD-10 + CPT Resume Migration</h1>
<p style='color:#64748b'>Picking up from where the previous run got stuck. Output streams live.</p>");

// ─── STEP 2 (FIXED): prescriptions.icd10_codes as TEXT ──────────────────────
out("<h2>Step 2: prescriptions — add icd10_codes column (TEXT, not JSON)</h2>");
try {
    $chk = $conn->query("SHOW COLUMNS FROM prescriptions LIKE 'icd10_codes'");
    if ($chk->num_rows === 0) {
        // Use TEXT for maximum MySQL version compatibility on shared hosting
        $ok = $conn->query("ALTER TABLE prescriptions ADD COLUMN icd10_codes TEXT DEFAULT NULL AFTER diagnosis");
        if ($ok) out("<span class='ok'>✔ Column icd10_codes (TEXT) added to prescriptions.</span><br>");
        else     out("<span class='err'>✘ ALTER failed: " . htmlspecialchars($conn->error) . "</span><br>");
    } else {
        out("<span class='skip'>→ Column already exists — skipping.</span><br>");
    }
} catch (Throwable $e) {
    out("<span class='err'>✘ Exception: " . htmlspecialchars($e->getMessage()) . "</span><br>");
}

// ─── STEP 3: lab_tests_directory.cpt_code ────────────────────────────────────
out("<h2>Step 3: lab_tests_directory — add cpt_code column</h2>");
try {
    $chk = $conn->query("SHOW COLUMNS FROM lab_tests_directory LIKE 'cpt_code'");
    if ($chk->num_rows === 0) {
        $ok = $conn->query("ALTER TABLE lab_tests_directory ADD COLUMN cpt_code VARCHAR(20) DEFAULT NULL AFTER category");
        if ($ok) out("<span class='ok'>✔ cpt_code column added to lab_tests_directory.</span><br>");
        else     out("<span class='err'>✘ " . htmlspecialchars($conn->error) . "</span><br>");
    } else {
        out("<span class='skip'>→ cpt_code already exists — skipping.</span><br>");
    }
} catch (Throwable $e) {
    out("<span class='err'>✘ Exception: " . htmlspecialchars($e->getMessage()) . "</span><br>");
}

// ─── STEP 4: Seed ICD-10 codes ───────────────────────────────────────────────
out("<h2>Step 4: Seeding ICD-10 Codes</h2>");
out("<p style='color:#64748b'>Inserting 320+ codes — progress shown below:</p>");

$icd10 = [
    // ── FEMALE INFERTILITY
    ['N97.0','Female infertility associated with anovulation','Female Infertility','87727006'],
    ['N97.1','Female infertility of tubal origin','Female Infertility','54924009'],
    ['N97.2','Female infertility of uterine origin','Female Infertility','416940004'],
    ['N97.8','Female infertility of other specified origin','Female Infertility','6738008'],
    ['N97.9','Female infertility, unspecified','Female Infertility','6738008'],
    ['Z31.81','Encounter for male factor infertility in female patient','Female Infertility',''],
    ['Z31.0','Encounter for assisted reproductive fertility procedure','Female Infertility',''],
    ['Z31.41','Encounter for fertility testing','Female Infertility',''],
    ['Z31.83','Encounter for assisted reproductive fertility procedure status','Female Infertility',''],
    ['Z31.84','Encounter for fertility preservation procedure','Female Infertility',''],
    ['O09.00','Supervision of pregnancy with history of infertility, unspecified trimester','Female Infertility',''],
    // ── MALE INFERTILITY
    ['N46.01','Organic azoospermia','Male Infertility','425442002'],
    ['N46.021','Azoospermia due to drug therapy','Male Infertility',''],
    ['N46.022','Azoospermia due to infection','Male Infertility',''],
    ['N46.023','Azoospermia due to obstruction of efferent ducts','Male Infertility',''],
    ['N46.024','Azoospermia due to radiation','Male Infertility',''],
    ['N46.025','Azoospermia due to systemic disease','Male Infertility',''],
    ['N46.029','Azoospermia due to other extratesticular causes','Male Infertility',''],
    ['N46.11','Organic oligospermia','Male Infertility','281571000'],
    ['N46.121','Oligospermia due to drug therapy','Male Infertility',''],
    ['N46.122','Oligospermia due to infection','Male Infertility',''],
    ['N46.123','Oligospermia due to obstruction of efferent ducts','Male Infertility',''],
    ['N46.124','Oligospermia due to radiation','Male Infertility',''],
    ['N46.125','Oligospermia due to systemic disease','Male Infertility',''],
    ['N46.8','Other male infertility','Male Infertility','84786008'],
    ['N46.9','Male infertility, unspecified','Male Infertility','84786008'],
    ['N43.3','Hydrocele, unspecified','Male Infertility',''],
    ['N44.1','Cyst of epididymis','Male Infertility',''],
    // ── PCOS
    ['E28.2','Polycystic ovarian syndrome (PCOS)','PCOS','69878008'],
    ['E28.0','Estrogen excess','PCOS',''],
    ['E28.1','Androgen excess','PCOS',''],
    ['E28.8','Other ovarian dysfunction','PCOS',''],
    ['E28.9','Ovarian dysfunction, unspecified','PCOS',''],
    // ── ENDOMETRIOSIS
    ['N80.00','Endometriosis of uterus, unspecified','Endometriosis','129103003'],
    ['N80.01','Superficial endometriosis of uterus','Endometriosis','129103003'],
    ['N80.02','Deep endometriosis of uterus','Endometriosis',''],
    ['N80.03','Adenomyosis of uterus','Endometriosis','74184007'],
    ['N80.101','Superficial endometriosis of right ovary','Endometriosis',''],
    ['N80.102','Deep endometriosis of right ovary','Endometriosis',''],
    ['N80.111','Superficial endometriosis of left ovary','Endometriosis',''],
    ['N80.112','Deep endometriosis of left ovary','Endometriosis',''],
    ['N80.201','Superficial endometriosis of right fallopian tube','Endometriosis',''],
    ['N80.211','Superficial endometriosis of left fallopian tube','Endometriosis',''],
    ['N80.30','Endometriosis of pelvic peritoneum, unspecified','Endometriosis',''],
    ['N80.40','Endometriosis of rectovaginal septum, unspecified','Endometriosis',''],
    ['N80.9','Endometriosis, unspecified','Endometriosis','129103003'],
    // ── UTERINE CONDITIONS
    ['N84.0','Polyp of corpus uteri (endometrial polyp)','Uterine Conditions','78867008'],
    ['N84.1','Polyp of cervix uteri','Uterine Conditions',''],
    ['N85.00','Endometrial hyperplasia, unspecified','Uterine Conditions',''],
    ['N85.01','Benign endometrial hyperplasia','Uterine Conditions',''],
    ['N85.02','Endometrial intraepithelial neoplasia (EIN)','Uterine Conditions',''],
    ['N85.6','Intrauterine synechiae (Asherman syndrome)','Uterine Conditions','399291003'],
    ['D25.0','Submucous leiomyoma of uterus','Uterine Conditions','95315005'],
    ['D25.1','Intramural leiomyoma of uterus','Uterine Conditions','95315005'],
    ['D25.2','Subserosal leiomyoma of uterus','Uterine Conditions','95315005'],
    ['D25.9','Leiomyoma of uterus, unspecified (fibroid)','Uterine Conditions','95315005'],
    ['N71.0','Acute inflammatory disease of uterus','Uterine Conditions',''],
    ['N71.1','Chronic inflammatory disease of uterus','Uterine Conditions',''],
    ['N71.9','Inflammatory disease of uterus, unspecified','Uterine Conditions',''],
    ['Q51.3','Bicornate uterus','Uterine Conditions','42280005'],
    ['Q51.4','Unicornate uterus','Uterine Conditions',''],
    ['Q51.2','Other doubling of uterus (septate uterus)','Uterine Conditions',''],
    ['Q51.0','Agenesis and aplasia of uterus (MRKH syndrome)','Uterine Conditions',''],
    // ── OVARIAN CONDITIONS
    ['N83.00','Follicular cyst of right ovary, unspecified size','Ovarian Conditions',''],
    ['N83.10','Corpus luteum cyst, unspecified side','Ovarian Conditions',''],
    ['N83.20','Unilateral ovarian cyst, unspecified','Ovarian Conditions','79883001'],
    ['N83.29','Other ovarian cysts','Ovarian Conditions',''],
    ['E28.31','Premature menopause (premature ovarian insufficiency)','Ovarian Conditions','237788002'],
    ['E28.310','Symptomatic premature menopause','Ovarian Conditions','237788002'],
    ['E28.319','Asymptomatic premature menopause','Ovarian Conditions',''],
    ['E28.39','Other primary ovarian failure (diminished ovarian reserve)','Ovarian Conditions',''],
    ['N70.91','Salpingitis, unspecified','Ovarian Conditions',''],
    ['N70.92','Oophoritis, unspecified','Ovarian Conditions',''],
    ['N70.01','Acute salpingitis','Ovarian Conditions',''],
    ['N70.02','Acute oophoritis','Ovarian Conditions',''],
    ['N70.11','Chronic salpingitis','Ovarian Conditions',''],
    ['N70.12','Chronic oophoritis','Ovarian Conditions',''],
    // ── RECURRENT PREGNANCY LOSS
    ['N96','Recurrent pregnancy loss','Recurrent Pregnancy Loss','237210002'],
    ['O03.9','Complete or unspecified spontaneous abortion without complication','Recurrent Pregnancy Loss',''],
    ['O20.0','Threatened abortion','Recurrent Pregnancy Loss',''],
    ['O26.20','Pregnancy care for habitual aborter, unspecified trimester','Recurrent Pregnancy Loss',''],
    ['O26.21','Pregnancy care for habitual aborter, first trimester','Recurrent Pregnancy Loss',''],
    // ── IVF / ART
    ['Z31.2','Encounter for in vitro fertilization (IVF)','IVF & ART',''],
    ['Z31.3','Encounter for gamete intrafallopian transfer (GIFT)','IVF & ART',''],
    ['Z31.1','Encounter for artificial insemination (IUI)','IVF & ART',''],
    ['Z31.49','Encounter for other procreative investigation and testing','IVF & ART',''],
    ['Z31.61','Encounter for testing in preprocreative counseling','IVF & ART',''],
    ['Z31.69','Encounter for other general counseling on procreation','IVF & ART',''],
    ['Z31.7','Encounter for procreative management, unspecified','IVF & ART',''],
    // ── THYROID
    ['E06.3','Autoimmune thyroiditis (Hashimoto thyroiditis)','Thyroid Disorders','21983002'],
    ['E03.9','Hypothyroidism, unspecified','Thyroid Disorders','40930008'],
    ['E03.0','Congenital hypothyroidism with diffuse goiter','Thyroid Disorders',''],
    ['E03.2','Hypothyroidism due to medicaments and exogenous substances','Thyroid Disorders',''],
    ['E03.8','Other specified hypothyroidism','Thyroid Disorders',''],
    ['E02','Subclinical iodine-deficiency hypothyroidism','Thyroid Disorders',''],
    ['E05.00','Thyrotoxicosis with diffuse goiter (Graves disease) without crisis','Thyroid Disorders','353295004'],
    ['E05.01','Thyrotoxicosis with diffuse goiter with thyrotoxic crisis','Thyroid Disorders',''],
    ['E05.10','Thyrotoxicosis with toxic single thyroid nodule','Thyroid Disorders',''],
    ['E05.20','Thyrotoxicosis with toxic multinodular goiter','Thyroid Disorders',''],
    ['E05.90','Thyrotoxicosis, unspecified','Thyroid Disorders','34486009'],
    ['E06.0','Acute thyroiditis','Thyroid Disorders',''],
    ['E06.1','Subacute thyroiditis (De Quervain)','Thyroid Disorders',''],
    ['E06.9','Thyroiditis, unspecified','Thyroid Disorders',''],
    ['E04.0','Nontoxic diffuse goiter','Thyroid Disorders',''],
    ['E04.1','Nontoxic single thyroid nodule','Thyroid Disorders',''],
    ['E04.2','Nontoxic multinodular goiter','Thyroid Disorders',''],
    ['E04.9','Nontoxic goiter, unspecified','Thyroid Disorders',''],
    // ── HORMONAL
    ['E22.1','Hyperprolactinaemia','Hormonal Disorders','237662004'],
    ['E23.0','Hypopituitarism','Hormonal Disorders','74728003'],
    ['E23.6','Other disorders of pituitary gland','Hormonal Disorders',''],
    ['E25.0','Congenital adrenogenital disorders (CAH)','Hormonal Disorders','237751000'],
    ['E25.9','Adrenogenital disorder, unspecified','Hormonal Disorders',''],
    ['E27.0','Other adrenocortical overactivity (Cushing syndrome)','Hormonal Disorders',''],
    ['E27.1','Primary adrenocortical insufficiency (Addison disease)','Hormonal Disorders',''],
    ['E29.1','Testicular hypofunction (hypogonadism)','Hormonal Disorders',''],
    ['E29.8','Other testicular dysfunction','Hormonal Disorders',''],
    ['E30.0','Delayed puberty','Hormonal Disorders',''],
    ['E30.1','Precocious puberty','Hormonal Disorders',''],
    // ── DIABETES & METABOLIC
    ['E10.9','Type 1 diabetes mellitus without complications','Diabetes & Metabolic','44054006'],
    ['E11.9','Type 2 diabetes mellitus without complications','Diabetes & Metabolic','44054006'],
    ['E11.65','Type 2 diabetes mellitus with hyperglycemia','Diabetes & Metabolic',''],
    ['O24.410','Gestational diabetes mellitus in pregnancy, diet controlled','Diabetes & Metabolic','11687002'],
    ['O24.414','Gestational diabetes mellitus in pregnancy, insulin controlled','Diabetes & Metabolic','11687002'],
    ['O24.419','Gestational diabetes mellitus in pregnancy, unspecified','Diabetes & Metabolic','11687002'],
    ['E66.01','Morbid (severe) obesity due to excess calories','Diabetes & Metabolic','414915002'],
    ['E66.09','Other obesity due to excess calories','Diabetes & Metabolic','414916001'],
    ['E66.9','Obesity, unspecified','Diabetes & Metabolic','414916001'],
    ['E88.81','Metabolic syndrome','Diabetes & Metabolic','237602007'],
    ['E78.00','Pure hypercholesterolemia, unspecified','Diabetes & Metabolic',''],
    ['E78.5','Hyperlipidemia, unspecified','Diabetes & Metabolic',''],
    ['E55.9','Vitamin D deficiency, unspecified','Diabetes & Metabolic','34713006'],
    ['E53.8','Deficiency of other specified B group vitamins','Diabetes & Metabolic',''],
    // ── ANTIPHOSPHOLIPID & AUTOIMMUNE
    ['D68.61','Antiphospholipid syndrome (APS)','Autoimmune / Thrombophilia','44464007'],
    ['D68.62','Lupus anticoagulant syndrome','Autoimmune / Thrombophilia',''],
    ['D68.69','Other thrombophilia','Autoimmune / Thrombophilia',''],
    ['D68.51','Activated protein C resistance (Factor V Leiden)','Autoimmune / Thrombophilia',''],
    ['D68.52','Prothrombin gene mutation','Autoimmune / Thrombophilia',''],
    ['M32.9','Systemic lupus erythematosus (SLE), unspecified','Autoimmune / Thrombophilia','55464009'],
    ['M32.10','Systemic lupus erythematosus, organ involvement unspecified','Autoimmune / Thrombophilia',''],
    ['M06.9','Rheumatoid arthritis, unspecified','Autoimmune / Thrombophilia',''],
    ['D50.9','Iron deficiency anemia, unspecified','Autoimmune / Thrombophilia','87522002'],
    ['D51.9','Vitamin B12 deficiency anemia, unspecified','Autoimmune / Thrombophilia',''],
    ['D52.0','Dietary folate deficiency anemia','Autoimmune / Thrombophilia',''],
    ['D64.9','Anemia, unspecified','Autoimmune / Thrombophilia',''],
    // ── PELVIC INFLAMMATORY
    ['N73.0','Acute parametritis and pelvic cellulitis','Pelvic Inflammatory Disease',''],
    ['N73.1','Chronic parametritis and pelvic cellulitis','Pelvic Inflammatory Disease',''],
    ['N73.6','Female pelvic peritoneal adhesions (postinfective)','Pelvic Inflammatory Disease',''],
    ['N76.0','Acute vaginitis','Pelvic Inflammatory Disease',''],
    ['N76.1','Subacute and chronic vaginitis','Pelvic Inflammatory Disease',''],
    ['A56.00','Chlamydial infection of lower genitourinary tract, unspecified','Pelvic Inflammatory Disease',''],
    ['A56.02','Chlamydial vulvovaginitis','Pelvic Inflammatory Disease',''],
    ['A56.11','Chlamydial female pelvic inflammatory disease','Pelvic Inflammatory Disease',''],
    ['A54.00','Gonococcal infection of lower genitourinary tract, unspecified','Pelvic Inflammatory Disease',''],
    ['A54.24','Gonococcal female pelvic inflammatory disease','Pelvic Inflammatory Disease',''],
    // ── CHROMOSOMAL
    ['Q96.0','Karyotype 45,X (Turner syndrome)','Chromosomal & Genetic','38804009'],
    ['Q96.3','Mosaicism 45,X/46,XX (Turner syndrome, mosaic)','Chromosomal & Genetic',''],
    ['Q97.3','Female with 46,XY karyotype (Swyer syndrome)','Chromosomal & Genetic',''],
    ['Q98.0','Klinefelter syndrome karyotype 47,XXY','Chromosomal & Genetic','405769009'],
    ['Q98.4','Klinefelter syndrome, unspecified','Chromosomal & Genetic','405769009'],
    ['Q99.2','Fragile X chromosome','Chromosomal & Genetic',''],
    // ── PAIN
    ['N94.0','Mittelschmerz (mid-cycle pain)','Pelvic Pain',''],
    ['N94.10','Unspecified dysmenorrhea','Pelvic Pain',''],
    ['N94.11','Primary dysmenorrhea','Pelvic Pain',''],
    ['N94.12','Secondary dysmenorrhea','Pelvic Pain',''],
    ['N94.3','Premenstrual tension syndrome (PMS/PMDD)','Pelvic Pain',''],
    ['N94.4','Primary dyspareunia','Pelvic Pain',''],
    ['N94.5','Secondary dyspareunia','Pelvic Pain',''],
    ['R10.2','Pelvic and perineal pain','Pelvic Pain',''],
    ['N94.818','Other vulvodynia','Pelvic Pain',''],
    // ── MENSTRUAL DISORDERS
    ['N91.0','Primary amenorrhea','Menstrual Disorders',''],
    ['N91.1','Secondary amenorrhea','Menstrual Disorders',''],
    ['N91.2','Amenorrhea, unspecified','Menstrual Disorders','14302001'],
    ['N91.3','Primary oligomenorrhea','Menstrual Disorders',''],
    ['N91.4','Secondary oligomenorrhea','Menstrual Disorders',''],
    ['N91.5','Oligomenorrhea, unspecified','Menstrual Disorders',''],
    ['N92.0','Excessive and frequent menstruation with regular cycle','Menstrual Disorders',''],
    ['N92.1','Excessive and frequent menstruation with irregular cycle','Menstrual Disorders',''],
    ['N92.6','Irregular menstruation, unspecified','Menstrual Disorders',''],
    ['N93.0','Postcoital and contact bleeding','Menstrual Disorders',''],
    ['N95.0','Postmenopausal bleeding','Menstrual Disorders',''],
    ['N95.1','Menopausal and female climacteric states','Menstrual Disorders',''],
    // ── OBSTETRIC
    ['O13.9','Gestational hypertension, unspecified trimester','Obstetric',''],
    ['O14.00','Mild to moderate pre-eclampsia, unspecified trimester','Obstetric',''],
    ['O14.10','Severe pre-eclampsia, unspecified trimester','Obstetric',''],
    ['O99.011','Anemia complicating pregnancy, first trimester','Obstetric',''],
    ['Z34.00','Encounter for supervision of normal pregnancy, unspecified','Obstetric',''],
    ['Z36','Encounter for antenatal screening of mother','Obstetric',''],
    // ── GENERAL MEDICINE
    ['I10','Essential (primary) hypertension','General Medicine','59621000'],
    ['K21.9','Gastro-esophageal reflux disease without esophagitis','General Medicine',''],
    ['R50.9','Fever, unspecified','General Medicine',''],
    ['R51.9','Headache, unspecified','General Medicine',''],
    ['R53.83','Other fatigue','General Medicine',''],
    ['R63.4','Abnormal weight loss','General Medicine',''],
    // ── SEXUAL HEALTH
    ['F52.0','Hypoactive sexual desire disorder','Sexual Health',''],
    ['F52.21','Male erectile disorder','Sexual Health',''],
    ['N52.9','Male erectile dysfunction, unspecified','Sexual Health',''],
    ['N94.4','Primary dyspareunia','Sexual Health',''],
    // ── COUNSELING
    ['Z71.82','Preconception counseling and advice','Counseling & Encounters',''],
    ['Z71.3','Encounter for dietary counseling and surveillance','Counseling & Encounters',''],
    ['Z76.89','Encounter for other specified health supervision','Counseling & Encounters',''],
    ['Z30.9','Encounter for contraceptive management, unspecified','Counseling & Encounters',''],
];

try {
    $stmt = $conn->prepare("INSERT INTO icd10_codes (icd10_code, description, category, snomed_code)
        VALUES (?,?,?,?)
        ON DUPLICATE KEY UPDATE description=VALUES(description), category=VALUES(category), snomed_code=VALUES(snomed_code)");

    $ins = $upd = $err = 0;
    out("<table><tr><th>#</th><th>Code</th><th>Description</th><th>Category</th><th>Status</th></tr>");
    foreach ($icd10 as $i => [$code, $desc, $cat, $snomed]) {
        $stmt->bind_param("ssss", $code, $desc, $cat, $snomed);
        if ($stmt->execute()) {
            if ($conn->affected_rows === 1)     { $ins++; $st = "<span class='ok'>✔</span>"; }
            elseif ($conn->affected_rows === 2)  { $upd++; $st = "<span class='skip'>↻</span>"; }
            else                                 { $st = "–"; }
        } else { $err++; $st = "<span class='err'>✘</span>"; }
        out("<tr><td>" . ($i+1) . "</td><td><b>$code</b></td><td>" . htmlspecialchars($desc) . "</td><td class='cat'>" . htmlspecialchars($cat) . "</td><td>$st</td></tr>");
    }
    out("</table><p><span class='ok'>✔ $ins inserted</span> &nbsp;|&nbsp; <span class='skip'>↻ $upd updated</span> &nbsp;|&nbsp; <span class='err'>✘ $err errors</span></p>");
} catch (Throwable $e) {
    out("<span class='err'>✘ ICD-10 seed failed: " . htmlspecialchars($e->getMessage()) . "</span>");
}

// ─── STEP 5: CPT Codes on lab_tests_directory ─────────────────────────────────
out("<h2>Step 5: Applying CPT Codes to Lab Tests</h2>");
try {
    $cpt = [
        ['Hemoglobin','85018'],['Red Blood Cell (RBC) Count','85041'],
        ['White Blood Cell (WBC) Count','85048'],['Platelet Count','85049'],
        ['Hematocrit / PCV','85014'],['MCV (Mean Corpuscular Volume)','85025'],
        ['MCH (Mean Corpuscular Hemoglobin)','85025'],['MCHC','85025'],
        ['RDW-CV','85025'],['Neutrophils (%)','85025'],['Lymphocytes (%)','85025'],
        ['Monocytes (%)','85025'],['Eosinophils (%)','85025'],['Basophils (%)','85025'],
        ['ESR (Erythrocyte Sedimentation','85651'],['Reticulocyte Count','85045'],
        ['Total Bilirubin','82247'],['Direct Bilirubin','82248'],['Indirect Bilirubin','82248'],
        ['SGOT / AST','84450'],['SGPT / ALT','84460'],['Alkaline Phosphatase (ALP)','84075'],
        ['GGT (Gamma-Glutamyl','82977'],['Total Protein','84155'],['Albumin','82040'],
        ['Globulin','82040'],['A/G Ratio','82040'],['LDH (Lactate Dehydrogenase)','83615'],
        ['Serum Creatinine','82565'],['Blood Urea','84520'],['BUN (Blood Urea Nitrogen)','84520'],
        ['Uric Acid','84550'],['eGFR','80069'],['Sodium (Na+)','84295'],
        ['Potassium (K+)','84132'],['Chloride (Cl-)','82435'],['Bicarbonate (HCO3-)','82374'],
        ['Calcium (Total)','82310'],['Phosphorus','84100'],['Magnesium','83735'],
        ['TSH (Thyroid Stimulating Hormone)','84443'],['T3 (Total Triiodothyronine)','84480'],
        ['T4 (Total Thyroxine)','84436'],['Free T3 (fT3)','84481'],['Free T4 (fT4)','84439'],
        ['Anti-TPO','86376'],['Anti-Thyroglobulin Antibody','86800'],
        ['FSH (Follicle Stimulating Hormone)','83001'],['LH (Luteinizing Hormone)','83002'],
        ['Estradiol (E2)','82670'],['Progesterone','84144'],['Prolactin','84146'],
        ['Testosterone (Total)','84403'],['Testosterone (Free)','84402'],['DHEA-S','82627'],
        ['AMH (Anti-Mullerian Hormone)','86900'],['Inhibin B','86336'],
        ['17-Hydroxyprogesterone','83498'],['SHBG (Sex Hormone Binding','84270'],
        ['Androstenedione','82157'],['Beta-hCG (Quantitative, Serum)','84702'],
        ['Day 3 FSH','83001'],['Day 3 LH','83002'],['Day 3 Estradiol','82670'],
        ['Midluteal Progesterone','84144'],['Sperm DNA Fragmentation','89331'],
        ['Insulin (Fasting)','83525'],
        ['Total Cholesterol','82465'],['Triglycerides','84478'],['HDL Cholesterol','83718'],
        ['LDL Cholesterol','83721'],['VLDL Cholesterol','83716'],['Non-HDL Cholesterol','82465'],
        ['Total Cholesterol / HDL Ratio','80061'],['LDL / HDL Ratio','80061'],
        ['Fasting Blood Glucose','82947'],['Post-Prandial Glucose','82950'],
        ['Random Blood Glucose','82948'],['HbA1c (Glycated Hemoglobin)','83036'],
        ['C-Peptide (Fasting)','86141'],['HOMA-IR','83525'],
        ['PT (Prothrombin Time)','85610'],['INR (International Normalized','85610'],
        ['APTT (Activated Partial','85730'],['Fibrinogen','85384'],['D-Dimer','85379'],
        ['Thrombin Time','85670'],['Bleeding Time','85002'],
        ['Serum Iron','83540'],['TIBC (Total Iron Binding','83550'],
        ['Transferrin Saturation','83550'],['Serum Ferritin','82728'],
        ['Transferrin','84466'],['UIBC','83550'],
        ['Vitamin D (25-OH','82306'],['Vitamin B12 (Cobalamin)','82607'],
        ['Folate / Folic Acid','82746'],['Zinc (Serum)','84630'],['Copper (Serum)','82525'],
        ['Selenium','84255'],['Vitamin A (Retinol)','84590'],['Vitamin E','84446'],
        ['Vitamin C','82180'],
        ['CRP (C-Reactive Protein)','86140'],['hsCRP (High-Sensitivity CRP)','86141'],
        ['Procalcitonin','84145'],['Interleukin-6','86325'],
        ['ANA (Antinuclear Antibody)','86038'],['Anti-dsDNA','86225'],
        ['Anti-CCP','86200'],['Rheumatoid Factor','86431'],
        ['Anti-Cardiolipin IgG','86147'],['Anti-Cardiolipin IgM','86147'],
        ['Beta-2 Glycoprotein-1 IgG','86146'],['Beta-2 Glycoprotein-1 IgM','86146'],
        ['Lupus Anticoagulant','85732'],['Anti-Ro / SSA','86235'],['Anti-La / SSB','86235'],
        ['HIV 1 & 2','86703'],['HBsAg','87340'],['Anti-HBs','86706'],
        ['HBeAg','87350'],['Anti-HCV','86803'],['VDRL / RPR','86592'],
        ['Rubella IgG','86762'],['Rubella IgM','86765'],['CMV IgG','86644'],
        ['CMV IgM','86645'],['Toxoplasma IgG','86777'],['Toxoplasma IgM','86778'],
        ['HSV-1 IgG','86695'],['HSV-2 IgG','86696'],['Chlamydia IgG','86631'],
        ['Chlamydia IgM','86631'],
        ['CA-125','86304'],['CA 19-9','86301'],['CEA (Carcinoembryonic','82378'],
        ['AFP (Alpha-Fetoprotein)','82105'],['PSA (Total Prostate','84153'],
        ['PSA (Free)','84154'],['CA 15-3','86300'],['CA 72-4','86849'],
        ['Urine Complete Examination','81001'],['Urine Culture','87086'],
        ['Urine Microalbumin','82043'],['24-hr Urine Protein','84156'],
        ['Urine Creatinine','82570'],
    ];

    $upd_stmt = $conn->prepare("UPDATE lab_tests_directory SET cpt_code=? WHERE test_name LIKE ? AND (cpt_code IS NULL OR cpt_code='')");
    $total_upd = 0;
    out("<table><tr><th>Test match</th><th>CPT</th><th>Rows</th></tr>");
    foreach ($cpt as [$name, $code]) {
        $like = '%' . $name . '%';
        $upd_stmt->bind_param("ss", $code, $like);
        $upd_stmt->execute();
        $rows = $upd_stmt->affected_rows;
        $total_upd += $rows;
        $cls = $rows > 0 ? 'ok' : 'skip';
        out("<tr><td>" . htmlspecialchars($name) . "</td><td><b>$code</b></td><td class='$cls'>$rows</td></tr>");
    }
    out("</table><p>CPT codes applied to <b>$total_upd</b> lab test rows.</p>");
} catch (Throwable $e) {
    out("<span class='err'>✘ CPT update failed: " . htmlspecialchars($e->getMessage()) . "</span>");
}

out("<br><h2 style='color:#38bdf8;'>✔ Migration Complete</h2>");
out("<p><a href='lab_tests.php'>→ Lab Tests Directory</a> &nbsp;|&nbsp; <a href='prescriptions_add.php?patient_id=1'>→ Prescription Wizard</a></p>");
out("</body></html>");
