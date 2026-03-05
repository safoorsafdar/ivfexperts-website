<?php
$pageTitle = "Fertility Glossary — IVF Terms Explained | IVF Experts";
$metaDescription = "Plain-English definitions of IVF, ICSI, AMH, azoospermia, PCOS and more. Medically reviewed by Dr. Adnan Jabbar, Fertility Specialist in Lahore, Pakistan.";
$breadcrumbs = [
    ['name' => 'Home', 'url' => 'https://ivfexperts.pk/'],
    ['name' => 'Fertility Glossary', 'url' => 'https://ivfexperts.pk/glossary/'],
];
include __DIR__ . '/../includes/header.php';
?>

<div class="max-w-4xl mx-auto px-6 py-12">
    <!-- Breadcrumb -->
    <nav class="text-sm text-slate-400 mb-8 flex items-center gap-2">
        <a href="/" class="hover:text-teal-600">Home</a>
        <span>/</span>
        <span class="text-slate-700 font-medium">Fertility Glossary</span>
    </nav>

    <div class="mb-10">
        <div class="inline-flex items-center gap-2 bg-teal-50 text-teal-700 text-xs font-bold px-4 py-1.5 rounded-full mb-4 border border-teal-200 uppercase tracking-wider">
            <i class="fa-solid fa-book-open-reader"></i> Fertility Glossary
        </div>
        <h1 class="text-4xl md:text-5xl font-extrabold text-slate-900 mb-4">Fertility Terms, Explained</h1>
        <p class="text-lg text-slate-500 max-w-2xl">Plain-language definitions of the medical terms you'll hear during your fertility journey — reviewed by Dr. Adnan Jabbar.</p>
    </div>

    <!-- Term cards -->
    <div class="grid sm:grid-cols-2 gap-4">
        <?php
        $terms = [
            ['slug' => 'ivf',          'label' => 'IVF',          'full' => 'In Vitro Fertilisation',               'desc' => 'How eggs and sperm are united in a laboratory to create embryos.'],
            ['slug' => 'icsi',         'label' => 'ICSI',         'full' => 'Intracytoplasmic Sperm Injection',       'desc' => 'Single sperm injected directly into an egg — the gold standard for male factor infertility.'],
            ['slug' => 'amh',          'label' => 'AMH',          'full' => 'Anti-Müllerian Hormone',                'desc' => 'The key blood test for measuring ovarian reserve.'],
            ['slug' => 'azoospermia',  'label' => 'Azoospermia',  'full' => 'Azoospermia',                           'desc' => 'Complete absence of sperm in ejaculated semen — and the treatment options.'],
            ['slug' => 'pcos',         'label' => 'PCOS',         'full' => 'Polycystic Ovary Syndrome',             'desc' => 'The most common hormonal cause of anovulatory infertility in women.'],
        ];
        foreach ($terms as $t): ?>
        <a href="/glossary/<?php echo $t['slug']; ?>"
           class="group card flex items-start gap-4 p-6 hover:border-teal-300 hover:-translate-y-0.5">
            <div class="w-12 h-12 rounded-xl bg-teal-50 border border-teal-100 flex items-center justify-center flex-shrink-0 text-teal-700 font-bold text-xs text-center group-hover:bg-teal-600 group-hover:text-white transition-colors">
                <?php echo htmlspecialchars($t['label']); ?>
            </div>
            <div>
                <p class="font-bold text-slate-900 group-hover:text-teal-700 transition-colors"><?php echo htmlspecialchars($t['full']); ?></p>
                <p class="text-sm text-slate-500 mt-0.5"><?php echo htmlspecialchars($t['desc']); ?></p>
            </div>
        </a>
        <?php endforeach; ?>
    </div>

    <!-- CTA -->
    <div class="mt-12 bg-teal-50 border border-teal-200 rounded-2xl p-8 text-center">
        <p class="text-xl font-bold text-slate-900 mb-2">Have a question about your fertility?</p>
        <p class="text-slate-600 mb-6">Dr. Adnan Jabbar offers free initial consultations via WhatsApp.</p>
        <?php
            require_once __DIR__ . '/../includes/wa.php';
        ?>
        <a href="<?php echo htmlspecialchars(waLink('Hi Dr. Adnan, I have a question about a fertility term I came across.')); ?>" target="_blank" rel="noopener noreferrer"
           class="btn-primary inline-flex items-center gap-2 px-8 py-4">
            <i class="fab fa-whatsapp text-xl"></i> Chat on WhatsApp
        </a>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
