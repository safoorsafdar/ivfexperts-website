# Local SEO — ivfexperts.pk
**Assessed:** 2026-03-03 | **Status:** 🔴 Not Started
**Goal:** Rank in Google Maps / Local Pack for fertility queries in Lahore, and capture out-of-city patients in Karachi & Islamabad

---

## Executive Summary

A physical fertility clinic in Lahore with teleconsultation reach across Pakistan has enormous local SEO potential. Currently, there is no Google Business Profile optimized for the practice, no consistent NAP (Name/Address/Phone) across Pakistani directories, and no city-specific landing pages for Karachi or Islamabad patients. Local pack rankings (the map results that appear above organic) drive a significant portion of medical bookings in Pakistan — and competition in this space is far lower than in Western markets.

**Biggest single win:** A fully optimized, review-rich Google Business Profile will generate consultation leads faster than almost any other tactic on this roadmap.

---

## GOOGLE BUSINESS PROFILE

### ✅ Task 1: Claim and fully optimize the Google Business Profile
- **URL:** https://business.google.com
- **Problem:** No verified GBP found for Dr. Adnan Jabbar / IVF Experts at ivfexperts.pk. An unverified or missing profile means the clinic is invisible in Google Maps results.
- **Steps:**
  1. Search Google Maps for "IVF Experts Lahore" — if a profile exists, claim it; if not, create a new one
  2. Verify ownership via postcard or phone
  3. Complete every field:
     - **Business name:** IVF Experts Pakistan — Dr. Adnan Jabbar
     - **Category (primary):** Fertility Clinic
     - **Additional categories:** Reproductive Health Clinic, Medical Clinic, Physician
     - **Address:** Full clinic address in Lahore (must match website exactly)
     - **Phone:** +923111101483
     - **Website:** https://ivfexperts.pk
     - **Hours:** Accurate weekly hours (update for holidays/Ramadan)
     - **Description:** 750-character description with primary keywords: "IVF specialist Lahore", "fertility clinic Pakistan", "Dr. Adnan Jabbar"
     - **Attributes:** "Online consultations available", "Telehealth services"
- **Priority:** 🔴 Critical

### ✅ Task 2: Add photos and media to GBP
- **Problem:** Profiles with 10+ photos get significantly more calls and direction requests than bare listings.
- **Upload (minimum 10):**
  - Clinic exterior (storefront/signage)
  - Reception / waiting area
  - Consultation room
  - Lab / embryology lab (if photogenic)
  - Dr. Adnan's professional headshot (use optimized version of dr-adnan.jpg after WebP conversion)
  - Team photo
  - Equipment (ultrasound machine, IVF lab equipment)
  - Logo (on white background)
- **Naming:** Before uploading, rename files descriptively: `ivf-clinic-lahore-exterior.jpg`, `dr-adnan-jabbar-fertility-specialist.jpg` — GBP reads EXIF and filenames.
- **Priority:** 🔴 High

### ✅ Task 3: Set up GBP posts (weekly cadence)
- **Problem:** GBP posts appear in the local panel and can drive calls. Most clinics never use them.
- **Post types to use:**
  - **Update posts:** New blog posts ("New article: IVF Cost in Pakistan 2026")
  - **Offer posts:** "Free initial teleconsultation — contact us on WhatsApp"
  - **Event posts:** Any seminars or webinars Dr. Adnan hosts
  - **Q&A responses:** Answer questions in the GBP Q&A section proactively
- **Cadence:** 1 post per week. Posts expire after 7 days.
- **Priority:** 🟡 Medium (ongoing)

---

## REVIEW ACQUISITION STRATEGY

### ✅ Task 4: Build a systematic review acquisition process
- **Problem:** No reviews visible on GBP (or very few). Review count and star rating are direct ranking factors in the local pack. In Pakistan's medical market, patients trust peer reviews heavily.
- **Target:** 50 verified Google reviews at 4.8+ stars within 6 months
- **Process:**
  1. After each successful consultation/procedure, send patient a WhatsApp message:
     > "We're so grateful to be part of your journey. If you're happy with our care, a Google review helps other couples find us: [short link]"
  2. Create a short Google review link: Go to your GBP → Get more reviews → copy/shorten the URL
  3. Add the review link to:
     - Post-appointment WhatsApp messages
     - Email follow-ups
     - Patient portal discharge instructions
  4. Respond to every review within 48 hours (both positive and negative)
- **Note:** Never offer incentives for reviews — violates Google policy and can result in profile suspension.
- **Priority:** 🔴 High (start immediately — reviews take time to accumulate)

### ✅ Task 5: Add review schema once reviews are collected
- **File:** `includes/footer.php` — add `aggregateRating` to MedicalBusiness schema (see also [03-schema-markup.md](03-schema-markup.md) Task 10)
- **Trigger:** Once 10+ verified reviews exist with an average ≥ 4.5
- **Priority:** 🟡 Medium (prerequisite: Task 4)

---

## LOCAL CITATIONS & DIRECTORY LISTINGS

### ✅ Task 6: Audit and fix NAP consistency
- **NAP = Name, Address, Phone** — must be identical across all platforms
- **Canonical NAP to use everywhere:**
  ```
  Name:    IVF Experts Pakistan — Dr. Adnan Jabbar
  Address: [Full Lahore clinic address — Street, Area, Lahore, Punjab, Pakistan]
  Phone:   +92 311 110 1483
  Website: https://ivfexperts.pk
  ```
- **Check and fix across:**
  - Google Business Profile
  - Facebook Page
  - Instagram Bio
  - Yelp (if listed)
  - Any existing directory listings
- **Priority:** 🔴 High (inconsistent NAP kills local rankings)

### ✅ Task 7: Submit to Pakistan medical directories
- **Target directories (submit to all):**
  - **PMDC (Pakistan Medical and Dental Council):** pmdc.gov.pk — ensure Dr. Adnan's listing is current
  - **Marham.pk:** Pakistan's largest doctor directory — create a full profile with photos and specialties
  - **Oladoc.com:** Create a detailed specialist profile
  - **DoctorOnline.pk:** Submit listing
  - **Shifa4U:** Doctor directory submission
  - **Rozee Medical Listings** (if applicable)
  - **Lahore-specific business directories:** Locally relevant business listings
- **For each listing:** Use canonical NAP, add website link, add services list, add photos
- **Priority:** 🔴 High (NAP citations are a local ranking signal)

### ✅ Task 8: Submit to international fertility directories
- **Target:**
  - Healthgrades (if accessible from Pakistan)
  - Vitals.com
  - RateMDs.com
  - FertilityIQ (major fertility resource site — request a listing)
  - IVFauthority.com
  - Global IVF clinic directories
- **Value:** International directories are cited by AI systems and build domain authority via backlinks
- **Priority:** 🟡 Medium

---

## CITY-SPECIFIC LANDING PAGES

### ✅ Task 9: Create Karachi teleconsultation landing page
- **URL:** `/fertility-specialist-karachi/index.php`
- **Target keyword:** "fertility specialist Karachi" / "IVF doctor Karachi teleconsultation"
- **Rationale:** Karachi is Pakistan's largest city. Patients there actively search for specialists — many are willing to travel to Lahore or use teleconsultation if they trust the doctor.
- **Content:**
  - H1: "Fertility Specialist for Karachi Patients — Dr. Adnan Jabbar"
  - How teleconsultation works for Karachi patients
  - Which appointments require travel to Lahore vs. which can be remote
  - Local context: "Serving patients from DHA Karachi, Clifton, Gulshan-e-Iqbal"
  - Strong WhatsApp CTA
  - Schema: LocalBusiness with `areaServed: Karachi`
- **Priority:** 🟡 Medium

### ✅ Task 10: Create Islamabad teleconsultation landing page
- **URL:** `/fertility-specialist-islamabad/index.php`
- **Target keyword:** "fertility specialist Islamabad" / "IVF doctor Islamabad"
- **Content:** Same structure as Karachi page, adjusted for Islamabad context
- **Schema:** `areaServed: Islamabad`
- **Priority:** 🟡 Medium

### ✅ Task 11: Create Okara local page (secondary location)
- **URL:** `/fertility-clinic-okara/index.php`
- **Rationale:** Okara is listed in site facts as a served location — this suggests Dr. Adnan may have a presence there. If so, a dedicated local page + separate GBP listing for Okara could drive significant local traffic from a low-competition area.
- **Confirm:** Whether Dr. Adnan has regular clinic hours in Okara before building
- **Priority:** 🟡 Medium (confirm scope first)

---

## LOCAL KEYWORD STRATEGY

### ✅ Task 12: Optimize existing pages for geo-modified keywords
- **Problem:** Most treatment pages don't include location modifiers in their H1s, body content, or meta descriptions. Geo-modified keywords ("IVF treatment Lahore", "PCOS specialist Pakistan") drive the most valuable local traffic.
- **Pages to update:**
  - `index.php` — meta description and H1 should include "Lahore"
  - `about/index.php` — bio should reference Lahore clinic prominently
  - `contact/index.php` — include full address, embedded Google Map, and location keywords
  - `art-procedures/ivf.php` — add "Available in Lahore, with teleconsultation from Karachi and Islamabad"
- **Top geo-modified keywords to target:**
  - "IVF specialist Lahore"
  - "fertility clinic Lahore Pakistan"
  - "ICSI treatment Lahore"
  - "azoospermia specialist Lahore"
  - "PCOS fertility doctor Pakistan"
  - "IVF cost Lahore PKR"
- **Priority:** 🟡 Medium (quick wins in existing pages)

---

## Metrics to Track
- Google Business Profile: views, searches, calls, direction requests (monthly)
- Local pack impressions in Search Console (filter by "Lahore" queries)
- Review count and average rating (monthly)
- Traffic from city-specific pages (GA4)
- Phone/WhatsApp clicks from GBP listing (GBP Insights)
