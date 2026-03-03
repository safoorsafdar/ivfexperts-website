# Page CRO Assessment — ivfexperts.pk
**Assessed:** 2026-03-03 | **Status:** 🔴 Not Started
**Primary conversion goal:** Book a consultation (WhatsApp / Contact Form)
**Target visitor:** Pakistani couples (and diaspora) seeking IVF/infertility treatment

---

## Executive Summary

The site has strong visual design and clear service coverage. The main CRO gaps are: the homepage H1 doesn't anchor value, social proof is vague ("10 years experience" not patient outcomes), trust signals lack specificity (no testimonials, no success rate numbers visible above fold), and the blog has zero contextual CTAs. The WhatsApp CTA is an excellent Pakistan-market choice — lean into it harder.

**Biggest single win:** Adding specific patient success signals above the fold (e.g. "500+ successful pregnancies") would increase consultation requests measurably.

---

## HOMEPAGE CRO

### ✅ Task 1: Replace rotating H1 with value-anchored static hero headline
- **Problem:** The JavaScript-rotated H1 ("Parenthood Begins with Clarity & Strategy") changes every 4.5 seconds. First-time visitors (cold traffic) can't anchor to a clear message. "Clarity & Strategy" communicates nothing about IVF.
- **What to test:**
  - Option A (outcome-focused): *"Get Pregnant with Pakistan's Most Trusted IVF Specialist"*
  - Option B (specificity): *"Advanced IVF & ICSI in Lahore — Personalized for Every Patient"*
  - Option C (authority): *"Dr. Adnan Jabbar — Pakistan's Only Dual-Trained Fertility Consultant & Embryologist"*
- **Keep** the rotating emotional sub-copy as a `<p>` tag below — it's beautifully written. Just anchor with a clear H1 first.
- **Priority:** 🔴 High

### ✅ Task 2: Add specific social proof above the fold
- **Problem:** "10 years experience" and "Dual Expertise" are vague. Patients want to know: *Has this doctor helped people like me?*
- **Current trust stats on homepage:** "10 years" (counter), "Dual Expertise certified" — no patient outcomes
- **What to add (above fold or immediately below hero):**
  ```
  500+ Successful Pregnancies  |  10+ Years Experience  |  Treating from 3 Countries
  ```
  Or testimonial snippets: *"After 3 failed IVF attempts elsewhere, Dr. Adnan's approach worked on the first cycle."* — with first name and city.
- **Source these numbers from Dr. Adnan's actual outcomes, then display prominently.**
- **Priority:** 🔴 High

### ✅ Task 3: Add a dedicated testimonials / success stories section to the homepage
- **Problem:** No patient testimonials visible anywhere on the site. Fertility patients are making one of the most emotionally significant decisions of their lives — social proof from other patients is the #1 trust signal.
- **What to build:**
  - 3–5 brief testimonials (with patient first name, city, treatment type)
  - Optional: "IVF Success Stories" section with short anonymized stories
  - Optional: Google Review badge with star rating (if reviews exist)
- **Placement:** Directly before or after the consultation CTA section
- **Priority:** 🔴 High

### ✅ Task 4: Strengthen primary CTA copy
- **Current:** "Schedule Your Consultation" (good) + "Meet Dr. Adnan Jabbar" (secondary)
- **Problems:**
  - "Schedule" creates mental effort — it implies forms, calendars, waiting
  - Secondary CTA "Meet Dr. Adnan Jabbar" feels equal weight to the primary, creating decision paralysis
- **Better primary CTAs:**
  - *"Start Your Fertility Journey"* (WhatsApp → quick, low friction)
  - *"Get a Free Initial Consultation"* (if applicable)
  - *"WhatsApp Dr. Adnan Now"* (Pakistan-specific — WhatsApp is primary communication)
- **Better secondary:** Reduce visual weight. Change to a text link: "Learn about Dr. Adnan →"
- **Priority:** 🟡 Medium

### ✅ Task 5: Add pricing transparency or cost guidance
- **Problem:** IVF cost is a top concern for Pakistani patients. The site has no mention of costs, affordability, or payment options anywhere. Patients who worry about cost will bounce without asking.
- **Fix options:**
  - Add "IVF Starting from PKR X" with a "Get personalized quote" CTA
  - Add a FAQ: "How much does IVF cost in Pakistan?" with a range
  - Add copy: "Flexible payment plans available" in the hero section
- **Priority:** 🟡 Medium

---

## TREATMENT PAGE CRO

### ✅ Task 6: Add contextual CTAs on every treatment/condition page
- **Problem:** Treatment pages (e.g., `/male-infertility/azoospermia.php`, `/art-procedures/ivf.php`) likely have a CTA section, but no inline CTAs within content. Long pages need CTAs at multiple scroll depths.
- **Fix:** Add 2–3 inline CTA blocks per treatment page:
  - After the diagnosis/symptoms section: *"Struggling with [condition]? WhatsApp Dr. Adnan for a private consultation."*
  - At mid-page: *"Get a personalized treatment plan — book your consultation today."*
  - At page end: Full CTA section
- **Use a reusable PHP `include` for the inline CTA block** to keep it consistent.
- **Priority:** 🔴 High

### ✅ Task 7: Add "Related Conditions / Treatments" cross-links on each page
- **Problem:** A patient reading about PCOS may also be a candidate for IVF or IUI. There are no cross-links helping patients discover adjacent relevant pages.
- **Fix:** Add a "You might also be interested in" section at the bottom of each condition/treatment page with 2–4 related page links (cards with brief description).
- This also improves internal linking for SEO.
- **Priority:** 🟡 Medium

### ✅ Task 8: Add FAQ section to every major treatment page
- **Problem:** No FAQ sections exist on treatment pages. FAQs address objections, keep patients on page longer, and enable FAQ schema for rich results.
- **Key questions to answer per condition page:**
  - "Is [condition] curable?"
  - "How much does [treatment] cost in Pakistan?"
  - "What is the success rate for [treatment]?"
  - "How many sessions will I need?"
  - "Can I travel from [Karachi/Islamabad] for treatment?"
- **Priority:** 🟡 Medium

---

## BLOG CRO

### ✅ Task 9: Add contextual CTAs to every blog post
- **Problem:** Blog is a major traffic opportunity for long-tail queries. If a patient finds the blog via Google, there is likely no or minimal CTA to convert them to a consultation.
- **Fix:** Add to blog template:
  - **Sidebar/sticky CTA** (desktop): "Ready to start your fertility journey? WhatsApp Dr. Adnan"
  - **Inline CTA after first ~500 words**: Topic-relevant CTA (e.g., blog about PCOS → CTA for PCOS treatment page)
  - **End-of-post CTA**: Full consultation booking block
- **Priority:** 🔴 High

### ✅ Task 10: Add author bio block to blog posts
- **Problem:** Blog posts likely show no author attribution. For a medical site, author credentials build trust and are required for E-E-A-T.
- **Fix:** Add an "About the Author" block at the bottom of every blog post:
  ```
  [Dr. Adnan photo] | Dr. Adnan Jabbar — Fertility Consultant & Clinical Embryologist
  With 10+ years of specialized experience in reproductive medicine...
  [Read full bio →]
  ```
- **Priority:** 🟡 Medium

---

## CONTACT PAGE CRO

### ✅ Task 11: Reduce contact form friction
- **Problem:** Contact forms for medical consultations often have too many fields. Every additional field reduces completion rates by ~10–15%.
- **Review:** Check current contact form fields — if more than 4–5 fields, simplify to: Name, Phone/WhatsApp, Condition (dropdown), and optional Message.
- **Priority:** 🟡 Medium

---

## GLOBAL CRO

### ✅ Task 12: Add WhatsApp click-to-chat with pre-filled message
- **Problem:** The WhatsApp button links to `https://wa.me/923111101483` with no pre-filled message. Patients have to type their first message, which adds friction.
- **Fix:** Use pre-filled text:
  ```
  https://wa.me/923111101483?text=Hello%20Dr.%20Adnan%2C%20I%20would%20like%20to%20book%20a%20fertility%20consultation.
  ```
  For treatment pages, pre-fill the condition: `...I%20have%20questions%20about%20IVF%20treatment.`
- **Priority:** 🟡 Medium

### ✅ Task 13: Add a sticky "Book Consultation" bar on mobile
- **Problem:** On mobile, the WhatsApp and Patient Login buttons are in the hamburger menu — not visible until the menu is opened. Mobile is likely 70%+ of traffic in Pakistan.
- **Fix:** Add a sticky bottom bar on mobile with:
  ```
  [WhatsApp icon] Chat Now  |  [Phone icon] Call
  ```
  Fixed at the bottom of the viewport on all pages.
- **Priority:** 🔴 High

### ✅ Task 14: Add a "Process" section explaining what to expect
- **Problem:** First-time IVF patients have anxiety about the unknown. A clear "What happens when you contact us?" section reduces hesitation.
- **Content:**
  1. WhatsApp / Contact → Dr. Adnan responds within hours
  2. Initial teleconsultation or in-person meeting
  3. Full diagnostic workup
  4. Personalized treatment plan
  5. Treatment begins
- **Placement:** Homepage (below hero), Contact page
- **Priority:** 🟡 Medium

---

## Copy Alternatives

### Homepage H1 Options
| Variant | Rationale |
|---|---|
| *"Expert IVF & Fertility Care in Lahore — Dr. Adnan Jabbar"* | Clear, geographic, authoritative |
| *"Pakistan's Leading IVF Specialist — Personalized Fertility Treatment"* | Authority claim, benefit hint |
| *"Get Pregnant with Compassionate, Evidence-Based IVF in Lahore"* | Outcome + differentiator |

### WhatsApp CTA Button Copy Options
| Variant | Rationale |
|---|---|
| *"WhatsApp for Free Consultation"* | Removes cost fear |
| *"Chat with Dr. Adnan on WhatsApp"* | Personal, direct |
| *"Book via WhatsApp — Reply in Hours"* | Sets clear expectation |

---

## Metrics to Track
- WhatsApp click rate (via GTM event)
- Contact form completion rate
- Scroll depth on treatment pages (via GA4)
- Bounce rate: homepage vs. treatment pages
- Blog to consultation conversion path (GA4 funnel)
