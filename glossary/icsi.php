<?php
$pageTitle = "What is ICSI (Intracytoplasmic Sperm Injection)? | Fertility Glossary | IVF Experts";
$metaDescription = "ICSI definition, when it is used, and success rates — explained in plain English by Dr. Adnan Jabbar, Fertility Specialist in Lahore, Pakistan.";
$breadcrumbs = [
    ['name' => 'Home',              'url' => 'https://ivfexperts.pk/'],
    ['name' => 'Fertility Glossary','url' => 'https://ivfexperts.pk/glossary/'],
    ['name' => 'ICSI',              'url' => 'https://ivfexperts.pk/glossary/icsi'],
];
$faqs = [
    ['q' => 'What does ICSI stand for?',                 'a' => 'ICSI stands for Intracytoplasmic Sperm Injection — a technique where a single sperm is injected directly into a mature egg.'],
    ['q' => 'Is ICSI better than conventional IVF?',     'a' => 'For couples with normal sperm, ICSI offers no benefit over conventional IVF. ICSI is indicated specifically for male factor infertility where sperm quality makes natural fertilisation in a dish unreliable.'],
    ['q' => 'What fertilisation rate does ICSI achieve?','a' => 'ICSI achieves 70–85% fertilisation per injected egg regardless of sperm quality (ASRM, 2023), compared to 60–70% for conventional IVF.'],
    ['q' => 'Can ICSI be used with surgically retrieved sperm?', 'a' => 'Yes. ICSI is the standard technique when sperm is retrieved surgically via TESE, Micro-TESE, PESA, or TESA procedures for azoospermia.'],
];
include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/faq-schema.php';
?>

<article class="max-w-3xl mx-auto px-6 py-12">
    <!-- Breadcrumb -->
    <nav class="text-sm text-slate-400 mb-8 flex items-center gap-2 flex-wrap">
        <a href="/" class="hover:text-teal-600">Home</a><span>/</span>
        <a href="/glossary/" class="hover:text-teal-600">Glossary</a><span>/</span>
        <span class="text-slate-700 font-medium">ICSI</span>
    </nav>

    <div class="inline-flex items-center gap-2 bg-teal-50 text-teal-700 text-xs font-bold px-3 py-1 rounded-full mb-4 border border-teal-200 uppercase tracking-wider">
        <i class="fa-solid fa-book-open-reader"></i> Fertility Glossary
    </div>

    <h1 class="text-4xl md:text-5xl font-extrabold text-slate-900 mb-3">Intracytoplasmic Sperm Injection (ICSI)</h1>
    <p class="text-slate-400 text-sm mb-8">Last updated: March 2026 · Medically reviewed by Dr. Adnan Jabbar</p>

    <!-- Definition -->
    <div class="bg-teal-50 border-l-4 border-teal-500 rounded-r-2xl p-6 mb-8">
        <p class="text-slate-700 text-lg leading-relaxed">
            <dfn class="font-bold text-slate-900 not-italic">Intracytoplasmic Sperm Injection (ICSI)</dfn> is a specialised form of IVF in which a single sperm is injected directly into a mature egg using a microscopic glass needle. It was developed to overcome severe male factor infertility and achieves fertilisation rates of 70–85% per injected egg regardless of sperm quality.
        </p>
    </div>

    <!-- Stats -->
    <div class="grid sm:grid-cols-3 gap-4 mb-10">
        <div class="bg-white border border-slate-100 rounded-xl p-5 text-center shadow-sm">
            <p class="text-3xl font-extrabold text-teal-700">70–85%</p>
            <p class="text-xs text-slate-500 mt-1">Fertilisation rate per injected egg (ASRM, 2023)</p>
        </div>
        <div class="bg-white border border-slate-100 rounded-xl p-5 text-center shadow-sm">
            <p class="text-3xl font-extrabold text-teal-700">50%+</p>
            <p class="text-xs text-slate-500 mt-1">Of all IVF cycles worldwide now use ICSI (ESHRE, 2022)</p>
        </div>
        <div class="bg-white border border-slate-100 rounded-xl p-5 text-center shadow-sm">
            <p class="text-3xl font-extrabold text-teal-700">~40%</p>
            <p class="text-xs text-slate-500 mt-1">Male factor contribution to couple infertility globally (WHO, 2023)</p>
        </div>
    </div>

    <!-- When is ICSI used -->
    <h2 class="text-2xl font-bold text-slate-900 mb-4">When is ICSI used?</h2>
    <p class="text-slate-600 mb-4 leading-relaxed">ICSI is the treatment of choice when the male partner has severe oligospermia (very low sperm count), poor motility (asthenospermia), abnormal morphology (teratospermia), or azoospermia requiring surgical sperm retrieval. It is also used when a previous conventional IVF cycle had low or failed fertilisation.</p>
    <p class="text-slate-600 mb-8 leading-relaxed">Importantly, large randomised controlled trials — including a Cochrane Review (2021) — have found no benefit to using ICSI over conventional IVF in couples with normal sperm. ICSI should not be used routinely; it is a targeted solution for a specific clinical problem.</p>

    <!-- Related terms -->
    <div class="border-t border-slate-100 pt-8 mb-8">
        <h2 class="text-xl font-bold text-slate-900 mb-4">Related terms</h2>
        <div class="flex flex-wrap gap-2">
            <a href="/glossary/ivf" class="bg-slate-100 hover:bg-teal-50 hover:text-teal-700 text-slate-700 text-sm px-3 py-1.5 rounded-full border border-slate-200 hover:border-teal-200 transition-colors font-medium">IVF</a>
            <a href="/glossary/azoospermia" class="bg-slate-100 hover:bg-teal-50 hover:text-teal-700 text-slate-700 text-sm px-3 py-1.5 rounded-full border border-slate-200 hover:border-teal-200 transition-colors font-medium">Azoospermia</a>
        </div>
    </div>

    <!-- FAQ -->
    <h2 class="text-xl font-bold text-slate-900 mb-4">Frequently asked questions about ICSI</h2>
    <div class="space-y-4 mb-10">
        <?php foreach ($faqs as $faq): ?>
        <details class="group border border-slate-200 rounded-xl overflow-hidden">
            <summary class="flex items-center justify-between px-5 py-4 cursor-pointer font-medium text-slate-800 hover:bg-slate-50 transition-colors list-none">
                <?php echo htmlspecialchars($faq['q']); ?>
                <i class="fa-solid fa-chevron-down text-xs text-slate-400 group-open:rotate-180 transition-transform flex-shrink-0 ml-4"></i>
            </summary>
            <div class="px-5 pb-4 text-slate-600 text-sm leading-relaxed border-t border-slate-100 pt-3">
                <?php echo htmlspecialchars($faq['a']); ?>
            </div>
        </details>
        <?php endforeach; ?>
    </div>

    <!-- CTA -->
    <div class="bg-teal-50 border border-teal-200 rounded-2xl p-8 text-center">
        <p class="text-xl font-bold text-slate-900 mb-2">Is ICSI right for you?</p>
        <p class="text-slate-600 mb-6">Dr. Adnan Jabbar offers free initial consultations via WhatsApp.</p>
        <?php require_once __DIR__ . '/../includes/wa.php'; ?>
        <a href="<?php echo htmlspecialchars(waLink('Hi Dr. Adnan, I read about ICSI and would like to discuss whether it is right for me.')); ?>"
           target="_blank" rel="noopener noreferrer"
           class="btn-primary inline-flex items-center gap-2 px-8 py-4">
            <i class="fab fa-whatsapp text-xl"></i> Ask about ICSI on WhatsApp
        </a>
        <div class="mt-4">
            <a href="/art-procedures/icsi" class="text-sm text-teal-700 font-semibold hover:underline">Read the full ICSI treatment page &rarr;</a>
        </div>
    </div>
</article>

<?php include __DIR__ . '/../includes/footer.php'; ?>
