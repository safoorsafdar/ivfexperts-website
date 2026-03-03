# Subdomain Restructure Plan — IVF Experts Platform

## Architecture Overview

The repository is divided into three independently deployable sub-projects:

| Sub-project | Subdomain | Folder | Hostinger Doc Root |
|---|---|---|---|
| Main Website (Frontend) | `ivfexperts.pk` | `/` (repo root) | `/public_html/` |
| Admin EMR | `4me.ivfexperts.pk` | `4me/` | `/public_html/4me/` |
| Patient Portal | `patient.ivfexperts.pk` | `patient/` | `/public_html/patient/` |

---

## Repo Folder Structure After Restructure

```
ivfexperts/
├── 4me/                        ← Admin EMR (4me.ivfexperts.pk)
│   ├── config/
│   │   └── db.php              ← SINGLE source of truth for DB credentials
│   ├── includes/
│   │   ├── auth.php
│   │   ├── header.php
│   │   ├── footer.php
│   │   └── sidebar.php
│   ├── index.php               ← Admin dashboard
│   ├── patients.php
│   ├── patients_view.php
│   ├── prescriptions_add.php
│   ├── ... (all admin files)
│   └── uploads/                ← Patient document uploads
│
├── patient/                    ← Patient Portal (patient.ivfexperts.pk)
│   ├── includes/
│   │   └── auth.php            ← Points to 4me/config/db.php
│   ├── index.php               ← Portal login
│   ├── dashboard.php
│   ├── verify.php
│   ├── view.php
│   └── docs/                   ← Implementation plans (this folder)
│
├── includes/                   ← Public site shared includes (UNTOUCHED)
├── assets/                     ← Public site assets (UNTOUCHED)
├── index.php                   ← Main website (UNTOUCHED)
├── about/, doctors/, blog/     ← Public pages (UNTOUCHED)
└── config/                     ← REMOVED (moved into 4me/)
```

---

## Database Connection Strategy

**Single DB config** — `4me/config/db.php` is the canonical database configuration file.

The patient portal accesses the same database by referencing the sibling `4me/config/db.php`:

```php
// patient/includes/auth.php
require_once dirname(__DIR__, 2) . '/4me/config/db.php';

// patient/dashboard.php, portal/index.php etc.
require_once dirname(__DIR__) . '/4me/config/db.php';
```

This ensures a single point of configuration for credentials, no duplication, and changes to DB credentials only need to be made in one place.

---

## Hostinger Deployment Steps

### 1. Set up subdomains in Hostinger hPanel
1. Log in to Hostinger → Websites → ivfexperts.pk → Subdomains
2. Create subdomain: `4me` → Document root: `public_html/4me`
3. Create subdomain: `patient` → Document root: `public_html/patient`

### 2. Deploy via Git / FTP
- Push the restructured repo to GitHub
- Pull on Hostinger (or FTP upload the `4me/` and `patient/` folders)

### 3. Verify paths on server
- Navigate to `https://4me.ivfexperts.pk/` → should show admin login
- Navigate to `https://patient.ivfexperts.pk/` → should show patient portal login

### 4. Run pending migrations on live server
After deployment:
- `https://4me.ivfexperts.pk/migrate_icd10_resume.php` — completes ICD-10 + CPT seeding
- `https://4me.ivfexperts.pk/migrate_procedures_financials.php` — adds payment_method column

---

## PHP Path Reference Map

After restructure, DB config paths change as follows:

| File Location | Old Path | New Path |
|---|---|---|
| `4me/includes/auth.php` | `dirname(__DIR__, 2) . '/config/db.php'` | `dirname(__DIR__) . '/config/db.php'` |
| `4me/login.php`, `4me/*.php` | `dirname(__DIR__) . '/config/db.php'` | `__DIR__ . '/config/db.php'` |
| `patient/includes/auth.php` | `dirname(__DIR__, 2) . '/config/db.php'` | `dirname(__DIR__, 2) . '/4me/config/db.php'` |
| `patient/dashboard.php`, `patient/*.php` | `dirname(__DIR__) . '/config/db.php'` | `dirname(__DIR__) . '/4me/config/db.php'` |
| `4me/patients/*.php` (DOCUMENT_ROOT style) | `$_SERVER['DOCUMENT_ROOT'] . "/config/db.php"` | `$_SERVER['DOCUMENT_ROOT'] . "/config/db.php"` *(unchanged — doc root IS 4me/)* |

---

## Files NOT Changed (Public Website)

The following are completely untouched:
- `index.php` — main homepage
- `includes/` — public header, footer, SEO
- `assets/` — compiled CSS, JS, images
- `about/`, `doctors/`, `blog/`, `contact/` — all public pages
- `blog/index.php` — references `config/db.php` at repo root (keep a symlink or copy)
- `sitemap.php`, `robots.txt`

> **Note:** After moving `config/` into `4me/`, the public site files that reference `config/db.php` at repo root (`blog/index.php`, `sitemap.php`, `contact/process.php`) will break. Options:
> - Keep a `config/db.php` stub at repo root that includes `4me/config/db.php`
> - Or update those public files to point to `4me/config/db.php`

---

## Status
- [ ] `portal/docs/` created ✓ (this file)
- [ ] `git mv admin 4me`
- [ ] `git mv config 4me/config`
- [ ] `git mv portal patient`
- [ ] Update PHP path references (see table above)
- [ ] Create `config/db.php` stub at repo root for public site compatibility
- [ ] Commit and push
- [ ] Configure Hostinger subdomains (manual step)
- [ ] Test `4me.ivfexperts.pk` and `patient.ivfexperts.pk`
