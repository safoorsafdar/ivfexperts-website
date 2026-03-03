<?php
/**
 * IVF Experts Database Configuration
 * Central connection file - included in all admin scripts
 */

// === Disable strict exception mode so prepare() returns false on error
// This allows fallback logic (e.g. graceful column detection) to work correctly
mysqli_report(MYSQLI_REPORT_OFF);

if (getenv('DB_HOST')) {
    define('DB_HOST', getenv('DB_HOST'));
    define('DB_NAME', getenv('DB_NAME'));
    define('DB_USER', getenv('DB_USER'));
    define('DB_PASS', getenv('DB_PASS'));
}
else {
    define('DB_HOST', 'localhost');
    define('DB_NAME', 'u400207225_adnanjabbar');
    define('DB_USER', 'u400207225_adnanjabbar');
    define('DB_PASS', '4991701AdnanJabbar');
}

// === Create connection
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// === Check connection
if ($conn->connect_error) {
    // Log the error securely — do NOT display credentials to the browser
    error_log("[IVF DB ERROR] Connection failed: " . $conn->connect_error);
    die("<div style='font-family:sans-serif;padding:2rem;color:#c00;'>⚠️ Database connection failed. Please contact your system administrator.</div>");
}

// === Set charset to prevent encoding issues
$conn->set_charset("utf8mb4");
?>