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
                <!-- Static SEO H1 — indexed by Google, visible to users -->
                <h1 class="text-3xl md:text-4xl font-extrabold text-slate-900 leading-tight mb-3">
                    IVF &amp; Fertility Specialist in Lahore — Dr. Adnan Jabbar
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