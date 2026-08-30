# Media Detection Guide - How MIF Finds Your Media

**Version:** 5.2.0
**Audience:** Technically aware WordPress users, beta testers
**Purpose:** Understand how Media Inventory Forge detects media files and tracks their usage

> **2026-08-30 correction pass:** This guide previously described a separate "scan for usage" / "view usage data" button and a file-system orphan scanner. Neither exists in the shipped plugin — usage detection is **automatic**, running immediately after **start scan** completes, with results shown inline in Card View and Table View (see `docs/user-manual.md`). The "Orphaned Media" / file-system-scanning content below (Method 4) describes a feature that was never built; it's kept here, clearly marked, only as a record of the original idea.

---

## Table of Contents

1. [Two Types of Detection](#two-types-of-detection)
2. [What Constitutes "Media"](#what-constitutes-media)
3. [Where Media Files Live](#where-media-files-live)
4. [How Detection Works](#how-detection-works)
5. [Detection Methods Explained](#detection-methods-explained)
6. [Combining the Data](#combining-the-data)
7. [Testing Your Installation](#testing-your-installation)

---

## Two Types of Detection

MIF performs **two separate but related tasks:**

### **1. Media Inventory Scanner** ("Regular Scanner")
**What it does:** Finds all media files that exist on your server
**What it tells you:** "These files are in your uploads folder"
**Button:** 🔍 **start scan**

### **2. Usage Detection Scanner** ("Where Used Scanner")
**What it does:** Finds where media files are being used in your content
**What it tells you:** "This file appears on these pages"
**Trigger:** Runs automatically right after **start scan** completes - there is no separate button.

### **The Relationship:**

```
Media Library (18 files total)
├─ 9 images
├─ 3 zip files
├─ 3 docx files
└─ 2 pdf files

Usage Scanner finds where these are used:
├─ Image #22: Used on Sample Page (Elementor)
├─ Image #22: Also in post content
└─ Other 7 images: Not found (not being used OR on pages we didn't scan yet)
```

**Important:** A file can exist in Media Library but NOT be used anywhere. That's how we identify "unused media" for cleanup.

---

## What Constitutes "Media"

In WordPress, "media" means files uploaded through the Media Library or stored in the uploads directory.

### **WordPress-Registered Media**
These are files WordPress knows about (have database records in `wp_posts` where `post_type = 'attachment'`):

| Type | Extensions | Examples |
|------|------------|----------|
| **Images** | jpg, jpeg, png, gif, svg, webp, ico | Photos, logos, graphics |
| **Documents** | pdf, doc, docx, xls, xlsx, ppt, pptx | Reports, spreadsheets |
| **Archives** | zip, tar, gz, rar | File bundles, backups |
| **Audio** | mp3, wav, ogg, m4a | Music, podcasts, sound effects |
| **Video** | mp4, mov, avi, wmv, mkv | Videos, tutorials |
| **Fonts** | ttf, otf, woff, woff2 | Custom fonts |

### **Orphaned Media (NOT IMPLEMENTED)**
Files that exist in the uploads folder but have NO database record - leftover from deleted attachments, manually uploaded via FTP, generated thumbnails for deleted images, old backups.

**MIF does not currently scan for these.** It only reports on files WordPress already knows about (`post_type = 'attachment'`); a true filesystem-vs-database orphan scanner is an unbuilt idea, not a shipped feature.

---

## Where Media Files Live

### **Primary Location: `/wp-content/uploads/`**

WordPress organizes uploads by year/month:

```
wp-content/uploads/
├── 2024/
│   ├── 01/
│   │   ├── logo.png
│   │   └── logo-150x150.png (thumbnail)
│   ├── 02/
│   └── 12/
├── 2025/
│   ├── 10/
│   └── 11/
└── elementor/
    └── css/
        └── post-123.css (generated files)
```

### **Other Media Locations:**

1. **Theme Files:** `/wp-content/themes/your-theme/images/`
   - Theme-bundled graphics
   - NOT in Media Library
   - MIF can optionally scan these

2. **Plugin Assets:** `/wp-content/plugins/plugin-name/assets/`
   - Plugin icons, images
   - Usually not in Media Library
   - MIF ignores these by default

3. **CSS Background Images:**
   - Referenced in theme CSS
   - May or may not be in Media Library
   - Can be external URLs

---

## How Detection Works

### **Phase 1: Find All Media (Inventory Scanner)**

The regular scanner does this:

```
1. Query WordPress Database
   SELECT * FROM wp_posts WHERE post_type = 'attachment'
   → Returns: All registered media files

2. (Not implemented) Scan uploads/ Directory for orphans
   → See "Orphaned Media (NOT IMPLEMENTED)" above

3. For Each File:
   - Read metadata (dimensions, size, type)
   - Identify file category
   - Check for variations (thumbnails, resized versions)
   - Calculate total storage used

4. Store Results in Memory
   → Creates inventory list
```

**Result:** A complete list of every media file on your site.

### **Phase 2: Find Where Used (Usage Scanner)**

The usage scanner searches:

```
1. Posts & Pages
   └─ Scan post_content for:
      • <img> tags with attachment IDs
      • Media URLs
      • Shortcodes: [gallery], [audio], [video]

2. Gutenberg Blocks
   └─ Parse block comments:
      • wp:image {"id":123}
      • wp:gallery {"ids":[1,2,3]}
      • wp:cover, wp:media-text

3. Featured Images
   └─ Check postmeta:
      • _thumbnail_id for each post

4. Page Builders
   └─ Elementor (classic v3 AND the newer "V4 atomic" editor):
      • Walk the decoded _elementor_data / _elementor_page_settings structure generically for any attachment ID or uploads URL, regardless of widget type
      • Also checks Elementor's font registry (elementor_fonts_manager_fonts) - a font counts as "used" on every page that actually applies its font-family name
   └─ Future: Bricks, WPBakery, Divi

5. Widgets
   └─ Parse widget data:
      • Image widgets
      • Text widgets with images
      • Custom widgets

6. Theme Customizer
   └─ Check theme mods:
      • custom_logo
      • header_image
      • background_image
      • site_icon (favicon - stored as its own option, checked independently of theme mods)

7. CSS Files
   └─ Scan stylesheets for:
      • background-image: url(...)
      • Only checks uploads/ URLs
```

**Result:** Database records linking each media file to where it's used.

---

## Detection Methods Explained

### **Method 1: Database Queries**
**Fast, Reliable, WordPress-native**

```sql
-- Featured Images
SELECT post_id, meta_value as attachment_id
FROM wp_postmeta
WHERE meta_key = '_thumbnail_id'
```

**Finds:** Featured images instantly
**Limitation:** Only finds what WordPress tracks in the database

### **Method 2: Content Parsing**
**Comprehensive, catches everything**

**Searches post content for patterns:**
```html
<!-- Classic Editor -->
<img class="wp-image-123" src="..." />

<!-- Gutenberg -->
<!-- wp:image {"id":123} -->

<!-- Shortcodes -->
[gallery ids="1,2,3,4"]
```

**Finds:** Images in any HTML format
**Limitation:** Slower, requires parsing every post

### **Method 3: JSON Parsing (Elementor)**

Elementor stores page/kit data as postmeta - classic v3 as flat JSON (`{"id": 123}` per field), the newer V4 atomic editor wrapping every value in a `{"$$type":...,"value":...}` envelope with `e-`-prefixed widget types. Rather than hand-enumerate every widget type's field shape, MIF walks the decoded structure generically, looking for any numeric value under a "media-like" key (image, gallery, thumbnail, logo, etc.) or a string containing an uploads-relative path.

**Finds:** All Elementor images, galleries, backgrounds, across both classic and V4 atomic
**Limitation:** Requires re-verifying against each new Elementor schema version if this generic approach ever stops matching

### **Method 4: File System Scanning (NOT IMPLEMENTED)**

The idea below (recursively walking `uploads/` to find files with no database record) was never built. MIF's inventory scanner only reports on WordPress-registered attachments.

```php
// NOT IMPLEMENTED - illustrative only
$files = scandir('/wp-content/uploads/', recursive)
foreach ($files as $file) {
    if (!in_database($file)) {
        mark_as_orphan($file)
    }
}
```

---

## Combining the Data

### **The Complete Picture:**

```
INVENTORY SCANNER          USAGE SCANNER           RESULT
─────────────────          ─────────────           ──────
18 media files       ─┬─>  Scan all content  ──>  Used: 3 files
                      │                             Unused: 15 files
                      │
                      └──> 3 usage locations found:
                            • ID 22 on Sample Page (Elementor)
                            • ID 22 on Sample Page (content)
                            • ID 22 on 404 page
```

### **How to Read the Results:**

**Scenario 1: File Found, Usage Found**
```
✓ Image: logo.png (ID: 22)
  Status: IN USE
  Locations:
    - Sample Page (Elementor widget)
    - Sample Page (post content)
```
**Action:** Keep this file - it's being used!

**Scenario 2: File Found, No Usage**
```
✓ Image: old-banner.jpg (ID: 15)
  Status: UNUSED
  Locations: None found
```
**Action:** Safe to delete (after checking)

**Scenario 3: Usage Found, File Missing**
```
✗ Post references attachment ID 99
  Status: BROKEN LINK
  Locations: About Page
```
**Action:** Fix the broken reference

**Scenario 4: Orphaned File (hypothetical - not implemented)**
```
✓ File: random-upload.zip
  Status: ORPHAN (not in Media Library)
  Locations: None (can't be used if WordPress doesn't know about it)
```
This scenario describes the unimplemented file-system scan above - MIF cannot currently detect files that exist on disk but have no Media Library record.

---

## Testing Your Installation

### **What Your Site Currently Has:**

**Media Library (WordPress knows about):**
- 9 images
- 3 zip files
- 3 docx files
- 2 pdf files
- **Total: 17 files registered**

**Usage Scanner Found:**
- Attachment 22: Used 3 times
  - Sample Page (Elementor image widget)
  - Sample Page (Elementor image widget) - different location
  - Sample Page (post content)

**This means:**
- 1 image is definitely being used
- 8 images have unknown status (need to check)
- All documents and zips have unknown status

### **Testing Steps:**

#### **Test 1: Full Inventory Scan**
1. Click: 🔍 **start scan**
2. Wait for completion
3. Check results panel
4. **Expected:** Should find ~17-18+ files (including thumbnails and variations)

#### **Test 2: Usage Detection**
Usage detection runs automatically right after Test 1 completes - there's nothing separate to click. Wait for it to finish, then check the Uses badge (Card View) or Uses column (Table View) on each image.
1. **Expected:** Should show a Uses count (or "Unused") for every image and SVG item.

#### **Test 3: Cross-Reference**
1. Compare the inventory list against the Uses badges.
2. **Question to answer:** Are there files flagged "Unused" that you know are actually in use somewhere MIF doesn't scan (see "Common Questions" below)?

#### **Test 4: Elementor Detection**
1. Edit Sample Page in Elementor
2. Add more images
3. Save
4. Run a fresh scan (**start scan**)
5. **Expected:** Should find the new images in the Uses count/Where Used list

#### **Test 5: Different Media Types**
1. Add a PDF to a post
2. Embed an audio file
3. Run a fresh scan
4. **Expected:** Should find PDFs and audio files being used (note: PDFs/audio don't currently show a Uses badge in the UI the way Images/SVG do - check via CSV or the database table if you need to confirm)

---

## Common Questions

### **"Why did usage scanner only find 1 image when I have 9?"**

Possible reasons:
1. The other 8 images aren't being used anywhere
2. They're on pages that aren't published
3. They're in content types we don't scan yet (ACF fields, WooCommerce products)
4. They're in page builders we don't support yet (Bricks, Divi, WPBakery)

### **"What's the difference between the two scanners?"**

| Inventory Scanner | Usage Scanner |
|-------------------|---------------|
| Finds files | Finds references to files |
| Looks at uploads/ folder | Looks at content/database |
| Fast | Slower (has to read all content) |
| Returns: file list | Returns: locations where used |

### **"Can a file be 'used' multiple times?"**

YES! Same image can be:
- Featured image on 3 posts
- In content on 5 pages
- In 2 Elementor sections
- As a widget image
- As site logo

Each usage location creates a separate database record.

### **"What about external images (hotlinked)?"**

External images (like `https://example.com/image.jpg`) are:
- ✅ Found by usage scanner (sees the reference)
- ❌ NOT in Media Library
- ❌ NOT in inventory scanner

**MIF focuses on YOUR media files (stored on your server).**

---

## Next Steps

After reading this guide:

1. ✅ Understand what each scanner does
2. ✅ Run both scanners on your site
3. ✅ Review the results
4. ✅ Check if any usage seems wrong or missing
5. ✅ Report findings for improvements

**Questions? Issues?** Document them - they'll help improve the detection system!

---

**Document Status:** Draft for Testing (corrected against shipped code)
**Last Updated:** 2026-08-30
**Next Review:** After beta testing feedback
