<?php
/**
 * IVF Experts Database Configuration
 * Central connection file - included in all admin scripts
 * DO NOT commit real credentials to Git - use .env or server env vars in production
 */

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
    // In production: log error instead of displaying
    // error_log("Database connection failed: " . $conn->connect_error);
    die("Connection failed: " . $conn->connect_error); // Remove die() in live site
}

// === Set charset to prevent encoding issues
$conn->set_charset("utf8mb4");

// === Optional: Throw exceptions on query errors (makes debugging easier)
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

?>