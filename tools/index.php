<?php
$pageTitle = "Free Fertility Tools | IVF Success Calculator | IVF Experts";
$metaDescription = "Free evidence-based fertility tools by Dr. Adnan Jabbar. Use our IVF Success Rate Calculator to estimate your chances based on age and diagnosis.";
$breadcrumbs = [
    ['name' => 'Home',  'url' => 'https://ivfexperts.pk/'],
    ['name' => 'Tools', 'url' => 'https://ivfexperts.pk/tools/'],
];
include __DIR__ . '/../includes/header.php';
?>

<div class="max-w-5xl mx-auto px-6 py-12">
    <nav class="text-sm text-slate-400 mb-8 flex items-center gap-2">
        <a href="/" class="hover:text-teal-600">Home</a><span>/</span>
        <span class="text-slate-700 font-medium">Free Tools</span>
    </nav>

    <div class="text-center mb-12">
        <div class="inline-flex items-center gap-2 bg-teal-50 text-teal-700 text-xs font-bold px-4 py-1.5 rounded-full mb-4 border border-teal-200 uppercase tracking-wider">
            <i class="fa-solid fa-calculator"></i> Free Tools
        </div>
        <h1 class="text-4xl md:text-5xl font-extrabold text-slate-900 mb-4">Evidence-Based Fertility Tools</h1>
        <p class="text-lg text-slate-500 max-w-2xl mx-auto">Personalised estimates and educational resources to help you understand your fertility journey — free, no sign-up required.</p>
    </div>

    <div class="grid sm:grid-cols-2 gap-6">
        <a href="/tools/ivf-success-calculator" class="group card flex flex-col p-8 hover:border-teal-300 hover:-translate-y-1">
            <div class="w-14 h-14 rounded-2xl bg-teal-50 border border-teal-100 flex items-center justify-center mb-6 text-teal-600 group-hover:bg-teal-600 group-hover:text-white transition-colors">
                <i class="fa-solid fa-chart-line text-2xl"></i>
            </div>
            <h2 class="text-xl font-bold text-slate-900 group-hover:text-teal-700 mb-2">IVF Success Rate Calculator</h2>
            <p class="text-slate-500 text-sm flex-1">Estimate your IVF success probability based on age, diagnosis, and ovarian reserve (AMH). Based on HFEA 2023 data.</p>
            <div class="mt-6 flex items-center gap-2 text-teal-600 text-sm font-semibold group-hover:gap-3 transition-all">
                Try the Calculator <i class="fa-solid fa-arrow-right text-xs"></i>
            </div>
        </a>

        <!-- Placeholder for future tools -->
        <div class="card flex flex-col p-8 opacity-50 cursor-not-allowed border-dashed">
            <div class="w-14 h-14 rounded-2xl bg-slate-100 border border-slate-200 flex items-center justify-center mb-6 text-slate-400">
                <i class="fa-solid fa-clock text-2xl"></i>
            </div>
            <h2 class="text-xl font-bold text-slate-700 mb-2">Ovulation Tracker</h2>
            <p class="text-slate-400 text-sm flex-1">Coming soon — predict your fertile window using your cycle length and last period date.</p>
            <div class="mt-6 text-xs text-slate-300 font-semibold uppercase tracking-wider">Coming Soon</div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
