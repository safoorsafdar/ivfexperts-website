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

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <a href="/tools/ivf-success-calculator" class="card p-8 block hover:border-teal-300 transition-colors group">
            <div class="w-12 h-12 rounded-xl bg-teal-50 flex items-center justify-center mb-5 group-hover:bg-teal-100 transition-colors">
                <i class="fa-solid fa-calculator text-teal-600 text-xl"></i>
            </div>
            <h3 class="text-xl font-bold text-slate-900 mb-2">IVF Success Calculator</h3>
            <p class="text-slate-500 text-sm leading-relaxed">Estimate your IVF success rate by age, diagnosis, and AMH. Based on HFEA 2023 data.</p>
            <span class="inline-flex items-center gap-1 text-teal-600 text-sm font-semibold mt-4">Use tool <i class="fa-solid fa-arrow-right text-xs"></i></span>
        </a>

        <a href="/tools/semen-analysis-interpreter" class="card p-8 block hover:border-teal-300 transition-colors group">
            <div class="w-12 h-12 rounded-xl bg-teal-50 flex items-center justify-center mb-5 group-hover:bg-teal-100 transition-colors">
                <i class="fa-solid fa-microscope text-teal-600 text-xl"></i>
            </div>
            <h3 class="text-xl font-bold text-slate-900 mb-2">Semen Analysis Interpreter</h3>
            <p class="text-slate-500 text-sm leading-relaxed">Check your sperm count, motility, and morphology against WHO 2021 reference values.</p>
            <span class="inline-flex items-center gap-1 text-teal-600 text-sm font-semibold mt-4">Use tool <i class="fa-solid fa-arrow-right text-xs"></i></span>
        </a>

        <a href="/tools/female-fertility-age-clock" class="card p-8 block hover:border-teal-300 transition-colors group">
            <div class="w-12 h-12 rounded-xl bg-teal-50 flex items-center justify-center mb-5 group-hover:bg-teal-100 transition-colors">
                <i class="fa-solid fa-clock text-teal-600 text-xl"></i>
            </div>
            <h3 class="text-xl font-bold text-slate-900 mb-2">Female Fertility Age Clock</h3>
            <p class="text-slate-500 text-sm leading-relaxed">See how your age and AMH relate to ovarian reserve and fertility potential.</p>
            <span class="inline-flex items-center gap-1 text-teal-600 text-sm font-semibold mt-4">Use tool <i class="fa-solid fa-arrow-right text-xs"></i></span>
        </a>

        <a href="/tools/ivf-cost-estimator-pakistan" class="card p-8 block hover:border-teal-300 transition-colors group">
            <div class="w-12 h-12 rounded-xl bg-teal-50 flex items-center justify-center mb-5 group-hover:bg-teal-100 transition-colors">
                <i class="fa-solid fa-receipt text-teal-600 text-xl"></i>
            </div>
            <h3 class="text-xl font-bold text-slate-900 mb-2">IVF Cost Estimator</h3>
            <p class="text-slate-500 text-sm leading-relaxed">Get a transparent PKR cost range for IVF, ICSI, PGT, and add-ons.</p>
            <span class="inline-flex items-center gap-1 text-teal-600 text-sm font-semibold mt-4">Use tool <i class="fa-solid fa-arrow-right text-xs"></i></span>
        </a>

        <a href="/tools/ovulation-calculator-fertile-window" class="card p-8 block hover:border-teal-300 transition-colors group">
            <div class="w-12 h-12 rounded-xl bg-teal-50 flex items-center justify-center mb-5 group-hover:bg-teal-100 transition-colors">
                <i class="fa-solid fa-calendar-days text-teal-600 text-xl"></i>
            </div>
            <h3 class="text-xl font-bold text-slate-900 mb-2">Ovulation Calculator</h3>
            <p class="text-slate-500 text-sm leading-relaxed">Find your fertile window and ovulation date for your next 3 cycles.</p>
            <span class="inline-flex items-center gap-1 text-teal-600 text-sm font-semibold mt-4">Use tool <i class="fa-solid fa-arrow-right text-xs"></i></span>
        </a>

        <a href="/tools/ivf-timeline-calculator" class="card p-8 block hover:border-teal-300 transition-colors group">
            <div class="w-12 h-12 rounded-xl bg-teal-50 flex items-center justify-center mb-5 group-hover:bg-teal-100 transition-colors">
                <i class="fa-solid fa-timeline text-teal-600 text-xl"></i>
            </div>
            <h3 class="text-xl font-bold text-slate-900 mb-2">IVF Timeline Calculator</h3>
            <p class="text-slate-500 text-sm leading-relaxed">Enter your IVF start date to see milestone dates week by week.</p>
            <span class="inline-flex items-center gap-1 text-teal-600 text-sm font-semibold mt-4">Use tool <i class="fa-solid fa-arrow-right text-xs"></i></span>
        </a>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
