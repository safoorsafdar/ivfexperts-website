# Analytics & Tracking Setup — ivfexperts.pk
**Assessed:** 2026-03-03 | **Status:** 🔴 Not Started
**Stack:** GTM (GTM-53FTJBJB) + GA4 (G-HQ78PRNQWM) — both installed, events not configured
**Goal:** Measure what actually drives consultation bookings so every other doc in this roadmap can be validated

---

## Executive Summary

GTM and GA4 are installed on the site but no custom events, conversions, or audiences are configured. This means there is currently no way to know: which pages drive WhatsApp clicks, whether blog readers convert, which traffic sources produce bookings, or whether any CRO changes improve results. Without proper tracking, the entire roadmap is flying blind. This doc covers every event that needs to be tracked, how to configure it in GTM, and what to monitor monthly.

**Priority order:** WhatsApp clicks → Form submissions → Tool usage → Scroll depth → Blog-to-conversion paths

---

## CONVERSION EVENTS (Configure First)

### ✅ Task 1: Track WhatsApp button clicks as GA4 conversions
- **Why:** WhatsApp is the primary conversion action on the site. This is the single most important event to measure.
- **GTM Configuration:**
  1. Create a **Click Trigger** in GTM:
     - Trigger type: Click — All Elements
     - Fire on: Click URL contains `wa.me`
  2. Create a **GA4 Event Tag:**
     - Event name: `whatsapp_click`
     - Parameters:
       - `page_location` — `{{Page URL}}`
       - `click_source` — `{{Click URL}}` (captures which WhatsApp link was clicked)
       - `page_type` — a custom variable (homepage / treatment / blog / tool)
  3. In GA4 → Admin → Conversions: Mark `whatsapp_click` as a conversion
- **Expected data:** See which pages and traffic sources drive the most WhatsApp clicks
- **Priority:** 🔴 Critical

### ✅ Task 2: Track contact form submissions as GA4 conversions
- **File:** `contact/index.php` — form submit handler
- **GTM Configuration:**
  - Option A (if form redirects to a thank-you page): Create a **Page View trigger** on `/contact/thank-you` or whatever the success URL is
  - Option B (if form submits via AJAX): Add a `dataLayer.push` in the PHP success response:
    ```php
    // In contact form handler, after successful DB insert or email send:
    echo '<script>
      window.dataLayer = window.dataLayer || [];
      window.dataLayer.push({
        "event": "contact_form_submit",
        "form_location": "contact_page"
      });
    </script>';
    ```
  - In GTM: Create a Custom Event trigger on `contact_form_submit`
  - Create a GA4 Event tag: `form_submit` with parameter `form_location`
  - Mark as conversion in GA4
- **Priority:** 🔴 Critical

### ✅ Task 3: Track phone number clicks
- **File:** `includes/header.php` and `contact/index.php` — anywhere phone number appears as a link
- **Ensure phone numbers are clickable links:** `<a href="tel:+923111101483">+92 311 110 1483</a>`
- **GTM Trigger:** Click URL contains `tel:`
- **GA4 Event:** `phone_click` with `page_location` parameter
- **Mark as conversion**
- **Priority:** 🔴 High

---

## PAGE-LEVEL EVENTS

### ✅ Task 4: Track scroll depth on key pages
- **Why:** Reveals whether patients are reading treatment pages or bouncing immediately.
- **GTM Configuration:**
  1. Enable GTM's built-in **Scroll Depth Trigger**:
     - Scroll depths: 25%, 50%, 75%, 90%
     - Fire on: All pages OR specific page paths (treatment pages, blog)
  2. GA4 Event tag: `scroll_depth`
     - Parameters: `percent_scrolled` (`{{Scroll Depth Threshold}}`), `page_location`
- **Priority:** 🔴 High

### ✅ Task 5: Track outbound link clicks
- **Why:** Patients may click links to HFEA, WHO, or social profiles. Knowing which external sites they visit informs content decisions.
- **GTM Configuration:**
  - Trigger: Click — Just Links, fire on All Outbound Clicks
  - Condition: Click URL does NOT contain `ivfexperts.pk`
  - GA4 Event: `outbound_link_click` with `link_url` parameter
- **Priority:** 🟡 Medium

---

## FREE TOOL EVENTS

### ✅ Task 6: Track tool usage funnel
- **When:** Configure when free tools are built (see [04-free-tool-strategy.md](04-free-tool-strategy.md))
- **Events to fire for each tool:**
  - `tool_start` — user opens the tool page
  - `tool_complete` — user submits the form / sees results
  - `tool_cta_click` — user clicks the WhatsApp CTA after seeing results
- **GTM / PHP implementation:** Add `dataLayer.push` in the tool's JavaScript or PHP result handler:
  ```javascript
  // When user clicks "Calculate" button:
  dataLayer.push({
    event: 'tool_complete',
    tool_name: 'ivf_success_calculator',
    age_bracket: selectedAge,  // anonymized bracket, not exact age
    diagnosis: selectedDiagnosis
  });
  ```
- **GA4 Funnel:** In GA4 → Explore → Funnel: `tool_start` → `tool_complete` → `tool_cta_click` → `whatsapp_click`
- **Priority:** 🟡 Medium (configure when tools are built)

---

## SEARCH CONSOLE INTEGRATION

### ✅ Task 7: Connect Search Console to GA4
- **Steps:**
  1. Go to Google Search Console → verify `ivfexperts.pk` is verified (check via DNS TXT record or HTML tag)
  2. In GA4 → Admin → Product Links → Search Console Links → Link
  3. Select the `ivfexperts.pk` property
  4. Enable linking
- **What this unlocks:** See which search queries bring traffic to which pages, directly inside GA4 reports
- **Reports to check monthly:**
  - Queries with high impressions but low CTR → improve title tags / meta descriptions
  - Pages with declining clicks → update content
  - New queries appearing → create targeted content
- **Priority:** 🔴 High

### ✅ Task 8: Submit sitemap in Search Console
- **Steps:**
  1. In Search Console → Sitemaps → Add new sitemap
  2. Enter: `https://ivfexperts.pk/sitemap.xml`
  3. Click Submit
  4. Verify it returns valid XML (prerequisite: fix from [01-seo-audit.md](01-seo-audit.md) Task 1)
- **Monitor:** Check for crawl errors and indexed page count monthly
- **Priority:** 🔴 Critical (do immediately)

---

## CORE WEB VITALS MONITORING

### ✅ Task 9: Set up Core Web Vitals monitoring
- **Current state:** LCP, CLS, and INP targets are listed in [01-seo-audit.md](01-seo-audit.md) but no monitoring is in place.
- **Targets:**
  - LCP (Largest Contentful Paint) < 2.5s
  - INP (Interaction to Next Paint) < 200ms
  - CLS (Cumulative Layout Shift) < 0.1
- **Free monitoring tools (set up all three):**
  1. **Google PageSpeed Insights:** https://pagespeed.web.dev — run for homepage, top treatment pages, blog. Screenshot results. Re-run monthly.
  2. **Search Console → Core Web Vitals report:** Shows field data (real user data) for your site — check monthly for "Poor" or "Needs Improvement" URLs
  3. **GTM — Web Vitals measurement:** Add the `web-vitals` library snippet via GTM to send CWV data to GA4:
     ```html
     <!-- Custom HTML tag in GTM, trigger: All Pages -->
     <script>
     (function() {
       var script = document.createElement('script');
       script.src = 'https://unpkg.com/web-vitals/dist/web-vitals.iife.js';
       script.onload = function() {
         webVitals.onLCP(function(m) { dataLayer.push({event:'web_vitals',metric_name:'LCP',metric_value:Math.round(m.value)}); });
         webVitals.onCLS(function(m) { dataLayer.push({event:'web_vitals',metric_name:'CLS',metric_value:Math.round(m.value*1000)}); });
         webVitals.onINP(function(m) { dataLayer.push({event:'web_vitals',metric_name:'INP',metric_value:Math.round(m.value)}); });
       };
       document.head.appendChild(script);
     })();
     </script>
     ```
  4. In GA4: Create an Event tag for `web_vitals` with `metric_name` and `metric_value` parameters
- **Priority:** 🟡 Medium

---

## GA4 AUDIENCES & REPORTING

### ✅ Task 10: Create GA4 audiences for retargeting and analysis
- **Audiences to create in GA4 → Admin → Audiences:**
  1. **High-intent visitors:** Users who visited 2+ treatment pages in a session
  2. **Blog readers:** Users who visited `/blog/` and scrolled 75%+
  3. **Tool users:** Users who triggered `tool_complete`
  4. **WhatsApp clickers:** Users who triggered `whatsapp_click` (to exclude from retargeting — they already converted)
  5. **AI referral traffic:** Sessions with source matching `perplexity.ai|chat.openai.com|bing.com/chat`
- **Use case:** GA4 audiences can be used for Google Ads retargeting if ads are ever run
- **Priority:** 🟡 Medium

### ✅ Task 11: Set up monthly reporting dashboard
- **Tool:** GA4 Explore (custom reports) OR Looker Studio (free, connects to GA4)
- **Monthly report should include:**

  | Metric | Where to find | Target |
  |---|---|---|
  | WhatsApp clicks | Conversions report | Growing MoM |
  | Form submissions | Conversions report | Growing MoM |
  | Organic search traffic | Acquisition → Traffic acquisition | Growing MoM |
  | Top landing pages | Engagement → Landing page | Track top 10 |
  | Blog sessions | Pages report, filter /blog/ | Growing MoM |
  | Core Web Vitals | Search Console CWV report | All "Good" |
  | Indexed pages | Search Console → Coverage | Growing MoM |
  | Average position | Search Console → Performance | Improving |
  | Clicks from AI sources | Acquisition, custom segment | Tracking baseline |

- **Cadence:** First Monday of each month. Save screenshots to a shared folder for trend tracking.
- **Priority:** 🟡 Medium

---

## GTM CONTAINER HEALTH

### ✅ Task 12: Audit existing GTM container for conflicts
- **Steps:**
  1. Log into GTM → Container GTM-53FTJBJB → Preview mode
  2. Visit the site in Preview mode and check:
     - Is GA4 base tag (G-HQ78PRNQWM) firing on every page?
     - Are there any old/duplicate tags from previous setups?
     - Any tags with errors or "Paused" status?
  3. Remove or disable any duplicate or broken tags
  4. Publish a clean version of the container after cleanup
- **Priority:** 🔴 High (do before adding new events)

---

## Metrics to Track
- WhatsApp click conversion rate (clicks ÷ sessions) — target 3%+ for high-intent pages
- Contact form completion rate — target 15%+ of visitors who reach the form
- Organic traffic MoM growth — target 15%+ growth per month for first 6 months
- Core Web Vitals: All pages in "Good" range
- Blog-to-WhatsApp conversion path (GA4 Path Exploration)
