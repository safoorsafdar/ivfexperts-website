# 4me Admin — Build Workflow

## Prerequisites
- Node.js ≥ 18
- Run `npm install` from `/e/Github/ivfexperts/` (repo root)

## Tailwind CSS (Admin)

The admin panel uses **Tailwind v4** compiled locally into:
```
4me/assets/css/admin-compiled.css  ← served by server (committed to Git)
4me/assets/css/admin-input.css     ← source file (edit this to add styles)
```

### Commands (run from repo root `e:\Github\ivfexperts\`)

| Command | Effect |
|---|---|
| `npm run admin:build` | One-time compile |
| `npm run admin:watch` | Watch mode (live recompile on save) |
| `npm run build:all` | Compile both public site + admin |

### Important
- **After editing any `admin-input.css`**, run `npm run admin:build` before committing
- Compiled `admin-compiled.css` IS committed to Git (Hostinger serves it directly)
- Do NOT edit `admin-compiled.css` directly — it gets overwritten on every build

## Alpine.js (Self-hosted)
```
4me/assets/js/alpine.min.js   ← Alpine.js v3.14.1
```
To update Alpine, download the new version from https://github.com/alpinejs/alpine/releases and replace this file.

## Error Logs
```
4me/logs/admin-YYYY-MM.log     ← PHP exceptions, errors
4me/logs/php-errors-YYYY-MM.log ← Native PHP errors
```
- Logs rotate monthly and auto-clear when > 2MB
- Protected from web access by `.htaccess` (Deny from all)
- NOT committed to Git (see `4me/logs/.gitignore`)

## Adding New Pages
1. Add `require_once __DIR__ . '/includes/auth.php';` at the top
2. All helpers, CSRF, error logging, and Tailwind CSS are automatically available
3. After adding new Tailwind classes, run `npm run admin:build` to compile them
