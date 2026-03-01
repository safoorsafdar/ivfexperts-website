<!DOCTYPE html>
<?php include("seo.php"); ?>
<html lang="en">
<head>
<!-- Google tag (gtag.js) -->
<script async src="https://www.googletagmanager.com/gtag/js?id=G-HQ78PRNQWM"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());
  gtag('config', 'G-HQ78PRNQWM');
</script>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($pageTitle) ?></title>
<meta name="description" content="<?= htmlspecialchars($metaDescription) ?>">
<meta name="robots" content="index, follow">
<link rel="canonical" href="<?= $currentUrl ?>">
<!-- Open Graph -->
<meta property="og:type" content="website">
<meta property="og:title" content="<?= htmlspecialchars($pageTitle) ?>">
<meta property="og:description" content="<?= htmlspecialchars($metaDescription) ?>">
<meta property="og:url" content="<?= $currentUrl ?>">
<meta property="og:site_name" content="<?= $siteName ?>">
<!-- Twitter -->
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="<?= htmlspecialchars($pageTitle) ?>">
<meta name="twitter:description" content="<?= htmlspecialchars($metaDescription) ?>">
<!-- Preconnect & Preloads -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="preconnect" href="https://cdnjs.cloudflare.com" crossorigin>
<link rel="preload" href="/assets/css/style.css?v=2" as="style" fetchpriority="high">
<link rel="preload" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap" as="style">
<link rel="preload" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/webfonts/fa-solid-900.woff2" as="font" type="font/woff2" crossorigin>
<!-- Core Styles -->
<link rel="stylesheet" href="/assets/css/style.css?v=2">
<script src="/assets/js/app.js?v=4" defer></script>
<!-- Deferred Non-Critical Styles -->
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap" rel="stylesheet" media="print" onload="this.media='all'">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" media="print" onload="this.media='all'">
<noscript>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</noscript>
<!-- Physician + Organization Schema -->
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@graph": [
    {
      "@type": "MedicalBusiness",
      "@id": "https://ivfexperts.pk/#organization",
      "name": "IVF Experts",
      "url": "https://ivfexperts.pk",
      "telephone": "+923111101483",
      "medicalSpecialty": "Reproductive Medicine"
    },
    {
      "@type": "Physician",
      "@id": "https://ivfexperts.pk/#physician",
      "name": "Dr. Adnan Jabbar",
      "jobTitle": "Fertility Specialist & Clinical Embryologist",
      "medicalSpecialty": "Reproductive Medicine",
      "worksFor": {
        "@id": "https://ivfexperts.pk/#organization"
      },
      "areaServed": "Pakistan"
    }
  ]
}
</script>
<!-- Dynamic Page Schema -->
<?php if(isset($schemaType) && ($schemaType == 'MedicalCondition' || $schemaType == 'MedicalProcedure')): ?>
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "<?= $schemaType ?>",
  "name": "<?= htmlspecialchars($pageTitle) ?>",
  "description": "<?= htmlspecialchars($metaDescription) ?>",
  "url": "<?= $currentUrl ?>",
  "relevantSpecialty": {
    "@type": "MedicalSpecialty",
    "name": "<?= $medicalSpecialty ?>"
  }
  <?php if($schemaType == 'MedicalProcedure'): ?>
  ,"provider": {
    "@id": "https://ivfexperts.pk/#physician"
  }
  <?php elseif($schemaType == 'MedicalCondition'): ?>
  ,"possibleTreatment": {
    "@type": "MedicalTherapy",
    "name": "Consultation with Dr. Adnan Jabbar"
  }
  <?php endif; ?>
}
</script>
<?php endif; ?>
<!-- Breadcrumb Schema -->
<script type="application/ld+json">
{
"@context": "https://schema.org",
"@type": "BreadcrumbList",
"itemListElement": [
<?php foreach($breadcrumbs as $index => $crumb): ?>
{
"@type": "ListItem",
"position": <?= $index + 1 ?>,
"name": "<?= $crumb['name'] ?>",
"item": "<?= $crumb['url'] ?>"
}<?= $index + 1 < count($breadcrumbs) ? "," : "" ?>
<?php endforeach; ?>
]
}
</script>
<!-- JavaScript for dynamic mega menu positioning -->
<script>
document.addEventListener('DOMContentLoaded', () => {
  const groups = document.querySelectorAll('nav .group');
  groups.forEach(group => {
    const dropdown = group.querySelector('.mega-dropdown');
    if (!dropdown) return;
    group.addEventListener('mouseenter', () => {
      const triggerRect = group.getBoundingClientRect();
      const dropdownWidth = dropdown.offsetWidth;
      const viewportWidth = window.innerWidth;
      let idealLeft = triggerRect.left + (triggerRect.width / 2) - (dropdownWidth / 2);
      const margin = 16;
      if (idealLeft < margin) {
        idealLeft = margin;
      }
      if (idealLeft + dropdownWidth > viewportWidth - margin) {
        idealLeft = viewportWidth - dropdownWidth - margin;
      }
      dropdown.style.left = `${idealLeft - triggerRect.left}px`;
    });
  });
});
</script>
</head>
<body class="bg-white text-gray-800 font-inter">
<header class="fixed top-0 left-0 w-full bg-white border-b border-gray-200 z-50">
<div class="max-w-7xl mx-auto px-6 py-4 flex justify-between items-center">
  <!-- LOGO -->
  <a href="/" class="text-2xl font-extrabold tracking-tight text-teal-700">
    IVF Experts
  </a>
  <!-- NAV -->
  <nav class="hidden md:flex items-center gap-x-10 text-sm font-semibold text-gray-700">
    <a href="/" class="hover:text-teal-600 transition">Home</a>
    <!-- ================= ABOUT DROPDOWN ================= -->
    <div class="relative group inline-block">
      <a href="/about/" class="hover:text-teal-600 transition">About</a>
      <div class="mega-dropdown absolute top-full left-0 w-[320px] opacity-0 invisible group-hover:opacity-100 group-hover:visible transition duration-200 pointer-events-none group-hover:pointer-events-auto z-50">
        <div class="bg-white shadow-2xl border border-gray-200 rounded-xl mt-4 py-3">
          <a href="/about/" class="flex items-center gap-3 px-5 py-3 hover:bg-teal-50 transition-colors text-gray-700">
            <div class="w-8 h-8 bg-teal-100 rounded-lg flex items-center justify-center"><i class="fa-solid fa-user-doctor text-teal-600 text-sm"></i></div>
            <div><div class="font-semibold text-sm">About Dr. Adnan</div><div class="text-[11px] text-gray-400">Background &amp; qualifications</div></div>
          </a>
          <a href="/doctors/" class="flex items-center gap-3 px-5 py-3 hover:bg-teal-50 transition-colors text-gray-700">
            <div class="w-8 h-8 bg-sky-100 rounded-lg flex items-center justify-center"><i class="fa-solid fa-people-group text-sky-600 text-sm"></i></div>
            <div><div class="font-semibold text-sm">Our Team</div><div class="text-[11px] text-gray-400">Meet the medical team</div></div>
          </a>
          <a href="/blog/" class="flex items-center gap-3 px-5 py-3 hover:bg-teal-50 transition-colors text-gray-700">
            <div class="w-8 h-8 bg-orange-100 rounded-lg flex items-center justify-center"><i class="fa-solid fa-newspaper text-orange-600 text-sm"></i></div>
            <div><div class="font-semibold text-sm">Blog / Articles</div><div class="text-[11px] text-gray-400">Research &amp; medical insights</div></div>
          </a>
        </div>
      </div>
    </div>
    <!-- ================= MALE INFERTILITY ================= -->
    <div class="relative group inline-block">
      <a href="/male-infertility/" class="hover:text-teal-600 transition">
        Male Infertility
      </a>
      <div class="mega-dropdown absolute top-full left-0 w-[900px] max-w-[calc(100vw-2rem)] opacity-0 invisible group-hover:opacity-100 group-hover:visible transition duration-200 pointer-events-none group-hover:pointer-events-auto z-50">
        <div class="bg-white shadow-2xl border border-gray-200 rounded-2xl mt-4">
          <div class="grid grid-cols-3 gap-8 p-8">
            <div>
              <h4 class="font-bold mb-4 text-gray-800 flex items-center gap-2"><i class="fa-solid fa-book-medical text-teal-600"></i> Overview</h4>
              <p class="text-gray-500 text-sm mb-4 leading-relaxed">
                Comprehensive male infertility evaluation and treatment in Lahore.
              </p>
              <a href="/male-infertility/" class="text-teal-600 text-sm font-semibold hover:underline inline-flex items-center gap-1">
                Male Infertility Overview <i class="fa-solid fa-arrow-right text-xs"></i>
              </a>
            </div>
            <div>
              <h4 class="font-bold mb-4 text-gray-800 flex items-center gap-2"><i class="fa-solid fa-stethoscope text-blue-600"></i> Common Conditions</h4>
              <ul class="space-y-2.5 text-gray-600 text-sm">
                <li><a href="/male-infertility/low-sperm-count.php" class="hover:text-teal-600 flex items-center gap-2 transition-colors"><i class="fa-solid fa-arrow-down-short-wide text-rose-400 w-4 text-center text-xs"></i> Low Sperm Count</a></li>
                <li><a href="/male-infertility/azoospermia.php" class="hover:text-teal-600 flex items-center gap-2 transition-colors"><i class="fa-solid fa-ban text-red-400 w-4 text-center text-xs"></i> Azoospermia (Zero Sperm)</a></li>
                <li><a href="/male-infertility/varicocele.php" class="hover:text-teal-600 flex items-center gap-2 transition-colors"><i class="fa-solid fa-circle-nodes text-purple-400 w-4 text-center text-xs"></i> Varicocele</a></li>
                <li><a href="/male-infertility/erectile-ejaculatory-dysfunction.php" class="hover:text-teal-600 flex items-center gap-2 transition-colors"><i class="fa-solid fa-triangle-exclamation text-amber-400 w-4 text-center text-xs"></i> Erectile &amp; Ejaculatory Dysfunction</a></li>
                <li><a href="/male-infertility/penile-doppler-ultrasound.php" class="hover:text-teal-600 flex items-center gap-2 transition-colors"><i class="fa-solid fa-wave-square text-cyan-400 w-4 text-center text-xs"></i> Penile Doppler Ultrasound</a></li>
              </ul>
            </div>
            <div>
              <h4 class="font-bold mb-4 text-gray-800 flex items-center gap-2"><i class="fa-solid fa-dna text-emerald-600"></i> Advanced &amp; Stem Cell</h4>
              <ul class="space-y-2.5 text-gray-600 text-sm">
                <li><a href="/male-infertility/dna-fragmentation.php" class="hover:text-teal-600 flex items-center gap-2 transition-colors"><i class="fa-solid fa-link-slash text-orange-400 w-4 text-center text-xs"></i> DNA Fragmentation</a></li>
                <li><a href="/male-infertility/unexplained-male-infertility.php" class="hover:text-teal-600 flex items-center gap-2 transition-colors"><i class="fa-solid fa-question text-gray-400 w-4 text-center text-xs"></i> Unexplained Male Infertility</a></li>
                <li><a href="/male-infertility/klinefelters-syndrome.php" class="hover:text-teal-600 flex items-center gap-2 transition-colors"><i class="fa-solid fa-person-circle-question text-indigo-400 w-4 text-center text-xs"></i> Klinefelter's Syndrome</a></li>
                <li><a href="/male-infertility/hypogonadotropic-hypogonadism.php" class="hover:text-teal-600 flex items-center gap-2 transition-colors"><i class="fa-solid fa-arrow-trend-down text-pink-400 w-4 text-center text-xs"></i> Hypogonadotropic Hypogonadism</a></li>
                <li><a href="/male-infertility/low-testicular-volume.php" class="hover:text-teal-600 flex items-center gap-2 transition-colors"><i class="fa-solid fa-minimize text-slate-400 w-4 text-center text-xs"></i> Low Testicular Volume</a></li>
                <li><a href="/male-infertility/primary-testicular-failure.php" class="hover:text-teal-600 flex items-center gap-2 transition-colors"><i class="fa-solid fa-circle-xmark text-red-400 w-4 text-center text-xs"></i> Primary Testicular Failure</a></li>
                <li><a href="/male-infertility/testicular-recovery-stemcell.php" class="hover:text-teal-600 flex items-center gap-2 transition-colors"><i class="fa-solid fa-seedling text-emerald-500 w-4 text-center text-xs"></i> Testicular Recovery via Stem Cell</a></li>
              </ul>
            </div>
          </div>
        </div>
      </div>
    </div>
    <!-- ================= FEMALE INFERTILITY ================= -->
    <div class="relative group inline-block">
      <a href="/female-infertility/" class="hover:text-teal-600 transition">
        Female Infertility
      </a>
      <div class="mega-dropdown absolute top-full left-0 w-[900px] max-w-[calc(100vw-2rem)] opacity-0 invisible group-hover:opacity-100 group-hover:visible transition duration-200 pointer-events-none group-hover:pointer-events-auto z-50">
        <div class="bg-white shadow-2xl border border-gray-200 rounded-2xl mt-4">
          <div class="grid grid-cols-3 gap-8 p-8">
            <div>
              <h4 class="font-bold mb-4 text-gray-800 flex items-center gap-2"><i class="fa-solid fa-book-medical text-pink-600"></i> Overview</h4>
              <p class="text-gray-500 text-sm mb-4 leading-relaxed">
                Structured female infertility diagnosis and ART planning.
              </p>
              <a href="/female-infertility/" class="text-teal-600 text-sm font-semibold hover:underline inline-flex items-center gap-1">
                Female Infertility Overview <i class="fa-solid fa-arrow-right text-xs"></i>
              </a>
            </div>
            <div>
              <h4 class="font-bold mb-4 text-gray-800 flex items-center gap-2"><i class="fa-solid fa-heart-pulse text-rose-600"></i> Structural &amp; Hormonal</h4>
              <ul class="space-y-2.5 text-gray-600 text-sm">
                <li><a href="/female-infertility/pcos.php" class="hover:text-teal-600 flex items-center gap-2 transition-colors"><i class="fa-solid fa-circle-dot text-violet-400 w-4 text-center text-xs"></i> PCOS</a></li>
                <li><a href="/female-infertility/endometriosis.php" class="hover:text-teal-600 flex items-center gap-2 transition-colors"><i class="fa-solid fa-virus text-rose-400 w-4 text-center text-xs"></i> Endometriosis</a></li>
                <li><a href="/female-infertility/blocked-tubes.php" class="hover:text-teal-600 flex items-center gap-2 transition-colors"><i class="fa-solid fa-road-barrier text-amber-400 w-4 text-center text-xs"></i> Blocked Tubes</a></li>
                <li><a href="/female-infertility/uterine-fibroids-polyps.php" class="hover:text-teal-600 flex items-center gap-2 transition-colors"><i class="fa-solid fa-shapes text-orange-400 w-4 text-center text-xs"></i> Uterine Fibroids &amp; Polyps</a></li>
                <li><a href="/female-infertility/adenomyosis.php" class="hover:text-teal-600 flex items-center gap-2 transition-colors"><i class="fa-solid fa-disease text-pink-400 w-4 text-center text-xs"></i> Adenomyosis</a></li>
              </ul>
            </div>
            <div>
              <h4 class="font-bold mb-4 text-gray-800 flex items-center gap-2"><i class="fa-solid fa-flask-vial text-emerald-600"></i> Complex &amp; Regenerative</h4>
              <ul class="space-y-2.5 text-gray-600 text-sm">
                <li><a href="/female-infertility/diminished-ovarian-reserve.php" class="hover:text-teal-600 flex items-center gap-2 transition-colors"><i class="fa-solid fa-battery-quarter text-amber-400 w-4 text-center text-xs"></i> Low Ovarian Reserve (AMH)</a></li>
                <li><a href="/female-infertility/recurrent-pregnancy-loss.php" class="hover:text-teal-600 flex items-center gap-2 transition-colors"><i class="fa-solid fa-rotate-left text-red-400 w-4 text-center text-xs"></i> Recurrent Miscarriages</a></li>
                <li><a href="/female-infertility/unexplained-infertility.php" class="hover:text-teal-600 flex items-center gap-2 transition-colors"><i class="fa-solid fa-question text-gray-400 w-4 text-center text-xs"></i> Unexplained Infertility</a></li>
                <li><a href="/female-infertility/primary-ovarian-failure.php" class="hover:text-teal-600 flex items-center gap-2 transition-colors"><i class="fa-solid fa-circle-xmark text-red-400 w-4 text-center text-xs"></i> Primary Ovarian Failure</a></li>
                <li><a href="/female-infertility/ovarian-tissue-preservation.php" class="hover:text-teal-600 flex items-center gap-2 transition-colors"><i class="fa-solid fa-snowflake text-cyan-400 w-4 text-center text-xs"></i> Oncofertility / Tissue Preservation</a></li>
                <li><a href="/female-infertility/stemcell-ovarian-rejuvenation.php" class="hover:text-teal-600 flex items-center gap-2 transition-colors"><i class="fa-solid fa-seedling text-emerald-500 w-4 text-center text-xs"></i> Stem Cell Ovarian Rejuvenation</a></li>
              </ul>
            </div>
          </div>
        </div>
      </div>
    </div>
    <!-- ================= ART PROCEDURES ================= -->
    <div class="relative group inline-block">
      <a href="/art-procedures/" class="hover:text-teal-600 transition">
        ART Procedures
      </a>
      <div class="mega-dropdown absolute top-full left-0 w-[900px] max-w-[calc(100vw-2rem)] opacity-0 invisible group-hover:opacity-100 group-hover:visible transition duration-200 pointer-events-none group-hover:pointer-events-auto z-50">
        <div class="bg-white shadow-2xl border border-gray-200 rounded-2xl mt-4">
          <div class="grid grid-cols-3 gap-8 p-8">
            <div>
              <h4 class="font-bold mb-4 text-gray-800 flex items-center gap-2"><i class="fa-solid fa-microscope text-indigo-600"></i> Overview</h4>
              <p class="text-gray-500 text-sm mb-4 leading-relaxed">
                Advanced assisted reproductive techniques in Lahore.
              </p>
              <a href="/art-procedures/" class="text-teal-600 text-sm font-semibold hover:underline inline-flex items-center gap-1">
                ART Procedures Overview <i class="fa-solid fa-arrow-right text-xs"></i>
              </a>
            </div>
            <div>
              <h4 class="font-bold mb-4 text-gray-800 flex items-center gap-2"><i class="fa-solid fa-syringe text-blue-600"></i> Core Procedures</h4>
              <ul class="space-y-2.5 text-gray-600 text-sm">
                <li><a href="/art-procedures/ivf.php" class="hover:text-teal-600 flex items-center gap-2 transition-colors"><i class="fa-solid fa-baby text-pink-400 w-4 text-center text-xs"></i> IVF Treatment</a></li>
                <li><a href="/art-procedures/icsi.php" class="hover:text-teal-600 flex items-center gap-2 transition-colors"><i class="fa-solid fa-bullseye text-red-400 w-4 text-center text-xs"></i> ICSI Treatment</a></li>
                <li><a href="/art-procedures/iui.php" class="hover:text-teal-600 flex items-center gap-2 transition-colors"><i class="fa-solid fa-hand-holding-medical text-teal-400 w-4 text-center text-xs"></i> IUI Insemination</a></li>
                <li><a href="/art-procedures/pgt.php" class="hover:text-teal-600 flex items-center gap-2 transition-colors"><i class="fa-solid fa-dna text-purple-400 w-4 text-center text-xs"></i> PGT / Gender Selection</a></li>
              </ul>
            </div>
            <div>
              <h4 class="font-bold mb-4 text-gray-800 flex items-center gap-2"><i class="fa-solid fa-flask text-emerald-600"></i> Advanced Laboratory</h4>
              <ul class="space-y-2.5 text-gray-600 text-sm">
                <li><a href="/art-procedures/fertility-preservation.php" class="hover:text-teal-600 flex items-center gap-2 transition-colors"><i class="fa-solid fa-snowflake text-cyan-400 w-4 text-center text-xs"></i> Egg &amp; Sperm Freezing</a></li>
                <li><a href="/art-procedures/ovarian-endometrial-prp.php" class="hover:text-teal-600 flex items-center gap-2 transition-colors"><i class="fa-solid fa-droplet text-rose-400 w-4 text-center text-xs"></i> Ovarian &amp; Endometrial PRP</a></li>
                <li><a href="/art-procedures/surgical-sperm-retrieval.php" class="hover:text-teal-600 flex items-center gap-2 transition-colors"><i class="fa-solid fa-scissors text-gray-400 w-4 text-center text-xs"></i> Surgical Sperm Retrieval</a></li>
                <li><a href="/art-procedures/laser-assisted-hatching.php" class="hover:text-teal-600 flex items-center gap-2 transition-colors"><i class="fa-solid fa-bolt text-yellow-400 w-4 text-center text-xs"></i> Laser-Assisted Hatching</a></li>
              </ul>
            </div>
          </div>
        </div>
      </div>
    </div>
    <!-- ================= STEM CELL ================= -->
    <div class="relative group inline-block">
      <a href="/stemcell/" class="hover:text-teal-600 transition">
        Stem Cell
      </a>
      <div class="mega-dropdown absolute top-full left-0 w-[700px] max-w-[calc(100vw-2rem)] opacity-0 invisible group-hover:opacity-100 group-hover:visible transition duration-200 pointer-events-none group-hover:pointer-events-auto z-50">
        <div class="bg-white shadow-2xl border border-gray-200 rounded-2xl mt-4">
          <div class="grid grid-cols-2 gap-8 p-8">
            <div>
              <h4 class="font-bold mb-4 text-gray-800 flex items-center gap-2"><i class="fa-solid fa-atom text-violet-600"></i> Stem Cell Types</h4>
              <ul class="space-y-2.5 text-gray-600 text-sm">
                <li><a href="/stemcell/adscs.php" class="hover:text-teal-600 flex items-center gap-2 transition-colors"><i class="fa-solid fa-droplet text-amber-400 w-4 text-center text-xs"></i> ADSCs (Adipose-Derived)</a></li>
                <li><a href="/stemcell/mesenchymal-umbilical.php" class="hover:text-teal-600 flex items-center gap-2 transition-colors"><i class="fa-solid fa-circle-nodes text-blue-400 w-4 text-center text-xs"></i> MSCs / MHUCs (Mesenchymal)</a></li>
                <li><a href="/stemcell/pluripotent-stem-cells.php" class="hover:text-teal-600 flex items-center gap-2 transition-colors"><i class="fa-solid fa-star text-yellow-400 w-4 text-center text-xs"></i> Pluripotent Stem Cells</a></li>
                <li><a href="/stemcell/multipotent-stem-cells.php" class="hover:text-teal-600 flex items-center gap-2 transition-colors"><i class="fa-solid fa-shapes text-indigo-400 w-4 text-center text-xs"></i> Multipotent Stem Cells</a></li>
              </ul>
            </div>
            <div>
              <h4 class="font-bold mb-4 text-gray-800 flex items-center gap-2"><i class="fa-solid fa-heart-circle-bolt text-rose-600"></i> Clinical Applications</h4>
              <ul class="space-y-2.5 text-gray-600 text-sm">
                <li><a href="/stemcell/role-in-infertility.php" class="hover:text-teal-600 flex items-center gap-2 transition-colors"><i class="fa-solid fa-seedling text-emerald-500 w-4 text-center text-xs"></i> Stem Cells &amp; Infertility</a></li>
                <li><a href="/male-infertility/testicular-recovery-stemcell.php" class="hover:text-teal-600 flex items-center gap-2 transition-colors"><i class="fa-solid fa-arrows-rotate text-teal-400 w-4 text-center text-xs"></i> Testicular Recovery</a></li>
                <li><a href="/female-infertility/stemcell-ovarian-rejuvenation.php" class="hover:text-teal-600 flex items-center gap-2 transition-colors"><i class="fa-solid fa-spa text-pink-400 w-4 text-center text-xs"></i> Ovarian Rejuvenation</a></li>
              </ul>
            </div>
          </div>
        </div>
      </div>
    </div>
    <a href="/contact/" class="hover:text-teal-600 transition">Contact</a>
  </nav>
  <!-- CTA Buttons (Desktop) - Patient Login + WhatsApp -->
  <div class="hidden md:flex items-center gap-2">
    <a href="/portal/"
       class="bg-sky-600 text-white px-3 py-2 rounded-md font-semibold hover:bg-sky-700 transition text-xs shadow-[0_4px_15px_rgba(14,165,233,0.3)] border-none flex items-center gap-2">
      <i class="fa-solid fa-laptop-medical text-xs"></i> Patient Login
    </a>
    <a href="https://wa.me/923111101483"
       class="bg-teal-700 text-white px-3 py-2 rounded-md font-semibold hover:bg-teal-800 transition text-xs shadow-[0_4px_15px_rgba(15,118,110,0.3)] border-none flex items-center gap-2">
      <i class="fa-brands fa-whatsapp text-sm"></i> WhatsApp
    </a>
  </div>
  <!-- HAMBURGER BUTTON (Mobile) -->
  <button id="mobile-menu-btn" aria-label="Open navigation menu" style="display:none;flex-direction:column;justify-content:center;align-items:center;width:40px;height:40px;border-radius:8px;background:transparent;border:none;cursor:pointer;">
    <span style="display:block;width:24px;height:2px;background:#374151;"></span>
    <span style="display:block;width:24px;height:2px;background:#374151;margin-top:6px;"></span>
    <span style="display:block;width:24px;height:2px;background:#374151;margin-top:6px;"></span>
  </button>
  <script>
    (function(){
      var btn = document.getElementById('mobile-menu-btn');
      if(btn){
        function checkWidth(){ btn.style.display = window.innerWidth < 768 ? 'flex' : 'none'; }
        checkWidth();
        window.addEventListener('resize', checkWidth);
      }
    })();
  </script>
</div>
</header>
<!-- MOBILE MENU OVERLAY -->
<div id="mobile-menu-overlay" style="display:none;position:fixed;inset:0;z-index:9999;">
  <!-- Backdrop -->
  <div id="mobile-menu-backdrop" style="position:absolute;inset:0;background:rgba(0,0,0,0.5);transition:opacity 0.3s;opacity:0;"></div>
  <!-- Slide-in Panel -->
  <div id="mobile-menu-panel" style="position:absolute;top:0;right:0;bottom:0;width:85%;max-width:400px;background:#fff;transform:translateX(100%);transition:transform 0.3s ease;overflow-y:auto;box-shadow:-4px 0 25px rgba(0,0,0,0.15);">
    <!-- Header -->
    <div style="display:flex;align-items:center;justify-content:space-between;padding:20px 24px;border-bottom:1px solid #e5e7eb;">
      <a href="/" style="font-size:1.35rem;font-weight:800;color:#0f766e;text-decoration:none;">IVF Experts</a>
      <button id="mobile-menu-close" style="width:40px;height:40px;display:flex;align-items:center;justify-content:center;border-radius:8px;background:transparent;cursor:pointer;border:none;font-size:24px;color:#374151;" aria-label="Close menu">&#x2715;</button>
    </div>
    <!-- Navigation Links -->
    <nav style="padding:16px 0;">
      <a href="/" style="display:block;padding:14px 24px;font-size:15px;font-weight:600;color:#1e293b;text-decoration:none;border-bottom:1px solid #f1f5f9;">
        <i class="fa-solid fa-house" style="width:20px;text-align:center;margin-right:8px;color:#0d9488;"></i> Home
      </a>
      <!-- About Accordion -->
      <div class="mobile-accordion" style="border-bottom:1px solid #f1f5f9;">
        <button class="mobile-accordion-toggle" style="display:flex;align-items:center;justify-content:space-between;width:100%;padding:14px 24px;font-size:15px;font-weight:600;color:#1e293b;background:transparent;border:none;cursor:pointer;text-align:left;">
          <span><i class="fa-solid fa-user-doctor" style="width:20px;text-align:center;margin-right:8px;color:#0d9488;"></i> About</span>
          <svg class="mobile-accordion-arrow" style="width:18px;height:18px;transition:transform 0.3s;flex-shrink:0;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
        </button>
        <div class="mobile-accordion-content" style="display:none;padding:0 24px 12px 24px;background:#f8fafc;">
          <a href="/about/" style="display:block;padding:10px 16px;font-size:14px;color:#0f766e;font-weight:600;text-decoration:none;border-radius:8px;"><i class="fa-solid fa-user-doctor" style="width:16px;text-align:center;margin-right:6px;"></i> About Dr. Adnan &rarr;</a>
          <a href="/doctors/" style="display:block;padding:8px 16px;font-size:13px;color:#475569;text-decoration:none;"><i class="fa-solid fa-people-group" style="width:16px;text-align:center;margin-right:6px;color:#0284c7;"></i> Our Team</a>
          <a href="/blog/" style="display:block;padding:8px 16px;font-size:13px;color:#475569;text-decoration:none;"><i class="fa-solid fa-newspaper" style="width:16px;text-align:center;margin-right:6px;color:#ea580c;"></i> Blog / Articles</a>
        </div>
      </div>
      <!-- Male Infertility Accordion -->
      <div class="mobile-accordion" style="border-bottom:1px solid #f1f5f9;">
        <button class="mobile-accordion-toggle" style="display:flex;align-items:center;justify-content:space-between;width:100%;padding:14px 24px;font-size:15px;font-weight:600;color:#1e293b;background:transparent;border:none;cursor:pointer;text-align:left;">
          <span><i class="fa-solid fa-mars" style="width:20px;text-align:center;margin-right:8px;color:#3b82f6;"></i> Male Infertility</span>
          <svg class="mobile-accordion-arrow" style="width:18px;height:18px;transition:transform 0.3s;flex-shrink:0;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
        </button>
        <div class="mobile-accordion-content" style="display:none;padding:0 24px 12px 24px;background:#f8fafc;">
          <a href="/male-infertility/" style="display:block;padding:10px 16px;font-size:14px;color:#0f766e;font-weight:600;text-decoration:none;border-radius:8px;"><i class="fa-solid fa-book-medical" style="width:16px;text-align:center;margin-right:6px;"></i> Overview &rarr;</a>
          <a href="/male-infertility/low-sperm-count.php" style="display:block;padding:8px 16px;font-size:13px;color:#475569;text-decoration:none;"><i class="fa-solid fa-arrow-down-short-wide" style="width:16px;text-align:center;margin-right:6px;color:#f43f5e;"></i> Low Sperm Count</a>
          <a href="/male-infertility/azoospermia.php" style="display:block;padding:8px 16px;font-size:13px;color:#475569;text-decoration:none;"><i class="fa-solid fa-ban" style="width:16px;text-align:center;margin-right:6px;color:#ef4444;"></i> Azoospermia (Zero Sperm)</a>
          <a href="/male-infertility/varicocele.php" style="display:block;padding:8px 16px;font-size:13px;color:#475569;text-decoration:none;"><i class="fa-solid fa-circle-nodes" style="width:16px;text-align:center;margin-right:6px;color:#a855f7;"></i> Varicocele</a>
          <a href="/male-infertility/erectile-ejaculatory-dysfunction.php" style="display:block;padding:8px 16px;font-size:13px;color:#475569;text-decoration:none;"><i class="fa-solid fa-triangle-exclamation" style="width:16px;text-align:center;margin-right:6px;color:#f59e0b;"></i> Erectile &amp; Ejaculatory Dysfunction</a>
          <a href="/male-infertility/dna-fragmentation.php" style="display:block;padding:8px 16px;font-size:13px;color:#475569;text-decoration:none;"><i class="fa-solid fa-link-slash" style="width:16px;text-align:center;margin-right:6px;color:#f97316;"></i> DNA Fragmentation</a>
          <a href="/male-infertility/unexplained-male-infertility.php" style="display:block;padding:8px 16px;font-size:13px;color:#475569;text-decoration:none;"><i class="fa-solid fa-question" style="width:16px;text-align:center;margin-right:6px;color:#94a3b8;"></i> Unexplained Male Infertility</a>
          <a href="/male-infertility/klinefelters-syndrome.php" style="display:block;padding:8px 16px;font-size:13px;color:#475569;text-decoration:none;"><i class="fa-solid fa-person-circle-question text-indigo-400 w-4 text-center text-xs"></i> Klinefelter's Syndrome</a>
          <a href="/male-infertility/hypogonadotropic-hypogonadism.php" style="display:block;padding:8px 16px;font-size:13px;color:#475569;text-decoration:none;"><i class="fa-solid fa-arrow-trend-down" style="width:16px;text-align:center;margin-right:6px;color:#ec4899;"></i> Hypogonadotropic Hypogonadism</a>
          <a href="/male-infertility/low-testicular-volume.php" style="display:block;padding:8px 16px;font-size:13px;color:#475569;text-decoration:none;"><i class="fa-solid fa-minimize" style="width:16px;text-align:center;margin-right:6px;color:#64748b;"></i> Low Testicular Volume</a>
          <a href="/male-infertility/primary-testicular-failure.php" style="display:block;padding:8px 16px;font-size:13px;color:#475569;text-decoration:none;"><i class="fa-solid fa-circle-xmark" style="width:16px;text-align:center;margin-right:6px;color:#ef4444;"></i> Primary Testicular Failure</a>
          <a href="/male-infertility/testicular-recovery-stemcell.php" style="display:block;padding:8px 16px;font-size:13px;color:#475569;text-decoration:none;"><i class="fa-solid fa-seedling" style="width:16px;text-align:center;margin-right:6px;color:#10b981;"></i> Testicular Recovery via Stem Cell</a>
          <a href="/male-infertility/penile-doppler-ultrasound.php" style="display:block;padding:8px 16px;font-size:13px;color:#475569;text-decoration:none;"><i class="fa-solid fa-wave-square" style="width:16px;text-align:center;margin-right:6px;color:#06b6d4;"></i> Penile Doppler Ultrasound</a>
        </div>
      </div>
      <!-- Female Infertility Accordion -->
      <div class="mobile-accordion" style="border-bottom:1px solid #f1f5f9;">
        <button class="mobile-accordion-toggle" style="display:flex;align-items:center;justify-content:space-between;width:100%;padding:14px 24px;font-size:15px;font-weight:600;color:#1e293b;background:transparent;border:none;cursor:pointer;text-align:left;">
          <span><i class="fa-solid fa-venus" style="width:20px;text-align:center;margin-right:8px;color:#ec4899;"></i> Female Infertility</span>
          <svg class="mobile-accordion-arrow" style="width:18px;height:18px;transition:transform 0.3s;flex-shrink:0;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
        </button>
        <div class="mobile-accordion-content" style="display:none;padding:0 24px 12px 24px;background:#f8fafc;">
          <a href="/female-infertility/" style="display:block;padding:10px 16px;font-size:14px;color:#0f766e;font-weight:600;text-decoration:none;border-radius:8px;"><i class="fa-solid fa-book-medical" style="width:16px;text-align:center;margin-right:6px;"></i> Overview &rarr;</a>
          <a href="/female-infertility/pcos.php" style="display:block;padding:8px 16px;font-size:13px;color:#475569;text-decoration:none;"><i class="fa-solid fa-circle-dot" style="width:16px;text-align:center;margin-right:6px;color:#8b5cf6;"></i> PCOS</a>
          <a href="/female-infertility/endometriosis.php" style="display:block;padding:8px 16px;font-size:13px;color:#475569;text-decoration:none;"><i class="fa-solid fa-virus" style="width:16px;text-align:center;margin-right:6px;color:#f43f5e;"></i> Endometriosis</a>
          <a href="/female-infertility/blocked-tubes.php" style="display:block;padding:8px 16px;font-size:13px;color:#475569;text-decoration:none;"><i class="fa-solid fa-road-barrier" style="width:16px;text-align:center;margin-right:6px;color:#f59e0b;"></i> Blocked Tubes</a>
          <a href="/female-infertility/uterine-fibroids-polyps.php" style="display:block;padding:8px 16px;font-size:13px;color:#475569;text-decoration:none;"><i class="fa-solid fa-shapes" style="width:16px;text-align:center;margin-right:6px;color:#f97316;"></i> Uterine Fibroids &amp; Polyps</a>
          <a href="/female-infertility/adenomyosis.php" style="display:block;padding:8px 16px;font-size:13px;color:#475569;text-decoration:none;"><i class="fa-solid fa-disease" style="width:16px;text-align:center;margin-right:6px;color:#ec4899;"></i> Adenomyosis</a>
          <a href="/female-infertility/diminished-ovarian-reserve.php" style="display:block;padding:8px 16px;font-size:13px;color:#475569;text-decoration:none;"><i class="fa-solid fa-battery-quarter" style="width:16px;text-align:center;margin-right:6px;color:#f59e0b;"></i> Low Ovarian Reserve (AMH)</a>
          <a href="/female-infertility/recurrent-pregnancy-loss.php" style="display:block;padding:8px 16px;font-size:13px;color:#475569;text-decoration:none;"><i class="fa-solid fa-rotate-left" style="width:16px;text-align:center;margin-right:6px;color:#ef4444;"></i> Recurrent Miscarriages</a>
          <a href="/female-infertility/unexplained-infertility.php" style="display:block;padding:8px 16px;font-size:13px;color:#475569;text-decoration:none;"><i class="fa-solid fa-question" style="width:16px;text-align:center;margin-right:6px;color:#94a3b8;"></i> Unexplained Infertility</a>
          <a href="/female-infertility/primary-ovarian-failure.php" style="display:block;padding:8px 16px;font-size:13px;color:#475569;text-decoration:none;"><i class="fa-solid fa-circle-xmark" style="width:16px;text-align:center;margin-right:6px;color:#ef4444;"></i> Primary Ovarian Failure</a>
          <a href="/female-infertility/ovarian-tissue-preservation.php" style="display:block;padding:8px 16px;font-size:13px;color:#475569;text-decoration:none;"><i class="fa-solid fa-snowflake" style="width:16px;text-align:center;margin-right:6px;color:#06b6d4;"></i> Oncofertility / Tissue Preservation</a>
          <a href="/female-infertility/stemcell-ovarian-rejuvenation.php" style="display:block;padding:8px 16px;font-size:13px;color:#475569;text-decoration:none;"><i class="fa-solid fa-seedling" style="width:16px;text-align:center;margin-right:6px;color:#10b981;"></i> Stem Cell Ovarian Rejuvenation</a>
        </div>
      </div>
      <!-- ART Procedures Accordion -->
      <div class="mobile-accordion" style="border-bottom:1px solid #f1f5f9;">
        <button class="mobile-accordion-toggle" style="display:flex;align-items:center;justify-content:space-between;width:100%;padding:14px 24px;font-size:15px;font-weight:600;color:#1e293b;background:transparent;border:none;cursor:pointer;text-align:left;">
          <span><i class="fa-solid fa-microscope" style="width:20px;text-align:center;margin-right:8px;color:#6366f1;"></i> ART Procedures</span>
          <svg class="mobile-accordion-arrow" style="width:18px;height:18px;transition:transform 0.3s;flex-shrink:0;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
        </button>
        <div class="mobile-accordion-content" style="display:none;padding:0 24px 12px 24px;background:#f8fafc;">
          <a href="/art-procedures/" style="display:block;padding:10px 16px;font-size:14px;color:#0f766e;font-weight:600;text-decoration:none;border-radius:8px;"><i class="fa-solid fa-microscope" style="width:16px;text-align:center;margin-right:6px;"></i> Overview &rarr;</a>
          <a href="/art-procedures/ivf.php" style="display:block;padding:8px 16px;font-size:13px;color:#475569;text-decoration:none;"><i class="fa-solid fa-baby" style="width:16px;text-align:center;margin-right:6px;color:#ec4899;"></i> IVF Treatment</a>
          <a href="/art-procedures/icsi.php" style="display:block;padding:8px 16px;font-size:13px;color:#475569;text-decoration:none;"><i class="fa-solid fa-baby-carriage" style="width:16px;text-align:center;margin-right:6px;color:#ef4444;"></i> ICSI Treatment</a>
          <a href="/art-procedures/iui.php" style="display:block;padding:8px 16px;font-size:13px;color:#475569;text-decoration:none;"><i class="fa-solid fa-hand-holding-medical" style="width:16px;text-align:center;margin-right:6px;color:#14b8a6;"></i> IUI Insemination</a>
          <a href="/art-procedures/pgt.php" style="display:block;padding:8px 16px;font-size:13px;color:#475569;text-decoration:none;"><i class="fa-solid fa-dna" style="width:16px;text-align:center;margin-right:6px;color:#a855f7;"></i> PGT / Gender Selection</a>
          <a href="/art-procedures/fertility-preservation.php" style="display:block;padding:8px 16px;font-size:13px;color:#475569;text-decoration:none;"><i class="fa-solid fa-snowflake" style="width:16px;text-align:center;margin-right:6px;color:#06b6d4;"></i> Egg &amp; Sperm Freezing</a>
          <a href="/art-procedures/ovarian-endometrial-prp.php" style="display:block;padding:8px 16px;font-size:13px;color:#475569;text-decoration:none;"><i class="fa-solid fa-droplet" style="width:16px;text-align:center;margin-right:6px;color:#f43f5e;"></i> Ovarian &amp; Endometrial PRP</a>
          <a href="/art-procedures/surgical-sperm-retrieval.php" style="display:block;padding:8px 16px;font-size:13px;color:#475569;text-decoration:none;"><i class="fa-solid fa-scissors" style="width:16px;text-align:center;margin-right:6px;color:#64748b;"></i> Surgical Sperm Retrieval</a>
          <a href="/art-procedures/laser-assisted-hatching.php" style="display:block;padding:8px 16px;font-size:13px;color:#475569;text-decoration:none;"><i class="fa-solid fa-bolt" style="width:16px;text-align:center;margin-right:6px;color:#eab308;"></i> Laser-Assisted Hatching</a>
        </div>
      </div>
      <!-- Stem Cell Accordion -->
      <div class="mobile-accordion" style="border-bottom:1px solid #f1f5f9;">
        <button class="mobile-accordion-toggle" style="display:flex;align-items:center;justify-content:space-between;width:100%;padding:14px 24px;font-size:15px;font-weight:600;color:#1e293b;background:transparent;border:none;cursor:pointer;text-align:left;">
          <span><i class="fa-solid fa-atom" style="width:20px;text-align:center;margin-right:8px;color:#8b5cf6;"></i> Stem Cell</span>
          <svg class="mobile-accordion-arrow" style="width:18px;height:18px;transition:transform 0.3s;flex-shrink:0;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
        </button>
        <div class="mobile-accordion-content" style="display:none;padding:0 24px 12px 24px;background:#f8fafc;">
          <a href="/stemcell/" style="display:block;padding:10px 16px;font-size:14px;color:#0f766e;font-weight:600;text-decoration:none;border-radius:8px;"><i class="fa-solid fa-atom" style="width:16px;text-align:center;margin-right:6px;"></i> Overview &rarr;</a>
          <a href="/stemcell/adscs.php" style="display:block;padding:8px 16px;font-size:13px;color:#475569;text-decoration:none;"><i class="fa-solid fa-droplet" style="width:16px;text-align:center;margin-right:6px;color:#f59e0b;"></i> ADSCs (Adipose-Derived)</a>
          <a href="/stemcell/mesenchymal-umbilical.php" style="display:block;padding:8px 16px;font-size:13px;color:#475569;text-decoration:none;"><i class="fa-solid fa-circle-nodes" style="width:16px;text-align:center;margin-right:6px;color:#3b82f6;"></i> MSCs / MHUCs (Mesenchymal)</a>
          <a href="/stemcell/pluripotent-stem-cells.php" style="display:block;padding:8px 16px;font-size:13px;color:#475569;text-decoration:none;"><i class="fa-solid fa-star" style="width:16px;text-align:center;margin-right:6px;color:#eab308;"></i> Pluripotent Stem Cells</a>
          <a href="/stemcell/multipotent-stem-cells.php" style="display:block;padding:8px 16px;font-size:13px;color:#475569;text-decoration:none;"><i class="fa-solid fa-shapes" style="width:16px;text-align:center;margin-right:6px;color:#6366f1;"></i> Multipotent Stem Cells</a>
          <a href="/stemcell/role-in-infertility.php" style="display:block;padding:8px 16px;font-size:13px;color:#475569;text-decoration:none;"><i class="fa-solid fa-seedling" style="width:16px;text-align:center;margin-right:6px;color:#10b981;"></i> Stem Cells &amp; Infertility</a>
        </div>
      </div>
      <a href="/contact/" style="display:block;padding:14px 24px;font-size:15px;font-weight:600;color:#1e293b;text-decoration:none;border-bottom:1px solid #f1f5f9;">
        <i class="fa-solid fa-envelope" style="width:20px;text-align:center;margin-right:8px;color:#0d9488;"></i> Contact
      </a>
    </nav>
    <!-- Mobile CTAs -->
    <div style="padding:16px 24px 32px;display:flex;flex-direction:column;gap:10px;">
      <a href="/portal/" style="display:flex;align-items:center;justify-content:center;gap:10px;background:#0284c7;color:#fff;padding:14px 24px;border-radius:12px;font-weight:700;font-size:15px;text-decoration:none;box-shadow:0 4px 15px rgba(14,165,233,0.3);">
        <i class="fa-solid fa-laptop-medical"></i>
        Patient Login
      </a>
      <a href="https://wa.me/923111101483" style="display:flex;align-items:center;justify-content:center;gap:10px;background:#0f766e;color:#fff;padding:14px 24px;border-radius:12px;font-weight:700;font-size:15px;text-decoration:none;box-shadow:0 4px 15px rgba(15,118,110,0.3);">
        <i class="fa-brands fa-whatsapp" style="font-size:20px;"></i>
        WhatsApp Consultation
      </a>
    </div>
  </div>
</div>
<div class="h-24" style="height:6rem;"></div>
