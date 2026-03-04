<?php
// Reusable author attribution block for treatment pages and blog articles.
// Set $authorByline = 'Reviewed by' OR 'Written by' before including (default: 'Medically reviewed by')
// Set $articleLastUpdated = 'March 2026' before including (optional)
$authorByline     = isset($authorByline) ? $authorByline : 'Medically reviewed by';
$articleLastUpdated = isset($articleLastUpdated) ? $articleLastUpdated : 'March 2026';
?>
<div class="mt-10 pt-8 border-t border-slate-200 flex items-start gap-5 bg-slate-50 rounded-2xl p-6" itemscope itemtype="https://schema.org/Person">
    <img src="/assets/images/dr-adnan.jpg"
         alt="Dr. Adnan Jabbar — Fertility Specialist & Clinical Embryologist"
         class="w-16 h-16 rounded-full object-cover flex-shrink-0 border-2 border-teal-100"
         itemprop="image">
    <div>
        <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-0.5"><?php echo htmlspecialchars($authorByline); ?></p>
        <p class="text-base font-bold text-slate-900" itemprop="name">Dr. Adnan Jabbar</p>
        <p class="text-xs text-slate-500" itemprop="jobTitle">MBBS, FCPS (Obs & Gyn) · Fertility Consultant & Clinical Embryologist</p>
        <p class="text-sm text-slate-600 mt-2 leading-relaxed" itemprop="description">
            15+ years of experience in IVF, ICSI, and reproductive medicine. Dual-trained as a Fertility Consultant and Clinical Embryologist, serving patients in Lahore, Karachi, and Islamabad.
        </p>
        <div class="flex items-center gap-4 mt-3">
            <a href="/about/" class="text-xs text-teal-700 font-semibold hover:underline">View full profile &rarr;</a>
            <?php if (!empty($articleLastUpdated)): ?>
            <span class="text-xs text-slate-400">Last updated: <?php echo htmlspecialchars($articleLastUpdated); ?></span>
            <?php endif; ?>
        </div>
    </div>
</div>
