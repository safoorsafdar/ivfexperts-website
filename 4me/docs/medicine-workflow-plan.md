# Medicine Workflow Redesign Plan
**IVF Experts EMR — 4me subdomain**
_Written: 2026-03-04 | Status: Ready for implementation_

---

## Problem Statement

The current medicine workflow has two disconnected systems:
1. `medications.php` — a standalone "arsenal" with minimal fields (name, type, vendor, price)
2. `prescriptions_add.php` / `prescriptions_edit.php` — free-text medicine entry that tries to auto-save to medications table but only saves `name` (no other data)

Result: Medicine data is incomplete, search autofill fills only the name, and there is no pre-fill of dosage/frequency/instructions from the database.

---

## Target Workflow (Single Source of Truth)

```
medications.php (Arsenal)
  └── Stores: brand_name, formula, default_dosage, default_frequency,
              default_duration, default_instructions, med_type

prescriptions_add.php / prescriptions_edit.php (Prescription)
  └── Medicine search box → shows: "Clomid (Clomiphene Citrate)"
  └── On select → auto-fills: dosage, frequency, duration, instructions
  └── Doctor can override any field
  └── On save → auto-INSERT medicine to arsenal (full data, only if name not exists)

patients_view.php (Patient 360)
  └── Prescription tab → medicine chips show: "Clomid" + "Clomiphene Citrate" subtitle

prescriptions_print.php (A4 Print)
  └── Medicine rows show: Brand name + (formula in brackets) under dosage line
```

---

## Database Changes

### medications table — add columns

```sql
ALTER TABLE medications
  ADD COLUMN formula VARCHAR(255) DEFAULT '' AFTER name,
  ADD COLUMN default_dosage VARCHAR(100) DEFAULT '' AFTER formula,
  ADD COLUMN default_frequency VARCHAR(100) DEFAULT '' AFTER default_dosage,
  ADD COLUMN default_duration VARCHAR(100) DEFAULT '' AFTER default_frequency,
  ADD COLUMN default_instructions TEXT AFTER default_duration;
```

**No changes needed to `prescription_items` table** — it already has:
`prescription_id, medicine_name, dosage, frequency, duration, instructions`

The link between a prescription item and the arsenal is by `medicine_name` (string match).
A future enhancement could add a `medication_id` FK, but this is not needed for Phase 1.

---

## Files to Change (in order)

| Phase | File | Change |
|-------|------|--------|
| 1 | `migrate_medications_v2.php` | New migration script — adds columns to medications table |
| 2 | `medications.php` | Enhanced add/edit forms with formula + default dosage/frequency/instructions |
| 3 | `api_search_medications.php` | Return full record (id, name, formula, default_dosage, default_frequency, default_duration, default_instructions) |
| 4 | `prescriptions_add.php` | Medicine row: search → autofill from API, auto-save to arsenal on Rx save |
| 5 | `prescriptions_edit.php` | Same search + autofill for new medicine rows; existing rows unchanged |
| 6 | `patients_view.php` | Show formula under brand name in prescription medicine chips |
| 7 | `prescriptions_print.php` | Show formula in brackets next to brand name on printed Rx |

---

## Phase 1 — Migration Script (`migrate_medications_v2.php`)

**File:** `4me/migrate_medications_v2.php`

Run once on live server via browser: `https://4me.ivfexperts.pk/migrate_medications_v2.php`

```php
<?php
require_once __DIR__ . '/includes/auth.php';

$steps = [];

// Add new columns if missing
$new_cols = [
    'formula'              => "ALTER TABLE medications ADD COLUMN formula VARCHAR(255) DEFAULT '' AFTER name",
    'default_dosage'       => "ALTER TABLE medications ADD COLUMN default_dosage VARCHAR(100) DEFAULT '' AFTER formula",
    'default_frequency'    => "ALTER TABLE medications ADD COLUMN default_frequency VARCHAR(100) DEFAULT '' AFTER default_dosage",
    'default_duration'     => "ALTER TABLE medications ADD COLUMN default_duration VARCHAR(100) DEFAULT '' AFTER default_frequency",
    'default_instructions' => "ALTER TABLE medications ADD COLUMN default_instructions TEXT AFTER default_duration",
];

foreach ($new_cols as $col => $sql) {
    $r = $conn->query("SHOW COLUMNS FROM medications LIKE '$col'");
    if ($r && $r->num_rows === 0) {
        if ($conn->query($sql)) {
            $steps[] = "✓ Added column: $col";
        } else {
            $steps[] = "✗ Failed to add $col: " . $conn->error;
        }
    } else {
        $steps[] = "↷ Column already exists: $col";
    }
}
?>
<!DOCTYPE html><html><body style="font-family:monospace;padding:40px;">
<h2>Medicine DB Migration v2</h2>
<?php foreach ($steps as $s) echo "<p>$s</p>"; ?>
<p><strong>Done. You can delete this file.</strong></p>
</body></html>
```

---

## Phase 2 — medications.php (Enhanced Arsenal)

**Current fields:** name, med_type, vendor, price
**Add fields:** formula (generic/chemical name), default_dosage, default_frequency, default_duration, default_instructions

### Add Form Changes
Replace current 4-field form with 8-field form:
```
Brand Name *          [text input]           e.g. "Clomid"
Formula (Generic)     [text input]           e.g. "Clomiphene Citrate"
Type *               [select dropdown]       Injection/Tablet/Capsule/Sachet/Syrup/Other
Default Dosage       [text input]            e.g. "50mg"
Default Frequency    [select dropdown]       Same options as prescription wizard
Default Duration     [text input]            e.g. "5 days"
Default Instructions [text input]            e.g. "From Day 2 of cycle"
Vendor / Pharmacy    [text input]            Optional
Price (Rs)           [number input]          Optional
```

### Table Display Changes
Add "Formula" column after "Name":
```
Name              Formula              Type    Vendor    Price    Actions
Clomid            Clomiphene Citrate   Tablet  GSK       350.00   [Edit][Delete]
Gonal-f 75 IU     Follitropin Alfa     Inj     Merck     -        [Edit][Delete]
```

### Edit Modal Changes
Include all 8 new fields (pass via `json_encode($m)` to `openEditModal()`).

### PHP Handler Changes
- INSERT: add `formula, default_dosage, default_frequency, default_duration, default_instructions` to INSERT statement
- UPDATE: same fields added to UPDATE statement
- bind_param type string: `"sssssssssd"` (or similar)

---

## Phase 3 — api_search_medications.php (Enhanced API)

**Current:** returns `[{id, name}, ...]`
**After:** returns `[{id, name, formula, default_dosage, default_frequency, default_duration, default_instructions}, ...]`

```php
$res = $conn->query("SELECT id, name, formula, default_dosage, default_frequency,
                            default_duration, default_instructions
                     FROM medications WHERE name LIKE '$like' OR formula LIKE '$like'
                     ORDER BY name ASC LIMIT 12");
```

Note: also search by formula so typing "clomiphene" finds "Clomid".

---

## Phase 4 — prescriptions_add.php (Medication Section Overhaul)

### Current State
- Row 0 pre-rendered (static HTML)
- `rxAddMed()` adds new rows via vanilla JS `insertAdjacentHTML`
- `rxMedSearch(input, idx)` — debounced fetch from API, fills dropdown
- `rxMedSelect(btn, name, idx)` — fills medicine_name input only

### Changes Required

#### rxMedSelect — autofill all fields
```javascript
function rxMedSelect(btn, med, idx) {
    document.getElementById('rxmed-name-' + idx).value = med.name;
    document.getElementById('rxmed-dosage-' + idx).value = med.default_dosage || '';
    document.getElementById('rxmed-freq-' + idx).value = med.default_frequency || '';
    document.getElementById('rxmed-dur-' + idx).value = med.default_duration || '';
    document.getElementById('rxmed-instr-' + idx).value = med.default_instructions || '';
    document.getElementById('rxmed-drop-' + idx).classList.add('hidden');
}
```

#### rxMedSearch — dropdown shows brand + formula
```javascript
// Build each dropdown item:
var label = item.name + (item.formula ? ' <span style="color:#94a3b8;font-size:11px;">(' + item.formula + ')</span>' : '');
var html = '<button type="button" onclick=\'rxMedSelect(this,' + JSON.stringify(item) + ',' + idx + ')\'>' + label + '</button>';
```

#### Add IDs to all inputs in Row 0 and rxAddMed()
Each field needs an `id="rxmed-dosage-N"`, `id="rxmed-freq-N"`, etc. for autofill to work.

#### Auto-save full medicine data on Rx save
In PHP handler, change `INSERT IGNORE INTO medications (name)` to:
```php
$auto_med = $conn->prepare(
    "INSERT IGNORE INTO medications (name, formula, default_dosage, default_frequency, default_duration, default_instructions)
     VALUES (?, ?, ?, ?, ?, ?)"
);
// bind: name, '' (formula unknown from Rx), dosage, frequency, duration, instructions
$auto_med->bind_param("ssssss", $name, $empty, $dose, $freq, $dur, $instr);
```

**Note:** formula is blank when auto-saved from prescription (user didn't type formula).
Doctor can fill it later via `medications.php`. This is acceptable behavior.

---

## Phase 5 — prescriptions_edit.php (Same Changes as Phase 4)

- Existing rows (PHP-rendered) — add `id` attributes to each field
- New rows added by `addMedRow()` — add `id` attributes + autocomplete IDs
- `addMedRow()` function: add autofill capability (search + dropdown + select)
- PHP handler: same auto-save full data to medications table

---

## Phase 6 — patients_view.php (Show Formula in Rx Tab)

In the prescription medication chips section, currently shows:
```
[Clomid] [500mg] [BD] [5 days]
```

Change to:
```
[Clomid
 Clomiphene Citrate]  [50mg] [1-0-1] [5 days]
```

Implementation: Join `prescription_items` with `medications` on `medicine_name`:
```php
$items_res = $conn->query("
    SELECT pi.*, COALESCE(m.formula, '') as formula
    FROM prescription_items pi
    LEFT JOIN medications m ON m.name = pi.medicine_name
    WHERE pi.prescription_id = {$rx['id']}
    AND pi.medicine_name != ''
");
```

Then in the chip HTML:
```php
<span class="font-bold"><?= esc($item['medicine_name']) ?></span>
<?php if ($item['formula']): ?>
  <span class="text-[10px] text-indigo-300 block"><?= esc($item['formula']) ?></span>
<?php endif; ?>
```

---

## Phase 7 — prescriptions_print.php (Show Formula on Printed Rx)

Currently each medicine row:
```
Clomid    50mg    BD    5 days    Empty stomach
```

After:
```
Clomid               50mg    BD    5 days    Empty stomach
Clomiphene Citrate
```

Same JOIN query as Phase 6 to fetch formula from medications table.

---

## Implementation Order & Dependencies

```
Phase 1 (migrate)    → run on server first, no code dependencies
Phase 2 (medications.php) → independent, can do anytime after Phase 1
Phase 3 (API)        → independent, can do anytime after Phase 1
Phase 4 (prescriptions_add) → requires Phase 3 (API must return full data)
Phase 5 (prescriptions_edit) → requires Phase 3
Phase 6 (patients_view) → independent (uses JOIN, no API)
Phase 7 (prescriptions_print) → independent (uses JOIN, no API)
```

---

## Verification Checklist

After implementing all phases:

- [ ] Run `migrate_medications_v2.php` — all columns added, no errors
- [ ] Go to `medications.php` → Add "Clomid" with formula "Clomiphene Citrate", dosage "50mg", frequency "1-0-1", duration "5 days"
- [ ] Go to `prescriptions_add.php` for any patient → Step 2 → type "Clom" in medicine name → dropdown shows "Clomid (Clomiphene Citrate)"
- [ ] Click it → dosage, frequency, duration, instructions auto-fill from DB
- [ ] Override duration to "10 days" → submit prescription
- [ ] Go to `patients_view.php` → Rx tab → medicine chips show "Clomid" + "Clomiphene Citrate" subtitle
- [ ] Click Print on that prescription → formula shows on printed Rx
- [ ] Add a NEW medicine "Metformin" directly in prescriptions_add.php (not in arsenal first) → submit
- [ ] Go to `medications.php` → "Metformin" appears in list with dosage/frequency auto-saved
- [ ] Edit that Metformin entry → add formula "Metformin HCl" → save
- [ ] Next prescription → type "Metformin" → dropdown shows "Metformin (Metformin HCl)"

---

## Notes & Edge Cases

1. **Duplicate medicine names:** `INSERT IGNORE` on `name` column (UNIQUE KEY) prevents duplicates. If same medicine name saved again from prescription, the existing DB record is kept untouched (doctor edits via medications.php).

2. **Formula search:** `api_search_medications.php` should search BOTH `name` and `formula` columns so typing the generic name (e.g. "metformin") finds the brand name entry.

3. **Frequency values:** The prescription wizard uses free-text for frequency in some rows and a `<select>` in others. `default_frequency` in medications table should store the same format as the `<select>` options (e.g. `"1-0-1"`, `"1-1-1"`, `"SOS"`) OR free-text if not in list.

4. **Old prescriptions:** Existing prescription_items rows are unaffected. Formula will show blank (LEFT JOIN returns NULL → empty string) until medicines are added to the arsenal.

5. **medications.php `med_type`:** Keep existing `med_type` field (Injection/Tablet etc.). No change needed.

---

_End of plan. Implement phase by phase. Test each phase before moving to next._
