# Content Architecture — ivfexperts.pk
**Designed:** 2026-03-03 | **Status:** 🔴 Not Started
**Type:** C — Full architecture (content map + implementation design), tasks marked write-first vs build-later

---

## The Mental Model

Think of the site content as **three rings around the patient**:

```
        ┌─────────────────────────────────────────────┐
        │             DISCOVERY LAYER                 │
        │   (Blog posts, Glossary, Tools, Guides)     │
        │  Patient finds us via Google / social / AI  │
        └──────────────┬──────────────────────────────┘
                       │ links inward
        ┌──────────────▼──────────────────────────────┐
        │           EDUCATION LAYER                   │
        │   (Condition + Procedure pages, FAQs)       │
        │    Patient understands their situation      │
        └──────────────┬──────────────────────────────┘
                       │ links inward
        ┌──────────────▼──────────────────────────────┐
        │           CONVERSION LAYER                  │
        │   (Contact, WhatsApp CTAs, Tools)           │
        │     Patient books a consultation            │
        └─────────────────────────────────────────────┘
```

Every page lives in one of the three rings. Every internal link moves the patient **inward** — from discovery → education → conversion. No page should be a dead end.

---

## Content Layer Definitions

| Layer | Content Types | Primary Goal |
|---|---|---|
| **Pillar** | Category hub pages (`/male-infertility/`, `/art-procedures/`, etc.) | Authority + SEO hub |
| **Cluster** | Condition & procedure pages (existing ~35 pages) | Rank for condition keywords |
| **Blog** | Long-form articles, guides, comparisons | Capture long-tail + blog SEO |
| **Glossary** | Medical term definitions | Bind all layers, capture "what is X" queries |
| **Comparison** | IVF vs ICSI, IUI vs IVF, etc. | AI citation gold, consideration-stage traffic |
| **Tools** | Calculators, interpreters | Lead gen, backlinks, top-of-funnel |
| **FAQ** | Sections within pages + standalone | Featured snippets, AI Overviews |
| **Patient Guides** | Downloadable/web step-by-step guides | Email capture, deep education |

---

## Full Site Content Map

```
ivfexperts.pk/
│
├── 📌 HOMEPAGE (hub of hubs)
│     Links to: all 5 pillars, blog, tools, contact
│
├── 📚 /glossary/                          [BUILD — new section]
│     └── Individual terms (see Glossary section below)
│
├── 🔧 /tools/                             [BUILD — new section]
│     ├── ivf-success-calculator/
│     ├── semen-analysis-interpreter/
│     ├── fertility-age-clock/
│     ├── ivf-cost-estimator/
│     └── ovulation-calculator/
│
├── 📝 /blog/                              [FIX URLs + add categories]
│     ├── Category: ivf-procedures/
│     ├── Category: male-fertility/
│     ├── Category: female-fertility/
│     ├── Category: pakistan-guide/
│     ├── Category: fertility-science/
│     └── Category: patient-stories/
│
├── ═══════════════════════════════════════
│   PILLAR 1: /male-infertility/           [UPGRADE existing]
│   ═══════════════════════════════════════
│     ├── Cluster pages (existing):
│     │     azoospermia, low-sperm-count, varicocele,
│     │     dna-fragmentation, erectile-ejaculatory-dysfunction,
│     │     unexplained-male-infertility, klinefelters-syndrome,
│     │     hypogonadotropic-hypogonadism, low-testicular-volume,
│     │     primary-testicular-failure, testicular-recovery-stemcell,
│     │     penile-doppler-ultrasound
│     │
│     ├── Blog posts (write):
│     │     Understanding Your Semen Analysis Report
│     │     Azoospermia — Can You Still Have a Baby?
│     │     Male Infertility in Pakistan — Breaking the Silence
│     │     Varicocele: Does Surgery Actually Help Fertility?
│     │     What Is Sperm DNA Fragmentation and Why Does It Matter?
│     │
│     ├── Glossary terms owned (write):
│     │     oligospermia, azoospermia, asthenospermia,
│     │     teratospermia, motility, morphology, sperm count,
│     │     DNA fragmentation, varicocele, testosterone,
│     │     FSH (male), Micro-TESE, PESA, TESA, TESE
│     │
│     └── Cross-links OUT to:
│           → /art-procedures/icsi.php (for all severe male factor)
│           → /art-procedures/surgical-sperm-retrieval.php
│           → /stemcell/testicular-recovery-stemcell.php (for NOA)
│           → /art-procedures/iui.php (for mild male factor)
│
├── ═══════════════════════════════════════
│   PILLAR 2: /female-infertility/         [UPGRADE existing]
│   ═══════════════════════════════════════
│     ├── Cluster pages (existing):
│     │     pcos, endometriosis, blocked-tubes,
│     │     diminished-ovarian-reserve, recurrent-pregnancy-loss,
│     │     unexplained-infertility, uterine-fibroids-polyps,
│     │     adenomyosis, primary-ovarian-failure,
│     │     ovarian-tissue-preservation, stemcell-ovarian-rejuvenation
│     │
│     ├── Blog posts (write):
│     │     PCOS and Getting Pregnant — Your Complete Guide
│     │     Low AMH — What It Means and What You Can Do
│     │     Recurrent Miscarriage — Why It Happens and How to Get Answers
│     │     Endometriosis and IVF — What to Know Before Treatment
│     │     Blocked Tubes: IVF or Surgery?
│     │
│     ├── Glossary terms owned (write):
│     │     AMH, FSH (female), LH, AFC, endometrium, follicle,
│     │     PCOS, endometriosis, adenomyosis, ovarian reserve,
│     │     blastocyst implantation, luteal phase, progesterone,
│     │     hysteroscopy, laparoscopy, HCG, estradiol
│     │
│     └── Cross-links OUT to:
│           → /art-procedures/ivf.php (PCOS, blocked tubes, endometriosis)
│           → /art-procedures/iui.php (mild PCOS, unexplained)
│           → /art-procedures/pgt.php (recurrent miscarriage)
│           → /art-procedures/ovarian-endometrial-prp.php (low AMH, thin lining)
│           → /stemcell/stemcell-ovarian-rejuvenation.php (low AMH, POF)
│           → /art-procedures/fertility-preservation.php (oncofertility)
│
├── ═══════════════════════════════════════
│   PILLAR 3: /art-procedures/             [UPGRADE existing]
│   ═══════════════════════════════════════
│     ├── Cluster pages (existing):
│     │     ivf, icsi, iui, pgt,
│     │     fertility-preservation, ovarian-endometrial-prp,
│     │     surgical-sperm-retrieval, laser-assisted-hatching
│     │
│     ├── Blog posts (write):
│     │     IVF Success Rates in Pakistan — Data & What to Expect
│     │     IVF vs ICSI — Which Is Right for You?            [COMPARISON]
│     │     IVF vs IUI — When to Choose Which                [COMPARISON]
│     │     IVF Cost in Pakistan 2026 — Complete Breakdown
│     │     What to Expect During Your IVF Cycle Week by Week
│     │     PGT-A Genetic Testing — Is It Right for You?
│     │     Gender Selection in Pakistan — Medical & Legal Facts
│     │     Fresh vs Frozen Embryo Transfer — Which Is Better? [COMPARISON]
│     │
│     ├── Glossary terms owned (write):
│     │     IVF, ICSI, IUI, PGT, embryo transfer, egg retrieval,
│     │     blastocyst, morula, stimulation protocol, trigger shot,
│     │     vitrification, cryopreservation, OHSS, implantation,
│     │     two-week wait, beta HCG, fresh transfer, frozen transfer,
│     │     assisted hatching, PICSI, IMSI
│     │
│     └── Cross-links OUT to:
│           → /male-infertility/ (for ICSI / surgical retrieval indications)
│           → /female-infertility/ (for IVF indications)
│           → /stemcell/ (for PRP, advanced cases)
│           → /tools/ (IVF calculator, cost estimator)
│
├── ═══════════════════════════════════════
│   PILLAR 4: /stemcell/                   [UPGRADE existing]
│   ═══════════════════════════════════════
│     ├── Cluster pages (existing):
│     │     adscs, mesenchymal-umbilical, pluripotent-stem-cells,
│     │     multipotent-stem-cells, role-in-infertility
│     │
│     ├── Blog posts (write):
│     │     Stem Cell Therapy for Infertility — What the Science Says
│     │     PRP for Fertility — Does It Work?
│     │     Ovarian Rejuvenation — New Hope for Low AMH
│     │
│     ├── Glossary terms owned (write):
│     │     stem cell, ADSCs, MSCs, Wharton's jelly, PRP,
│     │     platelet-rich plasma, ovarian rejuvenation,
│     │     regenerative medicine, in-vitro gametogenesis (IVG)
│     │
│     └── Cross-links OUT to:
│           → /female-infertility/stemcell-ovarian-rejuvenation.php
│           → /male-infertility/testicular-recovery-stemcell.php
│           → /art-procedures/ovarian-endometrial-prp.php
│
└── ═══════════════════════════════════════
    PILLAR 5: /blog/category/pakistan-guide/  [NEW — write-only pillar]
    ═══════════════════════════════════════
      ├── Cornerstone (write first):
      │     Fertility Treatment in Pakistan 2026 — Complete Guide
      │
      ├── Supporting posts (write):
      │     IVF Cost in Pakistan 2026 — PKR Breakdown
      │     IVF Clinics in Lahore — How to Choose the Right One
      │     Can I Travel from Islamabad / Karachi for IVF in Lahore?
      │     Questions to Ask Your IVF Doctor Before Starting
      │     What to Bring to Your First Fertility Consultation
      │
      ├── Cross-links OUT to:
      │     → All 4 content pillars
      │     → /contact/
      │     → /tools/ (cost estimator)
      │
      └── (No dedicated hub page needed — blog category listing serves as hub)
```

---

## Blog Category Structure

Blog categories mirror the 5 pillars. Each post belongs to one primary category and can have secondary tags.

```
/blog/
├── Category: ivf-procedures/         → Pillar 3
│     Posts about: IVF, ICSI, IUI, PGT, protocols, costs, timelines
│
├── Category: male-fertility/         → Pillar 1
│     Posts about: sperm health, male conditions, semen analysis, surgery
│
├── Category: female-fertility/       → Pillar 2
│     Posts about: PCOS, AMH, endometriosis, hormones, ovulation
│
├── Category: pakistan-guide/         → Pillar 5
│     Posts about: costs in PKR, clinics, travel, cultural context
│
├── Category: fertility-science/      → Pillar 4
│     Posts about: stem cells, PRP, genetic testing, research
│
└── Category: patient-stories/        → All pillars
      Posts about: anonymized case studies, journeys, outcomes
```

### Blog Post Types (within any category)

| Type | Format | Example |
|---|---|---|
| **Condition Guide** | 1500w, H2s as questions | "What is Azoospermia?" |
| **Procedure Guide** | 1500w + timeline | "What to Expect During IVF" |
| **Comparison** | Table + pros/cons | "IVF vs ICSI" |
| **Data Page** | Tables + citations | "IVF Success Rates Pakistan" |
| **Decision Guide** | Decision tree | "Should I Do IUI or IVF?" |
| **Cultural/Context** | Narrative + data | "Male Infertility in Pakistan" |
| **Cost/Pricing** | Breakdown table | "IVF Cost in Pakistan 2026" |
| **Patient Story** | Case study format | Anonymized journey |

---

## Glossary Architecture

The glossary is the **binding layer** — it links every content type together through medical terminology.

### URL Structure
```
/glossary/                          → Hub: A–Z index of all terms
/glossary/ivf/                      → Individual term
/glossary/azoospermia/
/glossary/amh/
```

### Glossary Term Page Structure (per term)
```
┌─────────────────────────────────────────┐
│  Term: AMH (Anti-Müllerian Hormone)     │
│  ────────────────────────────────────── │
│  1. Plain-language definition (50w)     │
│  2. Why it matters for fertility (75w)  │
│  3. Normal ranges by age (table)        │
│  4. What low AMH means + options (75w)  │
│  5. Related terms: [FSH] [AFC] [IVF]   │
│  6. → Full article: "Low AMH Guide"    │
│  7. → Condition page: /dim-ovarian-res/ │
│  8. → CTA: "Get your AMH tested →"     │
└─────────────────────────────────────────┘
```

### Glossary Categories (organize the A-Z hub by section)

| Section | Example Terms |
|---|---|
| **Diagnoses** | Azoospermia, PCOS, Endometriosis, POF, Varicocele |
| **Hormones & Tests** | AMH, FSH, LH, Estradiol, HCG, AFC, Testosterone |
| **Procedures** | IVF, ICSI, IUI, PGT, Micro-TESE, PESA, TESA |
| **Embryology** | Blastocyst, Morula, Vitrification, Implantation, OHSS |
| **Medications** | Gonal-F, Menopur, Trigger shot, Progesterone suppositories |
| **Advanced** | ADSCs, MSCs, PRP, In-vitro gametogenesis, DNA fragmentation |

### Internal Linking Rule for Glossary
> **First mention rule:** The first time a medical term appears on any page (blog post, condition page, procedure page), it links to the glossary definition. Subsequent mentions are plain text.

---

## Cross-Pillar Connection Map

This is how the five pillars link to each other. These cross-links are the topological glue of the site.

```
                    MALE INFERTILITY
                    /male-infertility/
                          │
              ┌───────────┼───────────┐
              ▼           ▼           ▼
         ICSI page   Surgical     Stem Cell
         (ART P3)    Sperm Ret.   Testicular
                     (ART P3)     (SC P4)
                          │
                          │ (if all male
                          │  tx fails →)
                          ▼
                    ART PROCEDURES
                    /art-procedures/
                          │
              ┌───────────┼───────────┐
              ▼           ▼           ▼
           IVF         ICSI         PGT
             │           │           │
             └─────┬─────┘           │
                   │                 │
                   ▼                 ▼
           FEMALE INFERTILITY    STEM CELL
           /female-infertility/  /stemcell/
                   │                 │
         ┌─────────┼──────┐          │
         ▼         ▼      ▼          │
       PCOS    Low AMH  Blocked   Ovarian
                │       Tubes     Rejuv.
                └───────────────────┘
                  (low AMH → SC P4)
```

### Explicit Cross-Links to Build (by condition/procedure)

| Source Page | Must Link To | Reason |
|---|---|---|
| `/male-infertility/azoospermia` | `/art-procedures/icsi`, `/art-procedures/surgical-sperm-retrieval`, `/stemcell/role-in-infertility` | Treatment pathway |
| `/male-infertility/low-sperm-count` | `/art-procedures/icsi`, `/art-procedures/iui` | Treatment pathway |
| `/male-infertility/dna-fragmentation` | `/art-procedures/icsi`, `/art-procedures/pgt` | Treatment pathway |
| `/male-infertility/varicocele` | `/art-procedures/icsi`, `/art-procedures/iui` | Post-varicocele ART |
| `/male-infertility/primary-testicular-failure` | `/stemcell/role-in-infertility`, `/art-procedures/surgical-sperm-retrieval` | Advanced cases |
| `/female-infertility/pcos` | `/art-procedures/iui`, `/art-procedures/ivf`, `/glossary/pcos` | Treatment pathway |
| `/female-infertility/endometriosis` | `/art-procedures/ivf`, `/art-procedures/ovarian-endometrial-prp` | Treatment pathway |
| `/female-infertility/blocked-tubes` | `/art-procedures/ivf` | IVF is primary Tx |
| `/female-infertility/diminished-ovarian-reserve` | `/art-procedures/ivf`, `/art-procedures/ovarian-endometrial-prp`, `/stemcell/stemcell-ovarian-rejuvenation` | Multiple options |
| `/female-infertility/recurrent-pregnancy-loss` | `/art-procedures/pgt`, `/art-procedures/ivf` | PGT-A workflow |
| `/female-infertility/primary-ovarian-failure` | `/stemcell/stemcell-ovarian-rejuvenation`, `/art-procedures/ivf` | Advanced Tx |
| `/art-procedures/iui` | `/art-procedures/ivf` ("if IUI fails") | Journey continuation |
| `/art-procedures/ivf` | `/art-procedures/icsi`, `/art-procedures/pgt`, `/art-procedures/laser-assisted-hatching` | Within-IVF options |
| `/art-procedures/surgical-sperm-retrieval` | `/art-procedures/icsi`, `/male-infertility/azoospermia` | Context + next step |
| `/stemcell/role-in-infertility` | `/female-infertility/stemcell-ovarian-rejuvenation`, `/male-infertility/testicular-recovery-stemcell` | Applications |
| Blog: "IVF vs ICSI" | `/art-procedures/ivf`, `/art-procedures/icsi`, `/male-infertility/` | Both sides of comparison |
| Blog: "Low AMH Guide" | `/female-infertility/diminished-ovarian-reserve`, `/stemcell/`, `/tools/fertility-age-clock` | Deep + tool |
| Blog: "Semen Analysis Guide" | `/male-infertility/`, `/tools/semen-analysis-interpreter`, all male conditions | Hub + tool |

---

## Patient Journey Maps

Content should guide three distinct patient types through the site.

### Journey 1: "We've been trying for 2 years, no diagnosis yet"

```
Google: "unexplained infertility Pakistan"
      ↓
/female-infertility/unexplained-infertility
      ↓ (reads page, clicks "What tests do you need?")
Blog: "Questions to Ask Your IVF Doctor"
      ↓ (reads, clicks "See our diagnostic approach")
/about/ (Dr. Adnan's credentials + methodology)
      ↓
WhatsApp CTA → Consultation booked
```

### Journey 2: "Husband has zero sperm count"

```
Google: "azoospermia treatment Lahore"
      ↓
/male-infertility/azoospermia
      ↓ (learns about Micro-TESE, clicks treatment link)
/art-procedures/surgical-sperm-retrieval
      ↓ (learns about ICSI after retrieval)
/art-procedures/icsi
      ↓ (checks cost)
Blog: "IVF Cost in Pakistan 2026"  OR  /tools/ivf-cost-estimator
      ↓
/contact/ → WhatsApp
```

### Journey 3: "Researching IVF from Islamabad"

```
Google: "best IVF specialist Pakistan teleconsultation"
      ↓
Blog: "Fertility Treatment in Pakistan 2026"
      ↓ (wants to understand if they're a candidate)
/tools/ivf-success-calculator
      ↓ (sees results, wants to verify with a doctor)
Blog: "Can I Travel from Islamabad for IVF in Lahore?"
      ↓
/contact/ (teleconsultation form)
```

---

## URL Convention Rules

| Content Type | Pattern | Example |
|---|---|---|
| Pillar hub | `/category/` | `/male-infertility/` |
| Cluster page | `/category/condition` | `/male-infertility/azoospermia` |
| Blog post | `/blog/slug` | `/blog/ivf-cost-pakistan-2026` |
| Blog category | `/blog/category/name` | `/blog/category/male-fertility` |
| Glossary hub | `/glossary/` | `/glossary/` |
| Glossary term | `/glossary/term` | `/glossary/azoospermia` |
| Free tool | `/tools/tool-name` | `/tools/ivf-success-calculator` |
| Doctor profile | `/doctors/name` | `/doctors/dr-adnan-jabbar` |
| Guide/download | `/guides/slug` | `/guides/ivf-patient-guide` |

**Rules:**
- All lowercase, hyphens only (no underscores)
- No `.php` extension visible (already handled by `.htaccess`)
- Descriptive, keyword-inclusive slugs
- Max 3 levels deep (exception: blog category posts at level 3 is fine)

---

## Implementation Tasks

### 📝 Write First (no code needed)

- [ ] **Task 1:** Write 5-entry glossary pilot (AMH, IVF, Azoospermia, PCOS, ICSI) to validate format before building full section
- [ ] **Task 2:** Add cross-link sections to top 5 condition pages (see cross-link table above)
- [ ] **Task 3:** Upgrade `/male-infertility/index.php` to 800+ word pillar page with all sub-page links
- [ ] **Task 4:** Upgrade `/female-infertility/index.php` to 800+ word pillar page
- [ ] **Task 5:** Upgrade `/art-procedures/index.php` to 800+ word pillar page
- [ ] **Task 6:** Write Month 1 blog posts (see [07-content-strategy.md](07-content-strategy.md))
- [ ] **Task 7:** Add "Related Conditions" section to all ART procedure pages

### 🔧 Build (code required)

- [ ] **Task 8:** Create `/glossary/` directory + hub + individual term template (PHP)
- [ ] **Task 9:** Implement blog category URL routing (`/blog/category/name/`) and `category` field in `blog_posts` DB table
- [ ] **Task 10:** Add `category` and `tags` columns to `blog_posts` DB table
- [ ] **Task 11:** Build blog category listing pages (template from existing blog listing)
- [ ] **Task 12:** Add "Related Posts" section to blog template (query by same category)
- [ ] **Task 13:** Add "You might also like" section to condition/procedure pages (reusable PHP include)
- [ ] **Task 14:** Create `/tools/` hub page + first tool (see [04-free-tool-strategy.md](04-free-tool-strategy.md))
- [ ] **Task 15:** Add breadcrumb nav display (visible HTML, not just schema) to all pages

---

## Quick Reference: Which Pages Link to What

```
Condition page checklist — every condition page must have:
  ✓ Link UP to parent pillar (breadcrumb + "Back to Male Infertility")
  ✓ Links to 2–3 recommended treatments (cross-pillar)
  ✓ Link to relevant glossary terms (first mention rule)
  ✓ Link to 1–2 related blog posts ("Learn more: [Blog post title]")
  ✓ Link to relevant free tool (if exists)
  ✓ WhatsApp CTA

Blog post checklist — every blog post must have:
  ✓ Link UP to parent pillar and blog category
  ✓ Links to 2–3 relevant condition/procedure pages (in-text)
  ✓ Link to 1 glossary term (for any defined medical term used)
  ✓ Link to related free tool (if relevant)
  ✓ "Related Posts" section (3 posts, same category)
  ✓ WhatsApp CTA

Glossary term checklist — every term page must have:
  ✓ Link to primary condition/procedure page
  ✓ Link to 1 blog post that covers the term in depth
  ✓ 2–3 "Related terms" links (other glossary pages)
  ✓ WhatsApp CTA ("Have questions about [term]? Ask Dr. Adnan")
```
