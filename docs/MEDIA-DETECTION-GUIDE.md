# Media Detection Guide - How MIF Finds Your Media

**Version:** 4.0.0
**Audience:** Technically aware WordPress users, beta testers
**Purpose:** Understand how Media Inventory Forge detects media files and tracks their usage

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
**Button:** 🔎 **scan for usage**

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

### **Orphaned Media**
Files that exist in the uploads folder but have NO database record:
- Leftover from deleted attachments
- Manually uploaded via FTP
- Generated thumbnails for deleted images
- Old backups

**MIF finds both registered AND orphaned media.**

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

2. Scan uploads/ Directory
   → Find: Files WordPress doesn't know about (orphans)

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

4. Page Builders (NEW in v4.0!)
   └─ Elementor:
      • Parse _elementor_data JSON
      • Find image widgets
      • Find gallery widgets
      • Find background images
      • Find video posters
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

### **Method 3: JSON Parsing**
**New for page builders**

**Elementor stores data as JSON in postmeta:**
```json
{
  "elType": "widget",
  "widgetType": "image",
  "settings": {
    "image": {"id": 123}
  }
}
```

**Finds:** All Elementor images, galleries, backgrounds
**Limitation:** Requires understanding each builder's format

### **Method 4: File System Scanning**
**Finds orphaned files**

```php
// Recursively scan uploads/
$files = scandir('/wp-content/uploads/', recursive)

// Check each file
foreach ($files as $file) {
    if (!in_database($file)) {
        mark_as_orphan($file)
    }
}
```

**Finds:** Files with no database record
**Limitation:** Can be slow on large sites

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

**Scenario 4: Orphaned File**
```
✓ File: random-upload.zip
  Status: ORPHAN (not in Media Library)
  Locations: None (can't be used if WordPress doesn't know about it)
```
**Action:** Probably safe to delete

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

#### **Test 2: Full Usage Scan**
1. Click: 🔎 **scan for usage**
2. Wait for completion
3. Click: 📋 **view usage data**
4. **Expected:** Should find all media currently in use

#### **Test 3: Cross-Reference**
1. Run both scans
2. Compare inventory vs. usage
3. **Question to answer:** Are there files in inventory with NO usage entries?

#### **Test 4: Elementor Detection**
1. Edit Sample Page in Elementor
2. Add more images
3. Save
4. Run usage scan again
5. **Expected:** Should find the new images

#### **Test 5: Different Media Types**
1. Add a PDF to a post
2. Embed an audio file
3. Run usage scan
4. **Expected:** Should find PDFs and audio files being used

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

**Document Status:** Draft for Testing
**Last Updated:** 2025-11-06
**Next Review:** After beta testing feedback
