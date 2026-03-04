<?php
/**
 * Inline mid-page consultation CTA
 * Optionally set $ctaContext before including to customise the message.
 * Example: $ctaContext = 'about ICSI treatment';
 * Then: include('../includes/cta-inline.php');
 */
$ctaMessage = isset($ctaContext) && $ctaContext
    ? 'Hi Dr. Adnan, I have questions ' . $ctaContext . '.'
    : 'Hi Dr. Adnan, I would like to book a fertility consultation.';
?>
<div class="my-10 p-6 md:p-8 bg-teal-50 border border-teal-200 rounded-2xl flex flex-col md:flex-row items-start md:items-center gap-6">
  <div class="flex-1">
    <p class="text-base font-semibold text-slate-900 mb-1">Have questions about your treatment options?</p>
    <p class="text-sm text-slate-600">Dr. Adnan Jabbar responds personally — usually within a few hours.</p>
  </div>
  <a href="https://wa.me/923111101483?text=<?php echo rawurlencode($ctaMessage); ?>"
     target="_blank" rel="noopener noreferrer"
     class="inline-flex items-center gap-2 bg-teal-700 text-white font-bold px-6 py-3 rounded-xl hover:bg-teal-600 transition-colors flex-shrink-0 text-sm">
    <i class="fab fa-whatsapp text-lg"></i>
    Ask Dr. Adnan
  </a>
</div>
