# Print System — Smart Pagination & Layout Roadmap
**Filed:** 2026-03-05 | **Status:** In Progress

---

## Problem Statement

The current `prescriptions_print.php` generates a single `<div class="a4-container">` with content flowing freely inside. When a prescription has many medications, long advice text, or many lab tests, the content **overflows the page boundary** on print. The printer receives a truncated or overflow-cut document. Additionally, the printer's own margin/size settings can override the intended layout, breaking the letterhead alignment.

---

## Goals

1. **Smart pagination** — when content approaches the bottom margin boundary, automatically break to a new page
2. **Repeating header + footer** on every page — patient info block at top, signature+date at bottom
3. **Letterhead-aware** — each new page gets the same digital letterhead background image for the "Print Digital PDF" mode
4. **Override printer settings** — suppress the browser print dialog's headers/footers and enforce our `@page` CSS margins, paper size, and orientation
5. **Clean PDF output** — both from admin "Print Digital PDF" and patient "Save as PDF" routes

---

## Architecture Decision

### Approach: CSS `@page` + `break-inside: avoid` + Cloned Running Header/Footer

We use **CSS Print Layout Level 3** techniques:
- `@page` rule with `margin` set from hospital letterhead settings — overrides printer defaults
- `page-break-before: always` on page break sentinel elements
- `break-inside: avoid` on the medication table rows and sections
- A **running header** via PHP-generated repeating `<thead>` (for tables) and a CSS `position: running(header)` element
- JavaScript **content height monitor** that measures rendered content height against the available page height and injects page breaks dynamically before overflow

### Why not just CSS?

Pure CSS pagination (via `page-break-after`) is unreliable for heterogeneous content (mixed tables, paragraphs, images). The JS height monitor approach is deterministic and works with all browsers.

---

## Implementation Plan

### Phase 1 — CSS `@page` Override & Printer Control
**File:** `prescriptions_print.php`

- [x] Lock `@page` to A4 size and hospital-specific margins from DB
- [x] Add `-webkit-print-color-adjust: exact` and `print-color-adjust: exact` globally
- [x] Set `@page { size: A4 portrait; }` explicitly — this overrides user printer settings
- [ ] Add `@page :first`, `@page :left`, `@page :right` rules
- [ ] Suppress browser-injected header/footer text via `margin: 0` + running elements trick

### Phase 2 — Repeating Header Block (Patient Demographics)
**File:** `prescriptions_print.php`

- [ ] Extract patient demographics into a standalone `<div class="rx-page-header">` component
- [ ] Use `position: running(rxHeader)` in the CSS for supported browsers
- [ ] For broad compatibility: use `<thead>` technique — wrap entire page in a `<table>` where `<thead>` repeats on each print page
- [ ] The header shows: Patient Name, MR#, Gender/Age, Date, RX number, QR code (compact version)

### Phase 3 — Repeating Footer Block (Signature + Page Number)
**File:** `prescriptions_print.php`

- [ ] Extract signature block into `<div class="rx-page-footer">`
- [ ] Use `<tfoot>` technique to repeat footer on every printed page
- [ ] Footer contains: "Digitally Signed" text, doctor signature image, page number (`Page X of Y`)
- [ ] Traceability code included in footer

### Phase 4 — Smart Content Pagination (JS Height Monitor)
**File:** `prescriptions_print.php`

- [ ] Write `paginateContent()` JS function that:
  1. Measures `window.innerHeight` equivalent in mm for A4 (297mm - margins)
  2. Iterates over each section (clinical notes, med table rows, lab tests, advice)
  3. When cumulative height exceeds `contentAreaHeight`, inject a `<div class="page-break">` sentinel
  4. After injection, re-measures
- [ ] Trigger `paginateContent()` on `DOMContentLoaded` before print
- [ ] For multi-page: inject letterhead background image into each `.a4-page` div

### Phase 5 — Multi-Page Letterhead for Digital Print Mode
**File:** `prescriptions_print.php`

- [ ] Refactor `printDigital()` to iterate over all `.rx-page` containers
- [ ] Inject one `<img class="letterhead-bg">` per `.rx-page` container
- [ ] Use `Promise.all()` to wait for all letterhead images to load before calling `window.print()`

### Phase 6 — Admin Settings UI (Margin Configuration)
**File:** `hospitals.php` / `settings.php`

- [ ] Ensure `margin_top`, `margin_bottom`, `margin_left`, `margin_right` fields exist and are displayed mm values
- [ ] Add a live preview in the settings page showing a miniaturized A4 with the margin zones highlighted
- [ ] Save margins as e.g. `"25mm"` strings in the DB

---

## Page Structure (Target HTML)

```html
<!-- Repeated for each "page" of the prescription -->
<div class="rx-page">
  <img class="letterhead-bg" src="..." />   <!-- digital mode only -->

  <table class="rx-layout-table">
    <thead class="rx-header">               <!-- repeats on every page -->
      <tr><td>
        [Patient Demographics Block]
        [RX Number + Date + QR Code]
      </td></tr>
    </thead>
    <tfoot class="rx-footer">              <!-- repeats on every page -->
      <tr><td>
        [Doctor Signature + Digital Sign text]
        [Page N of M]
        [Traceability Code]
      </td></tr>
    </tfoot>
    <tbody class="rx-body">               <!-- unique content per page -->
      <tr><td>
        [Clinical Notes]
        [Medication Table]
        [Lab Tests]
        [General Advice]
        [Next Visit]
      </td></tr>
    </tbody>
  </table>
</div>
```

---

## Files to Modify

| File | Changes |
|------|---------|
| `4me/prescriptions_print.php` | Full layout refactor — smart pagination, repeating header/footer, multi-page letterhead |
| `4me/semen_analyses_print.php` | Apply same pattern |
| `4me/hospitals.php` (if exists) | Ensure margin fields (mm) are saved as strings |

---

## Testing Checklist

- [ ] Short prescription (1-3 meds) — single page, no page break
- [ ] Long prescription (10+ meds) — auto-breaks, header/footer on page 2
- [ ] Long general advice text — wraps without cutting
- [ ] "Print Digital PDF" with letterhead — letterhead appears on all pages
- [ ] "Print on Physical Letterhead" — content fits within pre-printed margins
- [ ] Patient portal "Save as PDF" — auto-prints with letterhead
- [ ] QR code and traceability code visible on every page
