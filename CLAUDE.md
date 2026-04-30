# CLAUDE.md — Media Inventory Forge (MIF)

> Read parent CLAUDE.md files first — in order:
> 1. `E:/projects/CLAUDE.md` — global conventions, git workflow, backups, CSS debugging
> 2. `E:/projects/plugins/CLAUDE.md` — WordPress plugin architecture, PHP/JS debugging, release workflow
>
> This file covers MIF-specific rules only.

---

## Plugin Identity

- **Plugin name:** Media Inventory Forge
- **Acronym / folder:** `mif` → `E:\projects\plugins\mif`
- **Version:** v5.0.2
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

All CSS classes, AJAX action names, and option keys use the `mif-` / `minvf_` prefix.

| Layer | Prefix | Example |
|-------|--------|---------|
| CSS classes | `mif-` | `mif-btn`, `mif-table` |
| AJAX actions | `minvf_` | `minvf_run_scan` |
| WP option keys | `minvf_` | `minvf_settings` |
| PHP constants | `MINVF_` | `MINVF_VERSION` |
| PHP classes | `MINVF_` | `MINVF_Admin`, `MINVF_Scanner` |
| JS localized object | `minvfData` | `minvfData.nonce` |

> **Note:** CSS classes remain `mif-` (unchanged) — WordPress.org only requires uniqueness for PHP-layer identifiers. PHP constants, classes, WP options, transients, user meta, and AJAX actions all use `MINVF_`/`minvf_`.

---

## MIF-Specific Rules

- **No build process:** Pure PHP/JS/CSS. Hard-refresh (Ctrl+Shift+R) after JS/CSS edits.
- **Performance:** Scans can be large. Paginate results; do not load all media into memory at once.
- **Network activation:** MIF supports network-activated mode (`Network: true` in plugin header) — any changes to admin menu registration must account for both single-site and multisite context.
- **Nonce constant:** `NONCE_ACTION` — defined once in the main class; never duplicated as an inline string.
- **Never use `vh` or `vw` units in CSS.** Viewport-relative units render unpredictably across different screen sizes, admin sidebar widths, and browser zoom levels. Use `px` for fixed layouts and `%` for fluid ones.

---

## JimRForge UI Standards — MIF Implementation

FSF v1.2.4 is the canonical UI prototype. Match it exactly for all visual work.
Full reference: `E:\projects\JIMRFORGE-UI-STANDARDS.md`

### Brand

- Organization name: **Jim R Forge** — not "JimRWeb", not "JimRForge"
- Author URI: https://jimrforge.com

### Color System — Exact Values

Never substitute, approximate, or invent color values. Copy exactly:

```css
:root {
    /* Browns */
    --clr-primary:    #3d2f1f;    /* Deep brown — headings, button text */
    --clr-secondary:  #6d4c2f;    /* Medium brown — body text */
    --clr-tertiary:   #86400e;    /* Accent brown */

    /* Gold */
    --clr-accent:     #f4c542;    /* Gold — buttons, highlights */
    --clr-btn-hover:  #dda824;    /* Gold hover (15–20% darker) */

    /* Backgrounds (4-level hierarchy) */
    --clr-age-bg:     #faf6f0;    /* Level 1: page background */
    --clr-card-bg:    #ffffff;    /* Level 2: card/container */
    --clr-light:      #faf9f6;    /* Level 3: panel background */
    --clr-white:      #fff;       /* Level 4: form field */

    /* Text */
    --clr-txt:        #6d4c2f;
    --clr-txt-light:  #faf9f6;
    --clr-txt-muted:  #64748b;

    /* Links */
    --clr-link:       #ce6565;
    --clr-link-hover: #b54545;

    /* Borders & shadows */
    --clr-border:     #c9b89a;
    --clr-shadow-sm:  0 1px 2px rgba(61, 47, 31, 0.08);
    --clr-shadow-md:  0 4px 6px rgba(61, 47, 31, 0.12);
    --clr-shadow-lg:  0 10px 20px rgba(61, 47, 31, 0.15);
    --clr-shadow-xl:  0 20px 30px rgba(61, 47, 31, 0.18);

    /* Status */
    --clr-success:    #059669;    --clr-success-bg: #ecfdf5;
    --clr-error:      #dc2626;    --clr-error-bg:   #fee2e2;
    --clr-warning:    #f59e0b;    --clr-warning-bg: #fef3c7;
    --clr-info:       #3b82f6;    --clr-info-bg:    #dbeafe;
}
```

### Typography

- **Font:** Inter (locally loaded, WOFF2)
- **Required files:** `assets/fonts/Inter-Regular.woff2`, `Inter-Medium.woff2`, `Inter-SemiBold.woff2`, `Inter-Bold.woff2`
- **Base size:** 16px
- **Weights:** 400, 500, 600, 700

```css
--fs-xxs: 11px;   --fs-xs: 13px;   --fs-sm: 14px;   --fs-md: 16px;
--fs-lg:  18px;   --fs-xl: 20px;   --fs-xxl: 24px;  --fs-xxxl: 32px;
```

Font-face declaration required for each weight (`font-display: swap`). Apply globally within the plugin scope with `font-family: var(--fs-body) !important`.

### Spacing Scale

```css
--sp-1: 4px;  --sp-2: 8px;  --sp-3: 12px; --sp-4: 16px;
--sp-5: 20px; --sp-6: 24px; --sp-8: 32px; --sp-9: 36px;
```

### Primary Button — `.mif-btn`

- Background: `--clr-accent` (#f4c542), Text: `--clr-primary` (#3d2f1f)
- Font: 14px, weight 600
- **Button text must be sentence case in HTML** — never via `text-transform`
- No border — box-shadow for depth only; border radius: 8px; padding: 8px 16px
- Hover: background `--clr-btn-hover`, `transform: translate(-2px, -2px)`
- Dashicons: `margin-top: 3px`, 8px gap via flexbox `gap`
- **No icons in modal buttons**

### Secondary / Cancel Button — `.mif-btn.mif-btn-secondary`

- Background: `--clr-txt-muted` (#64748b), Text: white (`!important`)
- Hover: background `#475569`

### Danger Button — `.mif-btn.mif-btn-danger`

- Background: `--clr-error` (#dc2626), Text: white
- Hover: background `#b91c1c`, `transform: translate(-2px, -2px)`

### Standard Dashicon Assignments

| Action | Icon class |
|--------|-----------|
| Reset / Undo | `dashicons-undo` |
| Save / Confirm | `dashicons-yes` |
| Add | `dashicons-plus-alt` |
| Delete / Clear | `dashicons-trash` |
| Copy | `dashicons-clipboard` |
| Export | `dashicons-download` |
| Import | `dashicons-upload` |

Tabs and inline text links do not use icons.

### Forge Header

Every plugin admin page includes the forge header:
- `assets/images/forge-banner.png`
- `assets/css/forge-header.css`

### Accessibility — Minimum WCAG 2.1 AA

- Focus style: `2px solid var(--clr-accent)`, `outline-offset: 2px`
- All icon-only buttons must have `aria-label`
- Keyboard navigation must work for all interactive elements
