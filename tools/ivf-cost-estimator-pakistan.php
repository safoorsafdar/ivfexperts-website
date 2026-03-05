<?php
$pageTitle = "IVF Cost Estimator Pakistan 2026 — Estimate Your Treatment Cost | IVF Experts";
$metaDescription = "Estimate the cost of your IVF cycle in Pakistan. Select your protocol and required add-ons to get a transparent PKR cost range. Free tool by Dr. Adnan Jabbar, Lahore.";
$breadcrumbs = [
    ['name' => 'Home',  'url' => 'https://ivfexperts.pk/'],
    ['name' => 'Tools', 'url' => 'https://ivfexperts.pk/tools/'],
    ['name' => 'IVF Cost Estimator', 'url' => 'https://ivfexperts.pk/tools/ivf-cost-estimator-pakistan'],
];
include __DIR__ . '/../includes/header.php';
?>

<div class="max-w-3xl mx-auto px-6 py-12">
    <!-- Breadcrumb -->
    <nav class="text-sm text-slate-400 mb-8 flex items-center gap-2 flex-wrap">
        <a href="/" class="hover:text-teal-600">Home</a><span>/</span>
        <a href="/tools/" class="hover:text-teal-600">Tools</a><span>/</span>
        <span class="text-slate-700 font-medium">IVF Cost Estimator</span>
    </nav>

    <div class="mb-8">
        <div class="inline-flex items-center gap-2 bg-teal-50 text-teal-700 text-xs font-bold px-4 py-1.5 rounded-full mb-4 border border-teal-200 uppercase tracking-wider">
            <i class="fa-solid fa-receipt"></i> Free Tool
        </div>
        <h1 class="text-4xl font-extrabold text-slate-900 mb-3">IVF Cost Estimator Pakistan</h1>
        <p class="text-slate-500 text-base">Select your treatment protocol and any add-ons to get a transparent PKR cost range estimate.</p>
    </div>

    <!-- Estimator form -->
    <div class="bg-white border border-slate-200 rounded-2xl p-8 shadow-sm mb-8">
        <div class="space-y-6">

            <!-- Protocol selection -->
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-3">Select your treatment protocol</label>
                <div id="protocol-group" class="space-y-3">

                    <label class="flex items-start gap-4 border border-slate-200 rounded-xl p-4 cursor-pointer hover:border-teal-400 transition-colors">
                        <input type="radio" name="protocol" value="iui" class="mt-1 accent-teal-600">
                        <div>
                            <p class="text-sm font-semibold text-slate-800">IUI (Intrauterine Insemination)</p>
                            <p class="text-xs text-slate-500 mt-0.5">For mild male factor or unexplained infertility with open tubes</p>
                        </div>
                    </label>

                    <label class="flex items-start gap-4 border border-slate-200 rounded-xl p-4 cursor-pointer hover:border-teal-400 transition-colors">
                        <input type="radio" name="protocol" value="ivf" class="mt-1 accent-teal-600">
                        <div>
                            <p class="text-sm font-semibold text-slate-800">IVF (standard)</p>
                            <p class="text-xs text-slate-500 mt-0.5">Conventional IVF with normal sperm parameters</p>
                        </div>
                    </label>

                    <label class="flex items-start gap-4 border border-slate-200 rounded-xl p-4 cursor-pointer hover:border-teal-400 transition-colors">
                        <input type="radio" name="protocol" value="icsi" class="mt-1 accent-teal-600">
                        <div>
                            <p class="text-sm font-semibold text-slate-800">IVF + ICSI</p>
                            <p class="text-xs text-slate-500 mt-0.5">Recommended for male factor infertility or previous fertilisation failure</p>
                        </div>
                    </label>

                    <label class="flex items-start gap-4 border border-slate-200 rounded-xl p-4 cursor-pointer hover:border-teal-400 transition-colors">
                        <input type="radio" name="protocol" value="pgt" class="mt-1 accent-teal-600">
                        <div>
                            <p class="text-sm font-semibold text-slate-800">IVF + ICSI + PGT (Gender Selection / Genetic Testing)</p>
                            <p class="text-xs text-slate-500 mt-0.5">Includes embryo biopsy and genetic laboratory analysis</p>
                        </div>
                    </label>

                </div>
            </div>

            <!-- Add-ons -->
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-3">Add-ons (optional)</label>
                <div class="space-y-3">

                    <label class="flex items-start gap-4 border border-slate-200 rounded-xl p-4 cursor-pointer hover:border-teal-400 transition-colors">
                        <input type="checkbox" class="addon-cb mt-1 accent-teal-600" value="freeze">
                        <div>
                            <p class="text-sm font-semibold text-slate-800">Embryo freezing &amp; first year storage</p>
                        </div>
                    </label>

                    <label class="flex items-start gap-4 border border-slate-200 rounded-xl p-4 cursor-pointer hover:border-teal-400 transition-colors">
                        <input type="checkbox" class="addon-cb mt-1 accent-teal-600" value="microtese">
                        <div>
                            <p class="text-sm font-semibold text-slate-800">Micro-TESE (surgical sperm retrieval for azoospermia)</p>
                        </div>
                    </label>

                    <label class="flex items-start gap-4 border border-slate-200 rounded-xl p-4 cursor-pointer hover:border-teal-400 transition-colors">
                        <input type="checkbox" class="addon-cb mt-1 accent-teal-600" value="prp">
                        <div>
                            <p class="text-sm font-semibold text-slate-800">Ovarian PRP (for poor responders / low AMH)</p>
                        </div>
                    </label>

                    <label class="flex items-start gap-4 border border-slate-200 rounded-xl p-4 cursor-pointer hover:border-teal-400 transition-colors">
                        <input type="checkbox" class="addon-cb mt-1 accent-teal-600" value="frozen_transfer">
                        <div>
                            <p class="text-sm font-semibold text-slate-800">Frozen embryo transfer (FET) in subsequent cycle</p>
                        </div>
                    </label>

                </div>
            </div>

            <button type="button" id="cost-btn"
                    class="btn-primary w-full py-4 text-base font-bold disabled:opacity-40 disabled:cursor-not-allowed"
                    disabled>
                <i class="fa-solid fa-receipt mr-2"></i> Estimate My IVF Cost
            </button>
        </div>
    </div>

    <!-- Result panel (hidden initially) -->
    <div id="cost-result" class="hidden bg-teal-50 border border-teal-200 rounded-2xl p-8 mb-8">
        <p class="text-sm font-semibold text-teal-600 uppercase tracking-wider mb-2">Estimated cost range</p>
        <p class="text-5xl font-extrabold text-teal-700 mb-1" id="cost-total">—</p>
        <p class="text-slate-600 text-sm mb-6">Pakistani Rupees — indicative range including all selected components</p>

        <div id="cost-breakdown" class="bg-white rounded-xl p-5 border border-teal-100 text-sm text-slate-600 leading-relaxed space-y-2 mb-6"></div>

        <p class="text-xs text-slate-500 mb-6">Medication costs are the largest variable in any IVF cycle and are not included in the figures above — they can range from PKR 80,000 to PKR 250,000 depending on your protocol and ovarian response. A full medication breakdown will be provided at your consultation.</p>

        <?php require_once __DIR__ . '/../includes/wa.php'; ?>
        <a href="#" id="cost-wa-cta"
           target="_blank" rel="noopener noreferrer"
           class="btn-primary w-full flex items-center justify-center gap-2 py-4 text-base font-bold">
            <i class="fab fa-whatsapp text-xl"></i> Get a Personalised Cost Quote
        </a>
        <p class="text-xs text-slate-400 mt-4 text-center">Indicative ranges only. Individual costs vary based on clinic, medication response, and clinical complexity. This estimate is not a formal quotation.</p>
    </div>

    <!-- Explainer prose -->
    <div class="prose prose-sm max-w-none text-slate-600">
        <h2 class="text-xl font-bold text-slate-900 mt-0">Why do IVF costs vary so much?</h2>
        <p>Stimulation medication is the single largest variable in any IVF cycle. The number of injections required depends on your ovarian reserve (AMH level) and how your body responds to treatment — this cannot be predicted precisely in advance. Poor responders may require higher doses; those at risk of OHSS may need modified protocols.</p>
        <p>Beyond medication, the total cost depends on whether ICSI is required (essential for male factor infertility), whether embryos need to be frozen for a later transfer, and whether genetic testing (PGT) is requested. Each add-on adds real laboratory and procedural cost.</p>
        <p>A consultation with Dr. Adnan Jabbar will give you a complete, itemised cost breakdown tailored to your specific diagnosis and protocol — with no hidden fees.</p>
    </div>
</div>

<script>
(function () {
    var PROTOCOLS = {
        iui:  { label: 'IUI cycle (including monitoring + procedure)', lo: 35000,  hi: 70000 },
        ivf:  { label: 'IVF cycle (consultation, stimulation, retrieval, transfer, luteal)', lo: 250000, hi: 420000 },
        icsi: { label: 'IVF + ICSI cycle', lo: 290000, hi: 480000 },
        pgt:  { label: 'IVF + ICSI + PGT cycle (incl. biopsy + genetic lab)', lo: 450000, hi: 750000 },
    };
    var ADDONS = {
        freeze:         { label: 'Embryo freezing + 1 year storage', lo: 30000,  hi: 50000 },
        microtese:      { label: 'Micro-TESE procedure', lo: 80000,  hi: 150000 },
        prp:            { label: 'Ovarian PRP', lo: 40000,  hi: 70000 },
        frozen_transfer:{ label: 'Frozen embryo transfer (FET)', lo: 60000,  hi: 100000 },
    };

    function fmt(n) { return 'PKR ' + n.toLocaleString(); }

    // Enable button when protocol selected
    document.querySelectorAll('input[name="protocol"]').forEach(function (r) {
        r.addEventListener('change', function () {
            document.getElementById('cost-btn').disabled = false;
        });
    });

    document.getElementById('cost-btn').addEventListener('click', function () {
        var protocolKey = document.querySelector('input[name="protocol"]:checked');
        if (!protocolKey) return;
        var proto = PROTOCOLS[protocolKey.value];

        var totalLo = proto.lo;
        var totalHi = proto.hi;
        var breakdown = [{ label: proto.label, lo: proto.lo, hi: proto.hi }];

        document.querySelectorAll('.addon-cb:checked').forEach(function (cb) {
            var a = ADDONS[cb.value];
            if (a) {
                totalLo += a.lo;
                totalHi += a.hi;
                breakdown.push({ label: a.label, lo: a.lo, hi: a.hi });
            }
        });

        document.getElementById('cost-total').textContent = fmt(totalLo) + ' \u2013 ' + fmt(totalHi);

        var bkEl = document.getElementById('cost-breakdown');
        bkEl.innerHTML = breakdown.map(function (b) {
            return '<div class="flex justify-between text-sm text-slate-600 border-b border-teal-100 pb-2">' +
                   '<span>' + b.label + '</span>' +
                   '<span class="font-semibold text-slate-800 ml-4 text-right">' + fmt(b.lo) + ' \u2013 ' + fmt(b.hi) + '</span></div>';
        }).join('');

        var msg = 'Hi Dr. Adnan, I used your IVF Cost Estimator. I selected ' + proto.label +
                  ' with an estimated range of ' + fmt(totalLo) + ' \u2013 ' + fmt(totalHi) +
                  '. I would like to get a personalised quote and book a consultation.';
        document.getElementById('cost-wa-cta').href = 'https://wa.me/923111101483?text=' + encodeURIComponent(msg);

        if (window.dataLayer) {
            window.dataLayer.push({
                event: 'tool_used',
                tool_name: 'ivf_cost_estimator',
                protocol: protocolKey.value,
                total_lo: totalLo,
                total_hi: totalHi,
            });
        }

        document.getElementById('cost-result').classList.remove('hidden');
        document.getElementById('cost-result').scrollIntoView({ behavior: 'smooth', block: 'start' });
    });
})();
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
