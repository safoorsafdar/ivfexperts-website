<?php
$pageTitle = "IVF Timeline Calculator — Week-by-Week Milestones | IVF Experts Pakistan";
$metaDescription = "Enter your IVF start date and see every milestone — stimulation, egg retrieval, transfer, and pregnancy test — week by week. Free tool by Dr. Adnan Jabbar, Lahore.";
$breadcrumbs = [
    ['name' => 'Home',  'url' => 'https://ivfexperts.pk/'],
    ['name' => 'Tools', 'url' => 'https://ivfexperts.pk/tools/'],
    ['name' => 'IVF Timeline Calculator', 'url' => 'https://ivfexperts.pk/tools/ivf-timeline-calculator'],
];
include __DIR__ . '/../includes/header.php';
?>

<div class="max-w-3xl mx-auto px-6 py-12">
    <!-- Breadcrumb -->
    <nav class="text-sm text-slate-400 mb-8 flex items-center gap-2 flex-wrap">
        <a href="/" class="hover:text-teal-600">Home</a><span>/</span>
        <a href="/tools/" class="hover:text-teal-600">Tools</a><span>/</span>
        <span class="text-slate-700 font-medium">IVF Timeline Calculator</span>
    </nav>

    <div class="mb-8">
        <div class="inline-flex items-center gap-2 bg-teal-50 text-teal-700 text-xs font-bold px-4 py-1.5 rounded-full mb-4 border border-teal-200 uppercase tracking-wider">
            <i class="fa-solid fa-timeline"></i> Free Tool
        </div>
        <h1 class="text-4xl font-extrabold text-slate-900 mb-3">IVF Timeline Calculator</h1>
        <p class="text-slate-500 text-base">Enter your IVF start date to see estimated milestone dates for your cycle.</p>
    </div>

    <!-- Calculator form -->
    <div class="bg-white border border-slate-200 rounded-2xl p-8 shadow-sm mb-8">
        <div class="space-y-6">

            <div>
                <label for="start-date" class="block text-sm font-semibold text-slate-700 mb-1">IVF start date</label>
                <p class="text-xs text-slate-400 mb-2">Day 1 of stimulation injections (as advised by your doctor)</p>
                <input type="date" id="start-date"
                       class="w-full border border-slate-200 rounded-xl px-4 py-3 text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-transparent bg-white">
            </div>

            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-2">Protocol type</label>
                <div class="grid grid-cols-2 gap-3">
                    <label class="border border-slate-200 rounded-xl p-3 cursor-pointer hover:border-teal-400 flex items-center gap-3 transition-colors">
                        <input type="radio" name="protocol-type" value="fresh" checked class="text-teal-600 focus:ring-teal-500">
                        <span class="text-sm font-medium text-slate-700">Fresh transfer</span>
                    </label>
                    <label class="border border-slate-200 rounded-xl p-3 cursor-pointer hover:border-teal-400 flex items-center gap-3 transition-colors">
                        <input type="radio" name="protocol-type" value="freeze" class="text-teal-600 focus:ring-teal-500">
                        <span class="text-sm font-medium text-slate-700">Freeze-all (FET)</span>
                    </label>
                </div>
            </div>

            <button type="button" id="timeline-btn" class="btn-primary w-full py-4 text-base font-bold">
                <i class="fa-solid fa-timeline mr-2"></i> Generate My IVF Timeline
            </button>
        </div>
    </div>

    <!-- Result panel (hidden initially) -->
    <div id="timeline-result" class="hidden mb-8">
        <h2 class="text-2xl font-bold text-slate-900 mb-6">Your IVF Timeline</h2>

        <div id="timeline-steps" class="relative pl-8 space-y-0 border-l-2 border-slate-200"></div>

        <!-- Amber warning -->
        <div class="bg-amber-50 border border-amber-200 rounded-xl p-5 mt-8 mb-6">
            <div class="flex items-start gap-3">
                <i class="fa-solid fa-triangle-exclamation text-amber-500 mt-0.5 shrink-0"></i>
                <p class="text-sm text-amber-800 leading-relaxed">
                    <strong>Important:</strong> These are estimated dates based on typical IVF protocols. Your actual dates will be adjusted based on your individual response to stimulation, follicle growth, and your doctor's clinical judgement. Always follow the schedule given by your fertility team.
                </p>
            </div>
        </div>

        <!-- WhatsApp CTA -->
        <?php require_once __DIR__ . '/../includes/wa.php'; ?>
        <a href="#" id="timeline-wa-cta"
           target="_blank" rel="noopener noreferrer"
           class="btn-primary w-full flex items-center justify-center gap-2 py-4 text-base font-bold">
            <i class="fab fa-whatsapp text-xl"></i> Book IVF Consultation with Dr. Adnan
        </a>
        <p class="text-xs text-slate-400 mt-4 text-center">Milestone dates are estimates only and are not a substitute for medical advice from your fertility specialist.</p>
    </div>

    <!-- Explainer prose -->
    <div class="prose prose-sm max-w-none text-slate-600">
        <h2 class="text-xl font-bold text-slate-900 mt-0">What does a typical IVF cycle involve?</h2>
        <p>A fresh IVF cycle typically spans 3–4 weeks from the first stimulation injection to the pregnancy blood test. It begins with daily FSH injections to stimulate the ovaries to produce multiple follicles, followed by monitoring scans, a trigger injection, egg retrieval under sedation, fertilisation in the embryology lab, and a blastocyst transfer on day 5. The pregnancy blood test (beta-hCG) is taken approximately two weeks after transfer.</p>
        <p>A freeze-all (FET) cycle extends the overall timeline by 4–6 weeks. After egg retrieval, all viable blastocysts are vitrified and the uterus is rested. In the following menstrual cycle, oestrogen and progesterone are used to prepare the uterine lining before the warmed embryo is transferred. This approach is often preferred for patients at risk of OHSS or where the uterine lining needs additional preparation.</p>
    </div>
</div>

<script>
(function () {
    var MONTHS = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
    function fmtDate(d) { return d.getDate() + ' ' + MONTHS[d.getMonth()] + ' ' + d.getFullYear(); }
    function addDays(date, days) { var d = new Date(date); d.setDate(d.getDate() + days); return d; }

    function buildTimeline(startDate, protocol) {
        var steps = [
            { day: 0,  icon: 'fa-syringe',       color: 'bg-teal-500',   label: 'Day 1: Stimulation begins',
              desc: 'Start daily FSH injections. Baseline ultrasound and blood tests confirm readiness.' },
            { day: 5,  icon: 'fa-circle-dot',     color: 'bg-teal-400',   label: 'Day 5\u20137: First monitoring scan',
              desc: 'Transvaginal ultrasound to measure follicle growth. Dose may be adjusted.' },
            { day: 10, icon: 'fa-circle-dot',     color: 'bg-teal-400',   label: 'Day 10\u201312: Second monitoring scan',
              desc: 'Follicles approaching target size (18\u201320mm). Trigger injection timing decided.' },
            { day: 12, icon: 'fa-bolt',           color: 'bg-yellow-500', label: 'Day 12\u201313: Trigger injection',
              desc: 'hCG or GnRH agonist trigger given at precise time. Egg retrieval scheduled 36 hours later.' },
            { day: 14, icon: 'fa-stethoscope',    color: 'bg-blue-500',   label: 'Day 14\u201315: Egg retrieval',
              desc: 'Outpatient procedure under sedation (~20 minutes). Eggs collected and handed to embryology lab.' },
            { day: 15, icon: 'fa-flask',          color: 'bg-purple-500', label: 'Day 15\u201316: Fertilisation',
              desc: 'Eggs fertilised with partner\'s sperm (IVF or ICSI). Fertilisation check reported next morning.' },
            { day: 19, icon: 'fa-seedling',       color: 'bg-green-500',  label: 'Day 19\u201320: Day 5 blastocyst check',
              desc: 'Embryos graded at blastocyst stage. Best quality selected for transfer or freezing.' },
        ];

        if (protocol === 'fresh') {
            steps.push(
                { day: 19, icon: 'fa-heart',          color: 'bg-pink-500',   label: 'Day 19\u201320: Fresh embryo transfer',
                  desc: 'Best blastocyst transferred to uterus. Procedure takes ~10 minutes, no sedation required.' },
                { day: 33, icon: 'fa-vial',            color: 'bg-rose-500',   label: 'Day 33\u201335: Pregnancy blood test',
                  desc: 'Beta-hCG blood test \u2014 the definitive pregnancy test. Results same day.' }
            );
        } else {
            steps.push(
                { day: 19, icon: 'fa-snowflake',       color: 'bg-blue-400',   label: 'Day 19\u201320: All embryos frozen',
                  desc: 'All viable blastocysts vitrified (flash frozen) for transfer in a subsequent cycle.' },
                { day: 45, icon: 'fa-calendar-check',  color: 'bg-teal-600',   label: '~4\u20136 weeks later: FET prep begins',
                  desc: 'Next menstrual cycle: oestrogen preparation of uterine lining begins (10\u201314 days).' },
                { day: 60, icon: 'fa-heart',            color: 'bg-pink-500',   label: '~Day 60: Frozen embryo transfer (FET)',
                  desc: 'Warmed blastocyst transferred on day 5 of progesterone supplementation.' },
                { day: 74, icon: 'fa-vial',             color: 'bg-rose-500',   label: '~Day 74: Pregnancy blood test',
                  desc: 'Beta-hCG blood test 14 days after FET.' }
            );
        }

        return steps;
    }

    document.getElementById('timeline-btn').addEventListener('click', function () {
        var startVal = document.getElementById('start-date').value;
        if (!startVal) { alert('Please select your IVF start date.'); return; }
        var startDate = new Date(startVal);
        var protocol  = document.querySelector('input[name="protocol-type"]:checked').value;

        var steps = buildTimeline(startDate, protocol);
        var stepsEl = document.getElementById('timeline-steps');
        stepsEl.innerHTML = '';

        steps.forEach(function (step) {
            var date = fmtDate(addDays(startDate, step.day));
            stepsEl.innerHTML +=
                '<div class="relative pb-8 last:pb-0">' +
                '<div class="absolute -left-[calc(2rem+1px)] top-1 w-8 h-8 rounded-full ' + step.color + ' flex items-center justify-center shadow-sm">' +
                '<i class="fa-solid ' + step.icon + ' text-white text-xs"></i></div>' +
                '<div class="bg-white border border-slate-200 rounded-xl p-4 ml-2">' +
                '<div class="flex items-start justify-between gap-4">' +
                '<p class="font-semibold text-sm text-slate-800">' + step.label + '</p>' +
                '<span class="text-xs font-bold text-teal-700 bg-teal-50 px-2 py-1 rounded-lg whitespace-nowrap">' + date + '</span>' +
                '</div>' +
                '<p class="text-xs text-slate-500 mt-1">' + step.desc + '</p>' +
                '</div></div>';
        });

        var msg = 'Hi Dr. Adnan, I used your IVF Timeline Calculator with a start date of ' +
                  fmtDate(startDate) + ' (' + (protocol === 'fresh' ? 'fresh' : 'freeze-all') +
                  ' protocol). I would like to book a consultation to plan my IVF cycle.';
        document.getElementById('timeline-wa-cta').href = 'https://wa.me/923111101483?text=' + encodeURIComponent(msg);

        if (window.dataLayer) {
            window.dataLayer.push({
                event: 'tool_used',
                tool_name: 'ivf_timeline_calculator',
                protocol_type: protocol,
            });
        }

        document.getElementById('timeline-result').classList.remove('hidden');
        document.getElementById('timeline-result').scrollIntoView({ behavior: 'smooth', block: 'start' });
    });
})();
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
