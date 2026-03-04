<?php
/**
 * FAQ Schema Helper
 * Usage: define $faqs array before including this file.
 * $faqs = [
 *   ['q' => 'Question text', 'a' => 'Answer text'],
 *   ...
 * ];
 * require_once __DIR__ . '/faq-schema.php';
 */
if (empty($faqs) || !is_array($faqs)) return;

$faqEntities = [];
foreach ($faqs as $item) {
    if (empty($item['q']) || empty($item['a'])) continue;
    $faqEntities[] = [
        '@type' => 'Question',
        'name' => $item['q'],
        'acceptedAnswer' => [
            '@type' => 'Answer',
            'text' => strip_tags($item['a']),
        ],
    ];
}

if (empty($faqEntities)) return;

$faqSchema = [
    '@context' => 'https://schema.org',
    '@type' => 'FAQPage',
    'mainEntity' => $faqEntities,
];

echo '<script type="application/ld+json">' .
    json_encode($faqSchema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) .
    '</script>' . "\n";
