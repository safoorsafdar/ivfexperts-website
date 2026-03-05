<?php
$pageTitle = "Female Fertility Age Clock — Ovarian Reserve by Age | IVF Experts";
$metaDescription = "See how age affects female fertility and ovarian reserve. Enter your age and optional AMH to get a personalised fertility window assessment. Free tool by Dr. Adnan Jabbar.";
$breadcrumbs = [
    ['name' => 'Home',  'url' => 'https://ivfexperts.pk/'],
    ['name' => 'Tools', 'url' => 'https://ivfexperts.pk/tools/'],
    ['name' => 'Female Fertility Age Clock', 'url' => 'https://ivfexperts.pk/tools/female-fertility-age-clock'],
];
include __DIR__ . '/../includes/header.php';
?>

<div class="max-w-3xl mx-auto px-6 py-12">
    <!-- Breadcrumb -->
    <nav class="text-sm text-slate-400 mb-8 flex items-center gap-2 flex-wrap">
        <a href="/" class="hover:text-teal-600">Home</a><span>/</span>
        <a href="/tools/" class="hover:text-teal-600">Tools</a><span>/</span>
        <span class="text-slate-700 font-medium">Female Fertility Age Clock</span>
    </nav>

    <div class="mb-8">
        <div class="inline-flex items-center gap-2 bg-teal-50 text-teal-700 text-xs font-bold px-4 py-1.5 rounded-full mb-4 border border-teal-200 uppercase tracking-wider">
            <i class="fa-solid fa-clock"></i> Free Tool
        </div>
        <h1 class="text-4xl font-extrabold text-slate-900 mb-3">Female Fertility Age Clock</h1>
        <p class="text-slate-500 text-base">Understand how your age and ovarian reserve relate to fertility — based on published population data.</p>
    </div>

    <!-- Form -->
    <div class="bg-white border border-slate-200 rounded-2xl p-8 shadow-sm mb-8">
        <div class="space-y-6">

            <div>
                <label for="age" class="block text-sm font-semibold text-slate-700 mb-2">Your age</label>
                <input type="number" id="age" min="20" max="50" step="1" placeholder="e.g. 32"
                       class="w-full border border-slate-200 rounded-xl px-4 py-3 text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-transparent">
            </div>

            <div>
                <label for="amh-age" class="block text-sm font-semibold text-slate-700 mb-1">AMH level (optional, ng/mL)</label>
                <p class="text-xs text-slate-400 mb-2">From your blood test — helps personalise the assessment</p>
                <input type="number" id="amh-age" min="0" max="20" step="0.1" placeholder="e.g. 2.1"
                       class="w-full border border-slate-200 rounded-xl px-4 py-3 text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-transparent">
            </div>

            <button type="button" id="age-clock-btn"
                    class="btn-primary w-full py-4 text-base font-bold">
                <i class="fa-solid fa-clock mr-2"></i> Show My Fertility Window
            </button>
        </div>
    </div>

    <!-- Result panel (hidden initially) -->
    <div id="age-clock-result" class="hidden mb-8">
        <div class="bg-white border border-slate-200 rounded-2xl p-8 shadow-sm mb-6">
            <p class="text-xs font-semibold text-teal-600 uppercase tracking-wider mb-3">Your fertility profile</p>
            <p id="age-clock-headline" class="text-3xl font-extrabold text-slate-900 mb-1"></p>
            <p id="age-clock-subline" class="text-sm text-slate-500 mb-5"></p>

            <!-- Fertility bar -->
            <div class="w-full bg-slate-100 rounded-full h-3 mb-6">
                <div id="age-clock-bar" class="h-3 rounded-full transition-all duration-700" style="width: 0%"></div>
            </div>

            <div id="age-clock-detail" class="bg-teal-50 rounded-xl p-5 border border-teal-100 text-sm text-slate-600 leading-relaxed"></div>
        </div>

        <?php require_once __DIR__ . '/../includes/wa.php'; ?>
        <a href="#" id="age-clock-wa-cta"
           target="_blank" rel="noopener noreferrer"
           class="btn-primary w-full flex items-center justify-center gap-2 py-4 text-base font-bold mb-4">
            <i class="fab fa-whatsapp text-xl"></i> Book a Fertility Consultation
        </a>
        <p class="text-xs text-slate-400 text-center">Based on published population data. Individual fertility varies. This tool is not a substitute for a medical consultation and AMH blood test.</p>
    </div>

    <!-- Explainer prose -->
    <div class="prose prose-sm max-w-none text-slate-600">
        <h2 class="text-xl font-bold text-slate-900 mt-0">How does age affect female fertility?</h2>
        <p>Women are born with all the eggs they will ever have — approximately one to two million at birth, declining to around 300,000–500,000 at puberty, and continuing to fall throughout the reproductive years. By the mid-thirties, this decline accelerates and, critically, the proportion of chromosomally normal eggs falls sharply. It is this combination of quantity and quality that explains why natural conception rates and IVF success rates both decline with age.</p>
        <p>Anti-Müllerian Hormone (AMH) is the best single blood marker for ovarian reserve — it reflects the size of the remaining egg pool. However, AMH measures quantity, not quality. Two women with the same AMH may have very different egg quality if they are different ages. This is why both your age and your AMH level together give the most informative picture of your fertility.</p>

        <h2 class="text-xl font-bold text-slate-900">Can I improve my ovarian reserve?</h2>
        <p>No intervention has been shown to increase ovarian reserve once it has declined. However, several modifiable factors affect the rate of decline and overall egg quality: smoking accelerates ovarian ageing and should be stopped; maintaining a healthy BMI improves hormonal balance and IVF outcomes; adequate folate and vitamin D support egg maturation; and avoiding environmental toxins where possible is prudent. For women who wish to preserve their fertility before the significant decline that begins around age 35, egg freezing (oocyte cryopreservation) is the most effective strategy currently available.</p>
    </div>
</div>

<script>
(function () {
    // Age-based fertility profile data (population averages)
    var AGE_DATA = {
        20: { score: 95, label: 'Peak reproductive years', barColor: 'bg-green-500',
              detail: 'You are in the peak reproductive window. Natural conception rates are highest and egg quality is at its best. If you are not trying to conceive now but may want to in the future, this is an ideal time to consider egg freezing.' },
        25: { score: 90, label: 'Excellent fertility', barColor: 'bg-green-500',
              detail: 'Fertility is excellent. Natural conception within 12 months is expected for most women without underlying conditions. IVF success rates if needed are around 35–40% per cycle.' },
        28: { score: 82, label: 'Good fertility', barColor: 'bg-green-400',
              detail: 'Fertility remains good. Natural conception is expected in most cases within 12 months. A fertility assessment at this age can provide useful baseline information, especially if you plan to delay family planning.' },
        30: { score: 75, label: 'Good fertility — gradual decline beginning', barColor: 'bg-green-400',
              detail: 'Fertility is still good but the natural decline has begun. IVF success rates are approximately 32% per cycle. If you have been trying to conceive for more than 12 months without success, a fertility assessment is recommended.' },
        32: { score: 65, label: 'Good fertility — decline accelerating', barColor: 'bg-lime-500',
              detail: 'Fertility remains good but the rate of decline is increasing. If you plan to conceive in the next 2–3 years, a proactive fertility assessment can identify any treatable issues early.' },
        35: { score: 52, label: 'Moderate fertility — timely assessment advised', barColor: 'bg-yellow-400',
              detail: 'Age 35 is a recognised clinical threshold. Egg quality decline accelerates meaningfully after this point. IVF success rates are approximately 25% per cycle. If trying to conceive, a fertility assessment after 6 months (rather than 12) is appropriate.' },
        37: { score: 40, label: 'Reduced fertility — prompt evaluation recommended', barColor: 'bg-orange-400',
              detail: 'Fertility has declined significantly. IVF success rates are approximately 19–25% per cycle. Prompt evaluation is recommended if you have been trying for 3–6 months without success. IVF with PGT-A (genetic testing of embryos) may be recommended.' },
        39: { score: 30, label: 'Reduced fertility — specialist consultation important', barColor: 'bg-orange-500',
              detail: 'Fertility is meaningfully reduced. IVF success rates are approximately 11–19% per cycle. A specialist consultation will assess your specific reserve and advise on the most appropriate treatment pathway, which may include PGT-A.' },
        42: { score: 18, label: 'Low fertility — specialist consultation essential', barColor: 'bg-red-400',
              detail: 'Fertility is significantly reduced and declining rapidly. IVF with own eggs yields approximately 11% per cycle; with PGT-A, the proportion of viable embryos is lower. Donor egg IVF achieves substantially higher success rates (50–60% per cycle) and is worth discussing.' },
        45: { score: 8, label: 'Very low fertility with own eggs', barColor: 'bg-red-600',
              detail: 'Natural conception with own eggs is very unlikely and IVF success with own eggs is approximately 2–5% per cycle. Donor egg IVF offers the most realistic pathway to pregnancy, with success rates of 50–60% per cycle regardless of recipient age.' },
    };

    function getProfile(age) {
        var keys = Object.keys(AGE_DATA).map(Number).sort(function(a,b){return a-b;});
        for (var i = keys.length - 1; i >= 0; i--) {
            if (age >= keys[i]) return AGE_DATA[keys[i]];
        }
        return AGE_DATA[20];
    }

    function getAmhNote(amh, age) {
        if (amh === null) return '';
        if (amh >= 3.5) return ' Your AMH of ' + amh + ' ng/mL indicates a good ovarian reserve for your age — this is reassuring.';
        if (amh >= 1.0) return ' Your AMH of ' + amh + ' ng/mL is within the normal range for your age group.';
        if (amh >= 0.5) return ' Your AMH of ' + amh + ' ng/mL is low-normal — ovarian reserve may be slightly reduced. A full consultation will give context.';
        if (amh >= 0.3) return ' Your AMH of ' + amh + ' ng/mL indicates reduced ovarian reserve. This affects IVF egg yield but does not mean conception is impossible.';
        return ' Your AMH of ' + amh + ' ng/mL indicates very low ovarian reserve. A specialist consultation is recommended promptly to discuss options.';
    }

    document.getElementById('age-clock-btn').addEventListener('click', function () {
        var age = parseInt(document.getElementById('age').value);
        var amh = parseFloat(document.getElementById('amh-age').value) || null;

        if (!age || age < 20 || age > 55) {
            alert('Please enter a valid age between 20 and 55.');
            return;
        }

        var profile  = getProfile(age);
        var amhNote  = getAmhNote(amh, age);

        document.getElementById('age-clock-headline').textContent = profile.label;
        document.getElementById('age-clock-subline').textContent  = 'Based on population data for age ' + age;
        document.getElementById('age-clock-bar').style.width      = profile.score + '%';
        document.getElementById('age-clock-bar').className        = 'h-3 rounded-full transition-all duration-700 ' + profile.barColor;
        document.getElementById('age-clock-detail').innerHTML     =
            '<strong class="text-slate-800">What this means:</strong> ' + profile.detail + amhNote;

        var msg = 'Hi Dr. Adnan, I used your Female Fertility Age Clock. I am ' + age + ' years old' +
            (amh ? ' with an AMH of ' + amh + ' ng/mL' : '') +
            '. I would like to book a fertility consultation.';
        document.getElementById('age-clock-wa-cta').href = 'https://wa.me/923111101483?text=' + encodeURIComponent(msg);

        if (window.dataLayer) {
            window.dataLayer.push({
                event: 'tool_used',
                tool_name: 'female_fertility_age_clock',
                age: age,
                amh_provided: amh !== null,
                fertility_score: profile.score,
            });
        }

        document.getElementById('age-clock-result').classList.remove('hidden');
        document.getElementById('age-clock-result').scrollIntoView({ behavior: 'smooth', block: 'start' });
    });
})();
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
