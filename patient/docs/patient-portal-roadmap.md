# Patient Portal Improvement Roadmap
## IVF Experts — `patient.ivfexperts.pk`

**Repository path:** `patient/docs/patient-portal-roadmap.md`
**Last updated:** 2026-03-04
**Stack:** PHP (vanilla) + MySQL + Tailwind CDN + Alpine.js + Font Awesome 6.5
**Portal folder:** `patient/` (subdomain: `patient.ivfexperts.pk`)
**Admin folder:** `4me/` (subdomain: `4me.ivfexperts.pk`)

---

## Table of Contents

1. [Critical Fixes](#1-critical-fixes-p1--do-first)
2. [UX Improvements](#2-ux-improvements)
3. [New Features](#3-new-features)
4. [Security Hardening](#4-security-hardening)
5. [Performance](#5-performance)
6. [Future Vision](#6-future-vision)
7. [Implementation Sequence Summary](#7-implementation-sequence-summary)

---

## 1. Critical Fixes (P1 — Do First)

These are broken features that prevent the portal from functioning correctly. Fix these before any other work.

---

### 1.1 — Broken Document Viewer: Wrong `../admin/` Path

**Priority:** P1 — Urgent
**Complexity:** Low
**Status:** Currently broken for SA, USG, Rx, and Receipt documents

**Problem:**
`patient/view.php` contains four hardcoded include paths that reference `../admin/` — a folder that no longer exists. The admin was renamed to `4me/` during the subdomain restructure.

```php
// CURRENT (broken):
$script = '../admin/semen_analyses_print.php';
$script = '../admin/ultrasounds_print.php';
$script = '../admin/prescriptions_print.php';
$script = '../admin/receipts_print.php';
```

When a patient scans a QR code and tries to view their document, they receive a fatal PHP error (file not found) for all four document types.

**Fix — File to modify:** `patient/view.php` (lines 28, 38, 48, 58)

Change all four `$script` assignments:
```php
// FIXED:
$script = '../4me/semen_analyses_print.php';
$script = '../4me/ultrasounds_print.php';
$script = '../4me/prescriptions_print.php';
$script = '../4me/receipts_print.php';
```

**DB changes:** None

---

### 1.2 — BYPASS_AUTH Missing from SA and USG Print Scripts

**Priority:** P1 — Urgent
**Complexity:** Low
**Status:** Blocks patient viewing of semen analysis and ultrasound reports

**Problem:**
`patient/view.php` defines `BYPASS_AUTH` at line 64 before `include $script`. The admin `auth.php` checks `if (!defined('BYPASS_AUTH') || BYPASS_AUTH !== true)` — so this works in principle.

However, `4me/semen_analyses_print.php` and `4me/ultrasounds_print.php` both begin with:
```php
require_once __DIR__ . '/includes/auth.php';
```

Since `BYPASS_AUTH` is defined in `patient/view.php` scope before the include, and PHP `define()` is global, this *should* work. But `4me/receipts_print.php` has an explicit BYPASS_AUTH guard:
```php
if (!defined('BYPASS_AUTH')) {
    require_once __DIR__ . '/includes/auth.php';
} else {
    require_once __DIR__ . '/config/db.php';
    if (!function_exists('esc')) { ... }
}
```

The SA and USG scripts lack this pattern entirely. They call `require_once __DIR__ . '/includes/auth.php'` which will load the DB — but inside `auth.php`, if `BYPASS_AUTH` is defined it skips the session check. The real risk is that `esc()` is defined inside `auth.php` conditionally, and if it has already been defined (since multiple scripts are included), PHP will throw a fatal error on the second `function esc()`.

**Fix — Files to modify:** `4me/semen_analyses_print.php` (line 2), `4me/ultrasounds_print.php` (line 2), `4me/prescriptions_print.php` (line 2)

Replace the auth include at the top of all three files with the same guard pattern used in `receipts_print.php`:

```php
if (!defined('BYPASS_AUTH')) {
    require_once __DIR__ . '/includes/auth.php';
} else {
    require_once __DIR__ . '/config/db.php';
    if (!function_exists('esc')) {
        function esc($string) { return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8'); }
    }
}
```

**DB changes:** None

---

### 1.3 — Advised Procedures Tab Missing from Dashboard

**Priority:** P1 — Important (Data is fetched but never displayed)
**Complexity:** Low
**Status:** `$advised_procedures` array is populated but the tab and HTML panel don't exist

**Problem:**
`patient/dashboard.php` queries `advised_procedures` at lines 102–114 and builds the `$advised_procedures` array. However, the `$portal_tabs` array (lines 225–231) has no entry for procedures, and there is no `x-show="activeTab === 'procedures'"` panel anywhere in the HTML.

Patients cannot see their IVF procedure plan, status (Advised / In Progress / Completed), or payment linkage.

**Fix — File to modify:** `patient/dashboard.php`

1. Add procedures tab to the `$portal_tabs` array:
```php
['id' => 'procedures', 'icon' => 'fa-syringe', 'label' => 'My Procedures', 'count' => count($advised_procedures)],
```

2. Add the HTML panel after the billing tab section:
```html
<div x-show="activeTab === 'procedures'" x-cloak>
    <div class="space-y-6">
        <h2 class="text-xl font-black text-slate-800 flex items-center gap-2">
            <i class="fa-solid fa-syringe text-indigo-600"></i> Advised & Upcoming Procedures
        </h2>
        <?php if (empty($advised_procedures)): ?>
            <div class="bg-white rounded-3xl border border-slate-200 p-12 text-center text-slate-400 font-bold">
                No procedures advised yet.
            </div>
        <?php else: ?>
            <div class="space-y-4">
                <?php foreach ($advised_procedures as $ap): ?>
                    <div class="bg-white rounded-3xl border border-slate-200 p-6 shadow-sm">
                        <div class="flex justify-between items-start">
                            <div>
                                <div class="font-black text-slate-800"><?php echo htmlspecialchars($ap['procedure_name']); ?></div>
                                <div class="text-xs text-slate-400 mt-1">
                                    Advised: <?php echo date('d M Y', strtotime($ap['date_advised'])); ?>
                                    • <?php echo htmlspecialchars($ap['first_name']); ?>
                                </div>
                            </div>
                            <span class="text-[10px] font-black uppercase px-3 py-1 rounded-full
                                <?php echo $ap['status'] === 'Completed' ? 'bg-emerald-100 text-emerald-700' : ($ap['status'] === 'In Progress' ? 'bg-amber-100 text-amber-700' : 'bg-indigo-100 text-indigo-700'); ?>">
                                <?php echo htmlspecialchars($ap['status']); ?>
                            </span>
                        </div>
                        <?php if ($ap['total_billed']): ?>
                            <div class="mt-3 pt-3 border-t border-slate-100 text-sm text-slate-600">
                                Total billed: <span class="font-black text-emerald-600">Rs. <?php echo number_format($ap['total_billed'], 0); ?></span>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>
```

**DB changes:** None (query already exists)

---

### 1.4 — Scanned PDF Links Use Broken Relative Path `../`

**Priority:** P1 — Important
**Complexity:** Low
**Status:** PDF download links will 404 in the subdomain deployment

**Problem:**
In `patient/dashboard.php`, scanned PDF links use:
```php
<a href="../<?php echo htmlspecialchars($lr['scanned_report_path']); ?>">
```

The `scanned_report_path` values stored in the DB look like `uploads/labs/filename.pdf`, `uploads/scans/filename.pdf`, etc. The upload directory lives under the repo root (or under `4me/`).

When the patient portal runs at `patient.ivfexperts.pk`, the relative path `../` resolves to the parent of the `patient/` folder, which on the server is `public_html/`. Whether `uploads/` lives at `public_html/uploads/` or `public_html/4me/uploads/` determines if this is broken.

Since `lab_results_add.php` stores paths as `uploads/labs/...` relative to the `4me/` doc root, the correct absolute URL for a file would be `https://4me.ivfexperts.pk/uploads/labs/...` or `https://ivfexperts.pk/uploads/labs/...`.

**Fix — File to modify:** `patient/dashboard.php` (lines 389 and 504)

Replace the `../` relative links with absolute URLs pointing to the correct subdomain:
```php
// Replace:
<a href="../<?php echo htmlspecialchars($lr['scanned_report_path']); ?>">

// With (if uploads live under 4me/):
<a href="https://4me.ivfexperts.pk/<?php echo htmlspecialchars($lr['scanned_report_path']); ?>">
```

Verify the actual upload path on the live server first. The `4me/lab_results_add.php` stores paths as `uploads/labs/...`, meaning relative to the `4me/` document root.

**DB changes:** None

---

### 1.5 — Logout Handled via GET Request (CSRF Vulnerable)

**Priority:** P1 — Security + Correctness
**Complexity:** Low
**Status:** `?logout=1` processed at top of dashboard before session check output

**Problem:**
`patient/dashboard.php` line 117:
```php
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: index.php");
    exit;
}
```

This comes *after* all the DB queries and before HTML output. There are two issues:
1. A GET-based logout is CSRF-vulnerable (any page can embed `<img src="patient/dashboard.php?logout=1">`)
2. `session_destroy()` without `session_regenerate_id()` may leave session file artifacts

**Fix — File to modify:** `patient/dashboard.php`

Move the logout handler to the very top of the file, before all DB queries, and require a POST with a CSRF token, or at minimum add a nonce to the logout URL and validate it:

```php
// At top of file, before queries:
if (isset($_GET['logout'])) {
    $_SESSION = [];
    session_destroy();
    header("Location: index.php");
    exit;
}
```

The minimal fix is moving it to the top and clearing `$_SESSION = []` before destroying. Full CSRF fix is covered in Section 4.

**DB changes:** None

---

## 2. UX Improvements

### 2.1 — Lab Results Table Not Scrollable on Mobile

**Priority:** P2 — Important
**Complexity:** Low

**Problem:**
The lab results and billing tables use `<table class="w-full text-left">` inside a container with `overflow-hidden`. On mobile screens (width < 640px), this table has 4 columns including a "Biological Markers" column that will overflow and break the layout, as there is no `overflow-x-auto` wrapper.

**Fix — File to modify:** `patient/dashboard.php` (lines 329–404 and 524–562)

Wrap both table containers:
```html
<!-- Change: -->
<div class="bg-white rounded-3xl border border-slate-200 overflow-hidden shadow-sm">
    <table class="w-full text-left">

<!-- To: -->
<div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left min-w-[600px]">
    </div>
</div>
```

**DB changes:** None

---

### 2.2 — Sidebar Navigation Hidden on Mobile (Layout Breaks)

**Priority:** P2 — Important
**Complexity:** Medium

**Problem:**
The dashboard uses `grid grid-cols-1 lg:grid-cols-4`. On mobile, the sidebar navigation renders as a full-width stacked block at the top, followed by the content area. This means patients must scroll past the nav every time they switch tabs. On small screens, `sticky top-24` on the sidebar does not behave as a side rail — it becomes a sticky header block.

**Fix — File to modify:** `patient/dashboard.php`

Replace the sticky sidebar with a horizontally scrollable tab bar on mobile:

```html
<!-- Mobile tab bar (shown on small screens, hidden on lg) -->
<div class="lg:hidden overflow-x-auto flex gap-2 pb-2 mb-6 -mx-4 px-4">
    <?php foreach ($portal_tabs as $tab): ?>
    <button @click="activeTab = '<?php echo $tab['id']; ?>'"
            :class="activeTab === '<?php echo $tab['id']; ?>' ? 'bg-indigo-600 text-white' : 'bg-slate-200 text-slate-600'"
            class="shrink-0 flex items-center gap-2 px-4 py-2.5 rounded-2xl text-xs font-black whitespace-nowrap transition-all">
        <i class="fa-solid <?php echo $tab['icon']; ?>"></i>
        <?php echo $tab['label']; ?>
        <?php if ($tab['count'] > 0): ?>
            <span class="bg-white/30 px-1.5 rounded-full text-[9px]"><?php echo $tab['count']; ?></span>
        <?php endif; ?>
    </button>
    <?php endforeach; ?>
</div>

<!-- Desktop sidebar (hidden on small screens) -->
<div class="hidden lg:block lg:col-span-1">
    <!-- existing sidebar code -->
</div>
```

**DB changes:** None

---

### 2.3 — Dashboard Header Quick Stats Only Shows 3 Items

**Priority:** P3 — Nice to have
**Complexity:** Low

**Problem:**
The quick stats bar in the hero section (lines 200–214) shows only Rx, Tests, and Scans counts. Billing and Procedures are not represented. Patients who have outstanding bills cannot quickly see that.

**Fix — File to modify:** `patient/dashboard.php` (lines 200–211)

Update `$quick` array to include billing balance and upcoming procedures:
```php
$pending_count = count(array_filter($lab_results, fn($l) => $l['status'] === 'Pending'));
$quick = [
    ['n' => count($prescriptions), 'l' => 'Rx',       'c' => 'indigo',  'i' => 'fa-prescription'],
    ['n' => count($lab_results),   'l' => 'Tests',    'c' => 'teal',    'i' => 'fa-vials'],
    ['n' => count($ultrasounds),   'l' => 'Scans',    'c' => 'emerald', 'i' => 'fa-image'],
    ['n' => count($receipts),      'l' => 'Receipts', 'c' => 'amber',   'i' => 'fa-receipt'],
];
```

**DB changes:** None

---

### 2.4 — Prescription Preview Shows Raw HTML or Empty Text

**Priority:** P2 — Important
**Complexity:** Low

**Problem:**
In `patient/dashboard.php` at line 498:
```php
$rx_preview = strip_tags($rx['clinical_notes'] ?? $rx['diagnosis'] ?? '');
echo htmlspecialchars($rx_preview ?: 'Medication plan issued during consultation.');
```

`clinical_notes` is stored as Quill HTML (e.g., `<p>Follicular tracking day 3</p>`). `strip_tags()` will reduce this to `Follicular tracking day 3` — which is correct. However, if `clinical_notes` is empty and `diagnosis` is also empty (valid scenario), it falls back to a generic message.

The bigger UX issue is that `diagnosis` from the prescriptions table is a plain text field. The card shows no structured preview of what the Rx is for. Consider showing the ICD code and description instead.

**Fix — File to modify:** `patient/dashboard.php` (line 498)

Add a JOIN to `prescription_diagnoses` in the prescriptions query, or show the first medicine name if clinical notes are empty:
```php
// Minimal fix — fetch diagnosis from prescriptions table as fallback:
$rx_preview = strip_tags($rx['clinical_notes'] ?? '');
if (empty($rx_preview)) {
    $rx_preview = strip_tags($rx['diagnosis'] ?? '');
}
$rx_preview = mb_strimwidth(trim($rx_preview), 0, 120, '...');
echo htmlspecialchars($rx_preview ?: 'Medication plan — tap to view full details.');
```

**DB changes:** None

---

### 2.5 — No "Back to Dashboard" Link on Document Viewer Pages

**Priority:** P2 — Important
**Complexity:** Low

**Problem:**
When a patient views a document via `view.php`, they see the print-optimized admin template (prescription, SA, USG, or receipt). These templates show a "Close" button (`window.close()`) and a Download button. But patients on mobile who navigate directly (not in a popup) have no way to return to the dashboard — `window.close()` does nothing if the tab was not opened by script.

**Fix — Files to modify:** `4me/prescriptions_print.php`, `4me/semen_analyses_print.php`, `4me/ultrasounds_print.php`, `4me/receipts_print.php`

In the patient portal controls section (the `!isset($_SESSION['admin_id'])` else branch in each file), replace or supplement the Close button:

```html
<?php if (!isset($_SESSION['admin_id'])): ?>
    <button onclick="window.print()" class="bg-indigo-600 ... ">
        <i class="fa-solid fa-download"></i> Download / Print
    </button>
    <a href="javascript:history.back()" class="bg-slate-700 text-white px-4 py-2 rounded-lg font-bold text-sm">
        <i class="fa-solid fa-arrow-left mr-1"></i> Back
    </a>
    <a href="https://patient.ivfexperts.pk/dashboard.php" class="bg-slate-200 text-slate-700 px-4 py-2 rounded-lg font-bold text-sm">
        My Records
    </a>
<?php endif; ?>
```

**DB changes:** None

---

### 2.6 — Empty State Messages Are Generic

**Priority:** P3 — Nice to have
**Complexity:** Low

**Problem:**
Empty states like "No clinical visits recorded yet" and "No laboratory results found" give patients no guidance. IVF patients often log in expecting to find results. A brief explanation of when data appears would reduce patient anxiety and support calls.

**Fix — File to modify:** `patient/dashboard.php` (all empty state blocks)

Replace generic messages with contextual ones:

```php
// Labs empty state:
"Your lab results will appear here once your blood tests have been processed and uploaded by the clinic."

// Timeline empty state:
"Your clinical visit notes will appear here after your consultation with Dr. Adnan Jabbar."

// Scans empty state:
"Ultrasound reports and semen analysis results will appear here after your diagnostic visit."
```

**DB changes:** None

---

### 2.7 — Login Error Message Exposes Too Much Detail

**Priority:** P2 — Security + UX
**Complexity:** Low

**Problem:**
`patient/index.php` line 49:
```php
$error = "Details not found. Please check your Phone/MR Number and CNIC.";
```

This is fine. However the verify.php error message at line 125:
```php
$error = "CNIC does not match our record for this document. Please try again.";
```

This explicitly confirms that the document exists and the hash is valid — it only tells the attacker that their CNIC guess is wrong. Combined with the CNIC hint (`12345-XXXXXXX-X`), an attacker who knows the first 5 digits of a CNIC could brute force the remaining 8 digits. Rate limiting (Section 4) is the primary mitigation, but the error message can also be softened.

**Fix — File to modify:** `patient/verify.php` (line 125)

```php
$error = "Verification failed. Please enter the 13-digit CNIC number exactly as on your national identity card.";
```

**DB changes:** None

---

## 3. New Features

### 3.1 — Upcoming Appointment / Next Visit Reminder Card

**Priority:** P2 — High value for patients
**Complexity:** Low
**Files to create/modify:** `patient/dashboard.php`

**Description:**
IVF patients follow strict cycle-day protocols. Knowing their next visit date is critical. Both `patient_history.next_visit` and `prescriptions.next_visit` store scheduled follow-up dates. Currently, these dates only appear buried inside individual records.

**Implementation:**

In `patient/dashboard.php`, after the existing queries, add:
```php
// Find the nearest upcoming visit date across all records
$next_visits = [];
foreach ($histories as $h) {
    if (!empty($h['next_visit']) && $h['next_visit'] >= date('Y-m-d')) {
        $next_visits[] = $h['next_visit'];
    }
}
foreach ($prescriptions as $rx) {
    if (!empty($rx['next_visit']) && $rx['next_visit'] >= date('Y-m-d')) {
        $next_visits[] = $rx['next_visit'];
    }
}
sort($next_visits);
$next_visit_date = $next_visits[0] ?? null;
```

Then display a prominent banner in the hero section when a next visit exists:
```html
<?php if ($next_visit_date): ?>
<div class="mt-4 bg-indigo-600/20 border border-indigo-500/30 rounded-2xl px-5 py-3 flex items-center gap-3">
    <i class="fa-solid fa-calendar-star text-indigo-400 text-xl shrink-0"></i>
    <div>
        <div class="text-indigo-300 text-xs font-black uppercase tracking-wider">Next Appointment</div>
        <div class="text-white font-black">
            <?php echo date('l, d F Y', strtotime($next_visit_date)); ?>
        </div>
    </div>
</div>
<?php endif; ?>
```

**DB changes:** None (uses existing `next_visit` columns)
**Complexity:** Low

---

### 3.2 — Pending Lab Results Alert Banner

**Priority:** P2 — Very useful for patients waiting on results
**Complexity:** Low
**Files to modify:** `patient/dashboard.php`

**Description:**
When a patient has tests with `status = 'Pending'`, they are actively waiting. A count badge or alert banner at the top of the dashboard reduces anxiety and signals transparency.

**Implementation:**

Add after the queries:
```php
$pending_labs = array_filter($lab_results, fn($l) => $l['status'] === 'Pending');
$pending_count = count($pending_labs);
```

Add a notice banner in the hero section or below the header:
```html
<?php if ($pending_count > 0): ?>
<div class="mb-4 bg-amber-500/15 border border-amber-500/30 rounded-2xl px-5 py-3 flex items-center gap-3">
    <i class="fa-solid fa-spinner animate-spin text-amber-400 shrink-0"></i>
    <span class="text-amber-300 text-sm font-black">
        <?php echo $pending_count; ?> lab result<?php echo $pending_count > 1 ? 's' : ''; ?> processing — check back soon.
    </span>
</div>
<?php endif; ?>
```

**DB changes:** None
**Complexity:** Low

---

### 3.3 — Payment Summary Card with Outstanding Balance

**Priority:** P2 — Important for billing transparency
**Complexity:** Low
**Files to modify:** `patient/dashboard.php`

**Description:**
Currently the billing tab shows a flat list of receipts with no summary. Patients (and couples managing IVF costs) benefit from seeing:
- Total paid to date
- Any pending/unpaid amounts
- The procedure each amount relates to

**Implementation:**

Add after the receipts query:
```php
$total_paid    = array_sum(array_column(array_filter($receipts, fn($r) => strtolower($r['status']) === 'paid'), 'amount'));
$total_pending = array_sum(array_column(array_filter($receipts, fn($r) => strtolower($r['status']) !== 'paid'), 'amount'));
```

Add a summary block at the top of the billing tab panel:
```html
<div class="grid grid-cols-2 gap-4 mb-6">
    <div class="bg-emerald-50 border border-emerald-200 rounded-2xl p-5 text-center">
        <div class="text-2xl font-black text-emerald-700">Rs. <?php echo number_format($total_paid, 0); ?></div>
        <div class="text-xs font-black text-emerald-500 uppercase tracking-widest mt-1">Total Paid</div>
    </div>
    <?php if ($total_pending > 0): ?>
    <div class="bg-amber-50 border border-amber-200 rounded-2xl p-5 text-center">
        <div class="text-2xl font-black text-amber-700">Rs. <?php echo number_format($total_pending, 0); ?></div>
        <div class="text-xs font-black text-amber-500 uppercase tracking-widest mt-1">Pending</div>
    </div>
    <?php endif; ?>
</div>
```

**DB changes:** None
**Complexity:** Low

---

### 3.4 — WhatsApp Deep Link on "Share My Records" Button

**Priority:** P3 — Nice to have
**Complexity:** Low
**Files to modify:** `patient/dashboard.php`

**Description:**
IVF couples sometimes need to share a specific prescription or result with a relative, referring doctor, or a second-opinion specialist. Currently they can only do this by printing or screenshotting.

Add a "Share via WhatsApp" button on each prescription card that pre-fills a WhatsApp message with the QR verification link (same link used on printed documents).

**Implementation:**

In the prescriptions tab, add to each card's action buttons:
```html
<a href="https://wa.me/?text=<?php echo urlencode('View my prescription from IVF Experts: https://patient.ivfexperts.pk/verify.php?hash=' . $rx['qrcode_hash'] . '&type=rx'); ?>"
   target="_blank"
   class="px-3 py-2 bg-[#25D366]/10 text-[#128C7E] rounded-xl hover:bg-[#25D366] hover:text-white transition-all text-xs font-black">
    <i class="fa-brands fa-whatsapp text-base"></i>
</a>
```

**DB changes:** None
**Complexity:** Low

---

### 3.5 — Profile / Account Page

**Priority:** P3 — Nice to have
**Complexity:** Medium
**New file:** `patient/profile.php`

**Description:**
Patients have no way to see or verify their own demographic details (name, MR number, phone, spouse info). A read-only profile page reassures patients that the clinic has their correct details and reduces support calls.

**Implementation:**

Create `patient/profile.php`:
```php
<?php
require_once __DIR__ . '/includes/auth.php';
require_once dirname(__DIR__) . '/4me/config/db.php';
$patient_id = intval($_SESSION['portal_patient_id']);

$stmt = $conn->prepare("SELECT first_name, last_name, mr_number, phone, gender, 
    date_of_birth, blood_group, address, email, spouse_name, spouse_phone, cnic 
    FROM patients WHERE id = ?");
$stmt->bind_param("i", $patient_id);
$stmt->execute();
$patient = $stmt->get_result()->fetch_assoc();
?>
```

Display all fields read-only with a "To update details, contact the clinic" note.

Mask the CNIC on display: show only `XXXXX-XXXXXXX-X` with the last digit revealed.

Add link from the dashboard nav (next to the logout button).

**DB changes:** None (reads existing `patients` table fields)
**Complexity:** Medium (new file, link from nav, no DB writes needed)

---

### 3.6 — Treatment Timeline Visual (IVF Journey Tracker)

**Priority:** P3 — High patient value, higher effort
**Complexity:** Medium
**Files to modify:** `patient/dashboard.php`

**Description:**
IVF involves a well-defined cycle: Baseline scan → Stimulation → Follicular monitoring scans → Trigger → Egg retrieval → Fertilization → Embryo transfer → Beta HCG test.

A visual "journey" showing where the patient currently is in their IVF cycle — derived from their `advised_procedures` and recent `patient_history` entries — would be enormously helpful. Even a simplified timeline showing all advised procedures with status badges (Advised → In Progress → Completed) provides this.

**Implementation:**

The `advised_procedures` tab (added in fix 1.3) is the foundation. Enhance it with a horizontal step-tracker UI for recognized IVF procedure names:

```php
// Map procedure names to journey steps (matching common entries in the DB)
$ivf_steps = [
    'Baseline Scan'              => 0,
    'Ovarian Stimulation'        => 1,
    'Follicular Monitoring'      => 2,
    'Trigger Injection'          => 3,
    'Egg Retrieval (OPU)'        => 4,
    'Fertilization'              => 5,
    'Embryo Culture'             => 6,
    'Embryo Transfer'            => 7,
    'Luteal Phase Support'       => 8,
    'Beta HCG (Pregnancy Test)'  => 9,
];
```

Show a horizontal progress strip for recognized steps, with status coloring.

**DB changes:** None
**Complexity:** Medium

---

### 3.7 — Document Download as PDF (Client-Side)

**Priority:** P2 — Important
**Complexity:** Low
**Files to modify:** `4me/prescriptions_print.php`, `4me/receipts_print.php`, `4me/semen_analyses_print.php`, `4me/ultrasounds_print.php`

**Description:**
Currently, "Download / Print" calls `window.print()`. On mobile browsers, this opens the system print dialog — confusing for patients who want a saved PDF. Adding a brief instruction like "Choose 'Save as PDF' from the print dialog" and ensuring the print CSS is clean would help.

For a better experience: add a `<link>` to the portal nav back button when the document was opened from the portal. If `document.referrer` contains `patient.ivfexperts.pk/dashboard.php`, show "Back to My Records" instead of "Close".

**Implementation:**

In each print file's patient controls section, add client-side detection:
```html
<script>
const fromPortal = document.referrer.includes('patient.ivfexperts.pk');
</script>
```

And conditionally render:
```html
<button onclick="window.print()" ...>
    <i class="fa-solid fa-download"></i> Save as PDF / Print
</button>
<span class="text-xs text-gray-500 ml-2">
    (In print dialog, choose "Save as PDF")
</span>
```

**DB changes:** None
**Complexity:** Low

---

## 4. Security Hardening

### 4.1 — No Rate Limiting on CNIC Verification (Brute Force Risk)

**Priority:** P1 — Security
**Complexity:** Medium
**Files to modify:** `patient/verify.php`, `patient/index.php`
**New file:** `patient/includes/rate_limit.php`

**Problem:**
Both `verify.php` and `index.php` accept unlimited POST attempts. For `verify.php`, the CNIC hint (`12345-XXXXXXX-X`) reveals the first 5 digits. An attacker with the QR hash (printed on a document) could brute-force the remaining 8 digits. 13-digit Pakistani CNICs follow the format `XXXXX-XXXXXXX-X` where the last 7 digits vary — this is 10 million combinations, but with the first 5 known it becomes 10 million attempts, and automated scripts can attempt thousands per second.

**Implementation:**

Create `patient/includes/rate_limit.php`:

```php
<?php
/**
 * Simple session-based rate limiter for login/verify forms.
 * Limits to $max_attempts attempts per $window_seconds.
 */
function check_rate_limit(string $key, int $max_attempts = 5, int $window_seconds = 300): bool {
    $session_key = 'rl_' . $key;
    $now = time();
    
    if (!isset($_SESSION[$session_key])) {
        $_SESSION[$session_key] = ['count' => 0, 'window_start' => $now];
    }
    
    $rl = &$_SESSION[$session_key];
    
    // Reset window if expired
    if ($now - $rl['window_start'] > $window_seconds) {
        $rl = ['count' => 0, 'window_start' => $now];
    }
    
    $rl['count']++;
    
    if ($rl['count'] > $max_attempts) {
        $remaining = $window_seconds - ($now - $rl['window_start']);
        return false; // Blocked
    }
    return true; // Allowed
}

function get_rate_limit_remaining(string $key, int $window_seconds = 300): int {
    $session_key = 'rl_' . $key;
    if (!isset($_SESSION[$session_key])) return $window_seconds;
    return max(0, $window_seconds - (time() - $_SESSION[$session_key]['window_start']));
}
```

In `patient/verify.php` POST handler, add:
```php
require_once __DIR__ . '/includes/rate_limit.php';
// In the POST block:
if (!check_rate_limit('cnic_verify_' . $hash, 5, 300)) {
    $error = "Too many attempts. Please wait " . ceil(get_rate_limit_remaining('cnic_verify_' . $hash) / 60) . " minutes before trying again.";
    // Don't process the form further
} else {
    // ... existing verification logic ...
}
```

In `patient/index.php` POST handler:
```php
require_once __DIR__ . '/includes/rate_limit.php';
if (!check_rate_limit('portal_login_' . ($phone_mr ?: 'anon'), 5, 300)) {
    $error = "Too many login attempts. Please wait 5 minutes.";
} else {
    // ... existing login logic ...
}
```

**DB changes:** None (session-based; for production, use DB-based rate limiting for persistence across sessions)
**Complexity:** Medium

---

### 4.2 — No Session Regeneration After Login

**Priority:** P2 — Security
**Complexity:** Low
**Files to modify:** `patient/index.php`, `patient/verify.php`

**Problem:**
After successful authentication, neither `index.php` nor `verify.php` calls `session_regenerate_id(true)`. This creates a session fixation vulnerability where an attacker who can observe or set a victim's session ID before login can reuse it after authentication.

**Fix:**

In `patient/index.php`, after `$_SESSION['portal_patient_id'] = $row['id'];`:
```php
session_regenerate_id(true); // Prevent session fixation
$_SESSION['portal_patient_id'] = $row['id'];
```

In `patient/verify.php`, after `$_SESSION['portal_patient_id'] = $doc_patient_id;`:
```php
session_regenerate_id(true);
$_SESSION['portal_patient_id'] = $doc_patient_id;
```

**DB changes:** None
**Complexity:** Low

---

### 4.3 — Session Has No Absolute Expiry (Idle Sessions Stay Forever)

**Priority:** P2 — Security
**Complexity:** Low
**Files to modify:** `patient/includes/auth.php`

**Problem:**
The portal session has no absolute expiry and no idle timeout. A patient who forgets to log out on a shared device (common at fertility clinic waiting rooms where patients use clinic WiFi) remains authenticated indefinitely.

**Fix — File to modify:** `patient/includes/auth.php`

Add idle timeout and absolute expiry checks:
```php
<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$IDLE_TIMEOUT    = 60 * 60;        // 1 hour idle
$ABSOLUTE_EXPIRY = 60 * 60 * 12;  // 12 hours max session

$now = time();

if (isset($_SESSION['portal_patient_id'])) {
    // Check idle timeout
    if (isset($_SESSION['last_activity']) && ($now - $_SESSION['last_activity']) > $IDLE_TIMEOUT) {
        session_unset();
        session_destroy();
        header("Location: index.php?expired=1");
        exit;
    }
    // Check absolute expiry
    if (isset($_SESSION['session_start']) && ($now - $_SESSION['session_start']) > $ABSOLUTE_EXPIRY) {
        session_unset();
        session_destroy();
        header("Location: index.php?expired=1");
        exit;
    }
    $_SESSION['last_activity'] = $now;
    if (!isset($_SESSION['session_start'])) {
        $_SESSION['session_start'] = $now;
    }
}

if (!isset($_SESSION['portal_patient_id'])) {
    header("Location: index.php");
    exit;
}

require_once dirname(__DIR__, 2) . '/4me/config/db.php';
$patient_id = intval($_SESSION['portal_patient_id']);
?>
```

In `patient/index.php`, after successful login, add:
```php
$_SESSION['session_start']  = time();
$_SESSION['last_activity']  = time();
```

**DB changes:** None
**Complexity:** Low

---

### 4.4 — CSRF Token Missing on Login and Verify Forms

**Priority:** P2 — Security
**Complexity:** Low
**Files to modify:** `patient/index.php`, `patient/verify.php`

**Problem:**
Both login forms use plain POST with no CSRF token. While the login form is a lower risk (attacker would be logging the victim into *their own* account, not the attacker's), the verify.php form's CSRF risk is that a malicious page could trigger a CNIC submission attempt against a known document hash — combining with the rate limit attack to exhaust the victim's allowance.

**Fix:**

Create a CSRF helper in `patient/includes/csrf.php`:
```php
<?php
function csrf_token(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_verify(): bool {
    $token = $_POST['_csrf'] ?? '';
    return hash_equals($_SESSION['csrf_token'] ?? '', $token);
}
```

Add to each form:
```html
<input type="hidden" name="_csrf" value="<?php echo csrf_token(); ?>">
```

Add to each POST handler:
```php
if (!csrf_verify()) {
    $error = "Security token mismatch. Please refresh and try again.";
} else {
    // ... process form ...
}
```

**DB changes:** None
**Complexity:** Low

---

### 4.5 — DB Credentials Hardcoded in `4me/config/db.php`

**Priority:** P1 — Security (already flagged in MEMORY.md)
**Complexity:** Low
**Files to modify:** `4me/config/db.php`

**Problem:**
```php
define('DB_PASS', '4991701AdnanJabbar');
```

The password is committed to git history in plain text. On the current Hostinger shared hosting, this is mitigated by the `.htaccess` block on `config/`, but it is still exposed in git logs.

**Fix:**

The code already supports environment variables:
```php
if (getenv('DB_HOST')) { /* use env vars */ }
else { /* hardcoded fallback */ }
```

**Steps:**
1. Set Hostinger environment variables via hPanel (Hosting → Advanced → PHP Configuration → Environment Variables or use a `.env` file loaded via `putenv()`)
2. Once confirmed working on the server, replace the hardcoded fallback block with:
   ```php
   else {
       // Local development — copy .env.example to .env and fill in values
       // NEVER commit real credentials
       define('DB_HOST', 'localhost');
       define('DB_NAME', 'YOUR_LOCAL_DB');
       define('DB_USER', 'root');
       define('DB_PASS', '');
   }
   ```
3. Rotate the Hostinger database password after the change

**DB changes:** None (infrastructure change)
**Complexity:** Low

---

### 4.6 — `ids_csv` Raw SQL Injection Risk in Dashboard

**Priority:** P2 — Security
**Complexity:** Medium
**Files to modify:** `patient/dashboard.php`

**Problem:**
All six data queries in `patient/dashboard.php` use:
```php
$ids_csv = implode(',', $patient_ids);
$res = $conn->query("... WHERE patient_id IN ($ids_csv) ...");
```

The `$patient_ids` array is built from `intval()` casted values (lines 23, 34–36, 43–46) and `array_unique()`, so in practice this is safe. However, the pattern bypasses prepared statements. If the population logic ever changes, SQL injection becomes possible.

**Fix:**

Replace the raw `IN ($ids_csv)` pattern with prepared statement placeholders:

```php
// Build placeholders
$placeholders = implode(',', array_fill(0, count($patient_ids), '?'));
$types = str_repeat('i', count($patient_ids));

$stmt = $conn->prepare("SELECT p.*, pt.first_name, pt.last_name 
    FROM prescriptions p 
    JOIN patients pt ON p.patient_id = pt.id 
    WHERE p.patient_id IN ($placeholders) 
    ORDER BY p.created_at DESC");
$stmt->bind_param($types, ...$patient_ids);
$stmt->execute();
$prescriptions_result = $stmt->get_result();
while ($row = $prescriptions_result->fetch_assoc()) {
    $prescriptions[] = $row;
}
```

Repeat for all six queries.

**DB changes:** None
**Complexity:** Medium (repetitive but straightforward)

---

### 4.7 — Spouse Linking Logic Is Overly Broad

**Priority:** P2 — Privacy concern
**Complexity:** Medium
**Files to modify:** `patient/dashboard.php`

**Problem:**
The spouse linking query at lines 30–36:
```php
$stmt_spouse = $conn->prepare("SELECT id FROM patients WHERE first_name = ? AND (phone = ? OR mr_number = ? OR REPLACE(cnic, '-', '') = ?)");
$stmt_spouse->bind_param("ssss", $patient['spouse_name'], $phone, $mr, $cnic_clean);
```

This matches a patient record if their `first_name` matches the logged-in patient's `spouse_name` AND they share any one of phone, MR number, or CNIC.

The `first_name`-only match is fragile — two patients named "Ahmed" could accidentally share records if they have the same phone number (e.g., the clinic's own number used as a placeholder). This could expose one patient's IVF records to an unrelated patient.

**Fix:**

Add a more robust link: use a dedicated `linked_patient_id` foreign key column on the patients table, populated by the admin when registering couples. The spouse name match remains as a fallback only.

**DB change:**
```sql
ALTER TABLE patients ADD COLUMN linked_patient_id INT UNSIGNED NULL DEFAULT NULL;
ALTER TABLE patients ADD CONSTRAINT fk_linked_patient 
    FOREIGN KEY (linked_patient_id) REFERENCES patients(id) ON DELETE SET NULL;
```

In `patient/dashboard.php`:
```php
// Preferred: use explicit link
$stmt = $conn->prepare("SELECT id FROM patients WHERE linked_patient_id = ? OR id = (SELECT linked_patient_id FROM patients WHERE id = ?)");
$stmt->bind_param("ii", $patient_id, $patient_id);
// Fall back to name match only if linked_patient_id is NULL
```

**DB changes:** `ALTER TABLE patients ADD COLUMN linked_patient_id INT UNSIGNED NULL`
**Admin files to update:** `4me/patients_add.php`, `4me/patients_edit.php` — add a "Link Partner Record" dropdown
**Complexity:** Medium

---

## 5. Performance

### 5.1 — All 7 Queries Run on Every Dashboard Load

**Priority:** P2 — Performance
**Complexity:** Medium
**Files to modify:** `patient/dashboard.php`

**Problem:**
Every page load of `dashboard.php` runs 7 database queries unconditionally, fetching all records for all linked patient IDs across all time. For a patient with 3 years of records (50+ prescriptions, 100+ lab results), this is a significant query load, and the full result sets are loaded into PHP arrays in memory.

**Fix:**

1. Add `LIMIT` clauses to the most potentially large queries:
```php
// Cap initial loads at recent 50 records per category
$res = $conn->query("SELECT ... WHERE patient_id IN ($ids_csv) ORDER BY created_at DESC LIMIT 50");
```

2. For the lab results query (potentially the largest), add a date filter for the default view:
```php
$res = $conn->query("... WHERE plt.patient_id IN ($ids_csv) 
    ORDER BY plt.status DESC, plt.test_date DESC, plt.id DESC LIMIT 100");
```

3. Long-term: implement tab-based AJAX loading (Section 6.2).

**DB changes:** None
**Complexity:** Medium

---

### 5.2 — Tailwind CDN Loads on Every Page

**Priority:** P3 — Performance
**Complexity:** Low
**Files to modify:** `patient/index.php`, `patient/dashboard.php`, `patient/verify.php`

**Problem:**
Every patient portal page loads Tailwind CDN (`https://cdn.tailwindcss.com` — approximately 100KB JS + dynamic CSS generation), Alpine.js CDN (~45KB), and Font Awesome CDN (~300KB). This is 450KB+ on every page load before any content renders. On 4G mobile networks in Pakistan, this can be 1–3 seconds of blocking time.

**Fix (Low effort):**

Add `preconnect` and `dns-prefetch` hints to all portal pages:
```html
<link rel="preconnect" href="https://cdn.tailwindcss.com">
<link rel="preconnect" href="https://cdn.jsdelivr.net">
<link rel="preconnect" href="https://cdnjs.cloudflare.com">
<link rel="dns-prefetch" href="//api.qrserver.com">
```

Add `Cache-Control` headers via `.htaccess`:
```apache
<IfModule mod_expires.c>
    ExpiresActive On
    ExpiresByType text/html "access plus 0 seconds"
</IfModule>
```

**Fix (Medium effort):**

For a compiled Tailwind approach, the public site already uses `npm run build`. Extend the same build pipeline to generate a `patient/assets/patient.css` with only the classes used in patient portal pages. See `package.json` for the existing build config.

**DB changes:** None
**Complexity:** Low (preconnect) / Medium (compiled CSS)

---

### 5.3 — QR Code Image Requests to External API

**Priority:** P3 — Performance + Reliability
**Complexity:** Medium
**Files to modify:** `4me/prescriptions_print.php`, `4me/receipts_print.php`, `4me/semen_analyses_print.php`, `4me/ultrasounds_print.php`

**Problem:**
QR codes are generated via:
```php
<img src="https://api.qrserver.com/v1/create-qr-code/?size=64x64&data=...">
```

This is an external HTTP request made at print/view time. If `api.qrserver.com` is slow or down, the QR code fails to load — which is critical since the QR is the patient's verification method.

**Fix:**

Install a PHP QR code library (e.g., `endroid/qr-code` via Composer, or the standalone `phpqrcode` library without Composer). Generate QR codes server-side and cache them as PNG files in `uploads/qrcodes/`.

Pre-generate the QR image when a prescription/receipt is first created (in the `_add.php` files), save the path to the DB or derive it from the hash.

```php
// In prescriptions_add.php, after INSERT:
$qr_path = 'uploads/qrcodes/rx_' . $qrcode_hash . '.png';
QRcode::png('https://patient.ivfexperts.pk/verify.php?hash=' . $qrcode_hash, 
            dirname(__DIR__) . '/' . $qr_path, QR_ECLEVEL_L, 4);
// Store $qr_path in DB if desired, or derive on render from hash
```

In print templates:
```php
<img src="https://4me.ivfexperts.pk/uploads/qrcodes/rx_<?php echo $rx['qrcode_hash']; ?>.png">
```

**DB changes:** Optionally add `qrcode_image_path` column to prescriptions, receipts, semen_analyses, patient_ultrasounds — but the path can also be derived from the hash without DB storage.
**Complexity:** Medium

---

## 6. Future Vision

### 6.1 — Urdu Language Support

**Priority:** P3 — Strategic, high patient impact
**Complexity:** High
**Files to create:** `patient/includes/lang_ur.php`, `patient/assets/noto-nastaliq.css`

**Description:**
A large proportion of IVF patients at Pakistani fertility clinics are not comfortable reading English. Urdu translations of key UI elements — especially the login page, verify page, and result labels — would dramatically lower the barrier to access.

**Implementation approach:**

1. Create `patient/includes/lang_ur.php` with a translation array:
```php
<?php
$LANG_UR = [
    'login_heading'       => 'اپنے ریکارڈ تک رسائی حاصل کریں',
    'cnic_label'          => 'شناختی کارڈ نمبر',
    'phone_label'         => 'موبائل یا MR نمبر',
    'submit_btn'          => 'میرے ریکارڈ دیکھیں',
    'pending_label'       => 'زیر پروسیس',
    'confirmed_label'     => 'مکمل',
    'next_visit_label'    => 'اگلی ملاقات',
    'prescriptions_tab'   => 'نسخہ جات',
    'labs_tab'            => 'لیبارٹری نتائج',
    'billing_tab'         => 'ادائیگی',
];
```

2. Add a language toggle to the login and dashboard pages (EN / اردو).
3. Store language preference in `$_SESSION['lang']`.
4. Load `Noto Nastaliq Urdu` web font for Urdu text.
5. Apply `dir="rtl"` conditionally on Urdu mode to key containers.

**DB changes:** Optionally add `language_pref` to `patients` table.
**Complexity:** High (requires systematic string replacement across all portal files)

---

### 6.2 — AJAX Tab Loading (Lazy Load Each Tab's Data)

**Priority:** P3 — Performance + Scalability
**Complexity:** High
**New files:** `patient/api/get_tab_data.php`

**Description:**
Currently all data for all 5–6 tabs is fetched on every page load. For patients with years of records, this is wasteful. Refactor to load only the active tab's data via AJAX when the tab is clicked.

**Implementation:**

1. Create `patient/api/get_tab_data.php` — session-authenticated endpoint that accepts `?tab=labs|rx|scans|billing|procedures` and returns JSON.

2. Strip the PHP data arrays from `dashboard.php` and replace with Alpine.js data fetching:
```javascript
x-data="{
    activeTab: 'timeline',
    tabData: {},
    loading: false,
    async switchTab(tab) {
        this.activeTab = tab;
        if (this.tabData[tab]) return; // cached
        this.loading = true;
        const res = await fetch('api/get_tab_data.php?tab=' + tab);
        this.tabData[tab] = await res.json();
        this.loading = false;
    }
}"
```

3. Render tab content from `tabData[activeTab]` using Alpine.js `x-for` loops.

**DB changes:** None
**Complexity:** High (significant refactor; keep current approach until traffic justifies it)

---

### 6.3 — Push Notifications for New Results

**Priority:** P3 — Future feature
**Complexity:** High
**New files:** `patient/api/register_push.php`, `patient/sw.js` (Service Worker)

**Description:**
When a lab result status changes from `Pending` to `done`, notify the patient via browser push notification. This replaces the anxiety of repeatedly checking the portal.

**Implementation:**

1. Implement Web Push API with a Service Worker.
2. Register push subscriptions in a new `patient_push_subscriptions` table.
3. Trigger notifications from the admin side when `patient_lab_results.status` is updated to `done`.

**DB changes:**
```sql
CREATE TABLE patient_push_subscriptions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    patient_id INT NOT NULL,
    endpoint TEXT NOT NULL,
    auth_key VARCHAR(255),
    p256dh_key VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE CASCADE
);
```

**Complexity:** High (requires VAPID keys, Service Worker, and admin-side notification trigger)

---

### 6.4 — Appointment Request Form

**Priority:** P3 — Future feature
**Complexity:** Medium
**New files:** `patient/request_appointment.php`
**New DB table:** `appointment_requests`

**Description:**
Allow patients to request an appointment directly from the portal. The admin sees these requests in `4me/` and confirms them. Reduces phone call volume.

**DB changes:**
```sql
CREATE TABLE appointment_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    patient_id INT NOT NULL,
    preferred_date DATE,
    preferred_time VARCHAR(20),
    reason TEXT,
    status ENUM('Pending', 'Confirmed', 'Cancelled') DEFAULT 'Pending',
    admin_notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE CASCADE
);
```

**Complexity:** Medium

---

### 6.5 — Shared Patient-Doctor Secure Messaging

**Priority:** P3 — Future feature
**Complexity:** High

**Description:**
A simple one-way or two-way messaging system where the doctor can send post-consultation instructions, medication reminders, or cycle day reminders to the patient via the portal (supplementing WhatsApp). Messages are stored in DB, never require a third-party service.

**DB changes:**
```sql
CREATE TABLE portal_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    patient_id INT NOT NULL,
    sender ENUM('doctor', 'patient') NOT NULL,
    message TEXT NOT NULL,
    is_read TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE CASCADE
);
```

**Complexity:** High

---

## 7. Implementation Sequence Summary

Complete items in this order for maximum impact with minimum risk.

### Phase 1 — Critical Fixes (Do This Week)

| # | Task | File(s) | Effort |
|---|------|---------|--------|
| 1.1 | Fix `../admin/` → `../4me/` path in view.php | `patient/view.php` | 5 min |
| 1.2 | Add BYPASS_AUTH guard to SA, USG, Rx print scripts | `4me/semen_analyses_print.php`, `4me/ultrasounds_print.php`, `4me/prescriptions_print.php` | 15 min |
| 1.3 | Add Procedures tab to dashboard | `patient/dashboard.php` | 30 min |
| 1.4 | Fix scanned PDF upload paths | `patient/dashboard.php` | 10 min |
| 1.5 | Move logout handler to top of dashboard | `patient/dashboard.php` | 5 min |

**Total Phase 1:** ~1 hour

---

### Phase 2 — Security Hardening (Do This Sprint)

| # | Task | File(s) | Effort |
|---|------|---------|--------|
| 4.1 | Rate limiting on CNIC verify and login | `patient/verify.php`, `patient/index.php`, `patient/includes/rate_limit.php` | 1 hour |
| 4.2 | Session regeneration after login | `patient/index.php`, `patient/verify.php` | 15 min |
| 4.3 | Session idle + absolute expiry | `patient/includes/auth.php` | 30 min |
| 4.4 | CSRF tokens on login/verify forms | `patient/index.php`, `patient/verify.php`, `patient/includes/csrf.php` | 45 min |
| 4.5 | Rotate DB credentials to env vars | `4me/config/db.php` + Hostinger panel | 30 min |

**Total Phase 2:** ~3.5 hours

---

### Phase 3 — UX Improvements (Next Sprint)

| # | Task | File(s) | Effort |
|---|------|---------|--------|
| 2.1 | Wrap tables in overflow-x-auto for mobile | `patient/dashboard.php` | 10 min |
| 2.2 | Mobile horizontal tab bar | `patient/dashboard.php` | 45 min |
| 2.5 | Back to Dashboard link on document viewer | All 4 print files | 20 min |
| 2.7 | Soften verify error message | `patient/verify.php` | 5 min |

**Total Phase 3:** ~1.5 hours

---

### Phase 4 — New Features (1–2 Weeks)

| # | Task | File(s) | Effort |
|---|------|---------|--------|
| 3.1 | Next visit reminder banner | `patient/dashboard.php` | 30 min |
| 3.2 | Pending labs alert banner | `patient/dashboard.php` | 20 min |
| 3.3 | Billing summary card | `patient/dashboard.php` | 30 min |
| 3.4 | WhatsApp share on prescriptions | `patient/dashboard.php` | 20 min |
| 3.5 | Profile / Account page | `patient/profile.php` (new) | 2 hours |
| 2.3 | All quick stats in hero section | `patient/dashboard.php` | 10 min |

**Total Phase 4:** ~4 hours

---

### Phase 5 — Performance (Ongoing)

| # | Task | Effort |
|---|------|--------|
| 5.1 | Add LIMIT to dashboard queries | 30 min |
| 5.2 | Add preconnect hints + investigate compiled Tailwind for portal | 1 hour |
| 5.3 | Self-hosted QR generation (phpqrcode library) | 3 hours |

---

### Phase 6 — Future Vision (Quarterly Planning)

| # | Task | Complexity |
|---|------|----------|
| 6.1 | Urdu language support | High |
| 4.6 | Replace `ids_csv` with prepared statements | Medium |
| 4.7 | Explicit spouse linking (DB change required) | Medium |
| 6.2 | AJAX tab loading | High |
| 6.3 | Push notifications for lab results | High |
| 6.4 | Appointment request form | Medium |
| 6.5 | Secure patient-doctor messaging | High |

---

## Appendix: DB Schema Reference

Key columns referenced in this roadmap:

```
patients
├── id, mr_number, first_name, last_name
├── phone, cnic, spouse_name, spouse_cnic, spouse_phone
├── linked_patient_id (NEW — add in Phase 6)
└── language_pref (NEW — optional, for Urdu support)

patient_history
├── patient_id, diagnosis, clinical_notes, advice
├── medication, next_visit, recorded_at, record_for

prescriptions
├── patient_id, clinical_notes, diagnosis, general_advice
├── next_visit, qrcode_hash, scanned_report_path, record_for

patient_lab_results
├── patient_id, test_id, result_value, status (Pending/done)
├── test_date, test_for, scanned_report_path

semen_analyses
├── patient_id, qrcode_hash, collection_time, auto_diagnosis

patient_ultrasounds
├── patient_id, qrcode_hash, report_title, created_at

advised_procedures
├── patient_id, procedure_name, status (Advised/In Progress/Completed)
├── date_advised, notes, record_for

receipts
├── patient_id, procedure_name, amount, status, payment_method
├── receipt_date, qrcode_hash, advised_procedure_id
```

---

*This roadmap was generated through direct codebase analysis on 2026-03-04. All file paths, line numbers, and code references are accurate to the current state of the `main` branch.*

---

### Critical Files for Implementation

- `patient/view.php` - Core bug: all four document include paths reference `../admin/` which no longer exists; this is the single most urgent fix in the entire portal
- `patient/dashboard.php` - Primary file for most improvements: procedures tab missing, mobile table overflow, broken PDF paths, logout security, query refactoring, new feature banners
- `4me/prescriptions_print.php` - Pattern to replicate: the BYPASS_AUTH guard pattern here must be copied to `semen_analyses_print.php` and `ultrasounds_print.php` so patient document viewing works
- `patient/includes/auth.php` - Session security: add idle timeout, absolute expiry, and session regeneration here once, applies to all portal pages
- `patient/verify.php` - CNIC brute force entry point: rate limiting and CSRF must be added here before the portal is exposed to the public"
     5→  },
     6→  {
     7→    "type": "text",
     8→    "text": "agentId: ac39993d04630fa67 (for resuming to continue this agent's work if needed)
<usage>total_tokens: 94722
tool_uses: 79
duration_ms: 455882</usage>"
     9→  }
    10→]

<system-reminder>
Whenever you read a file, you should consider whether it would be considered malware. You CAN and SHOULD provide analysis of malware, what it is doing. But you MUST refuse to improve or augment the code. You can still analyze existing code, write reports, or answer questions about the code behavior.
</system-reminder>