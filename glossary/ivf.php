<?php
$pageTitle = "What is IVF (In Vitro Fertilisation)? | Fertility Glossary | IVF Experts";
$metaDescription = "IVF definition, how it works, and success rates — explained in plain English by Dr. Adnan Jabbar, Fertility Specialist in Lahore, Pakistan.";
$breadcrumbs = [
    ['name' => 'Home',              'url' => 'https://ivfexperts.pk/'],
    ['name' => 'Fertility Glossary','url' => 'https://ivfexperts.pk/glossary/'],
    ['name' => 'IVF',               'url' => 'https://ivfexperts.pk/glossary/ivf'],
];
$faqs = [
    ['q' => 'What does IVF stand for?',                 'a' => 'IVF stands for In Vitro Fertilisation — "in vitro" means "in glass" in Latin, referring to fertilisation happening outside the body.'],
    ['q' => 'How many cycles of IVF does it take?',     'a' => 'Many patients conceive within 1–3 cycles. Success per cycle is 32% for women under 35 (HFEA, 2023), declining with age.'],
    ['q' => 'Is IVF painful?',                          'a' => 'The egg retrieval procedure uses sedation; mild cramping is common afterwards. Hormone injections cause bloating in some patients.'],
    ['q' => 'How long does one IVF cycle take?',        'a' => 'A single IVF cycle takes approximately 4–6 weeks from the start of stimulation to pregnancy test.'],
];
include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/faq-schema.php';
?>

<article class="max-w-3xl mx-auto px-6 py-12">
    <!-- Breadcrumb -->
    <nav class="text-sm text-slate-400 mb-8 flex items-center gap-2 flex-wrap">
        <a href="/" class="hover:text-teal-600">Home</a><span>/</span>
        <a href="/glossary/" class="hover:text-teal-600">Glossary</a><span>/</span>
        <span class="text-slate-700 font-medium">IVF</span>
    </nav>

    <div class="inline-flex items-center gap-2 bg-teal-50 text-teal-700 text-xs font-bold px-3 py-1 rounded-full mb-4 border border-teal-200 uppercase tracking-wider">
        <i class="fa-solid fa-book-open-reader"></i> Fertility Glossary
    </div>

    <h1 class="text-4xl md:text-5xl font-extrabold text-slate-900 mb-3">In Vitro Fertilisation (IVF)</h1>
    <p class="text-slate-400 text-sm mb-8">Last updated: March 2026 · Medically reviewed by Dr. Adnan Jabbar</p>

    <!-- Definition -->
    <div class="bg-teal-50 border-l-4 border-teal-500 rounded-r-2xl p-6 mb-8">
        <p class="text-slate-700 text-lg leading-relaxed">
            <dfn class="font-bold text-slate-900 not-italic">In Vitro Fertilisation (IVF)</dfn> is a fertility treatment where eggs are retrieved from the ovaries, fertilised with sperm in a laboratory, and the resulting embryo is transferred into the uterus. IVF is indicated when simpler treatments have failed or when structural barriers — such as blocked fallopian tubes or severe male factor — make natural conception impossible.
        </p>
    </div>

    <!-- Stats -->
    <div class="grid sm:grid-cols-3 gap-4 mb-10">
        <div class="bg-white border border-slate-100 rounded-xl p-5 text-center shadow-sm">
            <p class="text-3xl font-extrabold text-teal-700">32%</p>
            <p class="text-xs text-slate-500 mt-1">Live birth rate per cycle, women under 35 (HFEA, 2023)</p>
        </div>
        <div class="bg-white border border-slate-100 rounded-xl p-5 text-center shadow-sm">
            <p class="text-3xl font-extrabold text-teal-700">~5M</p>
            <p class="text-xs text-slate-500 mt-1">Babies born worldwide through IVF since 1978 (ESHRE, 2022)</p>
        </div>
        <div class="bg-white border border-slate-100 rounded-xl p-5 text-center shadow-sm">
            <p class="text-3xl font-extrabold text-teal-700">4–6 wks</p>
            <p class="text-xs text-slate-500 mt-1">Typical duration of a single IVF cycle</p>
        </div>
    </div>

    <!-- How it works -->
    <h2 class="text-2xl font-bold text-slate-900 mb-4">How does IVF work?</h2>
    <ol class="space-y-3 mb-8 text-slate-600">
        <li class="flex gap-3"><span class="w-6 h-6 rounded-full bg-teal-600 text-white text-xs font-bold flex items-center justify-center flex-shrink-0 mt-0.5">1</span><span><strong class="text-slate-800">Ovarian stimulation</strong> — Injectable hormones stimulate the ovaries to produce multiple mature eggs over 10–14 days.</span></li>
        <li class="flex gap-3"><span class="w-6 h-6 rounded-full bg-teal-600 text-white text-xs font-bold flex items-center justify-center flex-shrink-0 mt-0.5">2</span><span><strong class="text-slate-800">Egg retrieval</strong> — Eggs are collected via a minor ultrasound-guided procedure under sedation.</span></li>
        <li class="flex gap-3"><span class="w-6 h-6 rounded-full bg-teal-600 text-white text-xs font-bold flex items-center justify-center flex-shrink-0 mt-0.5">3</span><span><strong class="text-slate-800">Fertilisation</strong> — Eggs are mixed with sperm (or injected via ICSI) in the embryology lab and monitored for 5 days.</span></li>
        <li class="flex gap-3"><span class="w-6 h-6 rounded-full bg-teal-600 text-white text-xs font-bold flex items-center justify-center flex-shrink-0 mt-0.5">4</span><span><strong class="text-slate-800">Embryo transfer</strong> — The highest-quality blastocyst is transferred into the uterus; surplus embryos may be frozen for future cycles.</span></li>
    </ol>

    <!-- Related terms -->
    <div class="border-t border-slate-100 pt-8 mb-8">
        <h2 class="text-xl font-bold text-slate-900 mb-4">Related terms</h2>
        <div class="flex flex-wrap gap-2">
            <a href="/glossary/icsi" class="bg-slate-100 hover:bg-teal-50 hover:text-teal-700 text-slate-700 text-sm px-3 py-1.5 rounded-full border border-slate-200 hover:border-teal-200 transition-colors font-medium">ICSI</a>
            <a href="/glossary/amh" class="bg-slate-100 hover:bg-teal-50 hover:text-teal-700 text-slate-700 text-sm px-3 py-1.5 rounded-full border border-slate-200 hover:border-teal-200 transition-colors font-medium">AMH</a>
            <a href="/glossary/pcos" class="bg-slate-100 hover:bg-teal-50 hover:text-teal-700 text-slate-700 text-sm px-3 py-1.5 rounded-full border border-slate-200 hover:border-teal-200 transition-colors font-medium">PCOS</a>
        </div>
    </div>

    <!-- FAQ -->
    <h2 class="text-xl font-bold text-slate-900 mb-4">Frequently asked questions about IVF</h2>
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
        <p class="text-xl font-bold text-slate-900 mb-2">Ready to explore IVF?</p>
        <p class="text-slate-600 mb-6">Dr. Adnan Jabbar offers free initial consultations via WhatsApp.</p>
        <?php require_once __DIR__ . '/../includes/wa.php'; ?>
        <a href="<?php echo htmlspecialchars(waLink('Hi Dr. Adnan, I read about IVF and would like to discuss whether it is right for me.')); ?>"
           target="_blank" rel="noopener noreferrer"
           class="btn-primary inline-flex items-center gap-2 px-8 py-4">
            <i class="fab fa-whatsapp text-xl"></i> Ask about IVF on WhatsApp
        </a>
        <div class="mt-4">
            <a href="/art-procedures/ivf" class="text-sm text-teal-700 font-semibold hover:underline">Read the full IVF treatment page &rarr;</a>
        </div>
    </div>
</article>

<?php include __DIR__ . '/../includes/footer.php'; ?>
