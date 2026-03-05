<?php
/**
 * One-time blog post seed script — Month 1 content.
 * Run ONCE then remove from server or add a lock file.
 * Access restricted: only runs from CLI or if a secret GET param is provided.
 */

// Simple protection: require ?seed_key=ivf2026 in URL
if (!isset($_GET['seed_key']) || $_GET['seed_key'] !== 'ivf2026') {
    http_response_code(403);
    exit('Forbidden. Add ?seed_key=ivf2026 to run.');
}

require_once __DIR__ . '/../config/db.php';

$posts = [
    [
        'title'            => 'Fertility Treatment in Pakistan: What to Expect in 2025',
        'slug'             => 'fertility-treatment-pakistan-guide',
        'category'         => 'Patient Guide',
        'author'           => 'Dr. Adnan Jabbar',
        'tags'             => 'IVF Pakistan, fertility treatment, infertility guide',
        'meta_title'       => 'Fertility Treatment in Pakistan: Complete Patient Guide 2025 | IVF Experts',
        'meta_description' => 'A comprehensive guide to fertility treatment options, costs, and what to expect during IVF in Pakistan — by Dr. Adnan Jabbar, Lahore.',
        'excerpt'          => 'Pakistan has seen significant growth in fertility treatment availability over the past decade. This guide walks you through the full patient journey — from first consultation to embryo transfer — with realistic expectations about timelines, costs, and success rates.',
        'content'          => '<h2>What are the fertility treatment options available in Pakistan?</h2>
<p>The primary fertility treatments available at established clinics in Pakistan are IUI (Intrauterine Insemination), IVF (In Vitro Fertilisation), and ICSI (Intracytoplasmic Sperm Injection). More advanced options including PGT (Preimplantation Genetic Testing), Micro-TESE for azoospermia, and Ovarian PRP are also available at specialist centres.</p>
<h2>How long does it take from first appointment to IVF treatment?</h2>
<p>After your initial consultation, a baseline workup takes 2–4 weeks (blood tests, semen analysis, ultrasound). Once results are reviewed and a protocol decided, an IVF cycle begins at the start of your next menstrual cycle — meaning the full process from first appointment to embryo transfer typically takes 6–10 weeks.</p>
<h2>What do fertility investigations involve?</h2>
<p>A standard fertility workup includes: for the female — Day 3 FSH, LH, AMH, antral follicle count (AFC) via transvaginal ultrasound, and a hysterosalpingogram (HSG) to assess tubal patency. For the male — a semen analysis (count, motility, morphology) and, where indicated, DNA fragmentation index (DFI) testing.</p>
<h2>What are realistic IVF success rates in Pakistan?</h2>
<p>IVF success rates in Pakistan mirror global benchmarks at specialist centres. A 2023 HFEA analysis puts live birth rates at approximately 32% per cycle for women under 35, declining to 11% for women aged 40–42. Success depends heavily on ovarian reserve, embryo quality, and the expertise of the embryology laboratory.</p>
<h2>How much does IVF cost in Pakistan?</h2>
<p>IVF costs in Pakistan vary by clinic and include consultation fees, medications, and laboratory charges. At IVF Experts, we provide transparent cost estimates at the consultation stage. A free initial WhatsApp consultation with Dr. Adnan Jabbar helps determine which pathway is most appropriate for your specific situation before any commitment to a financial plan.</p>',
        'status'           => 'Published',
        'published_at'     => '2026-03-01 09:00:00',
    ],
    [
        'title'            => 'IVF vs ICSI: Which Is Right for You?',
        'slug'             => 'ivf-vs-icsi-comparison',
        'category'         => 'Treatment Comparison',
        'author'           => 'Dr. Adnan Jabbar',
        'tags'             => 'IVF, ICSI, male infertility, comparison',
        'meta_title'       => 'IVF vs ICSI: Key Differences, When to Use Each | IVF Experts Lahore',
        'meta_description' => 'IVF and ICSI are often confused. Dr. Adnan Jabbar explains the key differences, when ICSI is necessary, and how the right choice affects your chances of success.',
        'excerpt'          => 'IVF and ICSI are both in vitro fertilisation treatments — but they are not the same. This comparison explains the key differences, when ICSI is medically necessary, and what the evidence says about success rates.',
        'content'          => '<h2>What is the main difference between IVF and ICSI?</h2>
<p>In conventional IVF, eggs and thousands of sperm are placed together in a laboratory dish and fertilisation happens naturally. In ICSI (Intracytoplasmic Sperm Injection), a single sperm is selected and injected directly into each mature egg using a micro-needle. ICSI is a form of IVF — it uses the same egg retrieval and embryo transfer steps — but adds the microinjection technique in between.</p>
<h2>When is ICSI medically necessary?</h2>
<p>ICSI is indicated when: (1) the male partner has a low sperm count, poor motility, or abnormal morphology that makes natural fertilisation in a dish unreliable; (2) there is a history of failed or low fertilisation in a previous conventional IVF cycle; (3) sperm has been surgically retrieved (e.g., Micro-TESE for azoospermia); (4) preimplantation genetic testing (PGT) is planned, requiring individually injected eggs to avoid contamination with surrounding sperm DNA.</p>
<h2>Does ICSI improve success rates compared to IVF?</h2>
<p>For couples with normal sperm parameters, large randomised trials (including a Cochrane Review, 2021) have found no statistically significant difference in live birth rates between ICSI and conventional IVF. ICSI does not improve outcomes when male factor is absent — it simply ensures fertilisation where sperm quality is a barrier. The evidence does not support routine ICSI for all patients.</p>
<h2>Is ICSI more expensive than IVF?</h2>
<p>Yes, ICSI adds laboratory time and specialist embryologist skill. The additional cost is justified only when there is a clear clinical indication. At IVF Experts, we do not recommend ICSI routinely — we assess your semen analysis results carefully and recommend it only when the evidence supports it.</p>
<h2>Which should I choose?</h2>
<p>The choice is clinical, not personal. If your semen parameters are normal, conventional IVF gives equivalent outcomes at lower cost. If male factor is present, ICSI is the evidence-based choice. A thorough semen analysis and consultation with Dr. Adnan Jabbar will determine the right protocol for your specific situation.</p>',
        'status'           => 'Published',
        'published_at'     => '2026-03-05 09:00:00',
    ],
    [
        'title'            => 'What Is a Semen Analysis? Understanding Your Results',
        'slug'             => 'semen-analysis-results-guide',
        'category'         => 'Male Infertility',
        'author'           => 'Dr. Adnan Jabbar',
        'tags'             => 'semen analysis, male infertility, sperm count, ICSI',
        'meta_title'       => 'Semen Analysis Results Explained — What Is Normal? | IVF Experts',
        'meta_description' => 'What do semen analysis results mean? Dr. Adnan Jabbar explains WHO reference values for sperm count, motility, morphology, and what to do if results are abnormal.',
        'excerpt'          => 'A semen analysis is the cornerstone of male fertility assessment. This guide explains what the key parameters mean, what the WHO considers normal, and what your options are if results come back abnormal.',
        'content'          => '<h2>What does a semen analysis measure?</h2>
<p>A semen analysis (SA) evaluates several key sperm parameters: total volume (mL), sperm concentration (million/mL), total sperm count, progressive motility (% moving forward), total motility (% moving at all), morphology (% with normal shape), and — in advanced analysis — DNA fragmentation index (DFI) and vitality.</p>
<h2>What are the WHO reference values for normal semen?</h2>
<p>The World Health Organization (WHO 2021) 5th edition reference values — representing the 5th percentile of fertile men — are:</p>
<ul><li><strong>Volume:</strong> ≥1.4 mL</li><li><strong>Concentration:</strong> ≥16 million/mL</li><li><strong>Total count:</strong> ≥39 million per ejaculate</li><li><strong>Progressive motility:</strong> ≥30%</li><li><strong>Total motility:</strong> ≥42%</li><li><strong>Normal morphology:</strong> ≥4% (strict Kruger criteria)</li></ul>
<p>Values below these thresholds do not mean conception is impossible — they indicate that fertility may be reduced and that further evaluation is warranted.</p>
<h2>What does it mean if my sperm count is low?</h2>
<p>A count below 16 million/mL (oligospermia) reduces natural conception probability but does not prevent it. Mild oligospermia can often be improved with lifestyle changes and antioxidant therapy over 90 days (the sperm maturation cycle). Moderate-to-severe oligospermia may require IUI or ICSI. Complete absence of sperm (azoospermia) requires evaluation to distinguish between obstructive and non-obstructive causes.</p>
<h2>What does low motility mean?</h2>
<p>Asthenospermia (low motility) is the most common cause of male factor infertility. Sperm that do not move progressively cannot reach and penetrate the egg. ICSI bypasses this requirement entirely, achieving 70–85% fertilisation rates even with severely asthenozoospermic samples.</p>
<h2>What should I do if my results are abnormal?</h2>
<p>One abnormal semen analysis should be repeated after 10–14 days, as daily variation is significant. If the repeat is also abnormal, a consultation with a male fertility specialist (ideally a dual-trained Fertility Consultant and Clinical Embryologist like Dr. Adnan Jabbar) will review the full clinical picture and advise on treatment options.</p>',
        'status'           => 'Published',
        'published_at'     => '2026-03-10 09:00:00',
    ],
];

$inserted = 0;
$skipped  = 0;
$errors   = [];

foreach ($posts as $p) {
    // Check if slug already exists
    $check = $conn->prepare("SELECT id FROM blog_posts WHERE slug = ?");
    $check->bind_param('s', $p['slug']);
    $check->execute();
    $check->store_result();
    if ($check->num_rows > 0) {
        $skipped++;
        $check->close();
        continue;
    }
    $check->close();

    $stmt = $conn->prepare("
        INSERT INTO blog_posts
            (title, slug, category, author, tags, meta_title, meta_description, excerpt, content, status, published_at, created_at, updated_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
    ");
    $stmt->bind_param(
        'sssssssssss',
        $p['title'], $p['slug'], $p['category'], $p['author'], $p['tags'],
        $p['meta_title'], $p['meta_description'], $p['excerpt'], $p['content'],
        $p['status'], $p['published_at']
    );
    if ($stmt->execute()) {
        $inserted++;
    } else {
        $errors[] = $stmt->error;
    }
    $stmt->close();
}

header('Content-Type: text/plain');
echo "Seed complete.\n";
echo "Inserted: $inserted\n";
echo "Skipped (already exist): $skipped\n";
if ($errors) echo "Errors: " . implode(', ', $errors) . "\n";
echo "\nDELETE this file from the server after seeding.";
