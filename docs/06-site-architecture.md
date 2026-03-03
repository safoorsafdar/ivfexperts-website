# Site Architecture Assessment — ivfexperts.pk
**Assessed:** 2026-03-03 | **Status:** 🔴 Not Started

---

## Executive Summary

The site has a solid flat architecture (max 2 levels deep) which is good for crawl efficiency. Key problems: blog uses query-parameter URLs which are crawl-unfriendly, several important pages are missing (privacy policy, terms, 404 handler, HTML sitemap), the category index pages aren't fully developed as pillar pages, and there's no cross-linking strategy connecting treatment pages to each other.

**Current site map:**
```
ivfexperts.pk/
├── index.php (Homepage)
├── about/ → index.php
├── contact/ → index.php
├── blog/ → index.php (+ ?article=slug — PROBLEM)
├── doctors/ → index.php
├── male-infertility/
│   ├── index.php (category hub)
│   ├── azoospermia.php
│   ├── low-sperm-count.php
│   ├── varicocele.php
│   ├── dna-fragmentation.php
│   ├── erectile-ejaculatory-dysfunction.php
│   ├── unexplained-male-infertility.php
│   ├── klinefelters-syndrome.php
│   ├── hypogonadotropic-hypogonadism.php
│   ├── low-testicular-volume.php
│   ├── primary-testicular-failure.php
│   ├── testicular-recovery-stemcell.php
│   └── penile-doppler-ultrasound.php
├── female-infertility/
│   ├── index.php (category hub)
│   ├── pcos.php
│   ├── endometriosis.php
│   ├── blocked-tubes.php
│   ├── diminished-ovarian-reserve.php
│   ├── recurrent-pregnancy-loss.php
│   ├── unexplained-infertility.php
│   ├── uterine-fibroids-polyps.php
│   ├── adenomyosis.php
│   ├── primary-ovarian-failure.php
│   ├── ovarian-tissue-preservation.php
│   └── stemcell-ovarian-rejuvenation.php
├── art-procedures/
│   ├── index.php (category hub)
│   ├── ivf.php
│   ├── icsi.php
│   ├── iui.php
│   ├── pgt.php
│   ├── fertility-preservation.php
│   ├── ovarian-endometrial-prp.php
│   ├── surgical-sperm-retrieval.php
│   └── laser-assisted-hatching.php
└── stemcell/
    ├── index.php (category hub)
    ├── adscs.php
    ├── mesenchymal-umbilical.php
    ├── pluripotent-stem-cells.php
    ├── multipotent-stem-cells.php
    └── role-in-infertility.php
```

---

## CRITICAL FIXES

### ✅ Task 1: Fix blog URL structure (query params → clean URLs)
- **Problem:** Blog at `/blog/?article=slug` — query parameters are crawl-unfriendly and rank poorly. This is the most impactful architecture fix.
- **Target:** `/blog/article-slug` (clean, descriptive URLs)
- **Steps:**
  1. Add `.htaccess` rule (before the existing .php removal rule):
     ```apache
     # Clean blog article URLs
     RewriteRule ^blog/([a-z0-9-]+)/?$ blog/index.php?article=$1 [L,QSA]
     ```
  2. Update `blog/index.php` to read `$slug` from `$_GET['article']` OR from the URL path:
     ```php
     // Support both clean URL and legacy query param
     $slug = isset($_GET['article']) ? trim($_GET['article']) : '';
     if (empty($slug)) {
         $pathParts = explode('/', trim($_SERVER['REQUEST_URI'], '/'));
         $slug = end($pathParts) ?? '';
     }
     ```
  3. Update `sitemap.php` to output `/blog/slug` not `/blog/?article=slug`
  4. Add 301 redirects from old URLs:
     ```apache
     RewriteCond %{QUERY_STRING} ^article=(.+)$
     RewriteRule ^blog/?$ /blog/%1? [R=301,L]
     ```
  5. Update all internal links referencing the old format
- **Priority:** 🔴 Critical

### ✅ Task 2: Create a custom 404 error page
- **Problem:** No `404.php` or custom error page exists. PHP/Apache default 404 pages look unprofessional and have no navigation back to useful content.
- **Steps:**
  1. Create `404.php` with site header/footer, friendly message, and links to main sections
  2. Add to `.htaccess`:
     ```apache
     ErrorDocument 404 /404.php
     ```
  3. Make the 404 page suggest: "Looking for fertility information? Try these:"
     - Links to Male Infertility, Female Infertility, ART Procedures, Contact
- **Priority:** 🔴 High

---

## MISSING PAGES

### ✅ Task 3: Create Privacy Policy page
- **URL:** `/privacy-policy/index.php`
- **Problem:** Footer links to `/privacy-policy/` which 404s. Required for E-E-A-T and legal compliance (Pakistan PDPA).
- **Minimum content:** What data is collected, how it's used, cookie policy, contact for data requests
- **Priority:** 🔴 High

### ✅ Task 4: Create Terms of Use page
- **URL:** `/terms-conditions/index.php`
- **Problem:** Footer links to `/terms-conditions/` which 404s.
- **Minimum content:** Medical disclaimer (information is not a substitute for professional medical advice), terms of website use, limitation of liability
- **Priority:** 🔴 High

### ✅ Task 5: Create HTML Sitemap page
- **URL:** `/sitemap/index.php` (or `/sitemap.html`)
- **Purpose:** An HTML sitemap (not the XML one) is user-friendly and helps crawlers find all pages. Useful for a site with 35+ treatment pages.
- **Content:** Organized list of all pages grouped by category
- **Priority:** 🟡 Medium

### ✅ Task 6: Create /tools/ directory (for free tools)
- **URL:** `/tools/index.php`
- **See:** [04-free-tool-strategy.md](04-free-tool-strategy.md) for full plan
- **Add to navigation:** Footer → Resources section, or new nav item
- **Priority:** 🟡 Medium (when first tool is built)

---

## INTERNAL LINKING STRATEGY

### ✅ Task 7: Add cross-links between related condition and procedure pages
- **Problem:** Treatment condition pages (e.g., `/female-infertility/pcos.php`) don't link to the relevant procedure pages (e.g., `/art-procedures/iui.php`, `/art-procedures/ivf.php`). This disconnects the patient journey and loses PageRank flow.
- **Internal linking map:**
  ```
  PCOS →  links to: IUI, IVF, Fertility Preservation
  Endometriosis → links to: IVF, Ovarian PRP, Surgical Sperm Retrieval
  Azoospermia → links to: ICSI, Micro-TESE (surgical-sperm-retrieval), Stem Cell
  Low AMH → links to: IVF, Ovarian PRP, Stem Cell Ovarian Rejuvenation
  Recurrent Miscarriage → links to: PGT, IVF
  Blocked Tubes → links to: IVF, IUI
  ```
- **Implementation:** Add a "Recommended Treatments for [Condition]" section at the bottom of each condition page with linked cards.
- **Priority:** 🔴 High (SEO + UX + CRO impact)

### ✅ Task 8: Upgrade category index pages to true pillar pages
- **Problem:** Category index pages (`/male-infertility/`, `/female-infertility/`, `/art-procedures/`) exist but may be thin. These should be comprehensive pillar pages that:
  - Define the category
  - Link to ALL sub-pages with descriptions
  - Include 500–800 words of quality content
  - Target the primary keyword for the category
- **SEO value:** These pages rank for category-level queries ("male infertility treatment Lahore") and pass PageRank to all sub-pages.
- **Steps:** Review each index.php — if thin, expand content significantly.
- **Priority:** 🔴 High

---

## NAVIGATION IMPROVEMENTS

### ✅ Task 9: Add "Resources" or "Tools" section to navigation
- **Problem:** The nav has Home, About, Male Infertility, Female Infertility, ART Procedures, Stem Cell, Contact. No section for: Blog, Tools, Sitemap, or Resources.
- **Currently:** Blog is buried under "About" dropdown — most patients won't look there.
- **Fix:** Options:
  - Move Blog to its own top-level nav item
  - Add a "Resources" dropdown: Blog, Free Tools, Patient Portal, FAQ
- **Priority:** 🟡 Medium

### ✅ Task 10: Ensure Stem Cell section is properly linked from treatment pages
- **Problem:** Stem cell therapy cross-links exist in nav but treatment pages in male/female infertility don't consistently link to related stem cell pages.
- **Example:** `/male-infertility/azoospermia.php` should link to `/stemcell/role-in-infertility.php` and `/male-infertility/testicular-recovery-stemcell.php`
- **Priority:** 🟡 Medium

---

## TARGET SITE ARCHITECTURE (After Fixes)

```
ivfexperts.pk/
├── index.php
├── about/
├── contact/
├── doctors/
│   ├── index.php (team overview)
│   └── dr-adnan-jabbar/ (individual profile — NEW)
├── blog/
│   ├── index.php (listing)
│   └── [slug] → clean URL articles (FIXED)
├── tools/ (NEW)
│   ├── index.php (hub)
│   ├── ivf-success-calculator/
│   ├── semen-analysis-interpreter/
│   └── ...
├── male-infertility/ (pillar page — UPGRADED)
│   └── [all conditions]
├── female-infertility/ (pillar page — UPGRADED)
│   └── [all conditions]
├── art-procedures/ (pillar page — UPGRADED)
│   └── [all procedures]
├── stemcell/ (pillar page — UPGRADED)
│   └── [all types]
├── privacy-policy/ (NEW)
├── terms-conditions/ (NEW)
├── sitemap/ (NEW — HTML)
└── 404.php (NEW)
```

### ✅ Task 11: Audit and update internal links site-wide after URL changes
- After implementing clean blog URLs and any other URL changes, run a full internal link audit:
  1. Search all PHP files for hard-coded `/blog/?article=` links and update them
  2. Check footer links, nav links, and any blog cross-references
  3. Verify no internal links point to `.php` extension URLs (should use clean URLs)
- **Priority:** 🟡 Medium (after Task 1 completed)
