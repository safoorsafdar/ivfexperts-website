<?php
// Set the correct header so browsers and search engines treat this as XML
header("Content-Type: text/xml;charset=utf-8");

// Base URL of your website
$baseUrl = "https://ivfexperts.pk";

// Database connection for dynamic blog posts
require_once __DIR__ . '/config/db.php';

// Define which directories we want to scan for public treatment pages
$directoriesToScan = [
    'male-infertility',
    'female-infertility',
    'art-procedures',
    'stemcell',
    'doctors'
];

// Start with standard root-level pages
$pages = [
    '/' => ['freq' => 'daily', 'priority' => '1.00'],
    '/about/' => ['freq' => 'monthly', 'priority' => '0.80'],
    '/contact/' => ['freq' => 'monthly', 'priority' => '0.70'],
    '/blog/' => ['freq' => 'weekly', 'priority' => '0.90'],
    '/doctors/' => ['freq' => 'monthly', 'priority' => '0.70'],
    '/portal/' => ['freq' => 'monthly', 'priority' => '0.50'],
];

// Glossary pages (clean URLs, no .php extension)
$pages['/glossary/'] = ['freq' => 'monthly', 'priority' => '0.70'];
$glossaryTerms = ['ivf', 'icsi', 'amh', 'azoospermia', 'pcos'];
foreach ($glossaryTerms as $term) {
    $pages['/glossary/' . $term] = ['freq' => 'monthly', 'priority' => '0.65'];
}

// Tools pages (clean URLs)
$pages['/tools/'] = ['freq' => 'monthly', 'priority' => '0.75'];
$pages['/tools/ivf-success-calculator'] = ['freq' => 'monthly', 'priority' => '0.80'];
$pages['/tools/semen-analysis-interpreter'] = ['freq' => 'monthly', 'priority' => '0.80'];
$pages['/tools/female-fertility-age-clock'] = ['freq' => 'monthly', 'priority' => '0.80'];
$pages['/tools/ivf-cost-estimator-pakistan'] = ['freq' => 'monthly', 'priority' => '0.80'];
$pages['/tools/ovulation-calculator-fertile-window'] = ['freq' => 'monthly', 'priority' => '0.80'];
$pages['/tools/ivf-timeline-calculator'] = ['freq' => 'monthly', 'priority' => '0.80'];

// Scan predefined directories dynamically
foreach ($directoriesToScan as $dir) {
    if (is_dir(__DIR__ . '/' . $dir)) {
        // Automatically add the index of the directory
        if (!isset($pages['/' . $dir . '/'])) {
            $pages['/' . $dir . '/'] = ['freq' => 'weekly', 'priority' => '0.90'];
        }

        // Find all PHP files in the directory
        $files = glob(__DIR__ . '/' . $dir . '/*.php');
        if ($files !== false) {
            foreach ($files as $file) {
                $filename = basename($file);
                if ($filename !== 'index.php') {
                    $pages['/' . $dir . '/' . $filename] = ['freq' => 'weekly', 'priority' => '0.80'];
                }
            }
        }
    }
}

// Add published blog posts dynamically from database
try {
    $res = $conn->query("SELECT slug, updated_at FROM blog_posts WHERE status = 'Published' ORDER BY published_at DESC");
    if ($res) {
        while ($row = $res->fetch_assoc()) {
            $pages['/blog/' . $row['slug'] . '/'] = [
                'freq' => 'monthly',
                'priority' => '0.75',
                'lastmod' => $row['updated_at']
            ];
        }
    }
}
catch (Exception $e) {
// Silently skip if blog_posts table doesn't exist yet
}

// Global fallback date if filemtime fails
$fallbackDate = date('Y-m-d\TH:i:sP');

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

foreach ($pages as $pageUrl => $meta) {
    $changefreq = $meta['freq'];
    $priority = $meta['priority'];

    // Attempt to get accurate last modified time
    $lastmod = $meta['lastmod'] ?? null;

    if (!$lastmod) {
        if ($pageUrl === '/') {
            $filePath = __DIR__ . '/index.php';
        }
        elseif (substr($pageUrl, -1) === '/') {
            $filePath = rtrim(__DIR__ . $pageUrl, '/') . '/index.php';
        }
        elseif (strpos($pageUrl, '?') !== false) {
            $filePath = null; // Dynamic URL, use fallback
        }
        else {
            $filePath = __DIR__ . str_replace('/', DIRECTORY_SEPARATOR, $pageUrl);
        }

        if ($filePath && file_exists($filePath) && is_file($filePath)) {
            $lastmod = date('Y-m-d\TH:i:sP', filemtime($filePath));
        }
        else {
            $lastmod = $fallbackDate;
        }
    }
    else {
        $lastmod = date('Y-m-d\TH:i:sP', strtotime($lastmod));
    }

    echo "  <url>\n";
    echo "    <loc>" . htmlspecialchars($baseUrl . $pageUrl) . "</loc>\n";
    echo "    <lastmod>" . htmlspecialchars($lastmod) . "</lastmod>\n";
    echo "    <changefreq>" . $changefreq . "</changefreq>\n";
    echo "    <priority>" . $priority . "</priority>\n";
    echo "  </url>\n";
}

echo '</urlset>';
?>
