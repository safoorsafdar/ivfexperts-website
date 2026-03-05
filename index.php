<?php
$pageTitle = "Dr. Adnan Jabbar | Premium IVF & Fertility Specialist in Lahore";
$metaDescription = "IVF & fertility specialist in Lahore, Pakistan — Dr. Adnan Jabbar. Expert IVF, ICSI, IUI, PCOS & male infertility treatment. Teleconsultations available for Karachi & Islamabad patients.";
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
                <!-- Static SEO H1 — indexed by Google, visible to users -->
                <h1 class="text-3xl md:text-4xl font-extrabold text-slate-900 leading-tight mb-3">
                    Pakistan's Trusted IVF &amp; Fertility Specialist — Dr. Adnan Jabbar, Lahore
                </h1>
                <!-- Visual rotating emotional copy — styled like a heading but is a <p> -->
                <p class="text-3xl md:text-4xl lg:text-5xl font-extrabold text-slate-900 leading-[1.15] mb-6">
                    <span id="hero-title" class="transition-opacity duration-700 block">Parenthood Begins with</span>
                    <span id="hero-highlight" class="text-teal-700 transition-opacity duration-700 block mt-2">Clarity &amp; Strategy.</span>
                </p>
                
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
                    <span class="text-3xl font-extrabold text-teal-700 counter block" data-target="10">0</span>
                    <p class="text-sm font-medium text-slate-500 mt-2">Years of specialized clinical experience</p>
                </div>
                <div>
                    <span class="text-2xl font-bold text-slate-800 flex items-center gap-2 block">
                        Dual Expertise
                    </span>
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
<!-- TOOLS SECTION START -->
<?php
/*
 * ═══════════════════════════════════════════════════════════════
 *  IVF EXPERTS — FERTILITY TOOLS SECTION
 *  File: landing-widgets-section.php
 *
 *  PLACEMENT IN index.php:
 *  Find the closing </section> of the "Architecture of Success"
 *  (DIAGNOSTIC APPROACH) block, then paste this entire file
 *  immediately AFTER that </section> and BEFORE the
 *  <!-- SPLIT SECTIONS FOR MALE/FEMALE FACTOR --> comment.
 * ═══════════════════════════════════════════════════════════════
 */
?>

<!-- ═══════════════════════════════════════════════════════
     FERTILITY TOOLS SECTION
═══════════════════════════════════════════════════════ -->
<section class="py-24 bg-slate-50 relative overflow-hidden" id="fertility-tools">

    <!-- Subtle background texture -->
    <div class="absolute inset-0 pointer-events-none" style="background-image: radial-gradient(circle at 1px 1px, rgba(15,118,110,0.04) 1px, transparent 0); background-size: 32px 32px;"></div>

    <div class="max-w-7xl mx-auto px-6 relative">

        <!-- ── Section Header ───────────────────────────── -->
        <div class="text-center max-w-2xl mx-auto mb-14 fade-in">
            <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-teal-50 border border-teal-100 text-teal-700 text-sm font-semibold mb-5">
                <span class="w-2 h-2 rounded-full bg-teal-500 animate-pulse"></span>
                Free Patient Tools &mdash; No Registration Required
            </div>
            <h2 class="text-3xl md:text-4xl font-bold text-slate-900 mb-4 tracking-tight">
                Understand Your Fertility <span class="text-teal-700">Today</span>
            </h2>
            <p class="text-lg text-slate-500 leading-relaxed">
                Clinically-informed tools used by patients across Pakistan. Get instant clarity on your reproductive health in seconds.
            </p>
        </div>


        <!-- ── Two Widgets ──────────────────────────────── -->
        <div class="grid lg:grid-cols-2 gap-6 mb-10">


            <!-- ┌─────────────────────────────────────┐
                 │  WIDGET 1: OVULATION CALCULATOR     │
                 └─────────────────────────────────────┘ -->
            <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden fade-in" style="transition-delay:100ms;">

                <!-- Accent line -->
                <div class="h-1 bg-gradient-to-r from-teal-500 to-emerald-400"></div>

                <div class="p-7">

                    <!-- Widget title row -->
                    <div class="flex items-center gap-3 mb-6">
                        <div class="w-10 h-10 rounded-xl bg-teal-50 flex items-center justify-center flex-shrink-0">
                            <i class="fa-solid fa-calendar-days text-teal-600 text-base"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-slate-800 leading-tight">Ovulation Calculator</h3>
                            <p class="text-sm text-slate-400 mt-0.5">Find your peak fertile window</p>
                        </div>
                    </div>

                    <!-- ── Form ── -->
                    <div id="ov-form">

                        <!-- Field 1: Last Period -->
                        <div class="mb-4">
                            <label for="ov-lmp" class="block text-sm font-semibold text-slate-600 mb-1.5">
                                First Day of Last Period
                            </label>
                            <input type="date" id="ov-lmp"
                                class="w-full px-4 py-3 rounded-xl border border-gray-200 bg-gray-50 text-slate-800 text-sm font-medium focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-transparent focus:bg-white transition-all duration-200">
                        </div>

                        <!-- Field 2: Cycle Length -->
                        <div class="mb-6">
                            <div class="flex items-center justify-between mb-2">
                                <label class="text-sm font-semibold text-slate-600">Cycle Length</label>
                                <span id="ov-cycle-val" class="text-sm font-bold text-teal-700 bg-teal-50 px-3 py-1 rounded-full border border-teal-100">28 days</span>
                            </div>
                            <input type="range" id="ov-cycle" min="21" max="40" value="28"
                                class="w-full h-2 rounded-full appearance-none cursor-pointer outline-none"
                                style="background: linear-gradient(to right, #0f766e 50%, #e2e8f0 50%);"
                                oninput="ovUpdateSlider(this)">
                            <div class="flex justify-between mt-1.5">
                                <span class="text-xs text-slate-400">21 days</span>
                                <span class="text-xs text-slate-400">40 days</span>
                            </div>
                        </div>

                        <!-- CTA Button -->
                        <button onclick="calculateOvulation()"
                            class="w-full bg-teal-700 hover:bg-teal-800 text-white font-semibold py-3.5 px-6 rounded-xl transition-all duration-200 hover:-translate-y-0.5 shadow-sm hover:shadow-md flex items-center justify-center gap-2 text-sm tracking-wide">
                            <i class="fa-solid fa-magnifying-glass-chart"></i>
                            Calculate My Fertile Window
                        </button>
                    </div>

                    <!-- ── Results (hidden initially) ── -->
                    <div id="ov-result" class="hidden">

                        <!-- Success header -->
                        <div class="flex items-center gap-2.5 bg-emerald-50 border border-emerald-100 rounded-xl px-4 py-3 mb-5">
                            <i class="fa-solid fa-circle-check text-emerald-500 text-base flex-shrink-0"></i>
                            <p class="text-sm font-semibold text-emerald-800">Your fertile window is ready</p>
                        </div>

                        <!-- Three result tiles -->
                        <div class="grid grid-cols-3 gap-3 mb-5">
                            <div class="bg-teal-50 border border-teal-100 rounded-xl p-3 text-center">
                                <i class="fa-solid fa-seedling text-teal-500 text-sm mb-1.5 block"></i>
                                <p class="text-xs font-semibold text-slate-500 mb-1">Window Opens</p>
                                <p id="ov-r-start" class="text-xs font-bold text-teal-700 leading-snug">—</p>
                            </div>
                            <div class="bg-emerald-50 border-2 border-emerald-400 rounded-xl p-3 text-center relative">
                                <span class="absolute -top-2.5 left-1/2 -translate-x-1/2 bg-emerald-500 text-white text-[9px] font-black px-1.5 py-0.5 rounded-full whitespace-nowrap tracking-wide">PEAK</span>
                                <i class="fa-solid fa-star text-emerald-500 text-sm mb-1.5 block animate-pulse"></i>
                                <p class="text-xs font-semibold text-slate-500 mb-1">Ovulation</p>
                                <p id="ov-r-peak" class="text-sm font-bold text-emerald-700 leading-snug">—</p>
                            </div>
                            <div class="bg-slate-50 border border-slate-200 rounded-xl p-3 text-center">
                                <i class="fa-solid fa-calendar-xmark text-slate-400 text-sm mb-1.5 block"></i>
                                <p class="text-xs font-semibold text-slate-500 mb-1">Next Period</p>
                                <p id="ov-r-next" class="text-xs font-bold text-slate-700 leading-snug">—</p>
                            </div>
                        </div>

                        <!-- Progress bar -->
                        <div class="mb-5">
                            <div class="flex justify-between text-xs font-semibold text-slate-400 mb-1.5">
                                <span>Fertile window</span>
                                <span class="text-teal-600 font-bold">6 days</span>
                            </div>
                            <div class="h-2 bg-slate-100 rounded-full overflow-hidden">
                                <div id="ov-r-bar" class="h-full bg-gradient-to-r from-teal-500 to-emerald-400 rounded-full transition-all duration-1000" style="width:0%"></div>
                            </div>
                        </div>

                        <p class="text-xs text-slate-400 leading-relaxed mb-4">
                            <i class="fa-solid fa-circle-info text-amber-400 mr-1"></i>
                            Estimates based on average cycle patterns. For irregular cycles or PCOS, consult Dr. Adnan for a precise assessment.
                        </p>

                        <!-- Reset -->
                        <button onclick="resetOvulation()"
                            class="w-full border border-gray-200 hover:border-teal-300 hover:bg-teal-50 text-slate-600 hover:text-teal-700 font-semibold py-3 px-6 rounded-xl transition-all duration-200 flex items-center justify-center gap-2 text-sm">
                            <i class="fa-solid fa-rotate-left text-xs"></i>
                            Recalculate
                        </button>
                    </div>

                </div>

                <!-- Footer -->
                <div class="px-7 py-3.5 bg-gray-50 border-t border-gray-100 flex items-center justify-between">
                    <span class="text-xs text-slate-400 flex items-center gap-1.5">
                        <i class="fa-solid fa-flask-vial text-teal-400"></i>
                        Clinically informed
                    </span>
                    <a href="/tools/ovulation-calculator.php" class="text-xs font-bold text-teal-600 hover:text-teal-800 flex items-center gap-1 group transition-colors">
                        Full tool
                        <i class="fa-solid fa-arrow-right text-[10px] group-hover:translate-x-0.5 transition-transform duration-150"></i>
                    </a>
                </div>
            </div>
            <!-- END WIDGET 1 -->


            <!-- ┌─────────────────────────────────────┐
                 │  WIDGET 2: FERTILITY AGE CLOCK      │
                 └─────────────────────────────────────┘ -->
            <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden fade-in" style="transition-delay:200ms;">

                <!-- Accent line -->
                <div class="h-1 bg-gradient-to-r from-indigo-500 to-violet-400"></div>

                <div class="p-7">

                    <!-- Widget title row -->
                    <div class="flex items-center gap-3 mb-6">
                        <div class="w-10 h-10 rounded-xl bg-indigo-50 flex items-center justify-center flex-shrink-0">
                            <i class="fa-solid fa-hourglass-half text-indigo-600 text-base"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-slate-800 leading-tight">Fertility Age Clock</h3>
                            <p class="text-sm text-slate-400 mt-0.5">Know your reproductive timeline</p>
                        </div>
                    </div>

                    <!-- ── Form ── -->
                    <div id="fac-form">

                        <!-- Field 1: DOB -->
                        <div class="mb-4">
                            <label for="fac-dob" class="block text-sm font-semibold text-slate-600 mb-1.5">
                                Your Date of Birth
                            </label>
                            <input type="date" id="fac-dob"
                                class="w-full px-4 py-3 rounded-xl border border-gray-200 bg-gray-50 text-slate-800 text-sm font-medium focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent focus:bg-white transition-all duration-200">
                        </div>

                        <!-- Field 2: Sex -->
                        <div class="mb-6">
                            <label class="block text-sm font-semibold text-slate-600 mb-2">Biological Sex</label>
                            <div class="grid grid-cols-2 gap-3">
                                <button id="btn-female" onclick="selectSex('female')"
                                    class="sex-btn flex items-center justify-center gap-2 py-3 rounded-xl border-2 border-gray-200 text-sm font-semibold text-slate-500 hover:border-indigo-300 hover:text-indigo-600 hover:bg-indigo-50 transition-all duration-200">
                                    <i class="fa-solid fa-venus text-base"></i> Female
                                </button>
                                <button id="btn-male" onclick="selectSex('male')"
                                    class="sex-btn flex items-center justify-center gap-2 py-3 rounded-xl border-2 border-gray-200 text-sm font-semibold text-slate-500 hover:border-indigo-300 hover:text-indigo-600 hover:bg-indigo-50 transition-all duration-200">
                                    <i class="fa-solid fa-mars text-base"></i> Male
                                </button>
                            </div>
                        </div>

                        <!-- CTA Button -->
                        <button onclick="calculateFertilityAge()"
                            class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-3.5 px-6 rounded-xl transition-all duration-200 hover:-translate-y-0.5 shadow-sm hover:shadow-md flex items-center justify-center gap-2 text-sm tracking-wide">
                            <i class="fa-solid fa-timeline"></i>
                            Assess My Fertility Timeline
                        </button>
                    </div>

                    <!-- ── Results (hidden initially) ── -->
                    <div id="fac-result" class="hidden">

                        <!-- Ring + tier side by side -->
                        <div class="flex items-center gap-5 mb-5">
                            <!-- SVG ring -->
                            <div class="relative flex-shrink-0" style="width:88px;height:88px;">
                                <svg class="-rotate-90" width="88" height="88" viewBox="0 0 88 88">
                                    <circle cx="44" cy="44" r="36" fill="none" stroke="#f1f5f9" stroke-width="7"/>
                                    <circle cx="44" cy="44" r="36" fill="none"
                                        id="fac-ring"
                                        stroke="url(#facGrad)"
                                        stroke-width="7"
                                        stroke-linecap="round"
                                        stroke-dasharray="226"
                                        stroke-dashoffset="226"
                                        style="transition: stroke-dashoffset 1.2s cubic-bezier(0.34,1.56,0.64,1)"/>
                                    <defs>
                                        <linearGradient id="facGrad" x1="0%" y1="0%" x2="100%" y2="0%">
                                            <stop offset="0%" id="fac-g1" stop-color="#6366f1"/>
                                            <stop offset="100%" id="fac-g2" stop-color="#8b5cf6"/>
                                        </linearGradient>
                                    </defs>
                                </svg>
                                <div class="absolute inset-0 flex flex-col items-center justify-center">
                                    <span id="fac-icon" class="text-lg leading-none mb-0.5">⏳</span>
                                    <span id="fac-age-display" class="text-xs font-black text-slate-700 leading-tight">—</span>
                                </div>
                            </div>

                            <!-- Text -->
                            <div class="flex-1 min-w-0">
                                <span id="fac-badge" class="inline-block px-2.5 py-1 rounded-full text-xs font-bold mb-2 bg-indigo-100 text-indigo-700"></span>
                                <p id="fac-headline" class="text-sm font-bold text-slate-800 leading-snug mb-1"></p>
                                <p id="fac-subtext" class="text-xs text-slate-500 leading-relaxed"></p>
                            </div>
                        </div>

                        <!-- Spectrum bar -->
                        <div class="mb-4">
                            <div class="flex justify-between text-xs font-semibold text-slate-400 mb-1.5">
                                <span>Peak fertility</span><span>Declining</span><span>Critical</span>
                            </div>
                            <div class="h-2.5 bg-slate-100 rounded-full overflow-hidden">
                                <div id="fac-bar" class="h-full rounded-full transition-all duration-1000 bg-gradient-to-r from-indigo-400 to-violet-500" style="width:0%"></div>
                            </div>
                        </div>

                        <!-- Insight box -->
                        <div id="fac-insight" class="rounded-xl p-4 mb-4 text-xs leading-relaxed font-medium border bg-indigo-50 text-indigo-800 border-indigo-100"></div>

                        <p class="text-xs text-slate-400 leading-relaxed mb-4">
                            <i class="fa-solid fa-triangle-exclamation text-amber-400 mr-1"></i>
                            Individual fertility varies. An AMH blood test with Dr. Adnan gives a precise personal assessment.
                        </p>

                        <!-- Reset -->
                        <button onclick="resetFertilityAge()"
                            class="w-full border border-gray-200 hover:border-indigo-300 hover:bg-indigo-50 text-slate-600 hover:text-indigo-700 font-semibold py-3 px-6 rounded-xl transition-all duration-200 flex items-center justify-center gap-2 text-sm">
                            <i class="fa-solid fa-rotate-left text-xs"></i>
                            Recalculate
                        </button>
                    </div>

                </div>

                <!-- Footer -->
                <div class="px-7 py-3.5 bg-gray-50 border-t border-gray-100 flex items-center justify-between">
                    <span class="text-xs text-slate-400 flex items-center gap-1.5">
                        <i class="fa-solid fa-chart-line text-indigo-400"></i>
                        Evidence-based
                    </span>
                    <a href="/tools/fertility-age-clock.php" class="text-xs font-bold text-indigo-600 hover:text-indigo-800 flex items-center gap-1 group transition-colors">
                        Full tool
                        <i class="fa-solid fa-arrow-right text-[10px] group-hover:translate-x-0.5 transition-transform duration-150"></i>
                    </a>
                </div>
            </div>
            <!-- END WIDGET 2 -->

        </div>
        <!-- END WIDGETS GRID -->


        <!-- ── 4 Tool Cards ─────────────────────────────── -->
        <div class="fade-in" style="transition-delay:300ms;">

            <div class="flex items-center justify-center gap-4 mb-8">
                <span class="h-px w-16 bg-gray-200 block"></span>
                <p class="text-xs font-bold text-slate-400 uppercase tracking-widest">More Fertility Tools</p>
                <span class="h-px w-16 bg-gray-200 block"></span>
            </div>

            <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4">

                <!-- Card 1 -->
                <a href="/tools/ivf-success-calculator.php"
                    class="group bg-white rounded-2xl border border-gray-200 p-6 hover:border-teal-200 hover:shadow-md transition-all duration-300 hover:-translate-y-1 fade-in" style="transition-delay:320ms;">
                    <div class="w-10 h-10 rounded-xl bg-teal-50 group-hover:bg-teal-600 flex items-center justify-center mb-4 transition-all duration-300">
                        <i class="fa-solid fa-percent text-teal-600 group-hover:text-white text-sm transition-colors duration-300"></i>
                    </div>
                    <h4 class="font-bold text-slate-800 text-sm mb-2 group-hover:text-teal-800 transition-colors">IVF Success Rate</h4>
                    <p class="text-xs text-slate-400 leading-relaxed mb-4">Estimate your personalized success probability based on age, AMH &amp; diagnosis.</p>
                    <span class="text-xs font-bold text-teal-600 flex items-center gap-1 group-hover:gap-2 transition-all">
                        Try Calculator <i class="fa-solid fa-arrow-right text-[10px]"></i>
                    </span>
                </a>

                <!-- Card 2 -->
                <a href="/tools/semen-analysis-interpreter.php"
                    class="group bg-white rounded-2xl border border-gray-200 p-6 hover:border-sky-200 hover:shadow-md transition-all duration-300 hover:-translate-y-1 fade-in" style="transition-delay:360ms;">
                    <div class="w-10 h-10 rounded-xl bg-sky-50 group-hover:bg-sky-600 flex items-center justify-center mb-4 transition-all duration-300">
                        <i class="fa-solid fa-microscope text-sky-600 group-hover:text-white text-sm transition-colors duration-300"></i>
                    </div>
                    <h4 class="font-bold text-slate-800 text-sm mb-2 group-hover:text-sky-800 transition-colors">Semen Analysis</h4>
                    <p class="text-xs text-slate-400 leading-relaxed mb-4">Understand your semen report with WHO reference ranges clearly explained.</p>
                    <span class="text-xs font-bold text-sky-600 flex items-center gap-1 group-hover:gap-2 transition-all">
                        Interpret Report <i class="fa-solid fa-arrow-right text-[10px]"></i>
                    </span>
                </a>

                <!-- Card 3 -->
                <a href="/tools/ivf-cost-estimator.php"
                    class="group bg-white rounded-2xl border border-gray-200 p-6 hover:border-emerald-200 hover:shadow-md transition-all duration-300 hover:-translate-y-1 fade-in" style="transition-delay:400ms;">
                    <div class="w-10 h-10 rounded-xl bg-emerald-50 group-hover:bg-emerald-600 flex items-center justify-center mb-4 transition-all duration-300">
                        <i class="fa-solid fa-coins text-emerald-600 group-hover:text-white text-sm transition-colors duration-300"></i>
                    </div>
                    <h4 class="font-bold text-slate-800 text-sm mb-2 group-hover:text-emerald-800 transition-colors">IVF Cost Estimator</h4>
                    <p class="text-xs text-slate-400 leading-relaxed mb-4">Get a transparent treatment cost estimate tailored to your clinical profile.</p>
                    <span class="text-xs font-bold text-emerald-600 flex items-center gap-1 group-hover:gap-2 transition-all">
                        Estimate Costs <i class="fa-solid fa-arrow-right text-[10px]"></i>
                    </span>
                </a>

                <!-- Card 4 -->
                <a href="/tools/ivf-timeline-calculator.php"
                    class="group bg-white rounded-2xl border border-gray-200 p-6 hover:border-violet-200 hover:shadow-md transition-all duration-300 hover:-translate-y-1 fade-in" style="transition-delay:440ms;">
                    <div class="w-10 h-10 rounded-xl bg-violet-50 group-hover:bg-violet-600 flex items-center justify-center mb-4 transition-all duration-300">
                        <i class="fa-solid fa-timeline text-violet-600 group-hover:text-white text-sm transition-colors duration-300"></i>
                    </div>
                    <h4 class="font-bold text-slate-800 text-sm mb-2 group-hover:text-violet-800 transition-colors">IVF Timeline</h4>
                    <p class="text-xs text-slate-400 leading-relaxed mb-4">Map your complete IVF journey from first injection to embryo transfer day.</p>
                    <span class="text-xs font-bold text-violet-600 flex items-center gap-1 group-hover:gap-2 transition-all">
                        View Timeline <i class="fa-solid fa-arrow-right text-[10px]"></i>
                    </span>
                </a>

            </div>
        </div>

        <!-- ── Bottom CTA ────────────────────────────────── -->
        <div class="text-center mt-12 fade-in" style="transition-delay:480ms;">
            <p class="text-sm text-slate-400 mb-5">
                <i class="fa-solid fa-circle-info text-teal-500 mr-1.5"></i>
                These tools give you a starting point. A consultation gives you a complete, personalised plan.
            </p>
            <a href="https://wa.me/923111101483"
                class="inline-flex items-center gap-3 bg-teal-700 hover:bg-teal-800 text-white font-semibold px-8 py-4 rounded-xl text-sm shadow-sm hover:shadow-md hover:-translate-y-0.5 transition-all duration-200">
                <i class="fa-brands fa-whatsapp text-base"></i>
                Book a Consultation with Dr. Adnan
            </a>
        </div>

    </div>
</section>
<!-- END FERTILITY TOOLS SECTION -->


<!-- ═══════════════════════════════════════════════════════
     STYLES — add inside <head> or append to style.css
═══════════════════════════════════════════════════════ -->
<style>
/* Range slider thumb */
input[type=range]::-webkit-slider-thumb {
    -webkit-appearance: none;
    width: 18px;
    height: 18px;
    border-radius: 50%;
    background: #0f766e;
    cursor: pointer;
    border: 2px solid #fff;
    box-shadow: 0 0 0 3px rgba(15,118,110,0.2);
    transition: box-shadow 0.15s;
}
input[type=range]::-webkit-slider-thumb:hover {
    box-shadow: 0 0 0 4px rgba(15,118,110,0.3);
}
input[type=range]::-moz-range-thumb {
    width: 18px; height: 18px;
    border-radius: 50%;
    background: #0f766e;
    cursor: pointer;
    border: 2px solid #fff;
    box-shadow: 0 0 0 3px rgba(15,118,110,0.2);
}
/* Sex button active state */
.sex-btn-active {
    border-color: #6366f1 !important;
    background-color: #eef2ff !important;
    color: #4f46e5 !important;
}
/* Result panel entrance */
@keyframes slideUp {
    from { opacity: 0; transform: translateY(8px); }
    to   { opacity: 1; transform: translateY(0); }
}
.result-enter { animation: slideUp 0.35s cubic-bezier(0.16,1,0.3,1) both; }
/* Input error state */
@keyframes shake {
    0%,100%{transform:translateX(0)}
    25%{transform:translateX(-4px)}
    75%{transform:translateX(4px)}
}
.input-error {
    border-color: #fca5a5 !important;
    background-color: #fff1f2 !important;
    animation: shake 0.35s ease;
}
</style>


<!-- ═══════════════════════════════════════════════════════
     JAVASCRIPT — place just before footer include
═══════════════════════════════════════════════════════ -->
<script>
// ── Shared state ─────────────────────────────────────────
var selectedSex = null;

// ── Date formatter ───────────────────────────────────────
function fmtD(d) {
    return d.toLocaleDateString('en-PK', { day: 'numeric', month: 'short' });
}
function addD(date, n) {
    var d = new Date(date);
    d.setDate(d.getDate() + n);
    return d;
}

// ═══════════════════════════════════════════
//  WIDGET 1 — OVULATION CALCULATOR
// ═══════════════════════════════════════════
function ovUpdateSlider(input) {
    var v = parseInt(input.value);
    document.getElementById('ov-cycle-val').textContent = v + ' days';
    var pct = ((v - 21) / (40 - 21)) * 100;
    input.style.background = 'linear-gradient(to right, #0f766e 0%, #0f766e ' + pct + '%, #e2e8f0 ' + pct + '%, #e2e8f0 100%)';
}

// Init slider on load
document.addEventListener('DOMContentLoaded', function() {
    var s = document.getElementById('ov-cycle');
    if (s) ovUpdateSlider(s);
});

function calculateOvulation() {
    var lmpVal = document.getElementById('ov-lmp').value;
    if (!lmpVal) {
        var el = document.getElementById('ov-lmp');
        el.classList.add('input-error');
        setTimeout(function(){ el.classList.remove('input-error'); }, 1000);
        return;
    }
    var cycle = parseInt(document.getElementById('ov-cycle').value);
    var lmp = new Date(lmpVal);
    var ov = addD(lmp, cycle - 14);
    var fertStart = addD(ov, -5);
    var fertEnd = addD(ov, 1);
    var nextP = addD(lmp, cycle);

    document.getElementById('ov-r-start').innerHTML = fmtD(fertStart) + '<br><span class="text-slate-400 font-normal">to ' + fmtD(fertEnd) + '</span>';
    document.getElementById('ov-r-peak').textContent = fmtD(ov);
    document.getElementById('ov-r-next').textContent = fmtD(nextP);

    document.getElementById('ov-form').classList.add('hidden');
    var res = document.getElementById('ov-result');
    res.classList.remove('hidden');
    res.classList.add('result-enter');

    setTimeout(function() {
        var bar = document.getElementById('ov-r-bar');
        if (bar) bar.style.width = Math.round((6 / cycle) * 100) + '%';
    }, 100);
}

function resetOvulation() {
    document.getElementById('ov-lmp').value = '';
    var s = document.getElementById('ov-cycle');
    s.value = 28;
    ovUpdateSlider(s);
    document.getElementById('ov-cycle-val').textContent = '28 days';
    document.getElementById('ov-r-bar').style.width = '0%';
    document.getElementById('ov-result').classList.add('hidden');
    document.getElementById('ov-result').classList.remove('result-enter');
    document.getElementById('ov-form').classList.remove('hidden');
}

// ═══════════════════════════════════════════
//  WIDGET 2 — FERTILITY AGE CLOCK
// ═══════════════════════════════════════════
function selectSex(sex) {
    selectedSex = sex;
    var btnF = document.getElementById('btn-female');
    var btnM = document.getElementById('btn-male');
    btnF.className = btnF.className.replace('sex-btn-active', '').trim();
    btnM.className = btnM.className.replace('sex-btn-active', '').trim();
    if (sex === 'female') {
        btnF.classList.add('sex-btn-active');
    } else {
        btnM.classList.add('sex-btn-active');
    }
}

function calculateFertilityAge() {
    var dobVal = document.getElementById('fac-dob').value;
    if (!dobVal || !selectedSex) {
        if (!dobVal) {
            var el = document.getElementById('fac-dob');
            el.classList.add('input-error');
            setTimeout(function(){ el.classList.remove('input-error'); }, 1000);
        }
        return;
    }
    var dob = new Date(dobVal);
    var today = new Date();
    var age = today.getFullYear() - dob.getFullYear();
    var mo = today.getMonth() - dob.getMonth();
    if (mo < 0 || (mo === 0 && today.getDate() < dob.getDate())) age--;
    if (age < 16 || age > 65) {
        var el2 = document.getElementById('fac-dob');
        el2.classList.add('input-error');
        setTimeout(function(){ el2.classList.remove('input-error'); }, 1000);
        return;
    }

    var d = getFacTier(age, selectedSex);
    document.getElementById('fac-icon').textContent = d.icon;
    document.getElementById('fac-age-display').textContent = age + ' yrs';
    document.getElementById('fac-badge').textContent = d.tier;
    document.getElementById('fac-badge').className = 'inline-block px-2.5 py-1 rounded-full text-xs font-bold mb-2 ' + d.badgeCls;
    document.getElementById('fac-headline').textContent = d.headline;
    document.getElementById('fac-subtext').textContent = d.subtext;

    document.getElementById('fac-g1').setAttribute('stop-color', d.g1);
    document.getElementById('fac-g2').setAttribute('stop-color', d.g2);

    var bar = document.getElementById('fac-bar');
    bar.className = 'h-full rounded-full transition-all duration-1000 ' + d.barCls;

    var ins = document.getElementById('fac-insight');
    ins.textContent = d.insight;
    ins.className = 'rounded-xl p-4 mb-4 text-xs leading-relaxed font-medium border ' + d.insCls;

    document.getElementById('fac-form').classList.add('hidden');
    var res = document.getElementById('fac-result');
    res.classList.remove('hidden');
    res.classList.add('result-enter');

    setTimeout(function() {
        document.getElementById('fac-ring').style.strokeDashoffset = 226 - (226 * d.bar / 100);
        bar.style.width = d.bar + '%';
    }, 100);
}

function getFacTier(age, sex) {
    if (sex === 'female') {
        if (age <= 25) return { icon:'🌸', tier:'Peak Fertility', badgeCls:'bg-emerald-100 text-emerald-700', g1:'#34d399', g2:'#10b981', headline:'You are in your prime reproductive years.', subtext:'Egg quantity and quality are at their highest. Best outcomes for conception and IVF.', bar:95, barCls:'bg-gradient-to-r from-emerald-400 to-teal-500', insight:'✓ Excellent ovarian reserve expected. IVF success rates exceed 60% per transfer in this age group.', insCls:'bg-emerald-50 text-emerald-800 border-emerald-100' };
        if (age <= 30) return { icon:'🌿', tier:'High Fertility', badgeCls:'bg-teal-100 text-teal-700', g1:'#2dd4bf', g2:'#0d9488', headline:'Strong fertility and excellent conception rates.', subtext:'Egg quality remains very high. A great window to start your family naturally or with IVF.', bar:80, barCls:'bg-gradient-to-r from-teal-400 to-teal-600', insight:'✓ Good ovarian reserve. Seek evaluation if trying for 12+ months without success.', insCls:'bg-teal-50 text-teal-800 border-teal-100' };
        if (age <= 35) return { icon:'🕐', tier:'Good Fertility', badgeCls:'bg-sky-100 text-sky-700', g1:'#38bdf8', g2:'#6366f1', headline:'Fertility is still good — plan with confidence.', subtext:'A gradual natural decline begins. Still the most common IVF age group with great results.', bar:60, barCls:'bg-gradient-to-r from-sky-400 to-indigo-500', insight:'⚡ AMH assessment recommended if planning soon. Fertility assessment after 6 months of trying.', insCls:'bg-sky-50 text-sky-800 border-sky-100' };
        if (age <= 38) return { icon:'⏳', tier:'Moderate Decline', badgeCls:'bg-amber-100 text-amber-700', g1:'#fbbf24', g2:'#f97316', headline:'Timely action makes a meaningful difference.', subtext:'Egg quantity declines more noticeably. Tailored IVF protocols can significantly improve outcomes.', bar:42, barCls:'bg-gradient-to-r from-amber-400 to-orange-400', insight:'⚠ Early consultation strongly advised. Personalised stimulation protocols maximise egg yield.', insCls:'bg-amber-50 text-amber-800 border-amber-100' };
        if (age <= 42) return { icon:'⌛', tier:'Significant Decline', badgeCls:'bg-orange-100 text-orange-700', g1:'#f97316', g2:'#ef4444', headline:'Advanced protocols are available and effective.', subtext:'Ovarian reserve is reduced, but specialised care and PGT testing still achieve pregnancies.', bar:22, barCls:'bg-gradient-to-r from-orange-400 to-rose-500', insight:'⚠ Prompt specialist consultation is critical. Donor egg options and advanced protocols available.', insCls:'bg-orange-50 text-orange-800 border-orange-100' };
        return { icon:'💬', tier:'Specialist Needed', badgeCls:'bg-rose-100 text-rose-700', g1:'#f43f5e', g2:'#dc2626', headline:'All options remain open with specialist support.', subtext:'Advanced IVF, donor programs, and specialist care can still help build your family.', bar:8, barCls:'bg-gradient-to-r from-rose-400 to-rose-600', insight:'📋 A detailed evaluation by Dr. Adnan will explore all available pathways including donor programs.', insCls:'bg-rose-50 text-rose-800 border-rose-100' };
    } else {
        if (age <= 35) return { icon:'💪', tier:'Peak Fertility', badgeCls:'bg-emerald-100 text-emerald-700', g1:'#34d399', g2:'#10b981', headline:'Male fertility is at its absolute peak.', subtext:'Sperm count, motility, and morphology are typically optimal at this age.', bar:92, barCls:'bg-gradient-to-r from-emerald-400 to-teal-500', insight:'✓ Excellent parameters expected. A baseline semen analysis is still a smart investment.', insCls:'bg-emerald-50 text-emerald-800 border-emerald-100' };
        if (age <= 45) return { icon:'✅', tier:'Good Fertility', badgeCls:'bg-teal-100 text-teal-700', g1:'#2dd4bf', g2:'#38bdf8', headline:'Male fertility remains strong.', subtext:'Gradual sperm DNA quality changes begin but conception remains highly achievable.', bar:70, barCls:'bg-gradient-to-r from-teal-400 to-sky-500', insight:'✓ DNA fragmentation testing recommended alongside standard semen analysis.', insCls:'bg-teal-50 text-teal-800 border-teal-100' };
        if (age <= 52) return { icon:'⏳', tier:'Gradual Decline', badgeCls:'bg-amber-100 text-amber-700', g1:'#fbbf24', g2:'#f97316', headline:'Sperm quality gradually declining.', subtext:'DNA fragmentation increases after 45. Manageable with optimisation and ICSI if needed.', bar:48, barCls:'bg-gradient-to-r from-amber-400 to-orange-500', insight:'⚡ DNA fragmentation testing and antioxidant therapy can meaningfully improve sperm quality.', insCls:'bg-amber-50 text-amber-800 border-amber-100' };
        return { icon:'💬', tier:'Specialist Advised', badgeCls:'bg-orange-100 text-orange-700', g1:'#f97316', g2:'#ef4444', headline:'A full evaluation is recommended.', subtext:'Hormonal assessment, sperm retrieval techniques, and ICSI offer effective pathways.', bar:25, barCls:'bg-gradient-to-r from-orange-400 to-rose-500', insight:'📋 Full hormonal and semen evaluation will identify the best treatment pathway available.', insCls:'bg-orange-50 text-orange-800 border-orange-100' };
    }
}

function resetFertilityAge() {
    selectedSex = null;
    document.getElementById('fac-dob').value = '';
    document.querySelectorAll('.sex-btn').forEach(function(b) {
        b.classList.remove('sex-btn-active');
    });
    document.getElementById('fac-ring').style.strokeDashoffset = '226';
    document.getElementById('fac-bar').style.width = '0%';
    document.getElementById('fac-result').classList.add('hidden');
    document.getElementById('fac-result').classList.remove('result-enter');
    document.getElementById('fac-form').classList.remove('hidden');
}
</script>
<!-- TOOLS SECTION END -->
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

<!-- TESTIMONIALS -->
<section class="section-md bg-soft">
  <div class="max-w-7xl mx-auto px-6">
    <div class="text-center mb-12">
      <p class="text-sm font-semibold text-teal-700 uppercase tracking-widest mb-2">Patient Stories</p>
      <h2 class="text-4xl font-extrabold text-slate-900">Real Families. Real Results.</h2>
      <p class="text-slate-500 mt-3 text-lg max-w-2xl mx-auto">Every fertility journey is unique — here are a few of the families we've been honoured to help.</p>
    </div>
    <div class="grid md:grid-cols-3 gap-8">
      <div class="card p-8">
        <div class="flex gap-1 mb-4">
          <?php for ($i = 0; $i < 5; $i++): ?>
          <i class="fas fa-star text-amber-400 text-sm"></i>
          <?php endfor; ?>
        </div>
        <p class="text-slate-700 leading-relaxed mb-6">"After two failed IVF cycles elsewhere, Dr. Adnan identified a protocol adjustment that worked on our very first cycle with him. We are now parents to a healthy baby boy. We cannot thank him enough."</p>
        <div class="flex items-center gap-3">
          <div class="w-10 h-10 rounded-full bg-teal-100 flex items-center justify-center text-teal-700 font-bold text-sm flex-shrink-0">SF</div>
          <div>
            <p class="font-semibold text-slate-900 text-sm">Sara F.</p>
            <p class="text-slate-500 text-xs">Lahore — IVF Patient</p>
          </div>
        </div>
      </div>
      <div class="card p-8">
        <div class="flex gap-1 mb-4">
          <?php for ($i = 0; $i < 5; $i++): ?>
          <i class="fas fa-star text-amber-400 text-sm"></i>
          <?php endfor; ?>
        </div>
        <p class="text-slate-700 leading-relaxed mb-6">"We were told my husband's azoospermia meant biological children were impossible. Dr. Adnan performed micro-TESE and retrieved sperm for ICSI. Our daughter is now 2 years old. He gave us a miracle."</p>
        <div class="flex items-center gap-3">
          <div class="w-10 h-10 rounded-full bg-teal-100 flex items-center justify-center text-teal-700 font-bold text-sm flex-shrink-0">AM</div>
          <div>
            <p class="font-semibold text-slate-900 text-sm">Ayesha M.</p>
            <p class="text-slate-500 text-xs">Karachi — ICSI / Micro-TESE Patient</p>
          </div>
        </div>
      </div>
      <div class="card p-8">
        <div class="flex gap-1 mb-4">
          <?php for ($i = 0; $i < 5; $i++): ?>
          <i class="fas fa-star text-amber-400 text-sm"></i>
          <?php endfor; ?>
        </div>
        <p class="text-slate-700 leading-relaxed mb-6">"I consulted Dr. Adnan via teleconsultation from Islamabad. His diagnosis of my PCOS was more thorough than anything I had received locally. He adjusted my protocol and I conceived naturally within three months."</p>
        <div class="flex items-center gap-3">
          <div class="w-10 h-10 rounded-full bg-teal-100 flex items-center justify-center text-teal-700 font-bold text-sm flex-shrink-0">RK</div>
          <div>
            <p class="font-semibold text-slate-900 text-sm">Rabia K.</p>
            <p class="text-slate-500 text-xs">Islamabad — PCOS Teleconsultation</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- HOW IT WORKS -->
<section class="section-md bg-white">
  <div class="max-w-5xl mx-auto px-6">
    <div class="text-center mb-12">
      <p class="text-sm font-semibold text-teal-700 uppercase tracking-widest mb-2">The Process</p>
      <h2 class="text-4xl font-extrabold text-slate-900">What to Expect When You Reach Out</h2>
      <p class="text-slate-500 mt-3 text-lg max-w-2xl mx-auto">We've streamlined the consultation process to make your first step as easy as sending a WhatsApp message.</p>
    </div>
    <div class="grid md:grid-cols-5 gap-4 md:gap-6">
      <?php
      $steps = [
        ['01', 'fas fa-comment-dots', 'Contact Us', 'Send a WhatsApp message or fill out our contact form. No waiting rooms, no complicated booking systems.'],
        ['02', 'fas fa-stethoscope', 'Initial Consultation', 'Meet Dr. Adnan in person at our Lahore clinic or via teleconsultation from anywhere in Pakistan.'],
        ['03', 'fas fa-flask', 'Full Diagnostic Workup', 'Comprehensive testing for both partners — hormonal profiles, semen analysis, ultrasound, and more.'],
        ['04', 'fas fa-clipboard-list', 'Personalized Plan', 'Dr. Adnan designs a treatment plan tailored specifically to your diagnosis, age, and goals.'],
        ['05', 'fas fa-baby', 'Treatment Begins', 'We walk with you through every cycle, every milestone, with clear communication at every step.'],
      ];
      foreach ($steps as [$num, $icon, $title, $desc]): ?>
      <div class="text-center fade-in">
        <div class="w-14 h-14 bg-teal-700 rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-lg">
          <i class="<?php echo htmlspecialchars($icon); ?> text-white text-xl"></i>
        </div>
        <p class="text-xs font-bold text-teal-700 uppercase tracking-widest mb-1"><?php echo htmlspecialchars($num); ?></p>
        <h3 class="text-base font-bold text-slate-900 mb-2"><?php echo htmlspecialchars($title); ?></h3>
        <p class="text-sm text-slate-500 leading-relaxed"><?php echo htmlspecialchars($desc); ?></p>
      </div>
      <?php endforeach; ?>
    </div>
    <div class="text-center mt-10">
      <a href="https://wa.me/923111101483?text=Hi%20Dr.%20Adnan%2C%20I%20would%20like%20to%20start%20my%20fertility%20journey."
         target="_blank" rel="noopener noreferrer"
         class="btn-primary inline-flex items-center gap-2 px-8 py-4">
        <i class="fab fa-whatsapp text-xl"></i>
        Start Your Journey Today
      </a>
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
