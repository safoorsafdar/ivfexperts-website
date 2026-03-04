<?php
/**
 * ICD-10 Search API — dual-source
 * 1) Local icd10_codes table (fast, offline)
 * 2) Server-side NIH NLM proxy (full ICD-10-CM, always complete)
 */
require_once __DIR__ . '/includes/auth.php';
header('Content-Type: application/json');

$q = trim($_GET['q'] ?? '');
if (strlen($q) < 2) {
    echo json_encode([]);
    exit;
}

$results = [];

// ── 1. Local DB ───────────────────────────────────────────────
try {
    $chk = $conn->query("SHOW TABLES LIKE 'icd10_codes'");
    if ($chk && $chk->num_rows > 0) {
        $term = '%' . $q . '%';
        $stmt = $conn->prepare(
            "SELECT icd10_code, description, category, snomed_code
             FROM icd10_codes
             WHERE icd10_code LIKE ? OR description LIKE ?
             ORDER BY CASE WHEN icd10_code LIKE ? THEN 0 ELSE 1 END,
                      category ASC, description ASC
             LIMIT 20"
        );
        if ($stmt) {
            $stmt->bind_param("sss", $term, $term, $term);
            $stmt->execute();
            $res = $stmt->get_result();
            while ($row = $res->fetch_assoc())
                $results[] = $row;
        }
    }
}
catch (Exception $e) { /* skip */
}

// ── 2. If local has < 5 results, proxy NIH NLM for full coverage ─
if (count($results) < 5) {
    $nihUrl = 'https://clinicaltables.nlm.nih.gov/api/icd10cm/v3/search?terms='
        . urlencode($q) . '&maxList=20&sf=code,name&df=code,name';
    $ctx = stream_context_create([
        'http' => ['timeout' => 4, 'ignore_errors' => true]
    ]);
    $raw = @file_get_contents($nihUrl, false, $ctx);
    if ($raw) {
        $nihData = json_decode($raw, true);
        $nihRows = $nihData[3] ?? [];
        foreach ($nihRows as $r) {
            // avoid duplicates already in local results
            $code = $r[0];
            if (!array_filter($results, fn($x) => $x['icd10_code'] === $code)) {
                $results[] = [
                    'icd10_code' => $code,
                    'description' => $r[1],
                    'category' => 'ICD-10-CM',
                    'snomed_code' => ''
                ];
            }
        }
    }
}

echo json_encode(array_values($results));
