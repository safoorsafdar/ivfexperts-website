<?php
$pageTitle = "Semen Analysis Interpreter — WHO 2021 Reference Values | IVF Experts";
$metaDescription = "Enter your semen analysis results and get an instant interpretation against WHO 2021 reference values. Free tool by Dr. Adnan Jabbar, Fertility Specialist, Lahore.";
$breadcrumbs = [
    ['name' => 'Home',  'url' => 'https://ivfexperts.pk/'],
    ['name' => 'Tools', 'url' => 'https://ivfexperts.pk/tools/'],
    ['name' => 'Semen Analysis Interpreter', 'url' => 'https://ivfexperts.pk/tools/semen-analysis-interpreter'],
];
include __DIR__ . '/../includes/header.php';
?>

<div class="max-w-3xl mx-auto px-6 py-12">
    <!-- Breadcrumb -->
    <nav class="text-sm text-slate-400 mb-8 flex items-center gap-2 flex-wrap">
        <a href="/" class="hover:text-teal-600">Home</a><span>/</span>
        <a href="/tools/" class="hover:text-teal-600">Tools</a><span>/</span>
        <span class="text-slate-700 font-medium">Semen Analysis Interpreter</span>
    </nav>

    <div class="mb-8">
        <div class="inline-flex items-center gap-2 bg-teal-50 text-teal-700 text-xs font-bold px-4 py-1.5 rounded-full mb-4 border border-teal-200 uppercase tracking-wider">
            <i class="fa-solid fa-microscope"></i> Free Tool
        </div>
        <h1 class="text-4xl font-extrabold text-slate-900 mb-3">Semen Analysis Interpreter</h1>
        <p class="text-slate-500 text-base">Enter your results below to see how they compare against WHO 2021 reference values.</p>
    </div>

    <!-- Input form -->
    <div class="bg-white border border-slate-200 rounded-2xl p-8 shadow-sm mb-8">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">

            <div>
                <label for="volume" class="block text-sm font-semibold text-slate-700 mb-1">Volume (mL)</label>
                <p class="text-xs text-slate-400 mb-2">Normal: ≥1.4 mL</p>
                <input type="number" id="volume" min="0" max="20" step="0.1" placeholder="e.g. 2.5"
                       class="w-full border border-slate-200 rounded-xl px-4 py-3 text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-transparent">
            </div>

            <div>
                <label for="concentration" class="block text-sm font-semibold text-slate-700 mb-1">Concentration (million/mL)</label>
                <p class="text-xs text-slate-400 mb-2">Normal: ≥16 million/mL</p>
                <input type="number" id="concentration" min="0" max="500" step="0.1" placeholder="e.g. 45"
                       class="w-full border border-slate-200 rounded-xl px-4 py-3 text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-transparent">
            </div>

            <div>
                <label for="motility" class="block text-sm font-semibold text-slate-700 mb-1">Progressive Motility (%)</label>
                <p class="text-xs text-slate-400 mb-2">Normal: ≥30%</p>
                <input type="number" id="motility" min="0" max="100" step="1" placeholder="e.g. 40"
                       class="w-full border border-slate-200 rounded-xl px-4 py-3 text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-transparent">
            </div>

            <div>
                <label for="morphology" class="block text-sm font-semibold text-slate-700 mb-1">Normal Morphology (%)</label>
                <p class="text-xs text-slate-400 mb-2">Normal: ≥4% (Kruger strict)</p>
                <input type="number" id="morphology" min="0" max="100" step="1" placeholder="e.g. 8"
                       class="w-full border border-slate-200 rounded-xl px-4 py-3 text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-transparent">
            </div>

        </div>

        <div class="mt-6">
            <button type="button" id="sa-btn"
                    class="btn-primary w-full py-4 text-base font-bold">
                <i class="fa-solid fa-microscope mr-2"></i> Interpret My Results
            </button>
        </div>
    </div>

    <!-- Result panel (hidden initially) -->
    <div id="sa-result" class="hidden bg-white border border-slate-200 rounded-2xl p-8 shadow-sm mb-8">
        <h2 class="text-xl font-bold text-slate-900 mb-5">Your Results vs WHO 2021 Reference Values</h2>

        <div id="sa-rows" class="space-y-3 mb-6"></div>

        <div id="sa-summary" class="bg-slate-50 border border-slate-200 rounded-xl p-5 mb-6 text-sm leading-relaxed"></div>

        <?php require_once __DIR__ . '/../includes/wa.php'; ?>
        <a href="#" id="sa-wa-cta"
           target="_blank" rel="noopener noreferrer"
           class="btn-primary w-full flex items-center justify-center gap-2 py-4 text-base font-bold">
            <i class="fab fa-whatsapp text-xl"></i> Discuss My Results with Dr. Adnan
        </a>

        <p class="text-xs text-slate-400 mt-4 text-center">WHO 2021 5th edition reference values (5th percentile of fertile men). This tool does not replace a clinical consultation.</p>
    </div>

    <!-- Explainer section -->
    <div class="prose prose-sm max-w-none text-slate-600">
        <h2 class="text-xl font-bold text-slate-900 mt-0">How to read your semen analysis</h2>
        <p>WHO reference values represent the 5th percentile of fertile men — meaning 95% of men who successfully fathered a child within 12 months had values at or above these thresholds. Values below reference range do not mean conception is impossible; they indicate a reduced probability that warrants clinical review.</p>

        <h2 class="text-xl font-bold text-slate-900">What if one or more parameters are below normal?</h2>
        <p>A single abnormal result should always be repeated after 10–14 days, as semen quality can vary significantly between samples. If results remain abnormal on repeat testing, a consultation with a fertility specialist is recommended. Many cases of impaired semen parameters are treatable, and ICSI can achieve excellent fertilisation rates even with severely compromised sperm.</p>
    </div>
</div>

<script>
(function () {
    var WHO = {
        volume:        { min: 1.4,  label: 'Volume',               unit: 'mL',             condition: 'Hypospermia (low volume)' },
        concentration: { min: 16,   label: 'Concentration',        unit: 'million/mL',     condition: 'Oligospermia (low count)' },
        motility:      { min: 30,   label: 'Progressive Motility', unit: '%',              condition: 'Asthenospermia (low motility)' },
        morphology:    { min: 4,    label: 'Normal Morphology',    unit: '% normal forms', condition: 'Teratospermia (abnormal morphology)' },
    };

    document.getElementById('sa-btn').addEventListener('click', function () {
        var inputs = {
            volume:        parseFloat(document.getElementById('volume').value),
            concentration: parseFloat(document.getElementById('concentration').value),
            motility:      parseFloat(document.getElementById('motility').value),
            morphology:    parseFloat(document.getElementById('morphology').value),
        };

        var rowsEl    = document.getElementById('sa-rows');
        var summaryEl = document.getElementById('sa-summary');
        rowsEl.innerHTML = '';

        var abnormal = [];
        var hasAny   = false;

        Object.keys(WHO).forEach(function (key) {
            var val = inputs[key];
            if (isNaN(val)) return;
            hasAny = true;
            var ref  = WHO[key];
            var pass = val >= ref.min;
            if (!pass) abnormal.push(ref.condition);

            var statusColor = pass ? 'text-green-700 bg-green-50 border-green-200' : 'text-red-700 bg-red-50 border-red-200';
            var statusIcon  = pass ? 'fa-circle-check text-green-500' : 'fa-circle-xmark text-red-500';
            var statusText  = pass ? 'Normal' : 'Below reference';

            rowsEl.innerHTML += '<div class="flex items-center justify-between border rounded-xl px-5 py-4 ' + statusColor + '">' +
                '<div><p class="font-semibold text-sm">' + ref.label + '</p>' +
                '<p class="text-xs mt-0.5">Your value: <strong>' + val + ' ' + ref.unit + '</strong> &nbsp;|&nbsp; Reference: &ge;' + ref.min + ' ' + ref.unit + '</p></div>' +
                '<div class="flex items-center gap-2 text-sm font-bold"><i class="fa-solid ' + statusIcon + '"></i>' + statusText + '</div>' +
                '</div>';
        });

        if (!hasAny) {
            alert('Please enter at least one result value.');
            return;
        }

        var summaryHtml;
        if (abnormal.length === 0) {
            summaryHtml = '<p class="font-bold text-green-800 text-base mb-1"><i class="fa-solid fa-circle-check mr-2"></i>All entered parameters are within WHO 2021 normal range.</p>' +
                          '<p class="text-sm text-slate-600">This is a good sign. If you are still having difficulty conceiving, a full workup including a female fertility assessment and DNA fragmentation index may be worthwhile.</p>';
        } else {
            summaryHtml = '<p class="font-bold text-red-800 text-base mb-2"><i class="fa-solid fa-triangle-exclamation mr-2"></i>' + abnormal.length + ' parameter(s) below WHO reference range</p>' +
                          '<p class="text-sm text-slate-600 mb-3">Conditions identified: <strong>' + abnormal.join(', ') + '</strong>.</p>' +
                          '<p class="text-sm text-slate-600">One abnormal result should be repeated after 10\u201314 days before drawing conclusions. A consultation with Dr. Adnan Jabbar will review the full clinical picture and advise on treatment options.</p>';
        }
        summaryEl.innerHTML = summaryHtml;

        var msg = 'Hi Dr. Adnan, I used your Semen Analysis Interpreter. ' +
            (abnormal.length > 0 ? 'I have below-normal results for: ' + abnormal.join(', ') + '.' : 'All parameters are within normal range but I still need a consultation.') +
            ' I would like to discuss next steps.';
        document.getElementById('sa-wa-cta').href = 'https://wa.me/923111101483?text=' + encodeURIComponent(msg);

        if (window.dataLayer) {
            window.dataLayer.push({
                event: 'tool_used',
                tool_name: 'semen_analysis_interpreter',
                abnormal_count: abnormal.length,
                abnormal_params: abnormal.join('|'),
            });
        }

        document.getElementById('sa-result').classList.remove('hidden');
        document.getElementById('sa-result').scrollIntoView({ behavior: 'smooth', block: 'start' });
    });
})();
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
