# CLAUDE.md — IVF Experts Project Instructions

This file is auto-loaded by Claude Code at the start of every session.
It defines project context, coding rules, and which skills to invoke for which tasks.

---

## Project Overview

| Field | Value |
|---|---|
| **Site** | ivfexperts.pk |
| **Brand** | IVF Experts — Dr. Adnan Jabbar, Fertility Specialist & Clinical Embryologist |
| **Market** | Pakistan — Lahore, Karachi, Islamabad, Okara + nationwide teleconsultations |
| **Platform** | Custom PHP + Tailwind CSS v4 |
| **Analytics** | GTM (GTM-53FTJBJB) + GA4 (G-HQ78PRNQWM) |
| **Repo** | github.com/adnanjabbar/ivfexperts-website |

---

## Tech Stack

| Layer | Technology |
|---|---|
| Backend | PHP (no framework — custom includes system) |
| CSS | Tailwind CSS v4 — compiled via `@tailwindcss/cli` |
| JS | Vanilla JS (`assets/js/app.js`) — no framework |
| Icons | Font Awesome 6 Solid (CDN) |
| Fonts | **Inter** (body, 400/600/800) + **Outfit** (headings, via `--font-heading`) |
| Build | `npm run build` — compiles `input.css` → `style.css` |
| Watch | `npm run watch` — live CSS recompilation during development |

### Critical: Always run after CSS changes
```bash
npm run build
```
Tailwind v4 scans PHP files for class usage. If you add new Tailwind classes, they must be compiled into `assets/css/style.css` before they appear.

---

## Directory Structure

```
/
├── index.php                  # Homepage
├── about/                     # About Dr. Adnan page
├── contact/                   # Contact + form processor
├── blog/                      # Blog listing + posts
├── doctors/                   # Doctors profile page
├── art-procedures/            # IVF, ICSI, IUI, PGT, etc.
├── female-infertility/        # Female condition pages (PCOS, endo, etc.)
├── male-infertility/          # Male condition pages (azoospermia, etc.)
├── stemcell/                  # Stem cell therapy pages
├── portal/                    # Patient reporting portal (authenticated)
├── admin/                     # Clinic management panel (authenticated)
├── includes/
│   ├── header.php             # Global nav + GTM + schema
│   ├── footer.php             # Global footer
│   ├── seo.php                # Dynamic meta tags + OG
│   └── physician-schema.php   # Reusable JSON-LD schema
├── assets/
│   ├── css/input.css          # Tailwind source — EDIT THIS
│   ├── css/style.css          # Compiled output — DO NOT EDIT DIRECTLY
│   ├── js/app.js              # All vanilla JS
│   └── images/                # Site imagery
├── config/db.php              # Database connection
├── docs/                      # Strategy and reference docs (see below)
├── CLAUDE.md                  # This file
└── Agents.md                  # Skill routing guide
```

---

## CSS & Component System

All custom components are defined in `assets/css/input.css`. Use these before writing inline Tailwind utility chains.

### Available Component Classes

| Class | What It Is |
|---|---|
| `.card` | White card — `rounded-2xl`, border, hover lift + glow effect |
| `.btn-primary` | Filled teal CTA button — `rounded-xl`, hover shadow |
| `.btn-outline` | Bordered teal button — fills on hover |
| `.section-lg` | Section with `py-24` padding |
| `.section-md` | Section with `py-16` padding |
| `.bg-soft` | Light grey background (`#f9fafb`) |
| `.bg-tint` | Subtle gradient background (slate-50 → slate-100) |
| `.fade-in` | JS-triggered scroll animation (add `.appear` via IntersectionObserver) |

### CSS Custom Properties (Tailwind v4 theme)

```css
--color-primary:       #0f766e   /* teal-700 — primary brand color */
--color-primary-dark:  #0c5f59   /* teal-800 — hover/active states */
--color-primary-light: #e6f6f5   /* teal-50-ish — wash backgrounds */
--font-sans:           "Inter"   /* body text */
--font-heading:        "Outfit"  /* all h1–h6 headings */
```

### Typography Rules

| Role | Font | Tailwind |
|---|---|---|
| H1 Display | Outfit, 800 | `text-6xl font-extrabold` (desktop) / `text-4xl` (mobile) |
| H2 Section | Outfit, 700 | `text-5xl font-extrabold` |
| H3 Card | Outfit, 600 | `text-2xl font-semibold` |
| Body Large | Inter, 400 | `text-xl leading-relaxed` |
| Body | Inter, 400 | `text-base leading-relaxed` |
| Badge | Inter, 600 | `text-sm font-semibold` |

### Layout Constants

| Rule | Value | Tailwind |
|---|---|---|
| Max container width | 1280px | `max-w-7xl mx-auto px-6` |
| Section padding (desktop) | 96px | `py-24` |
| Section padding (mobile) | 64px | `py-16` |
| Card padding | 32px | `p-8` |
| Grid gap | 24–32px | `gap-6` / `gap-8` |

---

## PHP Page Pattern

Every public-facing page follows this exact include structure:

```php
<?php
$pageTitle = "Page Title | IVF Experts";
$metaDescription = "Meta description here.";
include("includes/header.php");
?>

<!-- PAGE CONTENT HERE -->

<?php include("includes/footer.php"); ?>
```

- `$pageTitle` and `$metaDescription` are consumed by `includes/seo.php` (which `header.php` includes)
- The `includes/header.php` also loads GTM, schema JSON-LD, CSS, and JS
- Relative paths in includes depend on directory depth — use root-relative paths (`/assets/css/style.css`)

---

## Key Docs Reference

Always consult the relevant doc before working on a task area. All docs are in `docs/`.

| Doc | Topic | When to Read It |
|---|---|---|
| `docs/brand-guidelines.md` | Brand voice, tone, copy rules, design tokens | Before writing any copy or building any UI |
| `docs/00-master-roadmap.md` | Sprint plan, overall priorities, progress | At the start of any new work session |
| `docs/01-seo-audit.md` | SEO fixes, meta, sitemap, images | Before touching SEO-related elements |
| `docs/02-page-cro.md` | Conversion optimisation tasks | Before modifying any public page |
| `docs/03-schema-markup.md` | JSON-LD schema rules | Before adding/editing schema |
| `docs/04-free-tool-strategy.md` | Calculator / tool pages | Before building any free tool |
| `docs/05-ai-seo.md` | AI search optimisation | Before writing authority content |
| `docs/06-site-architecture.md` | URL structure, page hierarchy | Before adding new pages or routes |
| `docs/07-content-strategy.md` | Blog topics, content calendar | Before writing blog posts |
| `docs/08-content-architecture.md` | Internal linking, pillar pages | Before editing content structure |
| `docs/09-local-seo.md` | Local SEO, Google Business, NAP | Before working on location signals |
| `docs/10-analytics-tracking.md` | GTM events, GA4 setup | Before adding any tracking code |
| `docs/11-link-building.md` | Outreach, backlinks | For off-page SEO work |
| `docs/12-whatsapp-email-marketing.md` | WhatsApp Business, email sequences | Before working on messaging or CTAs |

---

## What NOT to Do

- **Never edit `assets/css/style.css` directly** — it is compiled output and will be overwritten
- **Never add raw hex colors** — use CSS custom properties or Tailwind tokens from `docs/brand-guidelines.md`
- **Never introduce a new font** — only Inter (body) and Outfit (headings) are loaded
- **Never use jQuery** — vanilla JS only
- **Never use PHP frameworks or Composer packages** without explicit discussion
- **Never write patient data to localStorage or sessionStorage** — admin panel handles PII; treat it with care
- **Never add inline `<style>` blocks** to PHP files — all CSS belongs in `input.css`
- **Never commit `style.css`** unless you ran `npm run build` first

---

## Git & Commits

- Branch: `main` is the primary and production branch
- Commit messages: imperative, concise, lowercase (`fix zero-date bug on patients view`)
- Always run `npm run build` before committing if you changed any CSS or added Tailwind classes
- See `Agents.md` for the skill to use when committing and creating PRs

---

## Skill Routing (Quick Reference)

> Full detail in `Agents.md`. This is the fast-lookup version.

| Task | Invoke This Skill First |
|---|---|
| Build any UI component or page | `frontend-design:frontend-design` |
| Any new feature or bug fix | `superpowers:brainstorming` → then implement |
| Write or rewrite marketing copy | `copywriting` |
| Edit existing copy | `copy-editing` |
| Write a blog post | `technical-blog-writing` |
| SEO audit or fixes | `seo-audit` |
| Schema markup | `schema-markup` |
| Analytics / GTM / GA4 | `analytics-tracking` |
| CRO on any public page | `page-cro` |
| Free tool (calculator, quiz) | `free-tool-strategy` |
| Content planning | `content-strategy` |
| Multi-step implementation | `superpowers:writing-plans` → `superpowers:executing-plans` |
| Debugging any bug | `superpowers:systematic-debugging` |
| Committing + pushing + PR | `commit-commands:commit-push-pr` |
