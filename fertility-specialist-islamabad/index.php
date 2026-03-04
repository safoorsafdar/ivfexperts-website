<?php
$pageTitle = "IVF &amp; Fertility Specialist in Islamabad | Dr. Adnan Jabbar — Teleconsultation";
$metaDescription = "Fertility specialist in Islamabad — Dr. Adnan Jabbar offers IVF, ICSI, IUI consultations for Islamabad patients via teleconsultation. Book via WhatsApp today.";
include("../includes/header.php");
?>

<!-- Hero -->
<section class="bg-teal-700 py-20 px-6 text-center text-white">
  <div class="max-w-3xl mx-auto">
    <p class="text-teal-200 text-sm font-semibold uppercase tracking-widest mb-3">Teleconsultation Available</p>
    <h1 class="text-4xl md:text-5xl font-extrabold mb-4 leading-tight">
      Fertility Specialist for Islamabad Patients
    </h1>
    <p class="text-xl text-teal-100 mb-8 leading-relaxed">
      Dr. Adnan Jabbar — Pakistan's leading IVF and fertility consultant — offers expert teleconsultations for patients across Islamabad. Get personalised fertility advice from the comfort of your home.
    </p>
    <a href="https://wa.me/923111101483?text=Hi%20Dr.%20Adnan%2C%20I%20am%20a%20patient%20from%20Islamabad%20and%20would%20like%20a%20teleconsultation."
       target="_blank" rel="noopener noreferrer"
       class="inline-flex items-center gap-2 bg-white text-teal-700 font-bold px-8 py-4 rounded-xl hover:bg-teal-50 transition-colors">
      <i class="fab fa-whatsapp text-xl"></i>
      Book Islamabad Teleconsultation
    </a>
  </div>
</section>

<!-- Why Teleconsult -->
<section class="section-md bg-soft">
  <div class="max-w-5xl mx-auto px-6">
    <h2 class="text-3xl font-extrabold text-slate-900 text-center mb-12">Why Islamabad Patients Choose Dr. Adnan</h2>
    <div class="grid md:grid-cols-3 gap-8">
      <div class="card p-8 text-center">
        <div class="w-14 h-14 bg-teal-100 rounded-full flex items-center justify-center mx-auto mb-4">
          <i class="fas fa-video text-teal-700 text-2xl"></i>
        </div>
        <h3 class="text-xl font-bold text-slate-900 mb-2">Full Teleconsultation</h3>
        <p class="text-slate-600">Receive a complete fertility assessment via video or WhatsApp — no need to travel to Lahore for the initial consultation.</p>
      </div>
      <div class="card p-8 text-center">
        <div class="w-14 h-14 bg-teal-100 rounded-full flex items-center justify-center mx-auto mb-4">
          <i class="fas fa-microscope text-teal-700 text-2xl"></i>
        </div>
        <h3 class="text-xl font-bold text-slate-900 mb-2">15+ Years of IVF Expertise</h3>
        <p class="text-slate-600">Dr. Adnan Jabbar is a Fertility Specialist and Clinical Embryologist with over 15 years of experience in IVF, ICSI, and reproductive medicine.</p>
      </div>
      <div class="card p-8 text-center">
        <div class="w-14 h-14 bg-teal-100 rounded-full flex items-center justify-center mx-auto mb-4">
          <i class="fas fa-map-marked-alt text-teal-700 text-2xl"></i>
        </div>
        <h3 class="text-xl font-bold text-slate-900 mb-2">Nationwide Coverage</h3>
        <p class="text-slate-600">IVF Experts serves patients across Pakistan — Islamabad, Lahore, Karachi, and beyond — through our teleconsultation service.</p>
      </div>
    </div>
  </div>
</section>

<!-- Services -->
<section class="section-md bg-white">
  <div class="max-w-5xl mx-auto px-6">
    <h2 class="text-3xl font-extrabold text-slate-900 text-center mb-4">Services Available for Islamabad Patients</h2>
    <p class="text-slate-600 text-center mb-12 text-lg">All consultations and treatment planning are available via teleconsultation. Procedures are performed at our Lahore clinic.</p>
    <div class="grid md:grid-cols-2 gap-6">
      <?php
      $services = [
        ['IVF (In Vitro Fertilisation)', 'Complete IVF cycle consultation, protocol planning, and results review via teleconsult.', '/art-procedures/ivf'],
        ['ICSI', 'Intracytoplasmic Sperm Injection for male factor infertility — full assessment and planning.', '/art-procedures/icsi'],
        ['IUI', 'Intrauterine Insemination — a simpler, lower-cost first-line fertility treatment.', '/art-procedures/iui'],
        ['PGT / Gender Selection', 'Preimplantation Genetic Testing for chromosomal screening and family balancing.', '/art-procedures/pgt'],
        ['Female Infertility', 'Assessment and treatment of PCOS, endometriosis, blocked tubes, and other conditions.', '/female-infertility/'],
        ['Male Infertility', 'Azoospermia, low sperm count, and other male factor conditions — fully assessable via teleconsult.', '/male-infertility/'],
      ];
      foreach ($services as [$title, $desc, $link]): ?>
      <div class="card p-6 flex gap-4">
        <div class="w-10 h-10 bg-teal-100 rounded-lg flex items-center justify-center flex-shrink-0 mt-1">
          <i class="fas fa-check text-teal-700"></i>
        </div>
        <div>
          <h3 class="text-lg font-bold text-slate-900 mb-1"><?php echo htmlspecialchars($title); ?></h3>
          <p class="text-slate-600 text-sm mb-2"><?php echo htmlspecialchars($desc); ?></p>
          <a href="<?php echo htmlspecialchars($link); ?>" class="text-teal-700 text-sm font-semibold hover:underline">Learn more →</a>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- CTA -->
<?php include('../includes/cta-consult.php'); ?>

<?php include("../includes/footer.php"); ?>
