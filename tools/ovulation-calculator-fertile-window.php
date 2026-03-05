<?php
$pageTitle = "Ovulation Calculator & Fertile Window | IVF Experts Pakistan";
$metaDescription = "Calculate your fertile window and ovulation date for up to 3 cycles. Enter your last period date and cycle length. Free tool by Dr. Adnan Jabbar, Lahore.";
$breadcrumbs = [
    ['name' => 'Home',                 'url' => 'https://ivfexperts.pk/'],
    ['name' => 'Tools',                'url' => 'https://ivfexperts.pk/tools/'],
    ['name' => 'Ovulation Calculator', 'url' => 'https://ivfexperts.pk/tools/ovulation-calculator-fertile-window'],
];
include __DIR__ . '/../includes/header.php';
?>

<div class="max-w-3xl mx-auto px-6 py-12">
    <!-- Breadcrumb -->
    <nav class="text-sm text-slate-400 mb-8 flex items-center gap-2 flex-wrap">
        <a href="/" class="hover:text-teal-600">Home</a><span>/</span>
        <a href="/tools/" class="hover:text-teal-600">Tools</a><span>/</span>
        <span class="text-slate-700 font-medium">Ovulation Calculator</span>
    </nav>

    <div class="mb-8">
        <div class="inline-flex items-center gap-2 bg-teal-50 text-teal-700 text-xs font-bold px-4 py-1.5 rounded-full mb-4 border border-teal-200 uppercase tracking-wider">
            <i class="fa-solid fa-calendar-days"></i> Free Tool
        </div>
        <h1 class="text-4xl font-extrabold text-slate-900 mb-3">Ovulation Calculator</h1>
        <p class="text-slate-500 text-base">Find your fertile window and estimated ovulation date for your next 3 cycles.</p>
    </div>

    <!-- Calculator form -->
    <div class="bg-white border border-slate-200 rounded-2xl p-8 shadow-sm mb-8">

        <div class="space-y-6">

            <div>
                <label for="lmp" class="block text-sm font-semibold text-slate-700 mb-2">First day of last period</label>
                <input type="date" id="lmp"
                       class="w-full border border-slate-200 rounded-xl px-4 py-3 text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-transparent bg-white">
            </div>

            <div>
                <label for="cycle-length" class="block text-sm font-semibold text-slate-700 mb-1">Average cycle length (days)</label>
                <p class="text-xs text-slate-400 mb-2">Most women have a cycle of 24–35 days. Default is 28.</p>
                <input type="number" id="cycle-length" min="21" max="45" step="1" value="28"
                       class="w-full border border-slate-200 rounded-xl px-4 py-3 text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-transparent">
            </div>

            <button type="button" id="ovul-btn" class="btn-primary w-full py-4 text-base font-bold">
                <i class="fa-solid fa-calendar-days mr-2"></i> Calculate My Fertile Windows
            </button>

        </div>
    </div>

    <!-- Result panel (hidden initially) -->
    <div id="ovul-result" class="hidden mb-8">
        <h2 class="text-2xl font-bold text-slate-900 mb-6">Your Fertile Windows — Next 3 Cycles</h2>

        <div id="ovul-cycles" class="space-y-4 mb-6"></div>

        <div class="bg-teal-50 border border-teal-100 rounded-2xl p-6 mb-6">
            <p class="text-sm text-teal-800 leading-relaxed">
                <strong>How to use this:</strong> Your fertile window is the 5-day window before ovulation plus ovulation day itself. Intercourse on any of these days — especially the 2–3 days before ovulation — gives you the highest chance of conception. The fertile window shown assumes ovulation occurs 14 days before your next period.
            </p>
        </div>

        <?php require_once __DIR__ . '/../includes/wa.php'; ?>
        <a href="#" id="ovul-wa-cta"
           target="_blank" rel="noopener noreferrer"
           class="btn-primary w-full flex items-center justify-center gap-2 py-4 text-base font-bold mb-4">
            <i class="fab fa-whatsapp text-xl"></i> Get Expert Guidance from Dr. Adnan
        </a>

        <p class="text-xs text-slate-400 text-center">These calculations assume a regular menstrual cycle. Irregular cycles, PCOS, or other conditions require specialist evaluation for accurate tracking.</p>
    </div>

    <!-- Explainer prose -->
    <div class="prose prose-sm max-w-none text-slate-600">
        <h2 class="text-xl font-bold text-slate-900 mt-0">How accurate are ovulation calculators?</h2>
        <p>The calendar method works best for women with consistent, regular cycles. It estimates ovulation as occurring 14 days before your next expected period. However, this assumption does not hold for everyone. Conditions like PCOS can cause highly unpredictable cycle lengths, making calendar-based tracking unreliable. For more precision, LH surge urine tests (ovulation predictor kits) detect the hormonal surge that triggers ovulation 24–36 hours in advance. The most accurate method is transvaginal ultrasound follicle tracking performed at a fertility clinic, which can confirm the exact timing of ovulation as it happens.</p>

        <h2 class="text-xl font-bold text-slate-900">When should I see a specialist?</h2>
        <p>If you have been trying to conceive for 12 months without success (or 6 months if you are over 35), a fertility evaluation is recommended. You should seek earlier assessment if you have irregular or absent periods, a history of painful periods or endometriosis, a previous pelvic infection or surgery, or known PCOS or thyroid disease. Dr. Adnan Jabbar offers consultations in Lahore and via teleconsultation across Pakistan.</p>
    </div>
</div>

<script>
(function () {
    var MONTHS = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];

    function fmtDate(d) {
        return d.getDate() + ' ' + MONTHS[d.getMonth()] + ' ' + d.getFullYear();
    }

    function addDays(date, days) {
        var d = new Date(date);
        d.setDate(d.getDate() + days);
        return d;
    }

    document.getElementById('ovul-btn').addEventListener('click', function () {
        var lmpVal = document.getElementById('lmp').value;
        var cycleLen = parseInt(document.getElementById('cycle-length').value) || 28;

        if (!lmpVal) { alert('Please enter the first day of your last period.'); return; }
        if (cycleLen < 21 || cycleLen > 45) { alert('Please enter a cycle length between 21 and 45 days.'); return; }

        var lmp = new Date(lmpVal);
        var cyclesEl = document.getElementById('ovul-cycles');
        cyclesEl.innerHTML = '';

        for (var i = 0; i < 3; i++) {
            var cycleStart  = addDays(lmp, cycleLen * i);
            var ovulDay     = addDays(cycleStart, cycleLen - 14);
            var fertStart   = addDays(ovulDay, -5);
            var fertEnd     = ovulDay;

            cyclesEl.innerHTML +=
                '<div class="bg-white border border-slate-200 rounded-2xl p-6">' +
                '<p class="text-xs font-bold text-teal-600 uppercase tracking-wider mb-3">Cycle ' + (i + 1) + ' — Period starts ' + fmtDate(cycleStart) + '</p>' +
                '<div class="grid grid-cols-2 gap-4">' +
                '<div class="bg-teal-50 rounded-xl p-4">' +
                '<p class="text-xs text-teal-600 font-semibold mb-1">Fertile Window</p>' +
                '<p class="text-base font-bold text-teal-800">' + fmtDate(fertStart) + '</p>' +
                '<p class="text-xs text-slate-500">to</p>' +
                '<p class="text-base font-bold text-teal-800">' + fmtDate(fertEnd) + '</p>' +
                '</div>' +
                '<div class="bg-slate-50 rounded-xl p-4">' +
                '<p class="text-xs text-slate-500 font-semibold mb-1">Estimated Ovulation</p>' +
                '<p class="text-base font-bold text-slate-800">' + fmtDate(ovulDay) + '</p>' +
                '<p class="text-xs text-slate-400 mt-1">Day ' + (cycleLen - 14) + ' of cycle</p>' +
                '</div>' +
                '</div>' +
                '</div>';
        }

        var msg = 'Hi Dr. Adnan, I used your Ovulation Calculator with a ' + cycleLen + '-day cycle. I would like guidance on timing and whether I need a fertility assessment.';
        document.getElementById('ovul-wa-cta').href = 'https://wa.me/923111101483?text=' + encodeURIComponent(msg);

        if (window.dataLayer) {
            window.dataLayer.push({
                event: 'tool_used',
                tool_name: 'ovulation_calculator',
                cycle_length: cycleLen,
            });
        }

        document.getElementById('ovul-result').classList.remove('hidden');
        document.getElementById('ovul-result').scrollIntoView({ behavior: 'smooth', block: 'start' });
    });
})();
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
