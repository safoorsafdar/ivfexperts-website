# Schema Markup Audit & Implementation Plan — ivfexperts.pk
**Assessed:** 2026-03-03 | **Status:** 🔴 Not Started
**Tech Stack:** Custom PHP — all schema is server-rendered JSON-LD (no JS injection issues)
**Validate at:** https://search.google.com/test/rich-results | https://validator.schema.org/

---

## Executive Summary

The site already has: MedicalBusiness, Physician, BreadcrumbList, MedicalCondition, and MedicalProcedure schemas — a solid foundation. The main problems are: (1) duplicate organization/physician schema in both header and footer, (2) breadcrumb URLs with `.php` extensions, (3) no FAQPage schema anywhere, (4) no Article schema on blog posts, (5) no WebSite schema, and (6) incomplete MedicalBusiness data. Fixing these unlocks FAQs, rich results, and stronger entity recognition.

**Rich Results Currently Possible (not yet implemented):**
- FAQ snippets (high value for medical queries)
- Breadcrumb trail in SERPs
- Article rich results (for blog)
- Sitelinks search box (via WebSite schema)

---

## CRITICAL FIXES

### ✅ Task 1: Remove duplicate schema from header.php
- **File:** `includes/header.php` — lines 54–79
- **Problem:** `MedicalBusiness` + `Physician` schemas are defined in BOTH `header.php` AND `footer.php`. The header version is incomplete (no address, no sameAs). Duplicate entities with different data confuse Google's entity resolution.
- **Fix:** Delete the schema block in `header.php` lines 54–79 entirely. Keep the complete version in `footer.php` only. The dynamic page-level schema (MedicalCondition/MedicalProcedure) stays in header.php — that's correct.
- **Priority:** 🔴 Critical

### ✅ Task 2: Fix breadcrumb URLs — strip .php extension
- **File:** `includes/seo.php` — `generateBreadcrumb()` function (lines 230–257)
- **Problem:** Breadcrumb schema URLs include `.php` (e.g., `https://ivfexperts.pk/male-infertility/azoospermia.php`), but canonical URLs don't (`.htaccess` strips the extension). Schema points to non-canonical, potentially 404 URLs.
- **Fix:**
  ```php
  foreach ($segments as $segment) {
      $path .= "/" . $segment;
      $cleanPath = str_replace('.php', '', $path); // ADD THIS LINE
      $name = ucwords(str_replace(["-", ".php"], [" ", ""], $segment));
      $breadcrumbs[] = [
          "name" => $name,
          "url"  => "https://ivfexperts.pk" . $cleanPath // USE cleanPath
      ];
  }
  ```
- **Priority:** 🔴 High

---

## HIGH PRIORITY — New Schema to Add

### ✅ Task 3: Add WebSite schema to homepage
- **File:** `includes/header.php` (only on homepage — wrap in PHP check `if (empty($uriPaths))`)
- **Purpose:** Enables Google sitelinks search box; strengthens entity recognition.
- **Implementation:**
  ```json
  {
    "@context": "https://schema.org",
    "@type": "WebSite",
    "@id": "https://ivfexperts.pk/#website",
    "url": "https://ivfexperts.pk",
    "name": "IVF Experts Pakistan",
    "description": "Pakistan's leading fertility specialist — IVF, ICSI, and advanced reproductive medicine by Dr. Adnan Jabbar",
    "publisher": {
      "@id": "https://ivfexperts.pk/#organization"
    },
    "potentialAction": {
      "@type": "SearchAction",
      "target": {
        "@type": "EntryPoint",
        "urlTemplate": "https://ivfexperts.pk/blog/?article={search_term_string}"
      },
      "query-input": "required name=search_term_string"
    }
  }
  ```
- **Priority:** 🔴 High

### ✅ Task 4: Add FAQPage schema to all major treatment pages
- **Files:** All treatment pages — `art-procedures/ivf.php`, `art-procedures/icsi.php`, `male-infertility/azoospermia.php`, `female-infertility/pcos.php`, etc.
- **Purpose:** FAQPage schema enables "People Also Ask" rich results — extremely valuable for medical queries.
- **Implementation pattern (add to each treatment page):**
  ```php
  $faqs = [
      ["q" => "How much does IVF cost in Lahore, Pakistan?",
       "a" => "IVF costs in Lahore typically range from PKR 300,000–600,000 depending on the protocol. Contact us for a personalized cost estimate based on your specific situation."],
      ["q" => "What is the IVF success rate in Pakistan?",
       "a" => "Success rates vary by age and diagnosis. Dr. Adnan Jabbar's protocols achieve success rates comparable to international standards. Patients under 35 with a good ovarian reserve typically have the highest success rates."],
      // Add 3-5 FAQs per page
  ];
  ```
  ```json
  {
    "@context": "https://schema.org",
    "@type": "FAQPage",
    "mainEntity": [
      {
        "@type": "Question",
        "name": "How much does IVF cost in Lahore, Pakistan?",
        "acceptedAnswer": {
          "@type": "Answer",
          "text": "..."
        }
      }
    ]
  }
  ```
- **Build a reusable PHP helper** in `includes/` that accepts an array of Q&A pairs and outputs the JSON-LD block.
- **Priority:** 🔴 High

### ✅ Task 5: Add Article / BlogPosting schema to blog posts
- **File:** `blog/index.php` — single article view block
- **Problem:** Blog posts have no structured data. Article schema enables rich results and helps AI systems identify author credentials.
- **Implementation:**
  ```json
  {
    "@context": "https://schema.org",
    "@type": "BlogPosting",
    "headline": "[article title]",
    "description": "[meta description]",
    "url": "https://ivfexperts.pk/blog/[slug]",
    "datePublished": "[published_at]",
    "dateModified": "[updated_at]",
    "author": {
      "@type": "Person",
      "name": "Dr. Adnan Jabbar",
      "url": "https://ivfexperts.pk/about/",
      "jobTitle": "Fertility Consultant & Clinical Embryologist"
    },
    "publisher": {
      "@id": "https://ivfexperts.pk/#organization"
    },
    "image": "[og_image_url]",
    "mainEntityOfPage": {
      "@type": "WebPage",
      "@id": "https://ivfexperts.pk/blog/[slug]"
    }
  }
  ```
- The `blog/index.php` already has the `$article` data from the DB query — use it to populate this schema.
- **Priority:** 🔴 High

---

## MEDIUM PRIORITY — Enrich Existing Schema

### ✅ Task 6: Complete MedicalBusiness schema in footer.php
- **File:** `includes/footer.php` — existing schema block (lines 115–144)
- **Current missing fields:**
  - `geo` (coordinates)
  - `openingHours`
  - `priceRange`
  - `hasMap` (Google Maps link)
  - `sameAs` (social profiles)
  - `image` (clinic or doctor photo)
  - `email`
- **Add these to the existing schema:**
  ```json
  "geo": {
    "@type": "GeoCoordinates",
    "latitude": "31.5204",
    "longitude": "74.3587"
  },
  "openingHoursSpecification": [
    {
      "@type": "OpeningHoursSpecification",
      "dayOfWeek": ["Monday", "Tuesday", "Wednesday", "Thursday", "Saturday"],
      "opens": "09:00",
      "closes": "17:00"
    }
  ],
  "priceRange": "PKR PKR",
  "sameAs": [
    "https://facebook.com/ivfexperts",
    "https://instagram.com/ivfexperts"
  ]
  ```
- Confirm actual hours and social URLs before adding.
- **Priority:** 🟡 Medium

### ✅ Task 7: Enhance MedicalCondition schema with more properties
- **File:** `includes/header.php` — dynamic schema block (lines 81–104)
- **Current:** Outputs only `@type`, `name`, `description`, `url`, `relevantSpecialty`, `possibleTreatment`
- **Add:**
  ```json
  "recognizingAuthority": {
    "@type": "MedicalOrganization",
    "name": "American Society for Reproductive Medicine"
  },
  "epidemiology": "Affects 1 in 6 couples worldwide"
  ```
- Also add `"code"` property for ICD-10 codes if medically accurate:
  ```json
  "code": {
    "@type": "MedicalCode",
    "code": "N97",
    "codingSystem": "ICD-10"
  }
  ```
- **Priority:** 🟡 Medium

### ✅ Task 8: Add Physician schema to the About page
- **File:** `about/index.php`
- **Problem:** The doctor's individual profile page has no specific schema. The Physician schema currently lives in the footer (global) — but `/about/` should have an enriched, page-specific Physician block.
- **Implementation:**
  ```json
  {
    "@context": "https://schema.org",
    "@type": "Physician",
    "@id": "https://ivfexperts.pk/#physician",
    "name": "Dr. Adnan Jabbar",
    "image": "https://ivfexperts.pk/assets/images/dr-adnan.jpg",
    "jobTitle": "Fertility Consultant & Clinical Embryologist",
    "description": "Dr. Adnan Jabbar is Pakistan's leading dual-trained fertility consultant and clinical embryologist...",
    "medicalSpecialty": "Reproductive Endocrinology and Infertility",
    "worksFor": { "@id": "https://ivfexperts.pk/#organization" },
    "url": "https://ivfexperts.pk/about/",
    "alumniOf": { "@type": "CollegeOrUniversity", "name": "[University name]" },
    "knowsAbout": ["IVF", "ICSI", "Male Infertility", "Azoospermia", "PCOS", "Stem Cell Fertility"]
  }
  ```
- **Priority:** 🟡 Medium

---

## LOW PRIORITY — Future Enhancements

### ✅ Task 9: Add HowTo schema to procedure pages
- **Files:** `art-procedures/ivf.php`, `art-procedures/icsi.php`, `art-procedures/iui.php`
- **Purpose:** HowTo schema for "How does IVF work?" enables step-by-step rich results.
- **Note:** Only use this where the page genuinely explains a process step-by-step.
- **Priority:** 🟢 Low

### ✅ Task 10: Add AggregateRating schema (once reviews collected)
- **Files:** `includes/footer.php` or homepage
- **Purpose:** Star ratings in SERPs significantly increase click-through rates.
- **Implementation:** Once testimonials are collected as structured data:
  ```json
  "aggregateRating": {
    "@type": "AggregateRating",
    "ratingValue": "4.9",
    "reviewCount": "87",
    "bestRating": "5"
  }
  ```
- Only add with real, verifiable data (Google penalizes fake ratings).
- **Priority:** 🟢 Low (pending review collection)

### ✅ Task 11: Add Event schema for webinars/seminars
- **Files:** New event pages (when applicable)
- **Purpose:** If Dr. Adnan hosts fertility seminars or webinars, Event schema enables rich results.
- **Priority:** 🟢 Low (future)

### ✅ Task 12: Validate all schema after implementation
- **Tool:** https://search.google.com/test/rich-results
- **Checklist:**
  - [ ] Homepage: WebSite + MedicalBusiness + Physician + BreadcrumbList
  - [ ] Treatment pages: MedicalCondition/MedicalProcedure + FAQPage + BreadcrumbList
  - [ ] Blog posts: BlogPosting + BreadcrumbList
  - [ ] About page: Physician + BreadcrumbList
  - [ ] Contact page: LocalBusiness extension + BreadcrumbList
- **Priority:** 🔴 Critical (after all schema additions)

---

## Schema Architecture Map

```
Every page:
  BreadcrumbList (fix URLs first)

Homepage only:
  WebSite (+ SearchAction)
  [Remove duplicate from here — keep in footer]

Footer (global — every page):
  MedicalBusiness (complete with address, hours, geo)
  Physician (complete with image, credentials)

Treatment pages (condition):
  MedicalCondition (name, description, code, specialty, treatment)
  FAQPage (3-5 Qs per page)

Treatment pages (procedure):
  MedicalProcedure (name, description, provider)
  FAQPage (3-5 Qs per page)

Blog listing:
  (no additional schema needed)

Blog article:
  BlogPosting (headline, author, dates, image)

About page:
  Physician (enriched, page-specific)

Contact page:
  MedicalBusiness (duplicate with contact details emphasized)
```
