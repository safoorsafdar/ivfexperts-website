<?php
define('BYPASS_AUTH', true);
require 'includes/auth.php';
$_GET['id'] = 1;
// Mock session so header.php doesn't crash if it expects it
$_SESSION['admin_id'] = 1;

// Capture output
ob_start();
require 'prescriptions_edit.php';
$html = ob_get_clean();

// Extract just the med_rows initialization and HTML block
preg_match('/var _medsData = (.*?);/', $html, $matches);
$json = $matches[1] ?? 'NOT FOUND';

preg_match('/<div id="med-rows" class="space-y-1">(.*?)<\/div>/s', $html, $matches_html);
$med_rows_html = $matches_html[1] ?? 'NOT FOUND';

// Also check for any input with name="meds[
$has_meds_inputs = substr_count($html, 'name="meds[');

header('Content-Type: application/json');
echo json_encode([
    'items_json' => json_decode($json, true),
    'med_rows_html' => trim($med_rows_html),
    'hardcoded_med_inputs_count' => $has_meds_inputs,
    // Add raw JS section incase there is an error
    'raw_js' => $json
]);
