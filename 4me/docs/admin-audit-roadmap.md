# 4me Admin — Full Audit & Foundational Roadmap
> Generated: 2026-03-05 · Auditor: Antigravity AI (senior-fullstack + ui-ux-pro-max)
> 
> This document is the single source of truth for all planned fixes, improvements, and architectural changes to the `4me/` admin section of the IVF Experts EMR.

---

## Executive Summary

The 4me admin is a working PHP/MySQL clinic management system with a good design foundation (Tailwind, Alpine.js, Inter font). However it carries significant technical debt across security, database reliability, code quality, and UX consistency. This roadmap addresses all discovered issues in a safe, phased sequence — **later phases depend on earlier ones.**

---

## 🔴 CRITICAL ISSUES (Fix Immediately)

| # | Issue | File(s) | Risk |
|---|-------|---------|------|
| C1 | DB credentials hardcoded in-repo tracked file | `config/db.php` | 🔴 CRITICAL — exposed on GitHub |
| C2 | 15+ migration/setup scripts publicly accessible via URL | `migrate_*.php`, `master_migration.php` | 🔴 DROP TABLE risk |
| C3 | Debug endpoints exposed on production | `debug_edit_meds.php`, `schema_check.php`, `diag_patients.php`, `diag_receipts.php` | 🔴 DB schema leak |
| C4 | No CSRF protection on any form | All `*.php` POST handlers | 🔴 Cross-site request forgery |
| C5 | SQL injection via string interpolation | Several files (`$rx_id` in raw query strings) | 🔴 Injection risk |

---

## ⚠️ HIGH PRIORITY ISSUES

| # | Issue | File(s) |
|---|-------|---------|
| H1 | Tailwind CSS & Alpine.js loaded from CDN (unreliable, slow, flash of unstyled content) | `includes/header.php` |
| H2 | `old_rx.php` (34KB) — dead legacy prescription writer still in repo | `old_rx.php` |
| H3 | `pas.php` (63 bytes) — pointless stub file | `pas.php` |
| H4 | `ghost_recovery.php` (12KB) — disaster-recovery script accessible publicly | `ghost_recovery.php` |
| H5 | `sync_hospitals.php` — one-time sync script living in web root | `sync_hospitals.php` |
| H6 | `patients/` subfolder with 6 unknown files — not audited yet | `patients/` |
| H7 | No rate limiting on API endpoints (`api_search_*.php`) | All `api_search_*.php` |
| H8 | `prescription_items` table — `medicine_name`, `frequency`, `duration` columns have `NOT NULL DEFAULT ''` — empty strings treated as valid data | DB schema |
| H9 | `medication_id` FK removed but column not cleaned — orphaned nullable int | `prescription_items` |
| H10 | No indexes on foreign-key columns in several tables | DB schema |

---

## 🟡 MEDIUM PRIORITY ISSUES

| # | Issue | File(s) |
|---|-------|---------|
| M1 | `patients_view.php` at 92KB — monolithic file, needs component extraction | `patients_view.php` |
| M2 | `prescriptions_add.php` at 48KB — split into partial includes needed | `prescriptions_add.php` |
| M3 | `lab_results_add.php` at 24KB — complex logic with duplicated form code | `lab_results_add.php` |
| M4 | No global `parseMM()` helper — duplicated across print files | `prescriptions_print.php`, `semen_analyses_print.php` |
| M5 | `esc()` function defined per-file in some scripts (not always via auth.php) | Several files |
| M6 | Flash messages stored in `$_SESSION` but never cleaned up on redirect failure | `includes/auth.php`, `includes/header.php` |
| M7 | `financials.php` at 26KB — complex with inline JS and no pagination on large datasets | `financials.php` |
| M8 | Sidebar `active_map` not updated with `prescriptions_edit.php` | `includes/sidebar.php` |
| M9 | Double logout links (header + sidebar) | `includes/header.php`, `includes/sidebar.php` |
| M10 | Blog add/edit uses a full rich text editor with no sanitization on output | `blog_add.php`, `blog.php` |

---

## 🟢 IMPROVEMENT OPPORTUNITIES

| # | Opportunity | Details |
|---|-------------|---------|
| I1 | DB: Add `updated_at` timestamp columns to main tables | Audit trail, patient safety |
| I2 | DB: Add `created_by` admin_id FK to clinical records | Traceability |
| I3 | UX: Unified empty-state component for all list pages | Consistency |
| I4 | UX: Keyboard shortcut system (⌘K search, ⌘N new patient) | Power-user productivity |
| I5 | UX: Breadcrumb nav component in header | Orientation |
| I6 | UX: Loading skeletons instead of blank pages during slow DB queries | Perceived performance |
| I7 | UX: Standardize all table designs — currently each page has different styling | Consistency |
| I8 | Code: Central `helpers.php` include (esc, flash, parseMM, formatDate, etc.) | DRY |
| I9 | Code: Wrap all DB writes in try/catch with proper rollback | Reliability |
| I10 | Perf: Add DB query result caching for medications list, ICD-10, lab tests | Speed |

---

## Phase Plan

---

### 🔴 Phase 1 — Security Lockdown (Priority: Do Today)
**Goal:** Eliminate all attack surface exposure before anything else.

#### 1.1 — Move DB credentials out of Git

- [ ] Create `.env` file (not tracked): `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS`  
- [ ] Update `config/db.php` to use `$_ENV` or `getenv()` exclusively  
- [ ] Add `.env` to `.gitignore`  
- [ ] Rotate database password on Hostinger after removing from history  

#### 1.2 — Delete / Protect temporary scripts

Remove from web root (delete or move to `_archive/` outside web root):

```
delete: debug_edit_meds.php
delete: diag_patients.php
delete: diag_receipts.php
delete: schema_check.php
delete: ghost_recovery.php
delete: sync_hospitals.php
delete: pas.php
delete: old_rx.php (legacy prescription writer)
```

Protect migration scripts with `.htaccess` deny rule (keep for future but block web access):

```apache
# In 4me/.htaccess — add:
<FilesMatch "^(migrate_|master_migration|cleanup_).*\.php$">
    Order deny,allow
    Deny from all
</FilesMatch>
```

#### 1.3 — Add CSRF protection

- [ ] Generate CSRF token on each page load, store in `$_SESSION['csrf_token']`  
- [ ] Add hidden `<input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">` to all forms  
- [ ] Validate token at top of every POST handler  
- [ ] Add `csrf_check()` helper to `includes/auth.php`  

#### 1.4 — Fix SQL injection risks

- [ ] Audit all `$conn->query("... $variable ...")` string interpolations  
- [ ] Convert to prepared statements with `bind_param()`  
- [ ] Key files: `prescriptions_edit.php` line 86 DELETE, `patients_view.php` tab queries  

#### 1.5 — Rate-limit API search endpoints

- [ ] Add simple rate limiter to `api_search_*.php` using session-based request counting  
- [ ] Return 429 if more than 60 requests/minute from same session  

---

### 🟡 Phase 2 — Code Cleanup & Architecture (Week 1)
**Goal:** Remove dead code, create shared utilities, make the codebase maintainable.

#### 2.1 — Create `includes/helpers.php`

Centralize shared utilities:
```php
// includes/helpers.php
function esc(string $s): string { ... }
function parseMM(string $mm): float { ... }
function formatDate(string $d, string $fmt = 'd M Y'): string { ... }
function csrf_token(): string { ... }
function csrf_check(): void { ... }
function paginate(int $total, int $page, int $per_page): array { ... }
```

Remove duplicate `esc()` definitions from individual files.

#### 2.2 — Extract partial includes for large files

- [ ] `patients_view.php` (92KB) → split into `patients/tab_prescriptions.php`, `patients/tab_labs.php`, `patients/tab_semen.php`, `patients/tab_ultrasounds.php`, `patients/tab_financials.php`  
- [ ] `prescriptions_add.php` (48KB) → extract medication JS, ICD JS, lab JS into `assets/js/rx_form.js`  
- [ ] Each extracted partial uses shared `helpers.php`  

#### 2.3 — Fix sidebar active map

```php
// In sidebar.php, add to $active_map:
'prescriptions.php' => ['prescriptions_add.php', 'prescriptions_edit.php', 'prescriptions_print.php'],
```

#### 2.4 — Remove double logout

- [ ] Remove logout `<a>` from `includes/header.php` top bar (keep power-off icon trigger)  
- [ ] Keep only the sidebar footer logout  

#### 2.5 — Fix `migrate_*.php` long-term

- [ ] Move all migration scripts to a `_migrations/` folder outside web root  
- [ ] Create `README_migrations.md` documenting which scripts were run and when  

---

### 🟠 Phase 3 — Database Hardening (Week 1–2)

**Goal:** Clean up schema, add missing indexes, enforce data integrity.

#### 3.1 — Fix `prescription_items` table

```sql
-- Fix empty-string defaults to NULL
ALTER TABLE prescription_items 
  MODIFY medicine_name VARCHAR(255) NOT NULL,
  MODIFY frequency VARCHAR(100) NULL DEFAULT NULL,
  MODIFY duration VARCHAR(100) NULL DEFAULT NULL,
  MODIFY med_type VARCHAR(100) NULL DEFAULT NULL;

-- Remove orphaned medication_id column (FK was already dropped)
ALTER TABLE prescription_items DROP COLUMN medication_id;

-- Add index for common lookups
CREATE INDEX idx_rx_items_prescription ON prescription_items(prescription_id);
```

#### 3.2 — Add missing indexes

```sql
-- Common query patterns that need indexes:
CREATE INDEX idx_prescriptions_patient ON prescriptions(patient_id);
CREATE INDEX idx_prescriptions_date ON prescriptions(created_at);
CREATE INDEX idx_semen_patient ON semen_analyses(patient_id);
CREATE INDEX idx_lab_results_patient ON lab_results(patient_id);
CREATE INDEX idx_lab_results_date ON lab_results(created_at);
CREATE INDEX idx_patients_mrn ON patients(mr_number);
CREATE INDEX idx_patients_name ON patients(first_name, last_name);
CREATE INDEX idx_leads_status ON leads(status, created_at);
CREATE INDEX idx_financials_date ON receipts(receipt_date);
```

#### 3.3 — Add audit columns to core tables

```sql
-- Add created_by + updated_at to clinical tables:
ALTER TABLE prescriptions ADD COLUMN created_by INT NULL, ADD COLUMN updated_at TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP;
ALTER TABLE semen_analyses ADD COLUMN updated_at TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP;
ALTER TABLE lab_results ADD COLUMN updated_at TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP;
ALTER TABLE patients ADD COLUMN updated_at TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP;
```

#### 3.4 — Create migration runner

- [ ] Build `4me/run_migration.php` (auth-protected, admin-only) as a one-click migration executor  
- [ ] Each migration is a numbered `.sql` file in `_migrations/`  
- [ ] Tracks which migrations have run in a `schema_migrations` table  

---

### 🔵 Phase 4 — UX & UI Consistency (Week 2–3)

**Goal:** Make the admin feel premium, fast and consistent everywhere using the ui-ux-pro-max principle.

#### 4.1 — Unified Data Table Component

Every list page (`patients.php`, `medications.php`, `leads.php`, etc.) should use the same table structure:

```html
<!-- Standard table layout: header (title + count + actions), table, pagination -->
<div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
  <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
    <div>
      <h2 class="font-bold text-gray-800">...</h2>
      <p class="text-xs text-gray-400">N records</p>
    </div>
    <a href="...add.php" class="btn-primary">+ Add New</a>
  </div>
  <table class="w-full">...</table>
  <div class="px-6 py-3 border-t border-gray-100 flex items-center justify-between">
    <!-- Pagination -->
  </div>
</div>
```

#### 4.2 — Global CSS utility classes in `header.php`

Add standard button and badge classes so pages don't re-define them:

```css
.btn-primary   { @apply inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-semibold text-white bg-teal-600 hover:bg-teal-700 transition-all; }
.btn-secondary { @apply inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-semibold text-gray-700 bg-gray-100 hover:bg-gray-200 transition-all; }
.btn-danger    { @apply inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-semibold text-white bg-rose-600 hover:bg-rose-700 transition-all; }
.badge-green   { @apply inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700; }
.badge-red     { @apply inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-rose-50 text-rose-700; }
.badge-blue    { @apply inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-sky-50 text-sky-700; }
.card          { @apply bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden; }
```

#### 4.3 — Empty states

Add a standard empty state to all list pages:

```html
<div class="py-20 text-center">
  <i class="fa-solid fa-database text-4xl text-gray-200 mb-4"></i>
  <h3 class="font-bold text-gray-400">No records found</h3>
  <p class="text-sm text-gray-300 mt-1">Get started by adding your first record.</p>
</div>
```

#### 4.4 — Keyboard shortcuts

Add to `includes/header.php`:
```javascript
document.addEventListener('keydown', (e) => {
  if ((e.metaKey || e.ctrlKey) && e.key === 'k') {
    e.preventDefault(); window.location = 'patients.php';
  }
});
```

#### 4.5 — Dashboard improvements (`index.php`)

- [ ] Add real-time "Today's appointments" count  
- [ ] Add "Pending lab results" quick badge  
- [ ] Add "Low inventory" alert count  
- [ ] Graph: monthly patient registrations (last 6 months)  
- [ ] Quick-action cards: New Patient, New Prescription, New Semen Analysis  

#### 4.6 — Mobile responsiveness audit

- [ ] Test and fix sidebar collapse on mobile  
- [ ] Make data tables horizontally scrollable on small screens  
- [ ] Fix `patients_view.php` tabs on mobile  

---

### 🟣 Phase 5 — Feature Completions (Week 3–4)

**Goal:** Finish incomplete features and fill functionality gaps.

#### 5.1 — Prescription print: Phase 6 (Admin Margin UI)

From the existing print roadmap, implement the margin configuration UI in `settings.php` or `hospitals_edit.php`:
- [ ] Top / Right / Bottom / Left margin inputs (mm)  
- [ ] Preview of how margins affect print layout  
- [ ] Per-hospital margin settings (already in DB as `margin_*` columns)  

#### 5.2 — Semen Analysis: fix file upload & data input

- [ ] Audit `semen_analyses_add.php` — verify file upload handler writes to correct `uploads/` path  
- [ ] Fix any missing `$_FILES` handling  
- [ ] Fix data not saving (likely same Alpine submit loop bug as prescriptions)  

#### 5.3 — Lab Results: spouse attribution display

- [ ] Ensure `record_for` correctly shown on lab results list  
- [ ] Badge "Patient" / "Spouse" on each result row  

#### 5.4 — Medications library improvements

- [ ] Add pagination (currently loads all medications, slow with large lists)  
- [ ] Add import from CSV  
- [ ] Add "mark as inactive" instead of delete  

#### 5.5 — Ultrasound templates

- [ ] `ultrasound_templates.php` — verify templates are being used in `ultrasounds_add.php`  
- [ ] Add template preview  

---

### 🟤 Phase 6 — Performance & Infrastructure (Month 2)

**Goal:** Make the system production-grade.

#### 6.1 — Compile Tailwind CSS

- [ ] Run `npm run build` to compile Tailwind into a single `assets/css/admin.css`
- [ ] Replace CDN `<script src="https://cdn.tailwindcss.com">` with local compiled file
- [ ] Add Tailwind to `.gitignore` in `node_modules` (already done), keep `assets/css/admin.css` tracked

#### 6.2 — Move Alpine.js to local build

- [ ] Download Alpine.js v3 minified into `assets/js/alpine.min.js`
- [ ] Replace CDN include with local file

#### 6.3 — Add DB query result caching

- [ ] Cache medications list in `$_SESSION` for 5 minutes (avoids re-fetching on every page load)  
- [ ] Cache ICD-10 autocomplete results in `localStorage` on client side  
- [ ] Cache lab tests directory in session cache  

#### 6.4 — Error logging

- [ ] Add global `set_exception_handler()` in `includes/auth.php`  
- [ ] Log all DB errors to `uploads/logs/error_YYYY-MM-DD.log`  
- [ ] Alert admin@ivfexperts.pk on critical errors (DB failure, etc.)  

#### 6.5 — HTTP security headers

Add to `4me/.htaccess`:

```apache
Header always set X-Content-Type-Options "nosniff"
Header always set X-Frame-Options "DENY"
Header always set X-XSS-Protection "1; mode=block"
Header always set Referrer-Policy "strict-origin-when-cross-origin"
```

---

## Files to Delete Immediately

```bash
# Execute these deletions — these files have NO business being in the web root:
rm 4me/debug_edit_meds.php       # debug script — exposes DB schema
rm 4me/diag_patients.php         # diagnostic — no longer needed
rm 4me/diag_receipts.php         # diagnostic — no longer needed
rm 4me/pas.php                   # 63-byte stub — does nothing
rm 4me/old_rx.php                # 34KB legacy prescription writer — replaced
rm 4me/ghost_recovery.php        # disaster recovery — archive offline
rm 4me/sync_hospitals.php        # one-time sync — done
```

## Migration Scripts to Archive (not delete — may be reference)

Move these out of web root to `_migrations/` (a folder above web root or blocked by .htaccess):

```
master_migration.php
migrate_definitions.php
migrate_hospitals_table.php
migrate_icd10_cpt.php
migrate_icd10_resume.php
migrate_lab_tests_library.php
migrate_leads_staff.php
migrate_medications_v2.php
migrate_patients_table.php
migrate_patients_v2.php
migrate_prescription_tests.php
migrate_procedures_financials.php
migrate_sa_files.php
migrate_spouse_enhancements.php
```

---

## DB Schema Summary (Current Known State)

| Table | Issues Found |
|-------|-------------|
| `prescription_items` | Empty-string defaults, orphaned `medication_id`, missing index |
| `prescriptions` | Missing `updated_at`, missing index on `patient_id` |
| `semen_analyses` | Missing `updated_at`, missing index on `patient_id` |
| `patients` | Missing `updated_at`, no compound index on name |
| `lab_results` | Missing `updated_at`, missing index on `patient_id` |
| `medications` | No `is_active` flag (can't soft-delete) |
| `leads` | Missing index on `(status, created_at)` |
| All tables | No `created_by` admin FK for audit trail |

---

## Phase Execution Order

```
Phase 1 (Security)   → TODAY  — Never skip this
Phase 2 (Cleanup)    → Week 1 — Unblocks Phase 4 UI work
Phase 3 (DB)         → Week 1 — Unblocks Phase 5 features
Phase 4 (UX/UI)      → Week 2 — Requires Phase 2 helpers
Phase 5 (Features)   → Week 3 — Requires Phase 3 DB fixes
Phase 6 (Infra)      → Month 2 — Long-term stability investment
```

---

*This roadmap is a living document. Mark items `[x]` as completed. Add new issues as discovered.*
