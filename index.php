<?php
$pageTitle = "Dr. Adnan Jabbar | Premium IVF & Fertility Specialist in Lahore";
$metaDescription = "Expert fertility consultant & clinical embryologist in Lahore. Providing individualized, empathetic, and evidence-based IVF, ICSI, and infertility treatments.";
include("includes/header.php");
?>

<!-- HERO SECTION -->
<section class="relative min-h-[90vh] flex items-center overflow-hidden pt-24 pb-16 lg:pt-32 lg:pb-24">
    <!-- Sophisticated Background -->
    <div class="absolute inset-0 bg-gradient-to-br from-white via-teal-50/50 to-slate-100/80 -z-20"></div>
    
    <!-- Animated Glow Orbs (Fixed to prevent layout shift) -->
    <div class="absolute top-0 right-0 w-[600px] h-[600px] bg-teal-200/40 rounded-full blur-[100px] -z-10 animate-pulse-slow mix-blend-multiply pointer-events-none transform-gpu" style="contain: paint layout;"></div>
    <div class="absolute bottom-10 left-10 w-[400px] h-[400px] bg-emerald-100/50 rounded-full blur-[80px] -z-10 mix-blend-multiply pointer-events-none transform-gpu" style="contain: paint layout;"></div>

    <div class="max-w-7xl mx-auto px-6 w-full grid lg:grid-cols-12 gap-16 items-center z-10">
        
        <!-- LEFT: EMOTIONAL NARRATIVE -->
        <div class="lg:col-span-7 fade-in">
            <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-teal-50 text-teal-700 text-sm font-semibold mb-6 border border-teal-100 shadow-sm">
                <span class="w-2 h-2 rounded-full bg-teal-500 animate-pulse"></span>
                Accepting Consultations in Lahore, Karachi, Islamabad & Across Pakistan
            </div>
            
            <div class="min-h-[220px] md:min-h-[180px] flex flex-col justify-start">
                <h1 class="text-4xl md:text-5xl lg:text-6xl font-extrabold text-slate-900 leading-[1.15] mb-6">
                    <span id="hero-title" class="transition-opacity duration-700 block">Parenthood Begins with</span>
                    <span id="hero-highlight" class="text-teal-700 transition-opacity duration-700 block mt-2">Clarity & Strategy.</span>
                </h1>
                
                <p id="hero-desc" class="text-lg md:text-xl text-slate-600 mb-8 max-w-2xl leading-relaxed transition-opacity duration-700">
                    We believe that every fertility journey is deeply personal. By integrating compassionate clinical care with elite embryology expertise, we design treatments that honor your unique path to parenthood.
                </p>
            </div>

            <script>
            document.addEventListener('DOMContentLoaded', () => {
                const slides = [
                    {
                        title: "Parenthood Begins with",
                        highlight: "Clarity & Strategy.",
                        desc: "We believe that every fertility journey is deeply personal. By integrating compassionate clinical care with elite embryology expertise, we design treatments that honor your unique path to parenthood."
                    },
                    {
                        title: "Your Journey Deserves",
                        highlight: "Unwavering Empathy.",
                        desc: "Infertility can feel overwhelming, but you don't have to face it alone. We listen carefully and construct an ethical, supportive environment centered entirely around your well-being."
                    },
                    {
                        title: "Advanced Science,",
                        highlight: "Personalized Care.",
                        desc: "Leveraging state-of-the-art clinical embryology and highly individualized IVF protocols, we maximize your chances of success without rushing into unnecessary procedures."
                    },
                    {
                        title: "Building Families with",
                        highlight: "Trust & Precision.",
                        desc: "From complex male factor diagnostics to nuanced female reproductive challenges, our dual-trained expertise guarantees a comprehensive roadmap tailored specifically for you."
                    },
                    {
                        title: "A Partnership in",
                        highlight: "Hope & Healing.",
                        desc: "Your dreams are our priority. We walk closely beside you through every emotional high and clinical milestone, ensuring you always feel informed, respected, and empowered."
                    },
                    {
                        title: "Expertise that Turns",
                        highlight: "Obstacles into Miracles.",
                        desc: "With thousands of successful cycles, we specialize in overcoming the most severe infertility barriers, turning scientific mastery into the joy of holding your baby."
                    }
                ];

                let currentSlide = 0;
                const titleEl = document.getElementById('hero-title');
                const highlightEl = document.getElementById('hero-highlight');
                const descEl = document.getElementById('hero-desc');

                setInterval(() => {
                    // Fade out
                    titleEl.style.opacity = '0';
                    highlightEl.style.opacity = '0';
                    descEl.style.opacity = '0';

                    setTimeout(() => {
                        currentSlide = (currentSlide + 1) % slides.length;
                        titleEl.textContent = slides[currentSlide].title;
                        highlightEl.textContent = slides[currentSlide].highlight;
                        descEl.textContent = slides[currentSlide].desc;
                        
                        // Fade in
                        titleEl.style.opacity = '1';
                        highlightEl.style.opacity = '1';
                        descEl.style.opacity = '1';
                    }, 600); // Quick pause for fade out
                }, 4500); // 4.5 seconds total viewing time per slide
            });
            </script>

            <div class="flex flex-wrap gap-5">
                <a href="#consultation" class="btn-primary text-white hover:text-teal-50 shadow-[0_8px_25px_rgba(15,118,110,0.25)]">
                    Schedule Your Consultation
                </a>
                <a href="/about/" class="btn-outline group flex items-center gap-2">
                    Meet Dr. Adnan Jabbar
                    <svg class="w-4 h-4 transform group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                    </svg>
                </a>
            </div>

            <!-- Trust Indicators -->
            <div class="mt-12 pt-10 border-t border-slate-200/60 grid grid-cols-2 gap-8">
                <div>
                    <h2 class="text-3xl font-extrabold text-teal-700 counter" data-target="10">0</h2>
                    <p class="text-sm font-medium text-slate-500 mt-2">Years of specialized clinical experience</p>
                </div>
                <div>
                    <h3 class="text-2xl font-bold text-slate-800 flex items-center gap-2">
                        Dual Expertise
                    </h3>
                    <p class="text-sm font-medium text-slate-500 mt-2">Certified Clinician & Embryologist</p>
                </div>
            </div>
        </div>

        <!-- RIGHT: GLASSMORPHIC CARD -->
        <div class="lg:col-span-5 fade-in" style="transition-delay: 200ms;">
            <div class="relative rounded-3xl overflow-hidden bg-white/60 backdrop-blur-xl border border-white/80 shadow-[0_20px_60px_rgba(0,0,0,0.05)] p-8 lg:p-10">
                <div class="absolute inset-0 bg-gradient-to-br from-white/40 to-transparent pointer-events-none"></div>
                
                <div class="relative z-10">
                    <h2 class="text-2xl font-bold text-slate-800 mb-6">Comprehensive Fertility Care</h2>
                    
                    <ul class="space-y-5">
                        <li class="flex items-start gap-4">
                            <div class="w-10 h-10 rounded-full bg-teal-50 flex items-center justify-center flex-shrink-0 text-teal-600 mt-1">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/>
                                </svg>
                            </div>
                            <div>
                                <h4 class="font-semibold text-slate-800">Advanced IVF & ICSI</h4>
                                <p class="text-sm text-slate-500 mt-1">Laboratory-optimized protocols for maximum success rates.</p>
                            </div>
                        </li>
                        <li class="flex items-start gap-4">
                            <div class="w-10 h-10 rounded-full bg-indigo-50 flex items-center justify-center flex-shrink-0 text-indigo-600 mt-1">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                                </svg>
                            </div>
                            <div>
                                <h4 class="font-semibold text-slate-800">Male factor infertility</h4>
                                <p class="text-sm text-slate-500 mt-1">Micro-TESE, Varicocele & DNA Fragmentation management.</p>
                            </div>
                        </li>
                        <li class="flex items-start gap-4">
                            <div class="w-10 h-10 rounded-full bg-emerald-50 flex items-center justify-center flex-shrink-0 text-emerald-600 mt-1">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <div>
                                <h4 class="font-semibold text-slate-800">Genetic Screening (PGT)</h4>
                                <p class="text-sm text-slate-500 mt-1">Ensuring healthy embryonic development prior to transfer.</p>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- THE DOCTOR'S PHILOSOPHY / EMOTIONAL CORE -->
<section class="section-lg bg-slate-950 text-center relative overflow-hidden">
    <!-- Decorative subtle lines -->
    <div class="absolute inset-0 opacity-10" style="background-image: radial-gradient(circle at 2px 2px, white 1px, transparent 0); background-size: 40px 40px;"></div>
    
    <div class="max-w-4xl mx-auto px-6 relative z-10 fade-in">
        <svg class="w-12 h-12 mx-auto text-teal-500 mb-8 opacity-80" fill="currentColor" viewBox="0 0 24 24">
            <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>
        </svg>
        <h2 class="text-3xl md:text-5xl font-extrabold text-white mb-8 leading-tight tracking-tight">
            Behind every consultation is a <br/><span class="text-teal-400">deeply personal hope.</span>
        </h2>
        <p class="text-xl text-slate-300 leading-relaxed font-light">
            Infertility is not just a medical diagnosis. It is an emotional journey marked by expectation and uncertainty. Our philosophy ensures that clinical precision coexists with empathy, transparency, and unyielding ethical guidance for couples across Pakistan—from Lahore to Multan, Sargodha to Sialkot.
        </p>
    </div>
</section>

<!-- DIAGNOSTIC APPROACH (CARDS) -->
<section class="section-lg bg-soft relative">
    <div class="max-w-7xl mx-auto px-6">
        
        <div class="text-center max-w-3xl mx-auto mb-20 fade-in">
            <h2 class="text-3xl md:text-4xl font-bold text-slate-900 mb-6">The Architecture of Success</h2>
            <p class="text-lg text-slate-600">Assisted reproduction is most successful when built upon a foundation of accurate diagnostics, individualized protocols, and impeccable laboratory conditions.</p>
        </div>

        <div class="grid md:grid-cols-3 gap-8">
            
            <div class="card group fade-in">
                <div class="w-16 h-16 rounded-2xl bg-teal-50 text-teal-600 flex items-center justify-center mb-8 group-hover:scale-110 group-hover:bg-teal-600 group-hover:text-white transition-all duration-300">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/>
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-slate-800 mb-4">Embryology Insight</h3>
                <p class="text-slate-600 leading-relaxed">
                    Direct laboratory understanding informs fertilization strategy. As a clinical embryologist, Dr. Adnan intimately manages the environment where life begins.
                </p>
            </div>

            <div class="card group fade-in" style="transition-delay: 150ms;">
                <div class="w-16 h-16 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center mb-8 group-hover:scale-110 group-hover:bg-indigo-600 group-hover:text-white transition-all duration-300">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-slate-800 mb-4">Structured Evaluation</h3>
                <p class="text-slate-600 leading-relaxed">
                    Thorough hormonal, seminal, ovarian reserve, and systemic health assessments precede any major treatment plan to avoid unnecessary procedures.
                </p>
            </div>

            <div class="card group fade-in" style="transition-delay: 300ms;">
                <div class="w-16 h-16 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center mb-8 group-hover:scale-110 group-hover:bg-emerald-600 group-hover:text-white transition-all duration-300">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-slate-800 mb-4">Ethical Roadmaps</h3>
                <p class="text-slate-600 leading-relaxed">
                    IVF is not the first answer for everyone. Decisions are guided purely by measurable biological indicators and evidence-based medical literature.
                </p>
            </div>

        </div>
    </div>
</section>
<?php
/*
 * ══════════════════════════════════════════════════════════════════
 *  IVF EXPERTS — LANDING PAGE TOOLS SECTION
 *  File: landing-widgets-section.php
 *
 *  HOW TO USE:
 *  Open your index.php and find this comment block:
 *
 *      <!-- DIAGNOSTIC APPROACH (CARDS) -->
 *      <section class="section-lg bg-soft relative">
 *          ...  (The "Architecture of Success" 3-card section)
 *          ...
 *      </section>
 *
 *  Paste the ENTIRE contents of this file
 *  DIRECTLY AFTER the closing </section> of that block,
 *  and BEFORE the opening <section> of the Male/Female split section.
 *
 *  Also paste the <style> block into your main CSS or in a <style>
 *  tag in your <head>. The <script> block can live inline here or
 *  be moved before <?php include("includes/footer.php"); ?>
 * ══════════════════════════════════════════════════════════════════
 */
?>


<!-- ══════════════════════════════════════════════════════════════
     FERTILITY TOOLS SECTION
══════════════════════════════════════════════════════════════ -->
<section class="relative py-24 overflow-hidden bg-white" id="fertility-tools">

    <!-- Layered decorative background -->
    <div class="absolute inset-0 -z-10" style="background-image: radial-gradient(circle at 2px 2px, rgba(15,118,110,0.045) 1px, transparent 0); background-size: 38px 38px;"></div>
    <div class="tools-orb tools-orb-left absolute -left-40 top-1/4 w-[500px] h-[500px] rounded-full bg-teal-100/40 blur-[120px] -z-10 pointer-events-none"></div>
    <div class="tools-orb tools-orb-right absolute -right-40 bottom-1/4 w-[400px] h-[400px] rounded-full bg-indigo-100/40 blur-[100px] -z-10 pointer-events-none"></div>

    <div class="max-w-7xl mx-auto px-6">

        <!-- Section Header -->
        <div class="text-center max-w-2xl mx-auto mb-16 fade-in">
            <div class="inline-flex items-center gap-2.5 px-5 py-2.5 rounded-full bg-teal-50 text-teal-700 text-sm font-bold mb-6 border border-teal-100 shadow-sm">
                <i class="fa-solid fa-wand-magic-sparkles text-teal-500 animate-pulse"></i>
                Free Patient Tools &middot; No Registration Required
            </div>
            <h2 class="text-3xl md:text-[2.6rem] font-extrabold text-slate-900 leading-tight tracking-tight mb-5">
                Understand Your Fertility <span class="text-teal-700">Today</span>
            </h2>
            <p class="text-lg text-slate-500 leading-relaxed">
                Clinically-informed tools used by patients across Pakistan — get instant clarity on your reproductive health in seconds.
            </p>
        </div>


        <!-- TWO WIDGETS SIDE BY SIDE -->
        <div class="grid lg:grid-cols-2 gap-8 mb-16">


            <!-- WIDGET 1: OVULATION CALCULATOR (TEAL) -->
            <div class="widget-card group relative rounded-3xl bg-white border border-slate-200/80 shadow-[0_4px_24px_rgba(0,0,0,0.06)] hover:shadow-[0_16px_48px_rgba(15,118,110,0.13)] transition-all duration-500 hover:-translate-y-1.5 overflow-hidden fade-in">

                <div class="h-1.5 w-full bg-gradient-to-r from-teal-500 via-emerald-400 to-teal-600"></div>
                <div class="absolute -top-16 -right-16 w-48 h-48 rounded-full bg-teal-50 blur-3xl opacity-60 pointer-events-none group-hover:opacity-100 transition-opacity duration-500"></div>

                <div class="p-8 relative z-10">
                    <!-- Header -->
                    <div class="flex items-start justify-between mb-7">
                        <div class="flex items-center gap-4">
                            <div class="widget-icon-teal relative w-14 h-14 rounded-2xl bg-gradient-to-br from-teal-500 to-emerald-600 flex items-center justify-center shadow-[0_8px_20px_rgba(15,118,110,0.3)] flex-shrink-0">
                                <i class="fa-solid fa-calendar-days text-white text-xl"></i>
                                <span class="absolute -top-1 -right-1 w-4 h-4 rounded-full bg-emerald-400 border-2 border-white animate-ping opacity-75"></span>
                                <span class="absolute -top-1 -right-1 w-4 h-4 rounded-full bg-emerald-400 border-2 border-white"></span>
                            </div>
                            <div>
                                <h3 class="text-xl font-extrabold text-slate-900 tracking-tight">Ovulation Calculator</h3>
                                <p class="text-sm text-slate-400 font-medium mt-0.5 flex items-center gap-1.5">
                                    <i class="fa-solid fa-circle-dot text-emerald-400 text-[10px]"></i>
                                    Find your peak fertile days
                                </p>
                            </div>
                        </div>
                        <span class="hidden sm:flex items-center gap-1.5 text-[11px] font-bold text-teal-600 bg-teal-50 border border-teal-100 px-3 py-1.5 rounded-full">
                            <i class="fa-solid fa-lock text-[9px]"></i> Private
                        </span>
                    </div>

                    <!-- Form -->
                    <div id="ov-form" class="space-y-5">
                        <div>
                            <label class="flex items-center gap-2 text-xs font-bold text-slate-500 uppercase tracking-widest mb-2">
                                <i class="fa-solid fa-calendar-check text-teal-500"></i>
                                First Day of Last Period
                            </label>
                            <div class="relative">
                                <i class="fa-regular fa-calendar absolute left-4 top-1/2 -translate-y-1/2 text-slate-300 text-sm pointer-events-none"></i>
                                <input type="date" id="ov-lmp"
                                    class="w-full pl-11 pr-4 py-3.5 rounded-xl border-2 border-slate-100 bg-slate-50/70 text-slate-800 font-semibold text-sm focus:outline-none focus:border-teal-400 focus:bg-white transition-all duration-200">
                            </div>
                        </div>

                        <div>
                            <label class="flex items-center justify-between text-xs font-bold text-slate-500 uppercase tracking-widest mb-3">
                                <span class="flex items-center gap-2"><i class="fa-solid fa-rotate text-teal-500"></i> Cycle Length</span>
                                <span id="ov-cycle-label" class="text-base font-black text-teal-700 normal-case tracking-normal">28 days</span>
                            </label>
                            <input type="range" id="ov-cycle" min="21" max="40" value="28"
                                class="w-full h-2 appearance-none rounded-full cursor-pointer outline-none ov-slider"
                                oninput="updateOvCycle(this)">
                            <div class="flex justify-between text-[11px] text-slate-400 font-medium mt-1.5">
                                <span>21 days</span><span>Short &rarr; Long</span><span>40 days</span>
                            </div>
                        </div>

                        <button onclick="calculateOvulation()"
                            class="w-full relative overflow-hidden group/btn bg-gradient-to-r from-teal-600 to-emerald-600 hover:from-teal-700 hover:to-emerald-700 text-white font-bold py-4 rounded-xl transition-all duration-300 shadow-[0_6px_20px_rgba(15,118,110,0.28)] hover:shadow-[0_10px_30px_rgba(15,118,110,0.38)] hover:-translate-y-0.5 text-sm tracking-wide flex items-center justify-center gap-2.5">
                            <i class="fa-solid fa-magnifying-glass-chart group-hover/btn:scale-110 transition-transform duration-200"></i>
                            Calculate My Fertile Window
                            <i class="fa-solid fa-arrow-right text-xs opacity-70 group-hover/btn:translate-x-1 transition-transform duration-200"></i>
                            <span class="btn-shimmer"></span>
                        </button>
                    </div>

                    <!-- Result -->
                    <div id="ov-result" class="hidden result-reveal">
                        <div class="flex items-center gap-2 mb-5 p-3 rounded-xl bg-emerald-50 border border-emerald-100">
                            <i class="fa-solid fa-circle-check text-emerald-500 text-lg flex-shrink-0"></i>
                            <div>
                                <p class="text-xs font-bold text-emerald-800">Your fertile window has been calculated</p>
                                <p class="text-[11px] text-emerald-600">Based on your <span id="ov-cycle-result-label">28-day</span> cycle</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-3 gap-3 mb-5">
                            <div class="text-center p-4 rounded-2xl bg-teal-50 border border-teal-100">
                                <i class="fa-solid fa-seedling text-teal-500 text-lg mb-2 block"></i>
                                <p class="text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">Fertile Window</p>
                                <p id="ov-start" class="text-xs font-extrabold text-teal-700 leading-tight">—</p>
                            </div>
                            <div class="text-center p-4 rounded-2xl bg-emerald-50 border border-emerald-200 ring-2 ring-emerald-400 relative">
                                <span class="absolute -top-2.5 left-1/2 -translate-x-1/2 text-[10px] font-black bg-emerald-500 text-white px-2 py-0.5 rounded-full whitespace-nowrap">PEAK DAY</span>
                                <i class="fa-solid fa-star text-emerald-500 text-lg mb-2 block animate-pulse"></i>
                                <p class="text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">Ovulation</p>
                                <p id="ov-peak" class="text-sm font-extrabold text-emerald-700 leading-tight">—</p>
                            </div>
                            <div class="text-center p-4 rounded-2xl bg-slate-50 border border-slate-200">
                                <i class="fa-solid fa-calendar-xmark text-slate-400 text-lg mb-2 block"></i>
                                <p class="text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">Next Period</p>
                                <p id="ov-next" class="text-sm font-extrabold text-slate-700 leading-tight">—</p>
                            </div>
                        </div>

                        <div class="mb-5">
                            <div class="flex items-center justify-between text-[11px] text-slate-500 font-semibold mb-1.5">
                                <span>Fertile window duration</span><span class="text-teal-600 font-bold">6 days</span>
                            </div>
                            <div class="h-2 bg-slate-100 rounded-full overflow-hidden">
                                <div id="ov-bar" class="h-full bg-gradient-to-r from-teal-400 to-emerald-400 rounded-full w-0 transition-all duration-1000"></div>
                            </div>
                        </div>

                        <p class="text-[11px] text-slate-400 leading-relaxed mb-4">
                            <i class="fa-solid fa-circle-info text-amber-400 mr-1"></i>
                            Estimates based on average cycle patterns. For irregular cycles or PCOS, consult Dr. Adnan for a precise ultrasound-tracked assessment.
                        </p>
                        <button onclick="resetOvulation()"
                            class="w-full flex items-center justify-center gap-2 text-sm font-bold text-teal-600 hover:text-teal-800 border-2 border-teal-100 hover:border-teal-300 hover:bg-teal-50 py-3 rounded-xl transition-all duration-200">
                            <i class="fa-solid fa-rotate-left text-xs"></i> Recalculate
                        </button>
                    </div>
                </div>

                <div class="px-8 py-4 bg-slate-50/80 border-t border-slate-100 flex items-center justify-between">
                    <p class="text-[11px] text-slate-400 font-medium flex items-center gap-1.5">
                        <i class="fa-solid fa-flask text-teal-400"></i> Clinically informed estimate
                    </p>
                    <a href="/tools/ovulation-calculator.php" class="text-[11px] font-bold text-teal-600 hover:text-teal-800 flex items-center gap-1 transition-colors group/link">
                        Advanced tool <i class="fa-solid fa-arrow-right text-[9px] group-hover/link:translate-x-0.5 transition-transform"></i>
                    </a>
                </div>
            </div>
            <!-- END WIDGET 1 -->


            <!-- WIDGET 2: FERTILITY AGE CLOCK (INDIGO) -->
            <div class="widget-card group relative rounded-3xl bg-white border border-slate-200/80 shadow-[0_4px_24px_rgba(0,0,0,0.06)] hover:shadow-[0_16px_48px_rgba(99,102,241,0.12)] transition-all duration-500 hover:-translate-y-1.5 overflow-hidden fade-in" style="transition-delay:120ms;">

                <div class="h-1.5 w-full bg-gradient-to-r from-indigo-500 via-violet-400 to-indigo-600"></div>
                <div class="absolute -top-16 -right-16 w-48 h-48 rounded-full bg-indigo-50 blur-3xl opacity-60 pointer-events-none group-hover:opacity-100 transition-opacity duration-500"></div>

                <div class="p-8 relative z-10">
                    <!-- Header -->
                    <div class="flex items-start justify-between mb-7">
                        <div class="flex items-center gap-4">
                            <div class="widget-icon-indigo relative w-14 h-14 rounded-2xl bg-gradient-to-br from-indigo-500 to-violet-600 flex items-center justify-center shadow-[0_8px_20px_rgba(99,102,241,0.3)] flex-shrink-0">
                                <i class="fa-solid fa-clock-rotate-left text-white text-xl"></i>
                                <span class="absolute -top-1 -right-1 w-4 h-4 rounded-full bg-violet-400 border-2 border-white animate-ping opacity-75"></span>
                                <span class="absolute -top-1 -right-1 w-4 h-4 rounded-full bg-violet-400 border-2 border-white"></span>
                            </div>
                            <div>
                                <h3 class="text-xl font-extrabold text-slate-900 tracking-tight">Fertility Age Clock</h3>
                                <p class="text-sm text-slate-400 font-medium mt-0.5 flex items-center gap-1.5">
                                    <i class="fa-solid fa-circle-dot text-violet-400 text-[10px]"></i>
                                    Know your reproductive timeline
                                </p>
                            </div>
                        </div>
                        <span class="hidden sm:flex items-center gap-1.5 text-[11px] font-bold text-indigo-600 bg-indigo-50 border border-indigo-100 px-3 py-1.5 rounded-full">
                            <i class="fa-solid fa-lock text-[9px]"></i> Private
                        </span>
                    </div>

                    <!-- Form -->
                    <div id="fac-form" class="space-y-5">
                        <div>
                            <label class="flex items-center gap-2 text-xs font-bold text-slate-500 uppercase tracking-widest mb-2">
                                <i class="fa-solid fa-cake-candles text-indigo-500"></i>
                                Your Date of Birth
                            </label>
                            <div class="relative">
                                <i class="fa-regular fa-calendar absolute left-4 top-1/2 -translate-y-1/2 text-slate-300 text-sm pointer-events-none"></i>
                                <input type="date" id="fac-dob"
                                    class="w-full pl-11 pr-4 py-3.5 rounded-xl border-2 border-slate-100 bg-slate-50/70 text-slate-800 font-semibold text-sm focus:outline-none focus:border-indigo-400 focus:bg-white transition-all duration-200">
                            </div>
                        </div>

                        <div>
                            <label class="flex items-center gap-2 text-xs font-bold text-slate-500 uppercase tracking-widest mb-2.5">
                                <i class="fa-solid fa-venus-mars text-indigo-500"></i>
                                Biological Sex
                            </label>
                            <div class="grid grid-cols-2 gap-3">
                                <button onclick="selectFacSex('female', this)" id="sex-btn-female"
                                    class="sex-toggle-btn flex items-center justify-center gap-2.5 py-3.5 rounded-xl border-2 border-slate-200 text-sm font-bold text-slate-500 hover:border-indigo-300 hover:bg-indigo-50/60 transition-all duration-200">
                                    <i class="fa-solid fa-venus text-slate-300 text-base transition-colors duration-200"></i> Female
                                </button>
                                <button onclick="selectFacSex('male', this)" id="sex-btn-male"
                                    class="sex-toggle-btn flex items-center justify-center gap-2.5 py-3.5 rounded-xl border-2 border-slate-200 text-sm font-bold text-slate-500 hover:border-indigo-300 hover:bg-indigo-50/60 transition-all duration-200">
                                    <i class="fa-solid fa-mars text-slate-300 text-base transition-colors duration-200"></i> Male
                                </button>
                            </div>
                        </div>

                        <button onclick="calculateFertilityAge()"
                            class="w-full relative overflow-hidden group/btn bg-gradient-to-r from-indigo-600 to-violet-600 hover:from-indigo-700 hover:to-violet-700 text-white font-bold py-4 rounded-xl transition-all duration-300 shadow-[0_6px_20px_rgba(99,102,241,0.28)] hover:shadow-[0_10px_30px_rgba(99,102,241,0.38)] hover:-translate-y-0.5 text-sm tracking-wide flex items-center justify-center gap-2.5">
                            <i class="fa-solid fa-timeline group-hover/btn:scale-110 transition-transform duration-200"></i>
                            Assess My Fertility Timeline
                            <i class="fa-solid fa-arrow-right text-xs opacity-70 group-hover/btn:translate-x-1 transition-transform duration-200"></i>
                            <span class="btn-shimmer"></span>
                        </button>
                    </div>

                    <!-- Result -->
                    <div id="fac-result" class="hidden result-reveal">
                        <!-- Animated ring + tier label -->
                        <div class="flex items-center gap-6 mb-5">
                            <div class="relative flex-shrink-0" style="width:96px; height:96px;">
                                <svg class="-rotate-90" width="96" height="96" viewBox="0 0 96 96">
                                    <circle cx="48" cy="48" r="40" fill="none" stroke="#f1f5f9" stroke-width="7"/>
                                    <circle cx="48" cy="48" r="40" fill="none"
                                        id="fac-ring" stroke="url(#facGrad)"
                                        stroke-width="7" stroke-linecap="round"
                                        stroke-dasharray="251" stroke-dashoffset="251"
                                        style="transition: stroke-dashoffset 1.2s cubic-bezier(0.34,1.56,0.64,1)"/>
                                    <defs>
                                        <linearGradient id="facGrad" x1="0%" y1="0%" x2="100%" y2="0%">
                                            <stop offset="0%" id="fac-grad-stop1" style="stop-color:#6366f1"/>
                                            <stop offset="100%" id="fac-grad-stop2" style="stop-color:#8b5cf6"/>
                                        </linearGradient>
                                    </defs>
                                </svg>
                                <div class="absolute inset-0 flex flex-col items-center justify-center">
                                    <span id="fac-icon" class="text-xl leading-none">&#8987;</span>
                                    <span id="fac-age-num" class="text-xs font-black text-slate-700 mt-0.5">— yrs</span>
                                </div>
                            </div>
                            <div class="flex-1">
                                <p id="fac-tier-badge" class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold mb-2 bg-indigo-100 text-indigo-700"></p>
                                <p id="fac-headline" class="text-sm font-extrabold text-slate-800 leading-snug mb-1"></p>
                                <p id="fac-subtext" class="text-xs text-slate-500 leading-relaxed"></p>
                            </div>
                        </div>

                        <!-- Spectrum bar -->
                        <div class="mb-5">
                            <div class="flex justify-between text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">
                                <span>Peak</span><span>Declining</span><span>Critical</span>
                            </div>
                            <div class="h-3 bg-slate-100 rounded-full overflow-hidden">
                                <div id="fac-spectrum-bar" class="h-full rounded-full transition-all duration-1000 ease-out bg-gradient-to-r from-indigo-400 to-violet-500" style="width:0%"></div>
                            </div>
                        </div>

                        <div id="fac-insight-box" class="rounded-xl p-4 mb-4 text-sm leading-relaxed font-medium border bg-indigo-50 text-indigo-800 border-indigo-200"></div>

                        <p class="text-[11px] text-slate-400 leading-relaxed mb-4">
                            <i class="fa-solid fa-triangle-exclamation text-amber-400 mr-1"></i>
                            Individual fertility varies significantly. An AMH blood test and consultation with Dr. Adnan gives a precise personal assessment.
                        </p>
                        <button onclick="resetFertilityAge()"
                            class="w-full flex items-center justify-center gap-2 text-sm font-bold text-indigo-600 hover:text-indigo-800 border-2 border-indigo-100 hover:border-indigo-300 hover:bg-indigo-50 py-3 rounded-xl transition-all duration-200">
                            <i class="fa-solid fa-rotate-left text-xs"></i> Recalculate
                        </button>
                    </div>
                </div>

                <div class="px-8 py-4 bg-slate-50/80 border-t border-slate-100 flex items-center justify-between">
                    <p class="text-[11px] text-slate-400 font-medium flex items-center gap-1.5">
                        <i class="fa-solid fa-chart-line text-indigo-400"></i> Evidence-based guidance
                    </p>
                    <a href="/tools/fertility-age-clock.php" class="text-[11px] font-bold text-indigo-600 hover:text-indigo-800 flex items-center gap-1 transition-colors group/link">
                        Advanced tool <i class="fa-solid fa-arrow-right text-[9px] group-hover/link:translate-x-0.5 transition-transform"></i>
                    </a>
                </div>
            </div>
            <!-- END WIDGET 2 -->

        </div>


        <!-- 4 TOOL CARDS -->
        <div class="fade-in" style="transition-delay:200ms;">
            <div class="text-center mb-8">
                <p class="text-sm font-bold text-slate-400 uppercase tracking-widest flex items-center justify-center gap-2">
                    <span class="w-12 h-px bg-slate-200 block"></span>
                    More Fertility Tools
                    <span class="w-12 h-px bg-slate-200 block"></span>
                </p>
            </div>

            <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-5">

                <a href="/tools/ivf-success-calculator.php"
                    class="tool-card group relative flex flex-col p-6 rounded-2xl bg-white border border-slate-200 hover:border-teal-300 shadow-sm hover:shadow-[0_12px_32px_rgba(15,118,110,0.10)] transition-all duration-400 hover:-translate-y-1.5 overflow-hidden fade-in" style="transition-delay:220ms;">
                    <div class="absolute inset-0 bg-gradient-to-br from-teal-50/0 group-hover:from-teal-50/70 to-transparent transition-all duration-400 pointer-events-none rounded-2xl"></div>
                    <div class="relative z-10">
                        <div class="w-12 h-12 rounded-xl bg-teal-50 group-hover:bg-teal-600 flex items-center justify-center mb-4 transition-all duration-300 shadow-sm group-hover:shadow-[0_6px_16px_rgba(15,118,110,0.3)]">
                            <i class="fa-solid fa-percent text-teal-600 group-hover:text-white text-lg transition-colors duration-300"></i>
                        </div>
                        <h4 class="font-extrabold text-slate-800 mb-1.5 text-base group-hover:text-teal-800 transition-colors">IVF Success Rate</h4>
                        <p class="text-xs text-slate-400 leading-relaxed mb-5">Estimate your personalized IVF success probability based on age, AMH &amp; diagnosis.</p>
                        <div class="flex items-center gap-1.5 text-xs font-bold text-teal-600 mt-auto group-hover:gap-2.5 transition-all">
                            <span>Try Calculator</span>
                            <i class="fa-solid fa-arrow-right text-[10px] group-hover:translate-x-1 transition-transform"></i>
                        </div>
                    </div>
                    <div class="absolute bottom-3 right-3 opacity-[0.04] group-hover:opacity-[0.08] transition-opacity">
                        <i class="fa-solid fa-percent text-teal-600 text-6xl"></i>
                    </div>
                </a>

                <a href="/tools/semen-analysis-interpreter.php"
                    class="tool-card group relative flex flex-col p-6 rounded-2xl bg-white border border-slate-200 hover:border-sky-300 shadow-sm hover:shadow-[0_12px_32px_rgba(14,165,233,0.10)] transition-all duration-400 hover:-translate-y-1.5 overflow-hidden fade-in" style="transition-delay:280ms;">
                    <div class="absolute inset-0 bg-gradient-to-br from-sky-50/0 group-hover:from-sky-50/70 to-transparent transition-all duration-400 pointer-events-none rounded-2xl"></div>
                    <div class="relative z-10">
                        <div class="w-12 h-12 rounded-xl bg-sky-50 group-hover:bg-sky-600 flex items-center justify-center mb-4 transition-all duration-300 shadow-sm group-hover:shadow-[0_6px_16px_rgba(14,165,233,0.3)]">
                            <i class="fa-solid fa-microscope text-sky-600 group-hover:text-white text-lg transition-colors duration-300"></i>
                        </div>
                        <h4 class="font-extrabold text-slate-800 mb-1.5 text-base group-hover:text-sky-800 transition-colors">Semen Analysis</h4>
                        <p class="text-xs text-slate-400 leading-relaxed mb-5">Understand your semen analysis report with WHO reference ranges clearly explained.</p>
                        <div class="flex items-center gap-1.5 text-xs font-bold text-sky-600 mt-auto group-hover:gap-2.5 transition-all">
                            <span>Interpret Report</span>
                            <i class="fa-solid fa-arrow-right text-[10px] group-hover:translate-x-1 transition-transform"></i>
                        </div>
                    </div>
                    <div class="absolute bottom-3 right-3 opacity-[0.04] group-hover:opacity-[0.08] transition-opacity">
                        <i class="fa-solid fa-microscope text-sky-600 text-6xl"></i>
                    </div>
                </a>

                <a href="/tools/ivf-cost-estimator.php"
                    class="tool-card group relative flex flex-col p-6 rounded-2xl bg-white border border-slate-200 hover:border-emerald-300 shadow-sm hover:shadow-[0_12px_32px_rgba(16,185,129,0.10)] transition-all duration-400 hover:-translate-y-1.5 overflow-hidden fade-in" style="transition-delay:340ms;">
                    <div class="absolute inset-0 bg-gradient-to-br from-emerald-50/0 group-hover:from-emerald-50/70 to-transparent transition-all duration-400 pointer-events-none rounded-2xl"></div>
                    <div class="relative z-10">
                        <div class="w-12 h-12 rounded-xl bg-emerald-50 group-hover:bg-emerald-600 flex items-center justify-center mb-4 transition-all duration-300 shadow-sm group-hover:shadow-[0_6px_16px_rgba(16,185,129,0.3)]">
                            <i class="fa-solid fa-coins text-emerald-600 group-hover:text-white text-lg transition-colors duration-300"></i>
                        </div>
                        <h4 class="font-extrabold text-slate-800 mb-1.5 text-base group-hover:text-emerald-800 transition-colors">IVF Cost Estimator</h4>
                        <p class="text-xs text-slate-400 leading-relaxed mb-5">Get a transparent IVF treatment cost estimate tailored to your clinical needs.</p>
                        <div class="flex items-center gap-1.5 text-xs font-bold text-emerald-600 mt-auto group-hover:gap-2.5 transition-all">
                            <span>Estimate Costs</span>
                            <i class="fa-solid fa-arrow-right text-[10px] group-hover:translate-x-1 transition-transform"></i>
                        </div>
                    </div>
                    <div class="absolute bottom-3 right-3 opacity-[0.04] group-hover:opacity-[0.08] transition-opacity">
                        <i class="fa-solid fa-coins text-emerald-600 text-6xl"></i>
                    </div>
                </a>

                <a href="/tools/ivf-timeline-calculator.php"
                    class="tool-card group relative flex flex-col p-6 rounded-2xl bg-white border border-slate-200 hover:border-violet-300 shadow-sm hover:shadow-[0_12px_32px_rgba(139,92,246,0.10)] transition-all duration-400 hover:-translate-y-1.5 overflow-hidden fade-in" style="transition-delay:400ms;">
                    <div class="absolute inset-0 bg-gradient-to-br from-violet-50/0 group-hover:from-violet-50/70 to-transparent transition-all duration-400 pointer-events-none rounded-2xl"></div>
                    <div class="relative z-10">
                        <div class="w-12 h-12 rounded-xl bg-violet-50 group-hover:bg-violet-600 flex items-center justify-center mb-4 transition-all duration-300 shadow-sm group-hover:shadow-[0_6px_16px_rgba(139,92,246,0.3)]">
                            <i class="fa-solid fa-timeline text-violet-600 group-hover:text-white text-lg transition-colors duration-300"></i>
                        </div>
                        <h4 class="font-extrabold text-slate-800 mb-1.5 text-base group-hover:text-violet-800 transition-colors">IVF Timeline</h4>
                        <p class="text-xs text-slate-400 leading-relaxed mb-5">Map your complete IVF journey from day one of stimulation to embryo transfer.</p>
                        <div class="flex items-center gap-1.5 text-xs font-bold text-violet-600 mt-auto group-hover:gap-2.5 transition-all">
                            <span>View My Timeline</span>
                            <i class="fa-solid fa-arrow-right text-[10px] group-hover:translate-x-1 transition-transform"></i>
                        </div>
                    </div>
                    <div class="absolute bottom-3 right-3 opacity-[0.04] group-hover:opacity-[0.08] transition-opacity">
                        <i class="fa-solid fa-timeline text-violet-600 text-6xl"></i>
                    </div>
                </a>

            </div>
        </div>

        <!-- CTA Strip -->
        <div class="mt-12 text-center fade-in" style="transition-delay:450ms;">
            <p class="text-slate-400 text-sm mb-5 font-medium">
                <i class="fa-solid fa-circle-info text-teal-400 mr-1.5"></i>
                Tools give you a starting point. A consultation gives you a complete plan.
            </p>
            <a href="https://wa.me/923111101483"
                class="inline-flex items-center gap-3 bg-teal-700 hover:bg-teal-800 text-white px-8 py-4 rounded-xl font-bold text-sm shadow-[0_8px_25px_rgba(15,118,110,0.25)] hover:shadow-[0_12px_35px_rgba(15,118,110,0.35)] hover:-translate-y-0.5 transition-all duration-200">
                <i class="fa-brands fa-whatsapp text-lg"></i>
                Book a Personal Consultation with Dr. Adnan
                <i class="fa-solid fa-arrow-right text-xs opacity-70"></i>
            </a>
        </div>

    </div>
</section>


<!-- STYLES — add inside <head> or your main CSS file -->
<style>
/* Slider track */
.ov-slider {
    background: linear-gradient(to right, #0f766e 50%, #d1fae5 50%);
}
input[type=range].ov-slider::-webkit-slider-thumb {
    -webkit-appearance: none;
    width: 20px; height: 20px;
    border-radius: 50%;
    background: #0f766e;
    cursor: pointer;
    box-shadow: 0 0 0 3px white, 0 0 0 5px rgba(15,118,110,0.25);
    transition: box-shadow 0.2s;
}
input[type=range].ov-slider::-webkit-slider-thumb:hover {
    box-shadow: 0 0 0 3px white, 0 0 0 7px rgba(15,118,110,0.35);
}
input[type=range].ov-slider::-moz-range-thumb {
    width: 20px; height: 20px; border-radius: 50%;
    background: #0f766e; cursor: pointer;
    border: 3px solid white;
    box-shadow: 0 0 0 2px rgba(15,118,110,0.3);
}

/* Shimmer sweep on buttons */
.btn-shimmer {
    position: absolute; inset: 0;
    background: linear-gradient(105deg, transparent 40%, rgba(255,255,255,0.18) 50%, transparent 60%);
    background-size: 200% 100%;
    background-position: 200% 0;
    transition: background-position 0.6s ease;
    border-radius: inherit; pointer-events: none;
}
button:hover .btn-shimmer { background-position: -200% 0; }

/* Result panel entrance */
@keyframes resultReveal {
    from { opacity:0; transform:translateY(10px); }
    to   { opacity:1; transform:translateY(0); }
}
.result-reveal { animation: resultReveal 0.4s cubic-bezier(0.16,1,0.3,1) both; }

/* Floating orbs */
@keyframes orbFloat {
    0%,100%{ transform:translateY(0); }
    50%    { transform:translateY(-22px); }
}
.tools-orb-left  { animation: orbFloat  8s ease-in-out infinite; }
.tools-orb-right { animation: orbFloat 11s ease-in-out infinite reverse; }

/* Widget icon entrance */
@keyframes iconPop {
    0%  { transform:scale(0.75); opacity:0; }
    65% { transform:scale(1.06); }
    100%{ transform:scale(1);    opacity:1; }
}
.widget-icon-teal,
.widget-icon-indigo { animation: iconPop 0.55s cubic-bezier(0.34,1.56,0.64,1) both; }

/* Shake */
@keyframes shake {
    0%,100%{transform:translateX(0)}
    20%{transform:translateX(-5px)}
    40%{transform:translateX(5px)}
    60%{transform:translateX(-3px)}
    80%{transform:translateX(3px)}
}
</style>


<!-- JAVASCRIPT — place just before <?php include("includes/footer.php"); ?> -->
<script>
// ── Shared state ──────────────────────────────────────────
let facSex = 'female';

// ── Utilities ─────────────────────────────────────────────
function fmtDate(d) {
    return d.toLocaleDateString('en-PK', { day:'numeric', month:'short' });
}
function addDays(date, n) {
    const d = new Date(date); d.setDate(d.getDate() + n); return d;
}
function shakeEl(id) {
    const el = document.getElementById(id);
    if (!el) return;
    el.style.animation = 'none'; el.offsetHeight;
    el.style.animation = 'shake 0.4s ease';
    el.classList.add('border-rose-400','bg-rose-50');
    setTimeout(() => { el.classList.remove('border-rose-400','bg-rose-50'); el.style.animation=''; }, 1500);
}

// ── WIDGET 1: Ovulation Calculator ───────────────────────
function updateOvCycle(input) {
    const v = parseInt(input.value);
    document.getElementById('ov-cycle-label').textContent = v + ' days';
    const pct = ((v - 21) / (40 - 21)) * 100;
    input.style.background = `linear-gradient(to right,#0f766e 0%,#0f766e ${pct}%,#d1fae5 ${pct}%,#d1fae5 100%)`;
}
document.addEventListener('DOMContentLoaded', () => {
    const s = document.getElementById('ov-cycle');
    if (s) updateOvCycle(s);
});

function calculateOvulation() {
    const lmpVal = document.getElementById('ov-lmp').value;
    const cycle  = parseInt(document.getElementById('ov-cycle').value);
    if (!lmpVal) { shakeEl('ov-lmp'); return; }

    const lmp       = new Date(lmpVal);
    const ov        = addDays(lmp, cycle - 14);
    const fStart    = addDays(ov, -5);
    const fEnd      = addDays(ov, 1);
    const nextPer   = addDays(lmp, cycle);

    document.getElementById('ov-cycle-result-label').textContent = cycle + '-day';
    document.getElementById('ov-start').innerHTML = fmtDate(fStart) + '<br><span class="text-[10px] text-slate-400">to ' + fmtDate(fEnd) + '</span>';
    document.getElementById('ov-peak').textContent  = fmtDate(ov);
    document.getElementById('ov-next').textContent  = fmtDate(nextPer);

    setTimeout(() => {
        const bar = document.getElementById('ov-bar');
        if (bar) bar.style.width = Math.round((6 / cycle) * 100) + '%';
    }, 150);

    document.getElementById('ov-form').classList.add('hidden');
    document.getElementById('ov-result').classList.remove('hidden');
}

function resetOvulation() {
    document.getElementById('ov-lmp').value = '';
    const bar = document.getElementById('ov-bar');
    if (bar) bar.style.width = '0%';
    document.getElementById('ov-form').classList.remove('hidden');
    document.getElementById('ov-result').classList.add('hidden');
}

// ── WIDGET 2: Fertility Age Clock ─────────────────────────
function selectFacSex(sex, btn) {
    facSex = sex;
    document.querySelectorAll('.sex-toggle-btn').forEach(b => {
        b.classList.remove('border-indigo-400','bg-indigo-50','text-indigo-700');
        b.classList.add('border-slate-200','text-slate-500');
        b.querySelector('i').classList.remove('text-indigo-500');
        b.querySelector('i').classList.add('text-slate-300');
    });
    btn.classList.remove('border-slate-200','text-slate-500');
    btn.classList.add('border-indigo-400','bg-indigo-50','text-indigo-700');
    btn.querySelector('i').classList.remove('text-slate-300');
    btn.querySelector('i').classList.add('text-indigo-500');
}

function calculateFertilityAge() {
    const dobVal = document.getElementById('fac-dob').value;
    if (!dobVal) { shakeEl('fac-dob'); return; }

    const dob   = new Date(dobVal);
    const today = new Date();
    let age = today.getFullYear() - dob.getFullYear();
    const mo = today.getMonth() - dob.getMonth();
    if (mo < 0 || (mo === 0 && today.getDate() < dob.getDate())) age--;
    if (age < 18 || age > 65) { shakeEl('fac-dob'); return; }

    const d = getFacData(age, facSex);
    document.getElementById('fac-icon').textContent      = d.icon;
    document.getElementById('fac-age-num').textContent   = age + ' yrs';
    document.getElementById('fac-headline').textContent  = d.headline;
    document.getElementById('fac-subtext').textContent   = d.subtext;

    const badge = document.getElementById('fac-tier-badge');
    badge.textContent = d.tier;
    badge.className   = 'inline-flex items-center px-3 py-1 rounded-full text-xs font-bold mb-2 ' + d.badgeClass;

    document.getElementById('fac-grad-stop1').style.stopColor = d.g1;
    document.getElementById('fac-grad-stop2').style.stopColor = d.g2;
    setTimeout(() => {
        document.getElementById('fac-ring').style.strokeDashoffset = 251 - (251 * d.bar / 100);
    }, 150);

    const specBar = document.getElementById('fac-spectrum-bar');
    specBar.className = 'h-full rounded-full transition-all duration-1000 ease-out ' + d.barClass;
    setTimeout(() => { specBar.style.width = d.bar + '%'; }, 150);

    const ins = document.getElementById('fac-insight-box');
    ins.textContent = d.insight;
    ins.className   = 'rounded-xl p-4 mb-4 text-sm leading-relaxed font-medium border ' + d.insightClass;

    document.getElementById('fac-form').classList.add('hidden');
    document.getElementById('fac-result').classList.remove('hidden');
}

function getFacData(age, sex) {
    if (sex === 'female') {
        if (age <= 25) return { tier:'🌸 Peak Fertility', badgeClass:'bg-emerald-100 text-emerald-700', icon:'🌸', g1:'#34d399', g2:'#10b981', headline:'You are in your prime reproductive years.', subtext:'Egg quantity and quality are at their highest. Optimal for natural conception or IVF.', bar:95, barClass:'bg-gradient-to-r from-emerald-400 to-teal-400', insight:'✓ Excellent ovarian reserve expected. IVF success rates exceed 55–65% per transfer in this age group.', insightClass:'bg-emerald-50 text-emerald-800 border-emerald-200' };
        if (age <= 30) return { tier:'🌿 High Fertility', badgeClass:'bg-teal-100 text-teal-700', icon:'🌿', g1:'#2dd4bf', g2:'#0d9488', headline:'Your fertility is strong and favorable.', subtext:'Egg quality remains excellent. High conception rates — a great time to start your family.', bar:80, barClass:'bg-gradient-to-r from-teal-400 to-teal-600', insight:'✓ Good ovarian reserve. Monthly conception probability ~20%. Evaluate after 12+ months if trying.', insightClass:'bg-teal-50 text-teal-800 border-teal-200' };
        if (age <= 35) return { tier:'🕐 Good Fertility', badgeClass:'bg-sky-100 text-sky-700', icon:'🕐', g1:'#38bdf8', g2:'#6366f1', headline:'Fertility is still good with early planning.', subtext:'Gradual natural decline begins, but outcomes remain excellent with the right support.', bar:60, barClass:'bg-gradient-to-r from-sky-400 to-indigo-500', insight:'⚡ AMH assessment recommended if planning soon. Most common age group for IVF with great results.', insightClass:'bg-sky-50 text-sky-800 border-sky-200' };
        if (age <= 38) return { tier:'⏳ Moderate Decline', badgeClass:'bg-amber-100 text-amber-700', icon:'⏳', g1:'#fbbf24', g2:'#f97316', headline:'Timely action matters — support is available.', subtext:'Egg quantity and quality decline more noticeably. Tailored IVF protocols improve outcomes.', bar:40, barClass:'bg-gradient-to-r from-amber-400 to-orange-400', insight:'⚠ Early consultation strongly advised. Personalized stimulation protocols maximize egg yield.', insightClass:'bg-amber-50 text-amber-800 border-amber-200' };
        if (age <= 42) return { tier:'⌛ Significant Decline', badgeClass:'bg-orange-100 text-orange-700', icon:'⌛', g1:'#f97316', g2:'#ef4444', headline:'Advanced IVF protocols are recommended.', subtext:'Ovarian reserve is reduced, but specialized care and PGT testing still achieve pregnancies.', bar:22, barClass:'bg-gradient-to-r from-orange-400 to-rose-500', insight:'⚠ Prompt specialist consultation critical. Donor egg options and advanced protocols available.', insightClass:'bg-orange-50 text-orange-800 border-orange-200' };
        return { tier:'💬 Specialist Needed', badgeClass:'bg-rose-100 text-rose-700', icon:'💬', g1:'#f43f5e', g2:'#dc2626', headline:'All options are still on the table.', subtext:'Advanced IVF, donor programs, and specialist care can still help build your family.', bar:8, barClass:'bg-gradient-to-r from-rose-400 to-rose-600', insight:'📋 Dr. Adnan will evaluate all options including donor egg programs and emerging stem cell therapies.', insightClass:'bg-rose-50 text-rose-800 border-rose-200' };
    } else {
        if (age <= 35) return { tier:'💪 Peak Fertility', badgeClass:'bg-emerald-100 text-emerald-700', icon:'💪', g1:'#34d399', g2:'#10b981', headline:'Male fertility is at its absolute peak.', subtext:'Sperm count, motility, and morphology are typically optimal.', bar:92, barClass:'bg-gradient-to-r from-emerald-400 to-teal-500', insight:'✓ Excellent sperm parameters expected. A baseline semen analysis is a smart investment.', insightClass:'bg-emerald-50 text-emerald-800 border-emerald-200' };
        if (age <= 45) return { tier:'✅ Good Fertility', badgeClass:'bg-teal-100 text-teal-700', icon:'✅', g1:'#2dd4bf', g2:'#38bdf8', headline:'Male fertility remains strong.', subtext:'Gradual sperm DNA quality changes begin but conception is highly achievable.', bar:70, barClass:'bg-gradient-to-r from-teal-400 to-sky-500', insight:'✓ DNA fragmentation testing recommended alongside standard semen analysis.', insightClass:'bg-teal-50 text-teal-800 border-teal-200' };
        if (age <= 50) return { tier:'⏳ Gradual Decline', badgeClass:'bg-amber-100 text-amber-700', icon:'⏳', g1:'#fbbf24', g2:'#f97316', headline:'Sperm quality is gradually declining.', subtext:'DNA fragmentation increases. Manageable with hormonal optimization and ICSI.', bar:48, barClass:'bg-gradient-to-r from-amber-400 to-orange-500', insight:'⚡ DNA fragmentation index testing advised. Antioxidant therapy can meaningfully improve sperm quality.', insightClass:'bg-amber-50 text-amber-800 border-amber-200' };
        return { tier:'💬 Specialist Advised', badgeClass:'bg-orange-100 text-orange-700', icon:'💬', g1:'#f97316', g2:'#ef4444', headline:'A comprehensive evaluation is recommended.', subtext:'Hormonal assessment, sperm retrieval, and ICSI offer effective solutions.', bar:25, barClass:'bg-gradient-to-r from-orange-400 to-rose-500', insight:'📋 Full hormonal and semen evaluation will identify the best treatment pathway forward.', insightClass:'bg-orange-50 text-orange-800 border-orange-200' };
    }
}

function resetFertilityAge() {
    facSex = 'female';
    document.getElementById('fac-dob').value = '';
    document.getElementById('fac-ring').style.strokeDashoffset = '251';
    const sb = document.getElementById('fac-spectrum-bar');
    if (sb) sb.style.width = '0%';
    document.querySelectorAll('.sex-toggle-btn').forEach(b => {
        b.classList.remove('border-indigo-400','bg-indigo-50','text-indigo-700');
        b.classList.add('border-slate-200','text-slate-500');
        b.querySelector('i').classList.remove('text-indigo-500');
        b.querySelector('i').classList.add('text-slate-300');
    });
    document.getElementById('fac-form').classList.remove('hidden');
    document.getElementById('fac-result').classList.add('hidden');
}
</script>
<!-- SPLIT SECTIONS FOR MALE/FEMALE FACTOR -->
<section class="py-24 bg-white overflow-hidden relative">
    <div class="max-w-7xl mx-auto px-6">
        
        <!-- Male Infertility -->
        <div class="grid lg:grid-cols-2 gap-16 items-center mb-32 fade-in">
            <div class="order-2 lg:order-1 relative rounded-3xl overflow-hidden shadow-2xl group">
                <!-- Placeholder for an emotionally resonant or high-quality medical background image -->
                <div class="aspect-[4/3] bg-gradient-to-br from-slate-200 to-slate-100 flex items-center justify-center p-12">
                     <div class="text-center p-8 bg-white/80 backdrop-blur rounded-2xl border border-white/60 shadow-lg">
                        <svg class="w-12 h-12 text-indigo-600 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                        <h4 class="font-bold text-xl text-slate-800">Male Diagnostics</h4>
                     </div>
                </div>
            </div>
            <div class="order-1 lg:order-2">
                <h2 class="text-3xl lg:text-4xl font-bold text-slate-900 mb-6">Overcoming Male Infertility</h2>
                <p class="text-lg text-slate-600 mb-6 leading-relaxed">
                    Low sperm count, poor motility, azoospermia, or high DNA fragmentation shouldn't end your hopes. Advanced micro-surgical retrieval and ICSI have revolutionized male infertility treatment.
                </p>
                <ul class="space-y-3 mb-10 text-slate-700">
                    <li class="flex items-center gap-3"><span class="w-1.5 h-1.5 rounded-full bg-teal-500"></span> Hormonal optimization & Varicocele repair</li>
                    <li class="flex items-center gap-3"><span class="w-1.5 h-1.5 rounded-full bg-teal-500"></span> Micro-TESE & complex sperm retrieval</li>
                    <li class="flex items-center gap-3"><span class="w-1.5 h-1.5 rounded-full bg-teal-500"></span> ICSI specifically tailored for severe male factor</li>
                </ul>
                <a href="/male-infertility/" class="btn-primary">Explore Male Treatments</a>
            </div>
        </div>

        <!-- Female Infertility -->
        <div class="grid lg:grid-cols-2 gap-16 items-center fade-in">
            <div>
                <h2 class="text-3xl lg:text-4xl font-bold text-slate-900 mb-6">Female Infertility Optimization</h2>
                <p class="text-lg text-slate-600 mb-6 leading-relaxed">
                    From diminished ovarian reserve to PCOS and endometriosis, navigating female infertility requires a nuanced, individualized stimulation protocol to maximize egg quality and yield.
                </p>
                <ul class="space-y-3 mb-10 text-slate-700">
                    <li class="flex items-center gap-3"><span class="w-1.5 h-1.5 rounded-full bg-teal-500"></span> Polycystic Ovary Syndrome (PCOS) management</li>
                    <li class="flex items-center gap-3"><span class="w-1.5 h-1.5 rounded-full bg-teal-500"></span> Low ovarian reserve & advanced maternal age protocols</li>
                    <li class="flex items-center gap-3"><span class="w-1.5 h-1.5 rounded-full bg-teal-500"></span> Endometriosis and tubal factor planning</li>
                </ul>
                <a href="/female-infertility/" class="btn-primary">Explore Female Treatments</a>
            </div>
            <div class="relative rounded-3xl overflow-hidden shadow-2xl group">
                <div class="aspect-[4/3] bg-gradient-to-br from-teal-100 to-emerald-50 flex items-center justify-center p-12">
                     <div class="text-center p-8 bg-white/80 backdrop-blur rounded-2xl border border-white/60 shadow-lg">
                        <svg class="w-12 h-12 text-teal-600 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                        </svg>
                        <h4 class="font-bold text-xl text-slate-800">Advanced Protocols</h4>
                     </div>
                </div>
            </div>
        </div>

    </div>
</section>

<!-- AFFILIATIONS / LOCATIONS -->
<section class="section-md bg-white border-t border-slate-100 fade-in">
    <div class="max-w-7xl mx-auto px-6 text-center">
        <h2 class="text-sm font-bold text-slate-400 uppercase tracking-widest mb-10">Affiliated Clinical Practice Locations & Online Consultations (Serving Lahore, Multan, Okara, Sahiwal, Rawalpindi & All Pakistan)</h2>
        
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-6 opacity-80">
            <div class="p-6 rounded-2xl bg-slate-50 border border-slate-100 flex flex-col items-center justify-center">
                <p class="font-bold text-slate-800">Healthnox Medical Center</p>
                <p class="text-xs text-slate-500 mt-1 uppercase tracking-wide">Lahore</p>
            </div>
            <div class="p-6 rounded-2xl bg-slate-50 border border-slate-100 flex flex-col items-center justify-center">
                <p class="font-bold text-slate-800">Skina International</p>
                <p class="text-xs text-slate-500 mt-1 uppercase tracking-wide">Lahore</p>
            </div>
            <div class="p-6 rounded-2xl bg-slate-50 border border-slate-100 flex flex-col items-center justify-center">
                <p class="font-bold text-slate-800">Latif Hospital</p>
                <p class="text-xs text-slate-500 mt-1 uppercase tracking-wide">Lahore</p>
            </div>
            <div class="p-6 rounded-2xl bg-slate-50 border border-slate-100 flex flex-col items-center justify-center">
                <p class="font-bold text-slate-800">AQ Ortho & Gynae</p>
                <p class="text-xs text-slate-500 mt-1 uppercase tracking-wide">Okara</p>
            </div>
        </div>
    </div>
</section>

<!-- CALL TO ACTION -->
<section id="consultation" class="section-lg relative overflow-hidden">
    <div class="absolute inset-0 bg-teal-900"></div>
    <!-- Soft background glow -->
    <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-full max-w-3xl aspect-square bg-teal-800 rounded-full blur-[120px] opacity-60 pointer-events-none"></div>
    
    <div class="max-w-4xl mx-auto px-6 relative z-10 text-center fade-in">
        <h2 class="text-3xl md:text-5xl font-bold text-white mb-8">Ready to bring clarity to your journey?</h2>
        <p class="text-lg text-teal-100/80 mb-12 max-w-2xl mx-auto">
            Schedule a consultation with Dr. Adnan Jabbar to evaluate your history, assess diagnostic needs, and structure a specialized plan.
        </p>
        
        <div class="flex flex-col sm:flex-row items-center justify-center gap-6">
            <a href="https://wa.me/923111101483" class="bg-white text-teal-900 px-8 py-4 rounded-xl font-bold shadow-xl hover:bg-slate-50 hover:scale-105 transition-all w-full sm:w-auto flex items-center justify-center gap-3">
                <svg class="w-6 h-6 text-green-600" fill="currentColor" viewBox="0 0 24 24"><path d="M12.031 6.172c-3.181 0-5.767 2.586-5.767 5.766 0 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.767-5.766-.001-3.187-2.575-5.77-5.764-5.771z"/></svg>
                WhatsApp Consultation
            </a>
            <a href="/contact/" class="bg-transparent border-2 border-teal-500/50 text-white px-8 py-4 rounded-xl font-bold hover:bg-teal-800 transition-all w-full sm:w-auto">
                View Contact Details
            </a>
        </div>
    </div>
</section>

<?php include("includes/footer.php"); ?>
