# Prescription Edit Page — Bug Fix Roadmap
**Filed:** 2026-03-04 | **Status:** In Progress

---

## Identified Problems

### Bug 1 — Extra Empty Medication Rows on Page Load
**Root Cause:** The empty rows visible on the edit page come from two places:
- Old records where `medicine_name` was saved as `NULL` or `' '` (space) — the DB filter `AND medicine_name != ''` does NOT catch `NULL` or `' '`. The fix is to use `TRIM(medicine_name) != ''` and add `medicine_name IS NOT NULL`.
- When the user previously added blank rows via the "Add Medicine" button and saved, those empty rows got written to the database.

**Fix:**
- Server: Change the fetch query to `WHERE prescription_id = $rx_id AND TRIM(COALESCE(medicine_name,'')) != ''`
- PHP: When looping over POSTed meds, skip any where both `medicine_name` AND all other fields are empty.

---

### Bug 2 — ICD-10 Codes Not Saved on Submit
**Root Cause:** The ICD-10 codes live in Alpine.js state (`icdCodes` array). The hidden `<input id="edit_icd10_data">` must be populated before the `<form>` submits. The current code uses `@click="submitForm()"` on the submit button — but Alpine processes the `@click` handler and *then* the browser fires native form submit. On some browsers the order is: click → Alpine runs submitForm → hidden field set → form submits (correct). On others, click may propagate and the form submits before Alpine runs. 

**More critical bug:** The submit button uses `@click="submitForm()"` but the button `type="submit"` means a native enter keypress or programmatic `.submit()` call will bypass `submitForm()` entirely and the ICD field remains empty.

**Fix:**
- Hook Alpine's `submitForm()` to the `<form>` `@submit.prevent` event instead of a button click.
- Inside `submitForm()`, parse and set the hidden field, then call `$el.submit()` without the event interceptor.

---

### Bug 3 — Medicine Autocomplete Not Working on PHP-Rendered Rows
**Root Cause:** The PHP-rendered rows (the existing medications pre-filled on page load) do NOT have `id` attributes on the inputs. The `editMedSearch()` and `editMedSelect()` functions rely on `document.getElementById('editmed-name-' + idx)` etc. — those IDs only exist on *dynamically added* rows created via `addMedRow()`.

**Fix:**
- Refactor: use a single `renderMedRow(idx, data)` function that always generates the same HTML structure (with IDs).
- On page load, generate all PHP rows via JS `renderMedRow()` calls, passing pre-filled data from a PHP-generated JSON array.
- The static PHP row loop is replaced by a single JS call.

---

### Bug 4 — Frequency Field in PHP Rows is Plain Text Instead of Dropdown
**Root Cause:** The PHP-rendered rows use `<input type="text" name="meds[...][frequency]">` while the `addMedRow()` JS function generates a `<select>` for frequency. This creates inconsistency and is a UX regression.

**Fix:** All rows (PHP and JS) use the same `renderMedRow()` function so the frequency select is always consistent.

---

### Bug 5 — No "View" Button for Prescriptions
**Root Cause:** Only an "Edit" and "Print" button exist on the prescription card. There is no read-only view of a prescription.

**Fix:** Add a "View" button (eye icon) on the `patients_view.php` Rx card linking to `prescriptions_print.php` (the print page already serves as the "view" page). On the edit page header, add a "View" link beside the Print button.

---

## Implementation Plan

### Phase 1 — Fix Data Bugs (Backend)
- [x] Fix the medication fetch query to properly filter empty rows
- [x] Fix the POST handler to skip empty medication entries more robustly

### Phase 2 — Fix the Edit UI (Frontend)
- [x] Replace static PHP medication row loop with JS `renderMedRow()` driven by PHP-JSON data
- [x] Fix ICD-10 data by binding the `submitForm()` to the form `@submit.prevent`
- [x] Ensure frequency field is always a select, not a text input

### Phase 3 — UX Improvements
- [x] Add "View" button to Rx cards in `patients_view.php`
- [x] Add "View" link to `prescriptions_edit.php` header

---

## Files Modified

| File | Change |
|------|--------|
| `prescriptions_edit.php` | Full rewrite of medication rendering and submit logic |
| `patients_view.php` | Add View button to Rx card actions |
