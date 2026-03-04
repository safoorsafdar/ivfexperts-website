<?php
/**
 * MIGRATION: medications table v2
 * Adds: formula, default_dosage, default_frequency, default_duration, default_instructions
 * Run once: https://4me.ivfexperts.pk/migrate_medications_v2.php
 * Delete after use.
 */
require_once __DIR__ . '/includes/auth.php';

$steps = [];

$new_cols = [
    'formula'               => "ALTER TABLE medications ADD COLUMN formula VARCHAR(255) NOT NULL DEFAULT '' AFTER name",
    'default_dosage'        => "ALTER TABLE medications ADD COLUMN default_dosage VARCHAR(100) NOT NULL DEFAULT '' AFTER formula",
    'default_frequency'     => "ALTER TABLE medications ADD COLUMN default_frequency VARCHAR(100) NOT NULL DEFAULT '' AFTER default_dosage",
    'default_duration'      => "ALTER TABLE medications ADD COLUMN default_duration VARCHAR(100) NOT NULL DEFAULT '' AFTER default_frequency",
    'default_instructions'  => "ALTER TABLE medications ADD COLUMN default_instructions TEXT AFTER default_duration",
];

foreach ($new_cols as $col => $sql) {
    $r = $conn->query("SHOW COLUMNS FROM `medications` LIKE '$col'");
    if (!$r) {
        $steps[] = ['error', "Could not check column $col: " . $conn->error];
    } elseif ($r->num_rows === 0) {
        if ($conn->query($sql)) {
            $steps[] = ['ok', "Added column: <strong>$col</strong>"];
        } else {
            $steps[] = ['error', "Failed to add $col: " . $conn->error];
        }
    } else {
        $steps[] = ['skip', "Column already exists: $col"];
    }
}
?>
<!DOCTYPE html>
<html>
<head><title>Medications Migration v2</title>
<style>
  body { font-family: monospace; padding: 40px; background: #f8fafc; }
  h2 { color: #0f766e; }
  .ok    { color: #16a34a; }
  .error { color: #dc2626; font-weight: bold; }
  .skip  { color: #94a3b8; }
  p { margin: 6px 0; font-size: 14px; }
  .done { margin-top: 24px; padding: 16px 24px; background: #f0fdf4; border: 1px solid #86efac; border-radius: 8px; color: #166534; font-weight: bold; }
</style>
</head>
<body>
<h2>Medications Table Migration v2</h2>
<?php foreach ($steps as [$type, $msg]): ?>
  <p class="<?= $type ?>">
    <?= $type === 'ok' ? '✓' : ($type === 'error' ? '✗' : '↷') ?> <?= $msg ?>
  </p>
<?php endforeach; ?>
<div class="done">Migration complete. <strong>Delete this file from the server after running.</strong></div>
</body>
</html>
