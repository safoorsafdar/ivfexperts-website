<?php
$pageTitle = "IVF Success Rate Calculator Pakistan | IVF Experts — Dr. Adnan Jabbar";
$metaDescription = "Estimate your IVF success rate based on age, diagnosis, and AMH level. Evidence-based calculator using HFEA 2023 data. Free, by Dr. Adnan Jabbar, Lahore.";
$breadcrumbs = [
    ['name' => 'Home',                   'url' => 'https://ivfexperts.pk/'],
    ['name' => 'Tools',                  'url' => 'https://ivfexperts.pk/tools/'],
    ['name' => 'IVF Success Calculator', 'url' => 'https://ivfexperts.pk/tools/ivf-success-calculator'],
];
include __DIR__ . '/../includes/header.php';
?>

<div class="max-w-3xl mx-auto px-6 py-12">
    <!-- Breadcrumb -->
    <nav class="text-sm text-slate-400 mb-8 flex items-center gap-2 flex-wrap">
        <a href="/" class="hover:text-teal-600">Home</a><span>/</span>
        <a href="/tools/" class="hover:text-teal-600">Tools</a><span>/</span>
        <span class="text-slate-700 font-medium">IVF Success Calculator</span>
    </nav>

    <div class="mb-8">
        <div class="inline-flex items-center gap-2 bg-teal-50 text-teal-700 text-xs font-bold px-4 py-1.5 rounded-full mb-4 border border-teal-200 uppercase tracking-wider">
            <i class="fa-solid fa-calculator"></i> Free Tool
        </div>
        <h1 class="text-4xl font-extrabold text-slate-900 mb-3">IVF Success Rate Calculator</h1>
        <p class="text-slate-500 text-base">Based on HFEA 2023 data. Estimates are per IVF cycle and are for educational purposes only.</p>
    </div>

    <!-- Calculator form -->
    <div class="bg-white border border-slate-200 rounded-2xl p-8 shadow-sm mb-8">
        <form id="ivf-calc-form" class="space-y-6" novalidate>

            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-2">Your age bracket</label>
                <div class="grid grid-cols-5 gap-2" id="age-group">
                    <button type="button" data-age="under35" class="calc-age-btn border border-slate-200 rounded-xl py-3 text-sm font-medium text-slate-600 hover:border-teal-500 hover:text-teal-700 transition-colors text-center">&lt;35</button>
                    <button type="button" data-age="35to37" class="calc-age-btn border border-slate-200 rounded-xl py-3 text-sm font-medium text-slate-600 hover:border-teal-500 hover:text-teal-700 transition-colors text-center">35–37</button>
                    <button type="button" data-age="38to39" class="calc-age-btn border border-slate-200 rounded-xl py-3 text-sm font-medium text-slate-600 hover:border-teal-500 hover:text-teal-700 transition-colors text-center">38–39</button>
                    <button type="button" data-age="40to42" class="calc-age-btn border border-slate-200 rounded-xl py-3 text-sm font-medium text-slate-600 hover:border-teal-500 hover:text-teal-700 transition-colors text-center">40–42</button>
                    <button type="button" data-age="over43" class="calc-age-btn border border-slate-200 rounded-xl py-3 text-sm font-medium text-slate-600 hover:border-teal-500 hover:text-teal-700 transition-colors text-center">43+</button>
                </div>
            </div>

            <div>
                <label for="diagnosis" class="block text-sm font-semibold text-slate-700 mb-2">Primary diagnosis</label>
                <select id="diagnosis" class="w-full border border-slate-200 rounded-xl px-4 py-3 text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-transparent bg-white">
                    <option value="">Select diagnosis…</option>
                    <option value="unexplained">Unexplained infertility</option>
                    <option value="pcos">PCOS (Polycystic Ovary Syndrome)</option>
                    <option value="tubal">Tubal factor / Blocked tubes</option>
                    <option value="male">Male factor (low count / ICSI needed)</option>
                    <option value="dim_reserve">Diminished ovarian reserve (low AMH)</option>
                    <option value="endometriosis">Endometriosis</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">AMH level (optional)</label>
                <p class="text-xs text-slate-400 mb-2">Anti-Müllerian Hormone — from your blood test, in ng/mL</p>
                <input type="number" id="amh" min="0" max="20" step="0.1" placeholder="e.g. 1.8"
                       class="w-full border border-slate-200 rounded-xl px-4 py-3 text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-transparent">
            </div>

            <button type="button" id="calc-btn"
                    class="btn-primary w-full py-4 text-base font-bold disabled:opacity-40 disabled:cursor-not-allowed"
                    disabled>
                <i class="fa-solid fa-calculator mr-2"></i> Calculate My IVF Success Rate
            </button>
        </form>
    </div>

    <!-- Result panel (hidden initially) -->
    <div id="calc-result" class="hidden bg-teal-50 border border-teal-200 rounded-2xl p-8 mb-8">
        <p class="text-sm font-semibold text-teal-600 uppercase tracking-wider mb-2">Estimated success rate per cycle</p>
        <p class="text-5xl font-extrabold text-teal-700 mb-1" id="result-rate">—</p>
        <p class="text-slate-600 text-sm mb-6" id="result-context"></p>
        <div id="result-advice" class="bg-white rounded-xl p-5 border border-teal-100 text-sm text-slate-600 leading-relaxed mb-6"></div>

        <?php require_once __DIR__ . '/../includes/wa.php'; ?>
        <a href="#" id="wa-cta"
           target="_blank" rel="noopener noreferrer"
           class="btn-primary w-full flex items-center justify-center gap-2 py-4 text-base font-bold">
            <i class="fab fa-whatsapp text-xl"></i> Discuss My Results with Dr. Adnan
        </a>
        <p class="text-xs text-slate-400 mt-4 text-center">Estimates based on HFEA 2023 aggregated data. Individual results vary. This tool is not a substitute for a medical consultation.</p>
    </div>

    <!-- How it works -->
    <div class="prose prose-sm max-w-none text-slate-600">
        <h2 class="text-xl font-bold text-slate-900 mt-0">How is the success rate calculated?</h2>
        <p>This calculator uses live birth rate data from the UK Human Fertilisation and Embryology Authority (HFEA) 2023 report — the world's most comprehensive IVF registry. The base rate is set by your age bracket, then adjusted for your primary diagnosis and AMH level.</p>
        <p>All rates are <em>per treatment cycle</em>. Multiple cycles significantly increase cumulative success rates — over 3 cycles, a woman under 35 has a cumulative live birth rate of approximately 65–70%.</p>
        <h2 class="text-xl font-bold text-slate-900">Limitations</h2>
        <p>This estimate does not account for embryo quality, uterine factors, sperm DNA fragmentation, or clinic-specific laboratory standards. It is an educational tool only. A consultation with Dr. Adnan Jabbar will give you a personalised, data-driven assessment.</p>
    </div>
</div>

<script>
(function () {
    // HFEA 2023 base live birth rates by age bracket (per cycle, as decimal)
    var BASE_RATES = {
        under35: 0.32,
        '35to37': 0.25,
        '38to39': 0.19,
        '40to42': 0.11,
        over43:  0.05,
    };

    // Diagnosis multipliers (relative to unexplained baseline)
    var DIAGNOSIS_MULTIPLIERS = {
        unexplained:   1.00,
        pcos:          1.10,
        tubal:         1.00,
        male:          1.05,
        dim_reserve:   0.70,
        endometriosis: 0.85,
    };

    // Diagnosis-specific advice
    var DIAGNOSIS_ADVICE = {
        unexplained:   'With unexplained infertility, IVF success rates are close to the population average for your age. Starting with IUI may be appropriate before escalating to IVF.',
        pcos:          'PCOS patients often have a good number of eggs retrieved. The main risk is Ovarian Hyperstimulation Syndrome (OHSS) — careful stimulation protocols manage this effectively.',
        tubal:         'Blocked fallopian tubes are one of the clearest indications for IVF. Bypassing the tubes entirely, IVF gives you the best available chance of conception.',
        male:          'Male factor infertility is addressed with ICSI, which achieves fertilisation rates of 70–85% per egg regardless of sperm quality. This brings your effective IVF success rate very close to the age-matched baseline.',
        dim_reserve:   'Low ovarian reserve (low AMH) means fewer eggs are retrieved per cycle, which lowers the statistical chance per cycle. Maximising egg collection through optimal stimulation protocols is key.',
        endometriosis: 'Endometriosis can reduce ovarian reserve and implantation success. Surgical treatment before IVF and optimised stimulation protocols can help improve your outcome.',
    };

    var selectedAge = null;

    // Age button selection
    document.querySelectorAll('.calc-age-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            document.querySelectorAll('.calc-age-btn').forEach(function (b) {
                b.classList.remove('border-teal-500', 'text-teal-700', 'bg-teal-50');
            });
            btn.classList.add('border-teal-500', 'text-teal-700', 'bg-teal-50');
            selectedAge = btn.dataset.age;
            updateCalcBtn();
        });
    });

    document.getElementById('diagnosis').addEventListener('change', updateCalcBtn);

    function updateCalcBtn() {
        var diagnosis = document.getElementById('diagnosis').value;
        document.getElementById('calc-btn').disabled = !(selectedAge && diagnosis);
    }

    document.getElementById('calc-btn').addEventListener('click', function () {
        var diagnosis = document.getElementById('diagnosis').value;
        var amh = parseFloat(document.getElementById('amh').value) || null;

        var baseRate = BASE_RATES[selectedAge];
        var multiplier = DIAGNOSIS_MULTIPLIERS[diagnosis] || 1.00;

        // AMH adjustment: low AMH (<0.5) reduces by 15%; low-normal AMH (<1.0) reduces by 8%
        if (amh !== null) {
            if (amh < 0.5) multiplier *= 0.85;
            else if (amh < 1.0) multiplier *= 0.92;
        }

        var rate = baseRate * multiplier;
        var lo = Math.round((rate - 0.03) * 100);
        var hi = Math.round((rate + 0.03) * 100);
        lo = Math.max(lo, 1);
        hi = Math.min(hi, 55);

        // Display result
        document.getElementById('result-rate').textContent = lo + '–' + hi + '%';
        document.getElementById('result-context').textContent =
            'Per single IVF cycle · Based on HFEA 2023 data for your age group and diagnosis';
        document.getElementById('result-advice').innerHTML =
            '<strong class="text-slate-800">What this means for you:</strong> ' + DIAGNOSIS_ADVICE[diagnosis];

        // Update WhatsApp CTA with context
        var ageLabelMap = { under35: 'under 35', '35to37': '35–37', '38to39': '38–39', '40to42': '40–42', over43: '43+' };
        var diagLabelMap = {
            unexplained: 'unexplained infertility', pcos: 'PCOS', tubal: 'blocked tubes',
            male: 'male factor infertility', dim_reserve: 'diminished ovarian reserve', endometriosis: 'endometriosis'
        };
        var msg = 'Hi Dr. Adnan, I used your IVF Success Calculator. I am aged ' + ageLabelMap[selectedAge] +
                  ' with ' + diagLabelMap[diagnosis] + '. My estimated success rate was ' + lo + '–' + hi +
                  '%. I would like to book a consultation.';
        document.getElementById('wa-cta').href = 'https://wa.me/923111101483?text=' + encodeURIComponent(msg);

        // GTM event
        if (window.dataLayer) {
            window.dataLayer.push({
                event: 'tool_used',
                tool_name: 'ivf_success_calculator',
                age_bracket: selectedAge,
                diagnosis: diagnosis,
                result_lo: lo,
                result_hi: hi,
            });
        }

        document.getElementById('calc-result').classList.remove('hidden');
        document.getElementById('calc-result').scrollIntoView({ behavior: 'smooth', block: 'start' });
    });
})();
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
