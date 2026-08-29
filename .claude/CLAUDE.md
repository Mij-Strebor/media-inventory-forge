# CLAUDE.md — Media Inventory Forge (MIF)

## INHERITANCE — ADDS TO, NEVER REPLACES

**Parent chain** — every rule from every level above remains fully in effect here:
1. `C:\Users\Owner\.claude\CLAUDE.md` — Root: all Claude Code sessions
2. `E:\projects\.claude\CLAUDE.md` — All E:\projects\ work
3. `E:\projects\plugins\.claude\CLAUDE.md` — All plugin development rules

**This file adds:** MIF-specific rules only.

---

## Plugin Identity

- **Plugin name:** Media Inventory Forge
- **Acronym / folder:** `mif` → `E:\projects\plugins\mif`
- **Version:** v5.1.0
- **GitHub:** https://github.com/Mij-Strebor/media-inventory-forge
- **Text domain:** `media-inventory-forge`
- **Admin page slug:** `media-inventory-forge`
- **Required capability:** `manage_options`
- **Branding:** Always "Jim R Forge" — never "JimRWeb" or "JimRForge"
- **Author URI:** https://jimrforge.com

---

## What MIF Does

MIF is a WordPress admin tool that scans and reports on the media library. It:
- Scans all media files (images, fonts, other file types)
- Reports file counts, sizes, and distribution by type
- Displays usage data (which posts reference each file)
- Provides a searchable, filterable table view

**MIF is read-only.** It never modifies, moves, or deletes media files. Any code that writes to files, the file system, or the media library is a bug.

---

## CRITICAL: MIF is Read-Only

- No class, method, or function in MIF may modify, move, rename, delete, or regenerate any file.
- `class-scanner.php`, `class-file-processor.php`, and all processor classes may only read — never write.
- If a future feature requires modifying files, it must be discussed explicitly before implementation.

---

## CSS / JS Prefix

| Layer | Prefix | Example |
|-------|--------|---------|
| CSS classes | `mif-` | `mif-btn`, `mif-table` |
| AJAX actions | `minvf_` | `minvf_run_scan` |
| WP option keys | `minvf_` | `minvf_settings` |
| PHP constants | `MINVF_` | `MINVF_VERSION` |
| PHP classes | `MINVF_` | `MINVF_Admin`, `MINVF_Scanner` |
| JS localized object | `minvfData` | `minvfData.nonce` |

> **Note:** CSS classes remain `mif-` — PHP-layer identifiers all use `MINVF_`/`minvf_`.

---

## MIF-Specific Rules

- **No build process:** Pure PHP/JS/CSS. Hard-refresh (Ctrl+Shift+R) after JS/CSS edits.
- **Automated tests exist here — the one exception to the plugins-level "no automated tests" rule.** `tests/Unit/FileUtilsTest.php` + `tests/bootstrap.php`. Run/extend these for `class-file-utils.php` changes rather than relying on manual admin testing alone.
- **Performance:** Scans can be large. Paginate results; do not load all media into memory at once.
- **Network activation:** MIF supports network-activated mode — any changes to admin menu registration must account for both single-site and multisite context.
- **Nonce constant:** `NONCE_ACTION` — defined once in the main class; never duplicated as an inline string.
- **Never use `vh` or `vw` units in CSS.** Use `px` for fixed layouts and `%` for fluid ones.

---

## JimRForge UI Standards — MIF Implementation

FSF v1.2.4 is the canonical UI prototype. Full reference: `E:\projects\JIMRFORGE-UI-STANDARDS.md`

### Brand

- Organization name: **Jim R Forge** — not "JimRWeb", not "JimRForge"

### Color System — Exact Values

```css
:root {
    --clr-primary:    #3d2f1f;    --clr-secondary:  #6d4c2f;    --clr-tertiary:   #86400e;
    --clr-accent:     #f4c542;    --clr-btn-hover:  #dda824;
    --clr-age-bg:     #faf6f0;    --clr-card-bg:    #ffffff;    --clr-light:      #faf9f6;    --clr-white: #fff;
    --clr-txt:        #6d4c2f;    --clr-txt-light:  #faf9f6;    --clr-txt-muted:  #64748b;
    --clr-link:       #ce6565;    --clr-link-hover: #b54545;
    --clr-border:     #c9b89a;
    --clr-shadow-sm:  0 1px 2px rgba(61, 47, 31, 0.08);
    --clr-shadow-md:  0 4px 6px rgba(61, 47, 31, 0.12);
    --clr-shadow-lg:  0 10px 20px rgba(61, 47, 31, 0.15);
    --clr-shadow-xl:  0 20px 30px rgba(61, 47, 31, 0.18);
    --clr-success: #059669; --clr-success-bg: #ecfdf5;
    --clr-error:   #dc2626; --clr-error-bg:   #fee2e2;
    --clr-warning: #f59e0b; --clr-warning-bg: #fef3c7;
    --clr-info:    #3b82f6; --clr-info-bg:    #dbeafe;
}
```

### Typography

Font: Inter (locally loaded, WOFF2); Base: 16px; Weights: 400/500/600/700

```css
--fs-xxs: 11px; --fs-xs: 13px; --fs-sm: 14px; --fs-md: 16px;
--fs-lg: 18px;  --fs-xl: 20px; --fs-xxl: 24px; --fs-xxxl: 32px;
```

### Spacing

```css
--sp-1: 4px; --sp-2: 8px; --sp-3: 12px; --sp-4: 16px;
--sp-5: 20px; --sp-6: 24px; --sp-8: 32px; --sp-9: 36px;
```

### Primary Button — `.mif-btn`

Background: `--clr-accent`, Text: `--clr-primary`; 14px/600; sentence case; no border; radius 8px; 8px 16px padding; hover: `--clr-btn-hover`, lift transform. **No icons in modal buttons.**

### Secondary — `.mif-btn.mif-btn-secondary`

Background: `#64748b`, Text: white; Hover: `#475569`

### Danger — `.mif-btn.mif-btn-danger`

Background: `#dc2626`, Text: white; Hover: `#b91c1c`, lift transform

### Forge Header + Accessibility

Every admin page: `assets/images/forge-banner.png` + `assets/css/forge-header.css`. Focus: `2px solid var(--clr-accent)`. All icon-only buttons: `aria-label`. Full keyboard navigation.
