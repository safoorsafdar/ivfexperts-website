<?php
require_once __DIR__ . '/includes/auth.php';

echo "<h1>Migrating Semen Diagnosis Definitions</h1>";

$sql = "CREATE TABLE IF NOT EXISTS semen_diagnosis_definitions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    condition_name VARCHAR(100) NOT NULL UNIQUE,
    definition TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if ($conn->query($sql)) {
    echo "Table `semen_diagnosis_definitions` ready.<br>";
}
else {
    die("Error creating table: " . $conn->error);
}

$definitions = [
    'Oligozoospermia' => "Oligozoospermia is a male fertility condition characterized by a low sperm count in the ejaculate. While a typical healthy range is 16 million to over 200 million sperm per milliliter of semen, someone with this condition falls below that 16 million threshold.",
    'Asthenozoospermia' => "Asthenozoospermia refers to reduced sperm motility, where the percentage of progressively moving sperm is below the WHO threshold. This condition can impact the sperm's ability to reach and fertilize the egg.",
    'Teratozoospermia' => "Teratozoospermia is characterized by a high percentage of sperm with abnormal morphology (shape). This can affect the sperm's ability to penetrate the egg.",
    'Azoospermia' => "Azoospermia is the medical condition where there is no measurable level of sperm in the ejaculate.",
    'Normozoospermia' => "Normozoospermia indicates that the semen parameters (concentration, motility, and morphology) are within the normal WHO reference range.",
    'Oligoasthenoteratozoospermia (OAT)' => "OAT syndrome is a condition that encompasses oligozoospermia (low sperm count), asthenozoospermia (poor sperm movement), and teratozoospermia (abnormal sperm shape).",
    'Necrozoospermia' => "Necrozoospermia is a condition where a high percentage of sperm in the ejaculate are dead (non-viable).",
    'Cryptozoospermia' => "Cryptozoospermia describes an extremely low sperm concentration, where sperm are only found after careful microscopic examination of a centrifuged semen sample.",
    'Leucocytospermia' => "Leucocytospermia (or Pyospermia) is the presence of an abnormally high number of white blood cells in the semen, which often indicates an underlying infection or inflammation.",
    'Hematospermia' => "Hematospermia is the presence of blood in the semen. While often benign, it can be a sign of infection, inflammation, or injury in the reproductive tract.",
    'Aspermia' => "Aspermia is the complete absence of semen and sperm upon ejaculation, often related to retrograde ejaculation or ductal obstruction.",
    'Hyperspermia' => "Hyperspermia is a condition where an abnormally large volume of semen is produced, typically exceeding 5-6 milliliters.",
    'Hypospermia' => "Hypospermia refers to an abnormally low volume of semen, specifically less than the WHO reference limit of 1.4 milliliters."
];

foreach ($definitions as $name => $def) {
    $stmt = $conn->prepare("INSERT INTO semen_diagnosis_definitions (condition_name, definition) VALUES (?, ?) ON DUPLICATE KEY UPDATE definition = VALUES(definition)");
    $stmt->bind_param("ss", $name, $def);
    $stmt->execute();
}

echo "Definitions seeded successfully.<br>";
?>
