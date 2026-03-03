# Agents.md — Skill Routing & Workflow Guide
## IVF Experts — ivfexperts.pk

This document defines which skills to invoke for every class of task on this project,
what docs to read first, and how different workflows chain together.

> **Quick nav:** [UI & Frontend](#ui--frontend) · [Copy & Content](#copy--content) · [SEO](#seo) · [Analytics](#analytics) · [CRO](#cro) · [Development Workflow](#development-workflow) · [Marketing](#marketing) · [Debugging](#debugging)

---

## Ground Rules

1. **Always read `docs/brand-guidelines.md` before writing copy or building UI.**
   Every color, font, sentence, and component decision is documented there.

2. **Always read `docs/00-master-roadmap.md` at the start of a new session.**
   It tells you what sprint you're in and what the next priority is.

3. **Invoke the brainstorming skill before building anything that isn't a trivial fix.**
   It prevents wasted effort and surfaces edge cases before code is written.

4. **The skill list below is authoritative.** If a task fits a skill, use it — do not skip it.

---

## UI & Frontend

### Building a new page or section

**When:** Adding any new public page, redesigning a section, or building a UI component.

**Workflow:**
1. Read `docs/brand-guidelines.md` — design tokens, component rules, copy voice
2. Invoke `superpowers:brainstorming` — understand structure and intent before writing HTML
3. Invoke `frontend-design:frontend-design` — generate the UI with full design quality
4. Run `npm run build` — compile Tailwind after adding any new utility classes
5. Invoke `superpowers:verification-before-completion` — verify output before claiming done

**Key constraints to enforce:**
- All CSS goes in `assets/css/input.css` — never inline `<style>` tags
- Use `.card`, `.btn-primary`, `.btn-outline`, `.section-lg` before writing custom chains
- Font: **Outfit** for headings (`h1`–`h6`), **Inter** for body text
- Primary color: `var(--color-primary)` (`#0f766e`) — never raw hex in HTML
- Respect the PHP include pattern — every page must start with `$pageTitle`, `$metaDescription`, `include("includes/header.php")`
- Max container: `max-w-7xl mx-auto px-6`

**Docs to read:**
- `docs/brand-guidelines.md` — full design system reference
- `docs/02-page-cro.md` — if the page has conversion goals

---

### Editing an existing page

**When:** Modifying layout, redesigning a section, fixing visual bugs.

**Workflow:**
1. Read the file before editing — understand the existing structure
2. Read `docs/brand-guidelines.md` — don't deviate from design tokens
3. Use `Edit` tool for targeted changes — do not rewrite full files unless asked
4. Run `npm run build` after any CSS changes
5. Invoke `superpowers:verification-before-completion` before declaring complete

---

### Admin panel UI

**When:** Building or editing pages in `admin/`.

- Admin pages use the same Tailwind CSS but follow a denser, utility-focused layout
- Admin is internal-only — apply `.card`, `.btn-primary` but prioritise clarity over aesthetics
- All admin pages are authenticated — never expose admin routes publicly
- Invoke `frontend-design:frontend-design` only if building a new admin module from scratch

---

## Copy & Content

### Writing new marketing copy (pages, hero, CTAs)

**Skill:** `copywriting`

**Read first:**
- `docs/brand-guidelines.md` — Section 2 (voice/tone) and Section 3 (copy by context)
- `docs/02-page-cro.md` — conversion intent for the specific page

**Rules enforced by brand guidelines:**
- Lead with the patient, not the clinic
- Short sentences for emotion, longer for explanation
- No passive voice
- Grade 8 reading level (Hemingway App)
- One idea per sentence — no comma-stacking

---

### Editing or improving existing copy

**Skill:** `copy-editing`

**Read first:**
- `docs/brand-guidelines.md` — words to use/avoid table, sentence structure rules

---

### Writing a blog post

**Skill:** `technical-blog-writing`

**Read first:**
- `docs/07-content-strategy.md` — topic priorities, content calendar
- `docs/08-content-architecture.md` — internal linking requirements
- `docs/brand-guidelines.md` — Section 3 (blog copy rules: Grade 8, "you/your", clear next step)

**Blog post structure (enforced):**
1. Hook — state the question the post answers
2. Context — why this matters to a Pakistani fertility patient
3. Explanation — evidence-based, jargon-free
4. Practical takeaway — what the reader can do or understand now
5. CTA — one clear next step (book consultation or related article link)

---

### Writing WhatsApp or email sequences

**Skill:** `email-sequence`

**Read first:**
- `docs/12-whatsapp-email-marketing.md` — channel strategy, sequence structure
- `docs/brand-guidelines.md` — voice rules (warm, direct, never pushy)

---

## SEO

### SEO audit or technical fixes

**Skill:** `seo-audit`

**Read first:**
- `docs/01-seo-audit.md` — full audit findings, prioritised task list

**Key areas this project needs work on:**
- Sitemap validation (`sitemap.php`)
- Meta/OG image tags
- H1/heading hierarchy on treatment pages
- Image alt text (see brand-guidelines.md — alt text rules)

---

### Schema markup (JSON-LD)

**Skill:** `schema-markup`

**Read first:**
- `docs/03-schema-markup.md` — schema tasks, what's already implemented
- `includes/physician-schema.php` — reusable schema block
- `includes/header.php` — where MedicalBusiness + Physician schema lives

**Key schemas this site uses:**
- `MedicalBusiness` — in header.php globally
- `Physician` — Dr. Adnan Jabbar, in header.php globally
- `FAQPage` — to be added on treatment pages (see 03-schema-markup.md)
- `Article` — for blog posts
- `BreadcrumbList` — needs fixing on interior pages

---

### AI SEO / answer engine optimisation

**Skill:** `ai-seo`

**Read first:**
- `docs/05-ai-seo.md` — authority signal tasks, content extractability checklist

**Focus areas:** Clear definitions of medical terms, FAQ sections, authority bylines, structured data.

---

### Local SEO

**Read first:**
- `docs/09-local-seo.md` — Google Business tasks, NAP consistency, city landing page plan

No dedicated skill — use `seo-audit` as the closest match and reference the local SEO doc.

---

### Content architecture / internal linking

**Read first:**
- `docs/08-content-architecture.md` — pillar page plan, cross-link matrix
- `docs/06-site-architecture.md` — URL structure, canonical rules

---

### Programmatic SEO (at scale)

**Skill:** `programmatic-seo`

**When:** Building template-driven pages at scale (e.g. city landing pages: `/ivf-lahore/`, `/ivf-karachi/`).

**Read first:**
- `docs/06-site-architecture.md` — approved URL patterns
- `docs/09-local-seo.md` — city page strategy

---

## Analytics

### GTM / GA4 setup or new event tracking

**Skill:** `analytics-tracking`

**Read first:**
- `docs/10-analytics-tracking.md` — GTM audit checklist, conversion events to implement

**IDs to never change:**
- GTM: `GTM-53FTJBJB`
- GA4: `G-HQ78PRNQWM`

**Key events this site needs:**
- WhatsApp button click (all pages)
- Consultation form submission
- Phone number click
- Scroll depth on treatment pages

---

## CRO

### Optimising a public page for conversions

**Skill:** `page-cro`

**Read first:**
- `docs/02-page-cro.md` — page-level CRO task list
- `docs/brand-guidelines.md` — CTA copy rules (no "Click Here", no "Submit")

---

### Optimising a form (contact, consultation request)

**Skill:** `form-cro`

**Read first:**
- `docs/02-page-cro.md` — form-specific tasks
- `contact/index.php` and `contact/process.php` — existing form implementation

---

### Building or optimising a popup / lead capture

**Skill:** `popup-cro`

**Read first:**
- `docs/12-whatsapp-email-marketing.md` — email capture strategy

---

### A/B testing

**Skill:** `ab-test-setup`

**Read first:**
- `docs/10-analytics-tracking.md` — analytics must be in place before A/B testing
- `docs/02-page-cro.md` — what to test

---

## Free Tools

### Building a calculator, quiz, or interactive tool

**Skill:** `free-tool-strategy`

**Read first:**
- `docs/04-free-tool-strategy.md` — tool ideas, priority order, build specs

**First tool target:** Fertility calculator (see doc for spec)

**Build workflow:**
1. `free-tool-strategy` — validate the tool concept and mechanics
2. `superpowers:brainstorming` — design the UI and logic
3. `frontend-design:frontend-design` — build the frontend
4. Vanilla JS only — no libraries for simple calculators
5. `analytics-tracking` — instrument tool completions as GA4 events

---

## Marketing

### Marketing ideas and growth strategy

**Skill:** `marketing-ideas`

**Read first:** `docs/00-master-roadmap.md` — what's already planned

---

### Paid ads (Google, Meta)

**Skill:** `paid-ads`

**Read first:**
- `docs/brand-guidelines.md` — voice/tone rules apply to ad copy too
- CTA copy rules: verb + emotional benefit, never "Learn More" alone

---

### Ad creative (copy variations, headlines)

**Skill:** `ad-creative`

**Read first:** `docs/brand-guidelines.md` — approved words, banned words

---

### Social media content

**Skill:** `social-content`

**Read first:** `docs/brand-guidelines.md` — social tone: human, relatable, occasionally personal

---

### Competitor comparison pages

**Skill:** `competitor-alternatives`

**Read first:** `docs/06-site-architecture.md` — URL structure for new pages

---

### Launch strategy (new feature or page)

**Skill:** `launch-strategy`

**Read first:** `docs/00-master-roadmap.md` — sprint context

---

## Development Workflow

### Starting any non-trivial task

**Workflow:**
1. Read `docs/00-master-roadmap.md` — confirm priority alignment
2. Invoke `superpowers:brainstorming` — explore intent before touching code
3. Invoke `superpowers:writing-plans` — write an implementation plan for multi-step tasks
4. Invoke `superpowers:using-git-worktrees` — create isolated workspace before implementing

---

### Implementing a plan

**Skill:** `superpowers:executing-plans` (single session)
**Skill:** `superpowers:subagent-driven-development` (parallel independent tasks)
**Skill:** `superpowers:dispatching-parallel-agents` (2+ truly independent workstreams)

---

### Implementing a feature or fix (TDD)

**Skill:** `superpowers:test-driven-development`

Use when writing PHP logic for admin or portal — not required for HTML/CSS-only changes.

---

### Debugging a bug or unexpected behaviour

**Skill:** `superpowers:systematic-debugging`

**Always invoke this before proposing a fix.** Do not guess at root causes.

---

### Verifying work before claiming it's done

**Skill:** `superpowers:verification-before-completion`

Run this before every "done" declaration. It requires actual verification — not assumption.

---

### Requesting a code review

**Skill:** `superpowers:requesting-code-review`

Invoke after completing a significant feature or implementation step.

---

### Receiving code review feedback

**Skill:** `superpowers:receiving-code-review`

Invoke before implementing review suggestions — verify they are technically sound first.

---

### Committing, pushing, opening a PR

**Skill:** `commit-commands:commit-push-pr`

Run `npm run build` first if any CSS was changed. Never commit `style.css` without building.

---

### Finishing a development branch

**Skill:** `superpowers:finishing-a-development-branch`

Invoke when implementation is complete and all checks pass.

---

## Skill Quick-Reference Table

| Task | Skill |
|---|---|
| Build a UI page or component | `frontend-design:frontend-design` |
| Creative or feature planning | `superpowers:brainstorming` |
| Write marketing copy | `copywriting` |
| Edit existing copy | `copy-editing` |
| Write a blog post | `technical-blog-writing` |
| SEO audit / technical SEO | `seo-audit` |
| Schema / JSON-LD | `schema-markup` |
| AI SEO / answer engine | `ai-seo` |
| Programmatic / city pages | `programmatic-seo` |
| GTM / GA4 / event tracking | `analytics-tracking` |
| Page CRO | `page-cro` |
| Form CRO | `form-cro` |
| Popup / lead capture | `popup-cro` |
| A/B test planning | `ab-test-setup` |
| Free tool (calculator, quiz) | `free-tool-strategy` |
| Paid ads | `paid-ads` |
| Ad creative copy | `ad-creative` |
| Social media content | `social-content` |
| Competitor comparison pages | `competitor-alternatives` |
| Email / WhatsApp sequences | `email-sequence` |
| Launch planning | `launch-strategy` |
| Marketing ideas | `marketing-ideas` |
| Multi-step plan writing | `superpowers:writing-plans` |
| Executing a plan | `superpowers:executing-plans` |
| Parallel independent tasks | `superpowers:dispatching-parallel-agents` |
| TDD for PHP logic | `superpowers:test-driven-development` |
| Debugging | `superpowers:systematic-debugging` |
| Verify before "done" | `superpowers:verification-before-completion` |
| Request code review | `superpowers:requesting-code-review` |
| Receive code review | `superpowers:receiving-code-review` |
| Commit + push + PR | `commit-commands:commit-push-pr` |
| Finish branch | `superpowers:finishing-a-development-branch` |
| Git worktree isolation | `superpowers:using-git-worktrees` |
