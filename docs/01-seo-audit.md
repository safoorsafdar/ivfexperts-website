# SEO Audit — ivfexperts.pk
**Assessed:** 2026-03-03 | **Status:** 🔴 Not Started

---

## Executive Summary

The site has solid foundational SEO (HTTPS, canonical tags, per-page meta, GTM/GA4, structured data). However, five critical/high issues need immediate attention before further optimization is worthwhile.

**Overall Grade: C+ (Foundations good, significant gaps blocking performance)**

---

## CRITICAL — Fix Immediately

### ✅ Task 1: Verify sitemap.xml serves valid XML
- **File:** `sitemap.xml` (0 bytes) + `sitemap.php` + `.htaccess` rewrite
- **Problem:** `/sitemap.xml` is a zero-byte static file. The `.htaccess` routes it to `sitemap.php`, but if the rewrite fails or caching serves the static file, Google gets an empty sitemap.
- **Steps:**
  1. Open browser, visit `https://ivfexperts.pk/sitemap.xml` — verify it returns valid XML (not blank)
  2. If blank: check Apache `mod_rewrite` is enabled and `.htaccess` rule `RewriteRule ^sitemap\.xml$ sitemap.php [L]` is active
  3. Submit the sitemap URL in Google Search Console → Sitemaps
  4. **Note:** Blog posts in sitemap use `?article=slug` query URLs — this is a separate issue (Task 6)
- **Priority:** 🔴 Critical

---

## HIGH — Fix Within 2 Weeks

### ✅ Task 2: Add `og:image` meta tag
- **File:** `includes/header.php`
- **Problem:** `seo.php` defines `$ogImage` (with a default lab photo URL) but `header.php` never outputs `<meta property="og:image">`. Every social share (WhatsApp, LinkedIn) shows no image.
- **Fix:** Add after line 35 in `includes/header.php`:
  ```html
  <meta property="og:image" content="<?= htmlspecialchars($ogImage) ?>">
  <meta property="og:image:width" content="1200">
  <meta property="og:image:height" content="630">
  <meta name="twitter:image" content="<?= htmlspecialchars($ogImage) ?>">
  ```
- Then in `seo.php`, assign page-specific `$ogImage` for key pages (doctor photo for /about/, procedure images for treatment pages)
- **Priority:** 🔴 High

### ✅ Task 3: Compress and convert images to WebP
- **Files:** `assets/images/` — All PNG/JPG images
- **Problem:** Images are massive and directly kill Core Web Vitals (LCP):
  - `dr-adnan.jpg` — **1.17 MB** → target: ~80 KB WebP
  - `hero_ivf_procedure.png` — **598 KB** → target: ~60 KB WebP
  - `art_procedures_lab.png` — **568 KB** → target: ~60 KB WebP
  - `hero_low_sperm_count.png` — **598 KB** → target: ~60 KB WebP
  - `clinic.jpg` — **181 KB** → target: ~40 KB WebP
- **Steps:**
  1. Convert all images to WebP using Squoosh (free: squoosh.app) or ImageMagick
  2. Use `<picture>` element with WebP + JPG fallback in each page that uses hero images
  3. Add `loading="lazy"` to all below-fold images (already on some)
  4. Add explicit `width` and `height` attributes to prevent CLS
- **Priority:** 🔴 High

### ✅ Task 4: Fix H1 on homepage to include primary keyword
- **File:** `index.php`
- **Problem:** H1 is JavaScript-rotated and starts as "Parenthood Begins with Clarity & Strategy" — no mention of IVF, Lahore, or any primary keyword. Google uses the initial render for indexing.
- **Fix:** Keep the emotional rotating text as a visual element, but anchor a static keyword H1:
  ```html
  <h1 class="sr-only">IVF & Fertility Specialist in Lahore, Pakistan | Dr. Adnan Jabbar</h1>
  <!-- OR replace the rotating h1 with a p/span and make a static h1 above it -->
  ```
  Recommended visible approach: *"Expert IVF & Fertility Specialist in Lahore, Pakistan"* as the static H1, with the emotional rotating content as a `<p>` or styled `<span>` below it.
- **Priority:** 🔴 High

### ✅ Task 5: Add favicon
- **File:** `includes/header.php`
- **Problem:** No `<link rel="icon">` in `<head>`. Blank favicons in browser tabs and absence from Google SERPs.
- **Fix:** Add inside `<head>` in `includes/header.php`:
  ```html
  <link rel="icon" type="image/png" href="/assets/images/logo.png" sizes="32x32">
  <link rel="apple-touch-icon" href="/assets/images/logo.png">
  ```
  Ideally: Generate a proper 32x32 favicon.ico from logo.png using realfavicongenerator.net.
- **Priority:** 🟡 Medium-High

---

## MEDIUM — Fix Within 1 Month

### ✅ Task 6: Implement clean blog URLs (fix `?article=slug`)
- **File:** `.htaccess` + `blog/index.php`
- **Problem:** Blog articles live at `/blog/?article=my-article-slug`. Query parameter URLs rank worse than clean URLs.
- **Target URL:** `/blog/my-article-slug/` or `/blog/my-article-slug`
- **Fix:**
  1. Add `.htaccess` rewrite rule:
     ```apache
     RewriteRule ^blog/([a-z0-9-]+)/?$ blog/index.php?article=$1 [L,QSA]
     ```
  2. Update `sitemap.php` to output `/blog/slug` not `/blog/?article=slug`
  3. Update any internal links that reference the old format
  4. Update `blog/index.php` to generate clean URL links in listing
  5. Add 301 redirects from old ?article= URLs to new clean URLs
- **Priority:** 🔴 High

### ✅ Task 7: Remove duplicate structured data
- **Files:** `includes/header.php` (lines 54–79) and `includes/footer.php` (lines 115–144)
- **Problem:** `MedicalBusiness` and `Physician` schemas are defined in both header AND footer. The header version is incomplete (no address). Duplicate schema confuses parsers.
- **Fix:** Delete the schema block from `includes/header.php` (lines 54–79). Keep only the more complete footer schema. The dynamic page schema (MedicalCondition/MedicalProcedure) in header.php should stay.
- **Priority:** 🟡 Medium

### ✅ Task 8: Fix breadcrumb schema — strip .php from URLs
- **File:** `includes/seo.php`, function `generateBreadcrumb()` (lines 230–257)
- **Problem:** The breadcrumb function uses raw `REQUEST_URI`, generating URLs like `https://ivfexperts.pk/male-infertility/azoospermia.php`. But `.htaccess` strips `.php` — the canonical is `/male-infertility/azoospermia`. Schema points to non-canonical URLs.
- **Fix:** In `generateBreadcrumb()`:
  ```php
  $cleanPath = str_replace('.php', '', $path);
  $breadcrumbs[] = [
      "name" => $name,
      "url" => "https://ivfexperts.pk" . $cleanPath
  ];
  ```
- **Priority:** 🟡 Medium

### ✅ Task 9: Create Privacy Policy and Terms of Use pages
- **Files:** Create `privacy-policy/index.php` and `terms-conditions/index.php`
- **Problem:** Footer links to both pages. Both 404. This hurts E-E-A-T and user trust.
- **Fix:** Create basic but complete Privacy Policy (data collected, how used, cookies) and Terms of Use pages. For a medical site, these are especially important for Google's trust signals.
- **Priority:** 🟡 Medium

### ✅ Task 10: Fix heading hierarchy on homepage
- **File:** `index.php`
- **Problem:** `<h2>` and `<h3>` tags are used for statistics counters and card labels (e.g., `<h2 class="text-3xl counter">0</h2>` for "10 years experience"). Headings should describe content sections, not style numbers.
- **Fix:** Replace heading tags used purely for visual styling with `<span>` or `<p>` elements. Use headings only for semantic section titles.
- **Priority:** 🟡 Medium

### ✅ Task 11: Refine title tags — reduce keyword stacking
- **File:** `includes/seo.php`
- **Problem:** Several titles over-optimize:
  - Homepage: *"Best IVF Specialist in Lahore, Pakistan | Gender Selection & ICSI"*
  - Male index: *"Male Infertility Specialist in Pakistan | Azoospermia & Varicocele"*
  - "Best" claims can trigger Google skepticism; 3+ keywords in title = thin quality signal
- **Recommended format:** `[Primary Topic] in [Location] | Dr. Adnan Jabbar`
- **Priority:** 🟢 Low

---

## LOW — Ongoing Improvements

### ✅ Task 12: Remove dead-code page-level title definitions
- **Files:** All individual `.php` pages (index.php, male-infertility/*.php, etc.)
- **Problem:** Each page defines `$pageTitle` before including `header.php`, but `seo.php` always overwrites these with URL-routing logic. The page-level definitions are unused.
- **Fix:** Either remove redundant definitions from individual pages, or convert them to use `$customPageTitle`/`$customMetaDescription` which `seo.php` does respect (lines 226–227).
- **Priority:** 🟢 Low

### ✅ Task 13: Add social media profile links to schema `sameAs`
- **File:** `includes/footer.php` — `MedicalBusiness` schema
- **Problem:** No `sameAs` property. Linking to social profiles strengthens Google's entity understanding.
- **Fix:** Once social profiles exist, add to the schema:
  ```json
  "sameAs": ["https://facebook.com/ivfexperts", "https://instagram.com/ivfexperts"]
  ```
- **Priority:** 🟢 Low

### ✅ Task 14: Create individual doctor profile pages
- **Files:** Create `doctors/dr-adnan-jabbar.php` + any additional doctors
- **Problem:** `/doctors/` only has `index.php`. Individual doctor pages enable better `Physician` schema and local SEO.
- **Priority:** 🟢 Low

### ✅ Task 15: Add `hreflang` for Urdu content (if planned)
- **Problem:** If Urdu-language content is added for Pakistani audience, `hreflang` tags will be needed.
- **Priority:** 🟢 Low (future)

---

## What's Working Well ✅
- robots.txt properly blocks /admin/, /config/, /portal/, /includes/
- HTTPS enforcement (301 redirect)
- GZIP compression + browser caching configured
- Canonical tags on all pages
- Per-page unique title + meta description
- GTM + GA4 analytics
- BreadcrumbList schema on all pages
- MedicalCondition/MedicalProcedure schema on treatment pages
- OpenGraph + Twitter Card tags (minus og:image)
- Mobile-responsive design
- Security headers (X-Frame-Options, X-Content-Type-Options, etc.)
- Clean URL structure (no .php extension visible due to htaccess)

---

## Metrics to Track
- Google Search Console: Impressions, Clicks, Average Position
- Core Web Vitals: LCP < 2.5s, INP < 200ms, CLS < 0.1
- Indexed pages: site:ivfexperts.pk count
- Sitemap submission status in Search Console
