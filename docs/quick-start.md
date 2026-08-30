# Media Inventory Forge — Quick Start Guide

**Version 5.2.0**

Get your first media inventory scan running in under five minutes.

---

## What is Media Inventory Forge?

Media Inventory Forge (MIF) is a **read-only** WordPress admin tool that scans your Media Library (and, optionally, your active theme's files) and reports on what it finds: file counts, storage usage by category, WordPress-generated image sizes, and a downloadable CSV.

**MIF never modifies, moves, renames, or deletes anything.** It only reads and reports.

---

## Installation

1. Download the plugin ZIP from [GitHub Releases](https://github.com/Mij-Strebor/media-inventory-forge/releases) (or WordPress.org, once published there).
2. In your WordPress admin, go to **Plugins → Add New Plugin → Upload Plugin**.
3. Choose the ZIP file and click **Install Now**.
4. Click **Activate Plugin**.

Requires WordPress 5.0+ and PHP 8.2+.

---

## Accessing the Plugin

Go to **Tools → Media Inventory Forge** in the WordPress admin sidebar.

---

## Running Your First Scan

1. **Choose your Scan Sources.** Under "Scan Sources," **Media Library** is checked by default — leave it checked for a standard inventory. Optionally check **Active Theme** as well to also catalog media files bundled inside your current theme's folder.
2. **Choose a view mode** (optional). "Image Display Mode" at the top of Scan Controls toggles between **Card View** (default — visual, grouped cards) and **Table View** (compact, sortable table with expandable rows). You can switch this at any time, before or after scanning.
3. Click **start scan**.
4. Watch the progress bar — MIF processes your library in batches so it won't time out, even on large sites. You can click **stop scan** at any time; results gathered so far are kept.
5. When the scan finishes, results appear automatically, along with a **Summary** panel (storage by category) and a **File Distribution** pie chart.

---

## Reading Your Results

- **Summary** — total storage used per category (Images, Fonts, SVG, Videos, Audio, PDFs, Documents, Archives, Other), plus a grand total.
- **File Distribution** — a pie chart breaking down storage by category.
- **Results area** — one collapsible section per category. Click a category header to expand or collapse it.
  - **Images** get special treatment: a WordPress Size Summary (grouped by thumbnail/medium/large/etc.) plus individual image cards with thumbnails.
  - **Fonts** are grouped by font family, showing all variants (WOFF, WOFF2, TTF, etc.) together.
  - Every item scanned from your active theme (rather than the Media Library) is tagged with a **Theme:** source badge so you can tell the two apart.

Switch to **Table View** at any point for a compact, sortable alternative — click any column header to sort by that column.

---

## Finding Unused Media

Right after your scan finishes, MIF automatically runs a second pass that checks where each media item is actually used across your site — post/page content, featured images, widgets, the theme customizer (logo, header image, background image, favicon), theme CSS, and Elementor (both classic and the newer "V4 atomic" editor, including fonts registered through Elementor's font manager).

- **Card View** — every image card shows a **Uses: N** badge (or a red **Unused** badge for zero uses), plus a "Where Used" list of the exact pages/locations it appears on. SVG items get the same treatment.
- **Table View** — the Images table gets a **Uses** column (highlighted red at zero), a "Where Used" list inside each row's expanded details, and a collapsible **Unused Images** panel at the bottom listing every zero-use image at a glance.

Use this to find real cleanup candidates — but always double-check before deleting anything MIF flags as unused, since a few usage patterns (hardcoded URLs in custom code, external references) aren't detectable by any automated scan.

---

## Exporting to CSV

Once a scan completes, click **export csv**. Your browser downloads a file named `media-inventory-YYYY-MM-DD-HH-MM-SS.csv` containing every scanned item: ID, title, category, extension, MIME type, dimensions, thumbnail URL, font family, file count, and total size — ready to open in Excel or Google Sheets.

---

## What MIF Does Not Do

- It does not filter or sort results by size, usage status, or upload date in the results list — you're looking at everything the scan found (Table View sorting is limited to Title, Files, Total Size, and Uses).
- It does not compress, convert, resize, or delete files. Pair MIF's reports with a dedicated optimization plugin if you plan to act on what you find.

---

## Next Steps

For a full walkthrough of every panel and setting, see the **[User Manual](user-manual.md)**.

---

*Media Inventory Forge is part of the Jim R Forge WordPress toolkit — [jimrforge.com](https://jimrforge.com)*
