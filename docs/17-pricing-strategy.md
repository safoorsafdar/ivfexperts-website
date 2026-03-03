# Pricing Strategy — ivfexperts.pk
**Assessed:** 2026-03-03 | **Status:** 🔴 Not Started
**Goal:** Build pricing transparency that reduces patient bounce, filters high-intent leads, and positions IVF Experts as premium-but-trustworthy in the Pakistan fertility market

---

## Executive Summary

Cost is the first question every Pakistani fertility patient asks — and almost no fertility clinic in Pakistan answers it publicly. This silence causes patients to bounce and contact three other clinics before returning, or to never return at all. The strategic choice for IVF Experts is not to publish a full price list, but to provide enough pricing clarity that patients feel respected, informed, and confident reaching out.

The goal is not to be the cheapest clinic in Lahore. It is to be the most trusted. Pricing communication should reinforce the premium positioning: "You get what you pay for — here is exactly what you are getting." A dedicated pricing/investment page, FAQ schema answering "how much does IVF cost in Pakistan," and package-based framing will reduce decision friction while capturing high-value consultation inquiries.

The biggest SEO opportunity in this space: "IVF cost Pakistan" and related queries receive thousands of monthly searches with almost no quality Pakistani content ranking for them. A well-built pricing page will rank and convert.

**Conversion goal:** Every pricing touchpoint ends with "Get Your Personalised Cost Estimate" → WhatsApp.

---

## PRICING PHILOSOPHY

### ✅ Task 1: Define and document the pricing communication stance
- **Problem:** No pricing guidance on the site causes bounce. Publishing full itemized pricing undermines premium positioning and invites price-shopping.
- **Recommended stance:** Publish ranges with context. Never publish a flat fee without explaining what it includes. Frame cost as investment, not expense.
- **Key principles:**
  - Publish consultation fee — always (low barrier, removes first friction point)
  - Publish service ranges — yes (e.g. "IVF treatment at IVF Experts starts from PKR 450,000")
  - Publish itemized procedure list — no (creates sticker shock without context)
  - Always pair pricing with a "what's included" list to justify the range
  - End every pricing section with: "Your exact investment depends on your protocol. Get a personalised estimate."
- **The framing:** Cost per cycle vs. cost per baby. A PKR 500,000 IVF cycle that succeeds on the first attempt is cheaper than three PKR 350,000 cycles at a lower-quality clinic.
- **Priority:** 🔴 High

---

## PAKISTAN IVF PRICING LANDSCAPE

### Market Pricing Ranges (PKR, 2026)

| Service | Market Low | Market High | IVF Experts Positioning |
|---|---|---|---|
| Initial Consultation | PKR 3,000 | PKR 8,000 | PKR 5,000–6,000 |
| Teleconsultation | PKR 2,000 | PKR 5,000 | PKR 3,000–4,000 |
| Semen Analysis | PKR 5,000 | PKR 15,000 | PKR 8,000–12,000 |
| IUI | PKR 30,000 | PKR 80,000 | PKR 50,000–70,000 |
| IVF (basic cycle) | PKR 350,000 | PKR 600,000 | PKR 450,000–550,000 |
| IVF + ICSI | PKR 420,000 | PKR 700,000 | PKR 520,000–650,000 |
| IVF + ICSI + PGT | PKR 700,000 | PKR 1,300,000 | PKR 900,000–1,200,000 |
| Surgical Sperm Retrieval (TESA/PESA) | PKR 80,000 | PKR 180,000 | PKR 100,000–150,000 |
| Fertility Preservation (egg freezing) | PKR 180,000 | PKR 400,000 | PKR 250,000–350,000 |
| Annual Embryo/Egg Storage | PKR 30,000 | PKR 80,000 | PKR 40,000–60,000 |

**Note:** Medications (gonadotrophins, progesterone, trigger shots) are always itemized separately. These range from PKR 80,000–200,000 per cycle and must be communicated upfront to avoid patient shock.

### ✅ Task 2: Confirm actual pricing with Dr. Adnan and document internally
- **Steps:**
  1. Review the table above with Dr. Adnan — confirm ranges are accurate
  2. Document internal pricing in a private clinic reference (not published)
  3. Define the ranges to publish publicly (slightly softer than exact fees)
  4. Update annually or when fees change
- **Priority:** 🔴 High (must be done before pricing page is built)

---

## WHAT TO PUBLISH ON THE WEBSITE

### ✅ Task 3: Build a dedicated investment/pricing page
- **URL:** `/ivf-cost-pakistan` (SEO-optimised — targets "IVF cost Pakistan" keyword)
- **Alternate URL:** `/investment` (cleaner for brand, worse for SEO — use the SEO URL with canonical)
- **Target keyword:** "IVF cost Pakistan" (~1,000+ monthly searches), "how much does IVF cost in Pakistan", "IVF price Lahore"
- **Page structure:**

```
H1: IVF Cost in Pakistan — What to Expect at IVF Experts

[Intro: Why pricing varies, why we're transparent]

Section 1: Consultation Fees
→ In-person: PKR X,XXX | Teleconsultation: PKR X,XXX

Section 2: Treatment Investment Ranges
→ Table: Service | Starting From | What's Included | Who It's For

Section 3: What's NOT Included (medications, lab tests — explain why)

Section 4: Multi-cycle and package options

Section 5: FAQ (schema-marked)
→ "How much does IVF cost in Pakistan?"
→ "Why does IVF cost vary so much between clinics?"
→ "Does IVF Experts offer payment plans?"
→ "What does the IVF cost include?"

Section 6: Get Your Personalised Estimate [WhatsApp CTA]
```

- **Priority:** 🔴 High

### ✅ Task 4: Add pricing FAQ schema to the page
- **Why:** FAQ schema captures featured snippets for "IVF cost Pakistan" queries — appears directly in Google search results before the user even clicks
- **Questions to schema-mark:** See page structure above (Section 5)
- **Implementation:** Add `FAQPage` JSON-LD to the page — see `docs/03-schema-markup.md` for schema format
- **Priority:** 🔴 High

---

## PACKAGING STRATEGY

Three tiers simplify the patient's decision without overwhelming them with line items.

### ✅ Task 5: Define and launch 3 service tiers on the pricing page

**Tier 1 — Assessment Package**
- Initial consultation (in-person or teleconsultation)
- Semen analysis + hormonal blood workup
- Pelvic ultrasound (antral follicle count)
- Personalised treatment recommendation report
- **Who it's for:** Couples at the beginning of their fertility journey, seeking clarity before committing to treatment
- **Starting from:** PKR 20,000–30,000
- **Value prop:** "Know exactly what you're dealing with before spending a rupee on treatment."

**Tier 2 — IVF / ICSI Treatment Cycle**
- All monitoring appointments (follicle tracking ultrasounds)
- Egg retrieval procedure
- Sperm preparation + ICSI fertilisation (if indicated)
- Embryo culture in lab (up to Day 5/blastocyst)
- Fresh embryo transfer
- Post-transfer luteal support protocol (excluding medications)
- **Who it's for:** Couples ready to begin their first ART cycle
- **Starting from:** PKR 450,000 (IVF) / PKR 520,000 (with ICSI)
- **Medications:** PKR 80,000–180,000 additional (prescribed individually)
- **Value prop:** "Everything from stimulation to transfer, managed by Pakistan's only dual-trained fertility specialist and embryologist."

**Tier 3 — Complex Case Package**
- Everything in Tier 2
- Surgical sperm retrieval (TESA/PESA/microTESE) — for azoospermia cases
- PGT (preimplantation genetic testing) — for repeat failure or genetic risk cases
- Extended embryo culture and vitrification (freeze-all strategy)
- Frozen embryo transfer included
- **Who it's for:** Severe male factor (azoospermia), recurrent miscarriage, repeat IVF failures, advanced age, genetic concerns
- **Starting from:** PKR 900,000–1,200,000 depending on procedure combination
- **Value prop:** "For complex cases that have failed elsewhere. Dr. Adnan's embryology expertise makes the difference."

- **Priority:** 🔴 High

---

## FINANCING & AFFORDABILITY

### ✅ Task 6: Define and publish a payment plan option
- **Problem:** IVF at PKR 450,000–650,000 is beyond many families' immediate reach, even in urban Pakistan
- **Options to consider:**
  - **Installment plan:** 50% before egg retrieval, 50% before transfer — reduces financial barrier significantly
  - **Multi-cycle discount:** Commit to 2 cycles upfront and receive 10–15% reduction on the second cycle — increases commitment and improves patient outcomes (reduces pressure to succeed first time)
  - **Assessment-to-treatment pathway:** Patients who complete the Assessment Package (Tier 1) and proceed to treatment within 60 days receive the assessment fee credited toward their treatment
- **What to publish:** "Flexible payment options available — discuss during your consultation." Never publish installment amounts publicly (too variable per case)
- **Priority:** 🟡 Medium

### ✅ Task 7: Create a diaspora/international patient pricing note
- **Context:** Pakistani diaspora in UK, UAE, Canada, USA regularly fly back for IVF (cost in PKR is 5–10x cheaper than Western clinics even at premium rates)
- **What to add:** A section on the pricing page addressing international patients — how to book a teleconsultation first, what to expect during the visit, how long to plan for a full cycle (3–4 weeks in Pakistan)
- **Currency:** Quote in PKR only — let patients do their own conversion. Avoids pricing complexity.
- **Priority:** 🟡 Medium

---

## PRICING & SEO OPPORTUNITY

### ✅ Task 8: Target high-intent pricing keywords with the investment page
- **Keywords to target:**

| Keyword | Monthly Searches (est.) | Intent | Priority |
|---|---|---|---|
| IVF cost Pakistan | 1,000–2,000 | Decision | 🔴 High |
| how much does IVF cost in Pakistan | 500–1,000 | Decision | 🔴 High |
| IVF price Lahore | 300–600 | Decision | 🔴 High |
| IVF cost Karachi | 200–400 | Decision | 🟡 Medium |
| ICSI cost Pakistan | 300–500 | Decision | 🔴 High |
| PGT cost Pakistan | 100–200 | Decision | 🟡 Medium |
| IVF treatment packages Pakistan | 200–400 | Decision | 🟡 Medium |
| azoospermia treatment cost Pakistan | 100–200 | Decision | 🟡 Medium |

- **Steps:**
  1. Build the `/ivf-cost-pakistan` page (Task 3)
  2. Include target keywords naturally in H2s, body copy, FAQ questions
  3. Add FAQ schema (Task 4)
  4. Build 2–3 internal links to this page from treatment pages (e.g. from `/art-procedures/ivf.php`)
  5. Submit URL to Google Search Console after publishing
- **Priority:** 🔴 High

### ✅ Task 9: Add pricing signals to all treatment pages
- **Problem:** Treatment pages (IVF, ICSI, IUI, etc.) have no pricing information — patients have to hunt for it
- **Solution:** Add a subtle pricing teaser box to each treatment page:
  ```
  💰 Investment
  IVF treatment at IVF Experts starts from PKR 450,000 per cycle.
  Medications are prescribed individually (PKR 80,000–180,000 typically).
  [Get Your Personalised Cost Estimate →] (WhatsApp link)
  ```
- **Priority:** 🟡 Medium

---

## PRICING COMMUNICATION IN OTHER CHANNELS

### ✅ Task 10: Create WhatsApp pricing response template
- **Problem:** When patients ask "how much does IVF cost?" on WhatsApp, staff need a consistent, honest, non-overwhelming answer
- **Template:**
  ```
  Thank you for reaching out to IVF Experts.

  IVF treatment with Dr. Adnan starts from PKR 450,000 per cycle. This includes all monitoring appointments, egg retrieval, embryo culture, and transfer.

  Medications are prescribed individually based on your protocol — these typically range from PKR 80,000–180,000.

  The exact investment for your case depends on your diagnosis and treatment plan. To give you an accurate estimate, we recommend starting with a consultation (PKR 5,000 in-person / PKR 3,500 teleconsultation).

  Would you like to book a consultation with Dr. Adnan?
  ```
- **Priority:** 🔴 High

### ✅ Task 11: Add consultation fee to homepage and contact page
- **Problem:** The consultation fee is the lowest-barrier entry point — patients are far more likely to book a PKR 5,000 consultation than a PKR 500,000 treatment cycle. Yet it's not visible anywhere on the site.
- **Steps:**
  1. Add a "Start with a Consultation" section to the homepage with fee clearly stated
  2. Add fee to the contact page above the form
  3. Update CTA: "Book a PKR 5,000 Consultation" (removes price ambiguity, anchors value)
- **Priority:** 🔴 High

---

## WHAT NOT TO DO

### ✅ Task 12: Pricing page guardrails (implement as editorial policy)
- **Never** publish a full itemized price list (creates sticker shock, invites price shopping)
- **Never** position IVF Experts as the cheapest option — this directly undermines the premium brand
- **Never** quote medications in the base price — always separate and explain why
- **Never** promise a fixed total — IVF costs vary by protocol; under-promise, over-deliver
- **Never** use "cheap" or "affordable" in copy — use "accessible," "transparent," "investment"
- **Always** end pricing content with a personalised-estimate CTA (WhatsApp)
- **Priority:** 🔴 High (editorial policy, not a build task)

---

## CROSS-DOC REFERENCES

| Doc | Connection |
|---|---|
| `docs/brand-guidelines.md` | Copy tone for pricing page — warm but direct, never salesy |
| `docs/02-page-cro.md` | Pricing transparency flagged as high-priority CRO gap |
| `docs/03-schema-markup.md` | FAQ schema implementation for pricing page |
| `docs/01-seo-audit.md` | Pricing page as new high-value SEO page to submit |
| `docs/12-whatsapp-email-marketing.md` | WhatsApp pricing response templates |
| `docs/16-patient-journey-map.md` | Pricing is critical in the Consideration stage of the patient journey |
