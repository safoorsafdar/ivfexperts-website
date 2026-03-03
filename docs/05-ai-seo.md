# AI SEO Assessment — ivfexperts.pk
**Assessed:** 2026-03-03 | **Status:** 🔴 Not Started
**Goal:** Get cited by Google AI Overviews, ChatGPT, Perplexity, and Gemini for fertility queries in Pakistan

---

## Executive Summary

AI search is growing rapidly — ~45% of Google searches now show AI Overviews. Medical queries are a primary area where AI Overviews appear. Currently, the site has minimal AI visibility because content isn't structured for extraction, lacks statistics with citations, has no author credentials on individual pages, and treatment pages don't use the Q&A content format that AI systems prefer. The opportunity is significant: there is very little well-structured fertility content in English targeting the Pakistani market, meaning ranking for AI citations has lower competition than comparable Western markets.

**Quick wins:** Structure content as extractable answer blocks + add FAQ sections + add author attribution on pages.

---

## AI BOT ACCESS AUDIT

### ✅ Task 1: Verify robots.txt allows all AI crawlers
- **File:** `robots.txt`
- **Current state:** `User-agent: *` + `Allow: /` — this allows all bots by default ✅
- **However:** No explicit allowances for AI-specific bots. Explicitly allowing them future-proofs against any accidental blocking.
- **Recommended addition to `robots.txt`:**
  ```
  # AI Search Crawlers (explicitly allowed)
  User-agent: GPTBot
  Allow: /

  User-agent: ChatGPT-User
  Allow: /

  User-agent: PerplexityBot
  Allow: /

  User-agent: ClaudeBot
  Allow: /

  User-agent: anthropic-ai
  Allow: /

  User-agent: Google-Extended
  Allow: /

  # Block only AI training crawlers (optional — Common Crawl)
  User-agent: CCBot
  Disallow: /
  ```
- **Priority:** 🟡 Medium (currently allowed via * but explicit is better)

---

## CONTENT EXTRACTABILITY AUDIT

### ✅ Task 2: Add definition blocks to top treatment pages
- **Problem:** AI systems looking for "What is IVF?" or "What is azoospermia?" need a clear 40–60 word definition in the first paragraph. Current pages open with emotional hero sections, not definitions.
- **Pages to fix:** `/art-procedures/ivf.php`, `/art-procedures/icsi.php`, `/male-infertility/azoospermia.php`, `/female-infertility/pcos.php`, `/female-infertility/endometriosis.php`
- **Pattern:** First paragraph of content (after hero) should read:
  ```
  [Term] is [concise definition in 1–2 sentences]. It is used to treat [conditions]
  and is recommended when [indication]. In Pakistan, [X] couples undergo [term]
  annually, with success rates of [X]%.
  ```
- **Priority:** 🔴 High

### ✅ Task 3: Add statistics with citations to all major content pages
- **Problem:** According to the Princeton GEO study, adding statistics boosts AI citation visibility by +37–40%. Current pages have no statistics with source citations.
- **Fix:** For each major condition/procedure page, add 3–5 cited statistics. Examples:
  - IVF page: "According to the HFEA, IVF success rates for women under 35 are approximately 40% per cycle (HFEA, 2023)"
  - PCOS page: "PCOS affects 10–15% of women of reproductive age worldwide (WHO, 2023)"
  - Azoospermia page: "Azoospermia (zero sperm count) is found in approximately 1% of all men and in 10–15% of infertile men (Jarvi et al., 2020)"
- **Sources to cite:** HFEA, WHO, SART, ASRM, published PubMed studies, ESHRE guidelines
- **Priority:** 🔴 High

### ✅ Task 4: Restructure H2/H3 headings to match query patterns
- **Problem:** Current page headings are branded/emotional (e.g., "Redefining the approach to Male Infertility"). AI systems match headings to user queries. A heading "What is Azoospermia?" gets cited for the query "what is azoospermia" — an emotional headline doesn't.
- **Fix:** For each treatment page, include headings that mirror how patients search:
  - H2: "What is [condition]?"
  - H2: "What causes [condition]?"
  - H2: "How is [condition] diagnosed?"
  - H2: "What are the treatment options for [condition] in Pakistan?"
  - H2: "What is the success rate of [treatment] in Pakistan?"
  - H2: "How much does [treatment] cost in Lahore?"
  - H2: "Frequently Asked Questions"
- The emotional hero content can stay — add these structured sections in the body content below.
- **Priority:** 🔴 High

### ✅ Task 5: Add "Last Updated" dates to all content pages
- **Problem:** AI systems heavily weight freshness. Undated content loses to dated content.
- **Fix:** Add to `seo.php` or each page template:
  ```php
  $lastUpdated = "March 2026"; // Update per page, or use filemtime()
  ```
  Display visibly near the top of content:
  ```html
  <p class="text-sm text-slate-400">Last reviewed: <?= $lastUpdated ?> by Dr. Adnan Jabbar</p>
  ```
- **Priority:** 🟡 Medium

### ✅ Task 6: Add visible author attribution to every page
- **Problem:** AI systems boost content with expert attribution (+25–30% citation boost). Dr. Adnan's name and credentials should appear on every treatment page, not just the about page.
- **Fix:** Add an "About the Author" or "Medically Reviewed By" block near the top or bottom of each treatment/condition page:
  ```html
  <div class="author-attribution">
    <img src="/assets/images/dr-adnan.jpg" alt="Dr. Adnan Jabbar">
    <div>
      <strong>Dr. Adnan Jabbar</strong>
      <span>Fertility Consultant & Clinical Embryologist | MBBS, MCPS, MRCOG Candidate</span>
      <a href="/about/">View Full Profile →</a>
    </div>
  </div>
  ```
- **Priority:** 🔴 High

---

## FAQ CONTENT (Critical for AI Overviews)

### ✅ Task 7: Create FAQ sections on all major treatment pages
- **Problem:** FAQ content is the #1 format cited in Google AI Overviews for medical queries. None of the treatment pages have FAQ sections.
- **Pages requiring FAQs (priority order):**
  1. `/art-procedures/ivf.php`
  2. `/art-procedures/icsi.php`
  3. `/female-infertility/pcos.php`
  4. `/male-infertility/azoospermia.php`
  5. `/female-infertility/endometriosis.php`
  6. `/art-procedures/pgt.php`
  7. All remaining treatment/condition pages
- **Template per FAQ item (40–60 words per answer):**
  ```
  Q: How many IVF cycles does it take to get pregnant?
  A: Most patients who achieve pregnancy do so within 2–3 IVF cycles.
     Statistics show that cumulative success rates after 3 cycles can
     reach 60–70% for women under 40. Dr. Adnan designs personalized
     protocols to maximize success in each attempt.
  ```
- **Priority:** 🔴 High

---

## COMPARISON CONTENT (AI Citation Goldmine)

### ✅ Task 8: Create "IVF vs ICSI" comparison page
- **URL:** `/blog/ivf-vs-icsi-which-is-right-for-me`
- **Target queries:** "IVF vs ICSI difference", "when is ICSI needed instead of IVF"
- **Why:** Comparison articles make up ~33% of all AI citation content. This query has clear AI Overview potential.
- **Format:** Comparison table + Q&A blocks. Both must be structured for extraction.
- **Priority:** 🟡 Medium

### ✅ Task 9: Create "IVF vs IUI" comparison page
- **URL:** `/blog/ivf-vs-iui-which-treatment-is-right`
- **Target queries:** "IVF or IUI which is better", "difference between IUI and IVF Pakistan"
- **Priority:** 🟡 Medium

### ✅ Task 10: Create "IVF Success Rates in Pakistan" data page
- **URL:** `/blog/ivf-success-rates-pakistan`
- **Why:** Original data pages are cited 3–5x more than opinion content. If Dr. Adnan can publish his own clinic's success rate data (anonymized aggregate), this becomes uniquely citable.
- **Format:** Tables by age bracket, by diagnosis, by protocol. Cite each data point source.
- **Priority:** 🔴 High (very high AI citation potential — unique data)

---

## THIRD-PARTY PRESENCE

### ✅ Task 11: Build presence on external platforms AI systems cite
- **Problem:** Brands are 6.5x more likely to be cited via third-party sources than their own domain. The site needs external presence.
- **Actions:**
  - **Wikipedia:** Check if Dr. Adnan Jabbar has a Wikipedia entry. If not, consider notable publications that could establish one.
  - **Healthgrades / similar:** Get listed on international doctor directories
  - **YouTube:** Create a YouTube channel with explainer videos (AI Overviews frequently cite YouTube)
  - **Quora:** Answer questions about IVF in Pakistan with depth and credentials
  - **Reddit:** Engage authentically in r/IVF and relevant communities
  - **Pakistan medical directories:** PMDC listing, health directories
  - **PubMed:** Any published research by Dr. Adnan should be linked from the site
- **Priority:** 🟡 Medium (ongoing)

---

## AI VISIBILITY MONITORING

### ✅ Task 12: Set up monthly AI visibility tracking
- **Process:** Once per month, test the top 20 queries across Google, ChatGPT, and Perplexity:
  1. Open an incognito window
  2. Search each query in Google — screenshot any AI Overview that appears
  3. Ask ChatGPT: "What are the best IVF specialists in Pakistan?" and related queries
  4. Ask Perplexity the same queries — note citations
  5. Log in a tracking spreadsheet: Date | Query | Platform | IVF Experts cited? | Who cited?
- **Free tools:** Manual checking is sufficient to start. Consider Peec AI or Otterly once budget allows.
- **Key queries to track:**
  - "best IVF specialist in Lahore"
  - "IVF cost in Pakistan"
  - "azoospermia treatment Pakistan"
  - "PCOS treatment for pregnancy Pakistan"
  - "IVF vs ICSI difference"
  - "low AMH treatment in Pakistan"
- **Priority:** 🟡 Medium (set up monthly cadence)

### ✅ Task 13: Track referral traffic from AI sources in GA4
- **Setup:** In GA4, create a custom segment for sessions with source matching: `perplexity.ai`, `chat.openai.com`, `bard.google.com`, `bing.com/chat`
- These referrals are small today but growing. Tracking from now gives a baseline.
- **Priority:** 🟢 Low (setup once)

---

## AI SEO Checklist (Per Page)

For every major treatment page, verify:
- [ ] Clear definition in first paragraph (40–60 words)
- [ ] H2 headings match query patterns ("What is X?", "How to treat X")
- [ ] 3+ cited statistics with source and year
- [ ] Expert attribution visible (Dr. Adnan's name + credentials)
- [ ] FAQ section (5+ Q&A pairs)
- [ ] "Last updated" date visible
- [ ] FAQPage schema implemented
- [ ] Author credentialed in the content (not just in bio)
- [ ] Internal links to related treatment pages
- [ ] No keyword stuffing (hurts AI visibility by -10%)
