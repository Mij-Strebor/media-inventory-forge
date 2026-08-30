# Media Inventory Forge — User Manual

**Version 5.2.0**

---

## Table of Contents

1. [Introduction](#introduction)
2. [Requirements & Installation](#requirements--installation)
3. [Accessing the Plugin](#accessing-the-plugin)
4. [Interface Overview](#interface-overview)
5. [Scan Sources](#scan-sources)
6. [Running a Scan](#running-a-scan)
7. [Card View](#card-view)
8. [Table View](#table-view)
9. [Usage Tracking & Unused Media Detection](#usage-tracking--unused-media-detection)
10. [File Distribution Chart](#file-distribution-chart)
11. [Exporting to CSV](#exporting-to-csv)
12. [File Categories & Supported Types](#file-categories--supported-types)
13. [Community & Tools](#community--tools)
14. [Security](#security)
15. [Troubleshooting](#troubleshooting)
16. [Current Limitations](#current-limitations)
17. [Support](#support)

---

## Introduction

Media Inventory Forge (MIF) is a professional WordPress admin tool that scans and reports on your media library. It is **strictly read-only**: no class or function in MIF modifies, moves, renames, or deletes any file. It exists to give you the data you need before you use a separate tool to act on it (compression, cleanup, CDN migration, etc.).

MIF scans two possible sources:
- Your WordPress **Media Library** (the standard source, on by default)
- Your **active theme's** files on disk (optional)

It does not scan plugins, uploads outside the Media Library, or other themes.

---

## Requirements & Installation

**Requirements**
- WordPress 5.0 or higher (tested up to 7.1)
- PHP 8.2 or higher
- Administrator access (`manage_options` capability)

**Installation**

1. Download the plugin ZIP from [GitHub Releases](https://github.com/Mij-Strebor/media-inventory-forge/releases).
2. In WordPress admin: **Plugins → Add New Plugin → Upload Plugin**.
3. Select the ZIP and click **Install Now**, then **Activate Plugin**.

On activation, MIF creates one internal database table used to store usage-tracking results (see [Usage Tracking & Unused Media Detection](#usage-tracking--unused-media-detection)); it has no effect on your site's existing data or performance.

MIF supports WordPress Multisite network activation.

---

## Accessing the Plugin

**Tools → Media Inventory Forge** in the WordPress admin sidebar. The page is admin-only — nothing MIF does is visible on the front end of your site.

---

## Interface Overview

From top to bottom, the admin page shows:

1. **Page title and version number**
2. **About panel** (collapsible) — a short explanation of what MIF does and does not do
3. **Scan Controls** panel — view mode toggle, scan source checkboxes, and the start/stop/export buttons
4. **File Distribution** panel — pie chart, populated after a scan
5. **Inventory Results** — Card View or Table View, depending on the toggle
6. **Community & Tools** panel (collapsible) — Project Hub, Documentation links, Related Tools & Plugins, and Support Development options (see [Community & Tools](#community--tools))

---

## Scan Sources

In the Scan Controls panel:

- **Media Library** (checked by default) — scans every attachment currently in your WordPress Media Library.
- **Active Theme** (unchecked by default) — additionally scans your currently active theme's folder on disk for media files (images, fonts, PDFs, documents, archives, audio, video) and includes them in the same report, tagged with a `Theme: [theme name]` source badge.

A "select all" checkbox above the two options toggles both sources at once, and shows an indeterminate state when only one is checked.

Scans only cover the *active* theme — inactive/installed-but-not-active themes are not scanned.

---

## Running a Scan

1. Confirm your Scan Sources selection.
2. Click **start scan**.
3. MIF processes your library in batches behind the scenes (this is automatic — there is no batch-size setting to configure) so large libraries don't hit PHP execution timeouts.
4. A progress bar shows files processed out of the total.
5. Click **stop scan** at any point to halt the scan early; whatever was gathered so far remains available.
6. When the scan finishes, results render automatically, the **Summary** panel appears, the **File Distribution** chart draws, and the **export csv** button becomes available.

Each new scan replaces the previous scan's results in your browser session (and in the transient MIF uses to feed Table View) — nothing from an earlier scan is merged in automatically. Run a fresh scan any time your library has changed and you want current numbers.

---

## Card View

The default results display. Each file category (Images, Fonts, SVG, Videos, Audio, PDFs, Documents, Archives, Other Documents, Other) appears as its own collapsible section, showing item count, file count, and total size in the header.

**Images** get an enhanced layout: a "WordPress Size Summary" sub-panel groups all image files by WordPress's standard generated sizes (Thumbnails ≤150px, Small 151–300px, Medium 301–768px, Large 769–1024px, Extra Large 1025–1536px, Super Large >1536px), followed by an "Individual Image Cards" grid with thumbnails, file counts, sizes, and dimensions per image.

**Fonts** are grouped by font family (extracted from the filename/title), listing all variants — WOFF, WOFF2, TTF, OTF, EOT — together under one row, with total files and size per family.

**SVG** items show a thumbnail preview of the actual image, alongside the same Uses badge described below.

Every item's source (Media Library vs. the active theme) is shown as a small badge next to its title.

If usage tracking has completed for the current scan (see [Usage Tracking & Unused Media Detection](#usage-tracking--unused-media-detection)), each Image and SVG card additionally shows:
- A **Uses: N** badge, or a red **Unused** badge when the item was found nowhere.
- A **Where Used** list directly on the card, linking to every page/location where that item appears (or a "candidate for removal" note if it has none).

At the bottom of the Images category, a full-width **Unused Images** panel lists every zero-use image with its thumbnail, so you don't have to scroll the whole card grid looking for red badges.

Collapse/expand state for each section is remembered across page reloads (stored in your browser's local storage).

---

## Table View

Toggle "Table View" under Image Display Mode to switch the entire results area to a compact table layout instead of cards.

- Each category still has its own collapsible section.
- Rows are expandable — click a row to reveal every file variant (thumbnail sizes, font variants, etc.) associated with that item, in a nested detail table.
- Columns marked with a sort indicator (Title, Files, Total Size, and — for Images, once usage tracking has completed — Uses) are clickable to sort ascending/descending.
- Your view mode preference (Card vs. Table) is saved to your WordPress user profile and restored the next time you open the page.

For the Images category, once usage tracking has completed:
- A **Uses** column shows each item's usage count, with a red background on rows where the count is 0.
- Expanding a row reveals a **Where Used** list underneath the file-details table, matching what Card View shows on each card.
- A collapsible **Unused Images** panel appears below the Images table, listing every zero-use image with its thumbnail.

Table View requires a completed scan in the current session — it reads from the results MIF just gathered (persisted server-side for your user account), not from a separate database query. Reloading the page after closing the tab clears this and requires a fresh scan.

---

## Usage Tracking & Unused Media Detection

Immediately after each media scan finishes, MIF automatically runs a second, server-side pass to determine where every media item is actually used. You do not need to click anything separate to trigger it — it's part of the same **start scan** action.

**What gets scanned for usage:**

- Post/page content — `<img>` tags, Gutenberg image/gallery/cover/media-text/video/audio blocks, `[gallery]` and audio/video shortcodes, and direct links to media files (PDFs, videos, documents, etc.)
- Featured images (post thumbnails)
- Widgets — both dedicated image/attachment fields and free-text/HTML widget content
- Theme customizer settings — custom logo, header image, background image, and the site icon (favicon)
- Theme stylesheets (`.css` files in your active theme) and any custom CSS entered in the customizer, for `url()` background-image references
- Elementor page/kit data, for both classic Elementor (v3) and the newer "V4 atomic" editor — including images used inside any widget or section, and fonts registered through Elementor's Font Manager (`elementor_fonts_manager_fonts`), counted per page that actually applies the font family

Revision posts are excluded from every scan, so old post revisions never inflate a usage count.

**Where results show up:** the Uses badge and Where Used list on Card View, and the Uses column, red zero-use highlighting, Where Used expansion, and Unused Images panel on Table View — all described above under [Card View](#card-view) and [Table View](#table-view).

**What it does not cover:** other page builders (WPBakery, Divi, Bricks) are detected but not yet scanned for media references; media referenced only through hardcoded URLs in custom PHP/JS, or through a CDN that rewrites URLs outside your uploads path, will not be found and may be incorrectly flagged as unused. Always double-check before deleting anything flagged as unused. Usage counts and locations are not included in the CSV export.

---

## File Distribution Chart

A canvas-drawn pie chart in the "File Distribution" panel, redrawn after every scan. Each slice represents one category's share of total storage, with a color-coded legend listing category names and percentages below the chart.

---

## Exporting to CSV

Click **export csv** after a scan completes. WordPress generates and downloads a file named `media-inventory-YYYY-MM-DD-HH-MM-SS.csv` (server date/time).

**Columns included:**

| Column | Description |
|---|---|
| ID | Media Library attachment ID (0 for theme files) |
| Title | Item title |
| Category | Images, Fonts, SVG, Videos, Audio, PDFs, Documents, Archives, Other Documents, or Other |
| Extension | File extension |
| MIME Type | Detected MIME type |
| Dimensions | Width × height, for images |
| Thumbnail URL | Preview image URL, where available |
| Font Family | Extracted font family name, for font items |
| File Count | Number of files that make up this item (original + WordPress-generated sizes) |
| Total Size | Combined size in bytes |
| Total Size (Formatted) | Combined size, human-readable |
| File Details | Per-file breakdown: filename, type, dimensions, size |

Open the CSV in Excel, Google Sheets, or any spreadsheet tool for further analysis.

---

## File Categories & Supported Types

MIF assigns every scanned item to one of these categories based on MIME type:

- **Images** — any `image/*` MIME type except SVG (JPG, PNG, GIF, WEBP, ICO, BMP, etc.)
- **SVG** — `image/svg+xml`
- **Fonts** — TTF, OTF, WOFF, WOFF2, EOT
- **Videos** — any `video/*` MIME type
- **Audio** — any `audio/*` MIME type
- **PDFs** — `application/pdf`
- **Documents** — Microsoft Office formats (DOC, DOCX, XLS, XLSX, PPT, PPTX)
- **Archives** — ZIP, RAR, TAR, GZ, 7Z, BZ2 (theme scans only)
- **Other Documents** — any other `application/*` MIME type
- **Other** — anything that doesn't match the above

Plain text files are intentionally excluded from scanning — they aren't treated as WordPress media.

---

## Community & Tools

A collapsible panel at the bottom of the admin page, matching the layout used across the Jim R Forge plugin family:

- **Project Hub** — a link to [jimrforge.com](https://jimrforge.com) for documentation, updates, and the full plugin ecosystem.
- **Documentation** — direct links to this User Manual and the Quick Start Guide (PDF).
- **Related Tools & Plugins** — Fluid Font Forge, Fluid Space Forge, and Atomic Framework Forge for Elementor (all available on WordPress.org), plus in-development tools.
- **Support Development** — Buy Me a Coffee, WordPress.org Support forum, and Rate buttons.

---

## Security

- All admin actions require the `manage_options` capability (Administrator).
- Every AJAX request (scan, export, view-preference save) is verified against a WordPress nonce.
- User-submitted data is sanitized before storage; CSV output is escaped appropriately for file-download context.
- MIF has no front-end footprint — nothing it does is reachable by non-admin users or site visitors.

---

## Troubleshooting

**Scan won't start / button unresponsive**
- Open your browser's DevTools console (F12) and check for JavaScript errors.
- Hard-refresh the page (Ctrl+Shift+R / Cmd+Shift+R) to rule out a stale cached script.
- Temporarily deactivate other plugins to check for a JavaScript conflict.

**Scan stalls or times out on a large library**
- MIF batches automatically, but very large libraries (10,000+ items) on constrained hosting may still hit server-side PHP timeout or memory limits. Ask your host to raise `max_execution_time` and `memory_limit`, or scan during lower-traffic periods.

**CSV export doesn't download**
- Confirm your browser allows downloads/pop-ups from the site.
- Make sure a scan has actually completed in the current session — the CSV export button submits the in-memory scan results from that session, not a re-query of the database.

**Table View shows "Click start scan to begin" even though I already scanned**
- Table View reads from the current session's scan results. If you reload the admin page after closing the browser tab, that in-memory data is gone — run the scan again.

---

## Current Limitations

Be aware of what MIF does **not** currently offer, so you don't go looking for it:

- No filtering by file size or upload date; Table View sorting is limited to Title, Files, Total Size, and (for Images) Uses.
- No pagination controls in Table View — all results for a category load at once.
- Usage detection covers WordPress core content areas, widgets, the theme customizer, and Elementor (classic and V4 atomic) — it does not yet scan other page builders (WPBakery, Divi, Bricks) for media references.
- Usage counts and locations are not included in the CSV export.
- No keyboard shortcuts.
- No batch-size configuration in the UI — MIF sizes its own batches automatically.

---

## Support

- **GitHub Issues:** [Report bugs or request features](https://github.com/Mij-Strebor/media-inventory-forge/issues)
- **GitHub Discussions:** [Community Q&A](https://github.com/Mij-Strebor/media-inventory-forge/discussions)
- **Professional services:** [jimrforge.com](https://jimrforge.com)

---

*Media Inventory Forge is part of the Jim R Forge WordPress toolkit.*
