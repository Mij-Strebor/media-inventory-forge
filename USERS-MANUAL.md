# Media Inventory Forge - User's Manual
**Version 4.1.0**

![Media Inventory Forge](https://img.shields.io/badge/WordPress-Plugin-blue.svg) ![Version](https://img.shields.io/badge/version-4.1.0-blue.svg) ![License](https://img.shields.io/badge/license-GPL%20v2%2B-green.svg)

---

## Table of Contents

1. [Introduction](#introduction)
2. [Getting Started](#getting-started)
3. [Core Features](#core-features)
4. [Interface Overview](#interface-overview)
5. [Scanning Your Media Library](#scanning-your-media-library)
6. [Understanding Results](#understanding-results)
7. [Table View & Filtering](#table-view--filtering)
8. [Unused Media Detection](#unused-media-detection)
9. [Exporting Data](#exporting-data)
10. [Real-World Use Cases](#real-world-use-cases)
11. [Best Practices](#best-practices)
12. [Troubleshooting](#troubleshooting)
13. [Technical Reference](#technical-reference)

---

## Introduction

### What is Media Inventory Forge?

Media Inventory Forge (MIF) is a professional WordPress plugin that provides comprehensive analysis and reporting of your media library. It's a **read-only** tool designed to help you understand your media assets without modifying any files.

### Key Principles

**Read-Only Analysis**
- MIF scans and reports on your media files
- It does NOT modify, compress, resize, or delete files
- All data is for planning and decision-making

**Comprehensive Inventory**
- Detailed categorization by file type
- Storage usage breakdowns
- WordPress size analysis
- Usage tracking across your site

**Professional Insights**
- Identify unused media
- Plan optimization strategies
- Document storage patterns
- Export data for external analysis

### Who Should Use MIF?

**WordPress Developers**
- Conduct media audits for client sites
- Document storage usage in proposals
- Identify optimization opportunities
- Plan migration strategies

**Agency Teams**
- Manage multiple WordPress installations
- Standardize media workflows
- Report storage costs to clients
- Audit inherited client sites

**Site Administrators**
- Understand storage consumption
- Plan cleanup strategies
- Monitor library growth over time
- Make informed hosting decisions

**Performance Specialists**
- Gather baseline metrics
- Identify large files for optimization
- Plan CDN implementation
- Document before/after comparisons

**SEO Specialists**
- Audit image alt text (future feature)
- Identify missing metadata
- Plan image optimization campaigns
- Document media performance

---

## Getting Started

### System Requirements

**WordPress**
- WordPress 5.0 or higher
- Tested up to WordPress 6.7

**Server Requirements**
- PHP 7.4 or higher
- MySQL 5.6 or higher
- Minimum 64MB PHP memory limit (128MB recommended)
- Max execution time: 30 seconds or higher

**Browser Requirements**
- Modern browser with JavaScript enabled
- Chrome, Firefox, Safari, or Edge (latest versions)

### Installation

**Method 1: Upload via WordPress Admin**

1. Download the latest release from [GitHub](https://github.com/Mij-Strebor/media-inventory-forge/releases)
2. Navigate to **Plugins → Add New** in WordPress admin
3. Click **Upload Plugin** button
4. Choose the downloaded ZIP file
5. Click **Install Now**
6. After installation completes, click **Activate Plugin**

**Method 2: Manual Installation**

1. Download and extract the ZIP file
2. Upload the `media-inventory-forge` folder to `/wp-content/plugins/`
3. Navigate to **Plugins** in WordPress admin
4. Find "Media Inventory Forge" and click **Activate**

**Method 3: Symlink for Development**

```bash
# Windows (run as Administrator in PowerShell)
mklink /D "C:\Local Sites\yoursite\app\public\wp-content\plugins\media-inventory-forge" "E:\projects\media-inventory-forge"

# Mac/Linux
ln -s /path/to/git/repo /path/to/wordpress/wp-content/plugins/media-inventory-forge
```

### First Launch

1. After activation, find **Tools → Media Inventory** in the WordPress admin menu
2. The plugin interface opens with the Scan Controls panel
3. Your first scan will build the initial inventory
4. Subsequent scans update the database with any changes

---

## Core Features

### Comprehensive File Categorization

MIF organizes your media library into these categories:

**Images** (`.jpg`, `.jpeg`, `.png`, `.gif`, `.webp`, `.avif`)
- Most common media type
- Includes WordPress-generated sizes
- Dimension analysis
- Format distribution

**SVG Graphics** (`.svg`)
- Scalable vector graphics
- Typically small file sizes
- No thumbnail generation

**Fonts** (`.woff`, `.woff2`, `.ttf`, `.otf`, `.eot`)
- Web font files
- Custom typography assets

**Videos** (`.mp4`, `.mov`, `.avi`, `.wmv`, `.flv`)
- Video content
- Largest file sizes typically
- May have preview thumbnails

**Audio** (`.mp3`, `.wav`, `.ogg`, `.m4a`)
- Audio files and podcasts
- Moderate file sizes

**Documents** (`.doc`, `.docx`, `.xls`, `.xlsx`, `.ppt`, `.pptx`, `.txt`, `.rtf`)
- Office documents
- Text files
- Downloadable resources

**PDFs** (`.pdf`)
- Separate category for importance
- Common download format
- May have preview thumbnails

### WordPress Size Analysis

MIF analyzes WordPress-generated image sizes:

| Size Category | Dimension Range | Typical Use |
|---------------|-----------------|-------------|
| **Thumbnail** | ≤ 150px | Gallery thumbnails, admin listings |
| **Small** | 151-300px | Small featured images, widgets |
| **Medium** | 301-768px | Blog post images, responsive layouts |
| **Large** | 769-1024px | Full-width content, featured images |
| **Extra Large** | 1025-1536px | Large displays, retina screens |
| **Super Large** | > 1536px | Original uploads, print quality |

### Storage Reporting

**Total Storage Used**
- Combined size of all media files
- Broken down by category
- Includes all WordPress-generated sizes

**File Counts**
- Number of files per category
- Original files vs. generated sizes
- Unused file counts

**Distribution Analysis**
- Percentage of storage per category
- Visual pie chart representation
- Largest files identification

### Usage Tracking

**Where Media is Used**
- Posts (published, draft, scheduled)
- Pages (all statuses)
- Widgets (all sidebars)
- Custom post types
- Theme files (limited detection)

**Usage Statistics**
- Used/unused counts
- Usage frequency (2+, 5+, 10+ times)
- Direct links to edit screens

**Accuracy: ~95%**
- Detects most standard WordPress usage
- May miss dynamic theme usage
- Some widgets may not be detected

---

## Interface Overview

### Main Screen Layout

**Forge Header**
- Dramatic gradient banner with plugin branding
- Version information (subtle, non-intrusive)

**Scan Controls Panel** (Left Column)
- Start/Stop scan button
- Progress bar with real-time statistics
- Batch size configuration
- Clear cache option

**File Distribution Panel** (Right Column)
- Interactive pie chart
- Color-coded by category
- Updates after each scan

**Storage Summary**
- Total files and storage
- Category breakdowns
- Collapsible sections

**Category Sections**
- Detailed file listings
- Sortable by size, date, usage
- Individual file information
- Direct links to media library

**View Toggles**
- Card View (default): Visual cards with thumbnails
- Table View: Sortable columns, pagination

**Filter Controls**
- File type filters
- Size range filters
- Usage status filters
- Date range filters

**Export Options**
- CSV download button
- Timestamped exports
- Complete data export

### Navigation

**Accessing MIF**
- WordPress Admin → Tools → Media Inventory

**Keyboard Shortcuts**
- `Tab`: Navigate between controls
- `Enter/Space`: Activate buttons
- `Escape`: Close modals/overlays

**Mobile Responsive**
- Optimized for tablets
- Touch-friendly controls
- Collapsible sections on small screens

---

## Scanning Your Media Library

### Running Your First Scan

1. Navigate to **Tools → Media Inventory**
2. Locate the **Scan Controls** panel (left side)
3. Click **Start Scan** button
4. Watch the progress bar fill in real-time
5. Review results when scan completes

**What Happens During a Scan:**
- WordPress media library is queried
- Each file is analyzed for metadata
- Usage is tracked across posts/pages/widgets
- Database is updated with findings
- Statistics are calculated
- Display is updated automatically

### Scan Settings

**Batch Size** (Default: 10 files)
- Number of files processed per request
- Lower values = safer for shared hosting
- Higher values = faster scans on dedicated servers

**Recommended Settings:**
- **Shared Hosting**: 5-10 files
- **VPS/Cloud**: 20-50 files
- **Dedicated Server**: 50-100 files

**Timeout Management**
- Each batch has a 30-second limit
- Progress saved after each batch
- Can resume interrupted scans
- No data loss on timeout

### Understanding Scan Progress

**Progress Indicators:**
```
Scanning... 234 / 1,247 files processed (18.8%)
[████████░░░░░░░░░░░░░░░░░░░░░░] 18.8%

Processed: 234 files
Remaining: 1,013 files
Estimated Time: 2 minutes
```

**Status Messages:**
- "Initializing scan..." - Database setup
- "Scanning media library..." - Active processing
- "Scan complete!" - Finished successfully
- "Scan paused" - User stopped scan
- "Scan error" - Issue encountered (details shown)

### Resuming Interrupted Scans

If a scan is interrupted:
1. Click **Start Scan** again
2. Scan resumes from last completed batch
3. No need to start over
4. Previous data is preserved

### Refresh vs. Clear Cache

**Refresh Scan** (Recommended)
- Updates existing data
- Adds new files
- Removes deleted files
- Maintains usage history
- Fast incremental update

**Clear Cache → Scan**
- Completely rebuilds database
- Useful after major library changes
- Resets all usage data
- Takes full scan time

---

## Understanding Results

### Storage Summary Interpretation

**Example Output:**
```
Total Media Library Storage
────────────────────────────
Total Files: 1,247 items
Total Storage: 127.4 MB

Category Breakdown:
├── Images: 945 files (89.3 MB) - 70%
├── PDFs: 156 files (24.1 MB) - 19%
├── Videos: 23 files (12.8 MB) - 10%
└── Other: 123 files (1.2 MB) - 1%
```

**What This Tells You:**
- **Total Files**: Number of items in media library
- **Total Storage**: Disk space consumed
- **Category %**: Distribution of storage use
- **File Counts**: Number of files per type

### WordPress Size Analysis

**Example Output:**
```
WordPress Image Sizes
─────────────────────
Thumbnails (≤150px): 245 files = 1.8 MB
Small (151-300px): 89 files = 3.2 MB
Medium (301-768px): 156 files = 12.4 MB
Large (769-1024px): 67 files = 8.9 MB
Extra Large (1025-1536px): 34 files = 15.2 MB
Super Large (>1536px): 23 files = 45.6 MB
```

**Insights:**
- **Thumbnail Heavy**: Good for galleries
- **Super Large Files**: Consider optimization
- **Missing Sizes**: Theme may register custom sizes
- **Even Distribution**: Well-balanced uploads

### Category Details

Each category section shows:

**File Information:**
- Filename
- Upload date
- File size
- Dimensions (images)
- Usage count
- Status (used/unused)

**Sortable Columns:**
- Click column header to sort
- Toggle ascending/descending
- Visual arrow indicators

**Quick Actions:**
- View in media library
- See usage locations
- Filter by similar files

### File Distribution Pie Chart

**Visual Breakdown:**
- Color-coded categories
- Percentage labels
- Interactive legend
- Hover for details

**Color Coding:**
```
Images:    Blue
SVG:       Teal
Fonts:     Purple
Videos:    Red
Audio:     Orange
Documents: Green
PDFs:      Brown
```

---

## Table View & Filtering

### Switching to Table View

1. Locate **View Toggle** above results
2. Click **Table View** button
3. Interface switches to tabular display
4. Preference saved for future visits

**Table View Features:**
- Sortable columns
- Pagination controls
- Compact information display
- Quick scanning of large datasets

### Column Sorting

**Available Columns:**
- **Title**: Alphabetical sort
- **Type**: Group by file category
- **Size**: Sort by file size
- **Date**: Sort by upload date
- **Usage**: Sort by usage count
- **Status**: Used/Unused grouping

**How to Sort:**
1. Click any column header
2. First click: Ascending order ▲
3. Second click: Descending order ▼
4. Third click: Return to default

### Pagination

**Items Per Page:**
- 50 items (default)
- 100 items
- 500 items
- View All (use with caution on large libraries)

**Navigation:**
- ← Previous page
- → Next page
- Jump to page number
- Shows current range (e.g., "1-50 of 1,247")

### Advanced Filtering

**Filter by Type:**
```
☑ Images
☐ SVG
☑ PDFs
☐ Videos
☐ Audio
☐ Documents
```

**Filter by Size:**
- Small (< 100 KB)
- Medium (100 KB - 1 MB)
- Large (1 MB - 5 MB)
- Very Large (> 5 MB)
- Custom range (specify in KB/MB)

**Filter by Usage:**
- ☑ Show All
- ☐ Used Only
- ☐ Unused Only
- ☐ Used 2+ times
- ☐ Used 5+ times
- ☐ Used 10+ times

**Filter by Date:**
- Last 30 days
- Last 90 days
- Last year
- Older than 1 year
- Custom date range

**Combining Filters:**
```
Example: Find large, unused PDFs from last year

✓ File Type: PDFs only
✓ Size: Large (1-5 MB) or Very Large (>5 MB)
✓ Usage: Unused Only
✓ Date: Last year
```

**Active Filter Display:**
```
Active Filters (3):
[Type: PDFs] [x]
[Size: Large] [x]
[Usage: Unused] [x]

Showing 23 of 1,247 files
```

**Clearing Filters:**
- Click [x] on individual filter
- Click "Clear All Filters" button
- Resets to show all files

---

## Unused Media Detection

### What is Unused Media?

**Definition:**
Media files in your WordPress library that are not currently used in:
- Published posts or pages
- Draft content
- Scheduled content
- Widgets
- Custom post types
- Theme files (limited detection)

**Why This Matters:**
- Identify cleanup candidates
- Reduce storage costs
- Improve media library organization
- Plan archival strategies

### Understanding Usage Tracking

**Detection Sources:**
```
✓ Post Content (all statuses)
✓ Page Content (all statuses)
✓ Featured Images
✓ Image Galleries
✓ Text Widgets
✓ Custom Widgets
✓ Custom Post Types
✓ ACF Image Fields (if ACF active)
~ Theme Files (partial detection)
```

**Accuracy: ~95%**
- Detects standard WordPress usage patterns
- May miss dynamic theme insertions
- Some custom widgets may not be detected
- Shortcodes are analyzed

**False Positives:**
- Files used in theme templates dynamically
- Files referenced via custom code
- Files used in plugins with custom storage
- Files in inactive widgets/menus

### Finding Unused Media

**Method 1: Usage Filter**
1. Switch to Table View
2. Enable "Unused Only" filter
3. Review results
4. Cross-reference before deleting

**Method 2: Sort by Usage**
1. Click "Usage" column header
2. Files with 0 uses appear first
3. Review individually
4. Verify usage before action

**Method 3: Usage Badges**
```
Filename.jpg [Used 0×]  ← Unused
Filename.jpg [Used 1×]  ← Used once
Filename.jpg [Used 5×]  ← Frequently used
```

### Viewing Usage Locations

**For Used Files:**
1. Click on file in results
2. "Usage Locations" panel appears
3. See exact posts/pages/widgets
4. Direct links to edit screens

**Example Display:**
```
Usage Locations for header-image.jpg

Posts (2):
├── "Welcome to Our Site" (Published)
│   └── Edit Post ↗
└── "About Our Company" (Published)
    └── Edit Post ↗

Pages (1):
└── "Homepage" (Published)
    └── Edit Page ↗

Total Uses: 3
```

### Safe Cleanup Workflow

**Before Deleting Unused Media:**

1. **Export the List**
   - Run unused filter
   - Export to CSV
   - Keep backup record

2. **Verify Accuracy**
   - Manually check 5-10 files
   - Search post content
   - Check theme files
   - Review recent changes

3. **Start Small**
   - Delete 10-20 files first
   - Test site thoroughly
   - Check front-end display
   - Verify no broken images

4. **Document Changes**
   - Note deletion date
   - Record file count
   - Save exported CSV
   - Keep before/after stats

5. **Monitor Site**
   - Check for broken images
   - Review site functionality
   - Monitor visitor reports
   - Keep backups for 30 days

**Recommended Approach:**
```
Week 1: Export unused list, verify 20% manually
Week 2: Delete obviously unused files (old drafts)
Week 3: Test site thoroughly, monitor errors
Week 4: Delete additional files if no issues
Week 5: Final cleanup, update documentation
```

---

## Exporting Data

### CSV Export Functionality

**What's Included:**
- Filename
- File type/extension
- File size (bytes)
- Upload date
- Dimensions (images)
- Usage count
- Usage locations
- WordPress sizes (images)

**Export Button Location:**
- Bottom of results section
- Always visible when data exists
- Timestamped filename

### Export Process

1. Click **Export to CSV** button
2. File downloads automatically: `media-inventory-YYYY-MM-DD-HHMMSS.csv`
3. Open in spreadsheet software
4. Analyze, filter, or share data

### Working with Exported Data

**Microsoft Excel:**
1. Open CSV file
2. Use "Text to Columns" if needed
3. Create pivot tables
4. Generate custom charts

**Google Sheets:**
1. File → Import → Upload CSV
2. Use built-in filters
3. Share with team members
4. Create custom reports

**Database Import:**
1. Clean column headers
2. Map data types
3. Import to MySQL/PostgreSQL
4. Run custom queries

### Common Export Use Cases

**Client Reporting:**
```
Generate monthly media reports:
1. Export CSV
2. Filter by upload date (last month)
3. Calculate storage added
4. Create summary charts
5. Include in client report
```

**Audit Documentation:**
```
Site audit workflow:
1. Export before optimization
2. Perform cleanup/optimization
3. Export after optimization
4. Compare files deleted
5. Calculate storage saved
```

**Migration Planning:**
```
Pre-migration inventory:
1. Export complete library
2. Identify large files
3. Plan CDN migration
4. Document file locations
5. Verify post-migration
```

### Advanced CSV Analysis

**Excel Formulas:**
```excel
# Total storage in MB
=SUM(C2:C1000)/1024/1024

# Average file size
=AVERAGE(C2:C1000)

# Files larger than 1MB
=COUNTIF(C2:C1000,">1048576")

# Unused files count
=COUNTIF(F2:F1000,"0")
```

**Pivot Table Ideas:**
- Storage by month uploaded
- Usage frequency distribution
- File type percentages
- Large file identification

---

## Real-World Use Cases

### Use Case 1: Client Site Audit

**Scenario:**
Agency taking over management of an existing WordPress site. Need to document current state and identify optimization opportunities.

**MIF Workflow:**

1. **Initial Scan**
   ```
   Navigate: Tools → Media Inventory
   Action: Start Scan
   Wait: Complete scan (3-5 minutes for 2,000 files)
   ```

2. **Documentation**
   ```
   Export: CSV for baseline records
   Screenshot: Storage summary
   Note: 2,147 files, 347 MB total
   ```

3. **Analysis**
   ```
   Finding: 456 unused files (89 MB)
   Finding: 67 files over 1 MB (156 MB total)
   Finding: 234 PNG files (should be JPEG/WEBP)
   ```

4. **Reporting**
   ```
   Client Report Section:
   ├── Current State: 347 MB storage
   ├── Optimization Potential: 89 MB unused files
   ├── Format Updates: 234 PNG → WEBP conversion
   └── Projected Savings: ~40% storage reduction
   ```

5. **Planning**
   ```
   Phase 1: Remove unused files (89 MB)
   Phase 2: Convert PNG to WEBP (estimate 50% savings)
   Phase 3: Compress large JPEGs (estimate 30% savings)
   Timeline: 3 months, quarterly reviews
   ```

**Result:**
Professional audit documentation, clear optimization roadmap, measurable goals for client.

### Use Case 2: eCommerce Product Image Cleanup

**Scenario:**
WooCommerce store with 5,000+ products. Many discontinued products remain in media library. Need to identify safe deletions.

**MIF Workflow:**

1. **Filter Unused Product Images**
   ```
   View: Table View
   Filter: Type = Images
   Filter: Usage = Unused Only
   Filter: Date = Older than 1 year
   Result: 1,234 files identified
   ```

2. **Verify Results**
   ```
   Sample Check: Review 50 random files
   Check: Search product SKUs in WooCommerce
   Verify: Not in any product variations
   Confirm: Not used in category pages
   ```

3. **Safe Deletion Strategy**
   ```
   Week 1: Delete images from discontinued products (verify in WooCommerce first)
   Week 2: Monitor 404 errors, customer reports
   Week 3: Delete additional verified unused images
   Week 4: Final verification, update documentation
   ```

4. **Documentation**
   ```
   Before: 5,247 files, 1.2 GB
   Deleted: 1,156 files, 487 MB
   After: 4,091 files, 713 MB
   Savings: 40.6% storage reduction
   ```

**Result:**
Clean media library, reduced hosting costs, faster backup times, improved site performance.

### Use Case 3: Blog Migration to CDN

**Scenario:**
High-traffic blog planning CloudFlare or AWS CloudFront CDN implementation. Need inventory of all media for migration planning.

**MIF Workflow:**

1. **Comprehensive Inventory**
   ```
   Run: Full media scan
   Export: Complete CSV
   Document: Current URLs for all files
   ```

2. **Categorize for CDN**
   ```
   Large Files (>1 MB): Priority for CDN
   Frequently Used: High cache priority
   Infrequently Used: Standard cache
   Unused: Consider archiving
   ```

3. **Migration Planning**
   ```
   Phase 1: Images >500 KB (immediate CDN)
   Phase 2: All images (full CDN)
   Phase 3: Videos and PDFs
   Phase 4: Fonts and documents
   ```

4. **URL Mapping**
   ```
   Create CSV with:
   ├── Original URL
   ├── CDN URL
   ├── File size
   ├── Usage count
   └── Migration priority
   ```

5. **Post-Migration Verification**
   ```
   Re-scan: Verify file access
   Compare: Before/after load times
   Check: Usage tracking still accurate
   Document: Performance improvements
   ```

**Result:**
Organized CDN migration, documented URL changes, performance benchmarks, complete audit trail.

### Use Case 4: WordPress Multisite Network Audit

**Scenario:**
University with 50 WordPress subsites in multisite network. Need to audit media usage across all sites.

**MIF Workflow:**

1. **Per-Site Scanning**
   ```
   For each subsite:
   ├── Switch to site
   ├── Run MIF scan
   ├── Export CSV
   └── Document results
   ```

2. **Aggregate Analysis**
   ```
   Combine all CSV exports
   Total Files: 45,678
   Total Storage: 12.4 GB
   Identify: Duplicate files across sites
   ```

3. **Cross-Site Findings**
   ```
   Finding: Same logo uploaded 47 times
   Finding: 2,345 duplicate event photos
   Finding: 8 sites over 1 GB storage
   Finding: 12,456 unused files (3.2 GB)
   ```

4. **Optimization Plan**
   ```
   Solution: Central media library for shared assets
   Solution: Network-wide image optimization
   Solution: Site-specific cleanup campaigns
   Timeline: 6 months network-wide optimization
   ```

5. **Ongoing Monitoring**
   ```
   Monthly: Scan top 10 sites by storage
   Quarterly: Full network audit
   Annual: Comprehensive optimization review
   Document: Storage trends over time
   ```

**Result:**
Network-wide visibility, identified duplicate files, standardized media practices, reduced storage costs.

### Use Case 5: News Site Performance Optimization

**Scenario:**
High-traffic news site with 10+ years of content. Site slow, hosting costs high. Need to identify optimization opportunities.

**MIF Workflow:**

1. **Baseline Inventory**
   ```
   Total Files: 34,567
   Total Storage: 8.7 GB
   Avg File Size: 251 KB
   ```

2. **Identify Problem Areas**
   ```
   Filter: Files > 1 MB
   Result: 456 files = 2.1 GB (24% of total)
   
   Filter: PNG files
   Result: 5,678 files = 3.4 GB (39% of total)
   
   Filter: Unused, older than 2 years
   Result: 8,234 files = 1.9 GB (22% of total)
   ```

3. **Optimization Strategy**
   ```
   Priority 1: Convert PNG to WEBP (3.4 GB)
   Priority 2: Archive old unused files (1.9 GB)
   Priority 3: Compress large JPEGs (2.1 GB)
   Estimated Savings: 60% (5.2 GB)
   ```

4. **Implementation Plan**
   ```
   Month 1: Convert recent PNGs to WEBP
   Month 2: Archive unused files (>2 years old)
   Month 3: Implement lazy loading for all images
   Month 4: Compress remaining large files
   Month 5: Move archived files to cold storage
   ```

5. **Performance Tracking**
   ```
   Before: 8.7 GB, 3.2s average page load
   After: 3.5 GB, 1.4s average page load
   Hosting: Downgraded plan, saved $80/month
   Backup: Faster, more reliable
   ```

**Result:**
Major performance improvement, significant cost savings, better user experience, future-proof media strategy.

### Use Case 6: Photographer Portfolio Site

**Scenario:**
Professional photographer with large RAW file uploads accidentally added to WordPress. Need to identify and replace with web-optimized versions.

**MIF Workflow:**

1. **Identify Large Files**
   ```
   Filter: Size > 5 MB
   Result: 89 files identified
   Avg Size: 12.4 MB (RAW files)
   Total: 1.1 GB wasted storage
   ```

2. **Document Current Usage**
   ```
   For each large file:
   ├── Note usage locations
   ├── Screenshot portfolio pages
   ├── Export usage CSV
   └── Create replacement checklist
   ```

3. **Prepare Replacements**
   ```
   External Process:
   ├── Export RAW files from WordPress
   ├── Process in Lightroom/Photoshop
   ├── Export as optimized JPEGs (1920px wide, 85% quality)
   ├── Average optimized size: 450 KB
   └── 96% file size reduction
   ```

4. **Replace Workflow**
   ```
   For each file:
   ├── Upload optimized version
   ├── Note new media ID
   ├── Update all usage locations
   ├── Verify display quality
   └── Delete RAW version
   ```

5. **Verification**
   ```
   Re-scan with MIF
   Before: 89 files, 1.1 GB
   After: 89 files, 40 MB
   Savings: 1.06 GB (96.4%)
   Quality: No visible difference on web
   ```

**Result:**
Dramatic storage savings, faster page loads, same visual quality, professional workflow documented.

### Use Case 7: Nonprofit Volunteer Team Management

**Scenario:**
Nonprofit with multiple volunteer content managers. Need to train new volunteers on media best practices using real data.

**MIF Workflow:**

1. **Current State Analysis**
   ```
   Run Scan: Document existing library
   Export CSV: Create training dataset
   Identify Issues:
   ├── Inconsistent naming
   ├── Duplicate uploads
   ├── Oversized images
   └── Unused legacy files
   ```

2. **Training Materials**
   ```
   Use MIF Results:
   ├── Show examples of good/bad uploads
   ├── Demonstrate size impacts
   ├── Explain unused file waste
   └── Teach export/analysis
   ```

3. **Best Practices Documentation**
   ```
   Based on MIF Data:
   ├── Max file size: 500 KB (show current violations)
   ├── Naming convention: event-date-description.jpg
   ├── Format guide: JPEG for photos, PNG for graphics
   └── Usage verification: Always tag content
   ```

4. **Ongoing Monitoring**
   ```
   Monthly Review:
   ├── Run MIF scan
   ├── Check for oversized uploads
   ├── Identify unused files
   ├── Provide volunteer feedback
   └── Update training materials
   ```

5. **Success Metrics**
   ```
   Before Training: 234 MB, 45% unused
   After 3 Months: 156 MB, 12% unused
   Improvement: 33% reduction, better organization
   ```

**Result:**
Better trained volunteers, cleaner media library, standardized practices, measurable improvements.

---

## Best Practices

### Regular Scanning Schedule

**Recommended Frequency:**

**Small Sites** (<500 files)
- Monthly scans sufficient
- After major content updates
- Before/after optimization

**Medium Sites** (500-2,000 files)
- Bi-weekly scans recommended
- After bulk uploads
- Quarterly deep audits

**Large Sites** (>2,000 files)
- Weekly scans for monitoring
- Daily during high-activity periods
- Monthly comprehensive reviews

**Enterprise Sites** (>10,000 files)
- Automated weekly scans
- Real-time monitoring integration
- Quarterly professional audits

### Media Library Hygiene

**Upload Standards:**
```
✓ Optimize before upload (use external tools)
✓ Use descriptive filenames
✓ Set reasonable max dimensions (1920-2560px)
✓ Choose appropriate formats (JPEG/PNG/WEBP)
✓ Add alt text immediately
✗ Don't upload RAW files
✗ Don't upload unnecessarily large files
✗ Don't upload duplicates
```

**Regular Maintenance:**
```
Monthly:
├── Review unused files from last month
├── Delete obvious unnecessary files
└── Update documentation

Quarterly:
├── Run comprehensive MIF scan
├── Export full inventory
├── Analyze trends
└── Plan optimizations

Annually:
├── Deep cleanup campaign
├── Review and update standards
├── Train team members
└── Document improvements
```

### Performance Considerations

**Scan Timing:**
- Run scans during low-traffic periods
- Schedule automated scans overnight
- Avoid scans during backups
- Monitor server resources

**Batch Size Optimization:**
```
Test Your Server:
1. Start with batch size 10
2. Monitor timeout errors
3. Increase gradually if stable
4. Settle on reliable size
5. Document optimal setting
```

**Memory Management:**
- Clear cache periodically
- Don't run multiple scans simultaneously
- Close unused browser tabs during scan
- Monitor PHP memory usage

### Data Security

**Exported CSV Files:**
- Store securely (may contain sensitive filenames)
- Don't commit to public repositories
- Encrypt for client delivery
- Delete after analysis complete

**Database Backups:**
- MIF uses its own database tables
- Include in regular backup schedule
- Test restoration process
- Document backup location

**Access Control:**
- Only administrator access to MIF
- Limit export capabilities if needed
- Audit who runs scans
- Monitor download activity

### Integration with Other Tools

**Optimization Workflow:**
```
1. MIF Scan (identify candidates)
   ↓
2. Image Optimization Plugin (compress)
   ↓
3. MIF Re-scan (verify results)
   ↓
4. Export comparison (document savings)
```

**Recommended Tool Combinations:**

**With ShortPixel/Imagify/EWWW:**
- Use MIF to identify large files
- Process with optimization plugin
- Re-scan to verify compression
- Export before/after comparison

**With CDN (CloudFlare/CloudFront):**
- Export MIF inventory
- Map files to CDN URLs
- Verify usage tracking post-migration
- Document performance gains

**With Backup Plugins:**
- MIF data helps size backup jobs
- Identify files to exclude from backup
- Plan incremental vs. full backups
- Optimize backup storage costs

---

## Troubleshooting

### Common Issues

**Issue: Scan Won't Start**

Symptoms:
- Button doesn't respond
- No progress bar appears
- JavaScript errors in console

Solutions:
```
1. Check browser console for errors
   - Open DevTools (F12)
   - Look for red error messages
   - Screenshot for support

2. Clear browser cache
   - Hard reload: Ctrl+Shift+R (Windows)
   - Hard reload: Cmd+Shift+R (Mac)

3. Disable conflicting plugins
   - Deactivate security plugins temporarily
   - Test with theme Twenty Twenty-Four
   - Reactivate one by one

4. Check nonce expiration
   - Refresh page
   - Try again
   - May occur after long idle time
```

**Issue: Scan Timeout Errors**

Symptoms:
- Scan stops partway through
- Error message displayed
- Progress doesn't resume

Solutions:
```
1. Reduce batch size
   - Change from 10 to 5
   - Test stability
   - Gradually increase if stable

2. Increase PHP timeout
   - Edit php.ini or .htaccess
   - Set max_execution_time = 60
   - Contact host if can't modify

3. Scan in smaller sessions
   - Run scan for 5 minutes
   - Pause, wait 1 minute
   - Resume scan
   - Repeat until complete

4. Check server resources
   - Contact hosting support
   - May need plan upgrade
   - Consider off-peak scanning
```

**Issue: Incorrect Usage Counts**

Symptoms:
- Files show as unused but are visible on site
- Usage count seems too low
- Missing usage locations

Explanations:
```
Common Causes:

1. Dynamic theme insertion
   - Theme adds images via PHP
   - Not detectable by MIF
   - Manually verify usage

2. Custom shortcodes
   - Some plugins use custom storage
   - Usage tracking may miss these
   - Check plugin documentation

3. Inactive widgets
   - Widgets in inactive sidebars count
   - Check Appearance → Widgets
   - Verify active widget areas

4. Cached results
   - Clear MIF cache
   - Re-run scan
   - Verify results update
```

**Issue: Missing Files in Results**

Symptoms:
- Known files don't appear
- File count seems low
- Categories incomplete

Solutions:
```
1. Check media library
   - Some files may not be attached
   - Uploaded outside WordPress
   - Not in media library database

2. File type not supported
   - Check if extension is recognized
   - See supported formats list
   - Contact support for additions

3. Database sync issue
   - Clear cache completely
   - Run fresh scan
   - Check for database errors

4. Permission issues
   - File not readable by PHP
   - Check file permissions (644)
   - Verify ownership
```

**Issue: CSV Export Fails**

Symptoms:
- Download doesn't start
- Empty file downloads
- Browser error message

Solutions:
```
1. Check browser settings
   - Allow pop-ups from site
   - Check download location
   - Try different browser

2. PHP output buffering
   - May conflict with export
   - Disable other plugins temporarily
   - Contact host about server config

3. Memory limit exceeded
   - Very large libraries (10,000+ files)
   - Contact host to increase PHP memory
   - Export in filtered chunks instead

4. File permissions
   - Temp directory not writable
   - Check /tmp permissions
   - Contact hosting support
```

### Performance Issues

**Slow Scan Speed**

Troubleshooting:
```
Expected Scan Times:
├── 500 files: 1-2 minutes
├── 1,000 files: 2-4 minutes
├── 5,000 files: 10-20 minutes
└── 10,000 files: 20-40 minutes

If slower than expected:

1. Check batch size (try 5 instead of 10)
2. Verify shared hosting resources
3. Scan during off-peak hours
4. Temporarily deactivate resource-heavy plugins
5. Consider VPS/cloud hosting upgrade
```

**Memory Errors**

Error Messages:
- "Allowed memory size exhausted"
- "Fatal error: Out of memory"

Solutions:
```
1. Increase PHP memory limit:
   In wp-config.php, add:
   define('WP_MEMORY_LIMIT', '256M');
   define('WP_MAX_MEMORY_LIMIT', '512M');

2. Reduce batch size to 3-5 files

3. Use database query optimization:
   - Clear old revisions
   - Optimize database tables
   - Remove orphaned metadata

4. Contact hosting:
   - Request memory limit increase
   - Consider plan upgrade
   - Ask about server optimization
```

### Browser Compatibility

**Recommended Browsers:**
- Chrome 90+
- Firefox 88+
- Safari 14+
- Edge 90+

**Known Issues:**
- IE 11: Not supported (use modern browser)
- Safari < 14: Pie chart may not render
- Firefox < 88: Some CSS features missing

### Getting Support

**Before Requesting Support:**

1. **Gather Information:**
   ```
   System Information:
   ├── WordPress version
   ├── PHP version
   ├── MIF version
   ├── Active theme
   ├── Active plugins
   ├── Hosting environment
   └── Browser/version
   ```

2. **Document the Issue:**
   ```
   Provide:
   ├── Steps to reproduce
   ├── Expected behavior
   ├── Actual behavior
   ├── Screenshots
   ├── Console errors
   └── When it started
   ```

3. **Try Basic Troubleshooting:**
   ```
   ✓ Clear browser cache
   ✓ Deactivate other plugins
   ✓ Switch to default theme
   ✓ Check browser console
   ✓ Try different browser
   ```

**Support Channels:**

**Community Support:**
- GitHub Issues: [Report bugs](https://github.com/Mij-Strebor/media-inventory-forge/issues)
- GitHub Discussions: [Ask questions](https://github.com/Mij-Strebor/media-inventory-forge/discussions)

**Professional Support:**
- Contact: [JimRWeb.com](https://jimrweb.com) for customization or consulting

---

## Technical Reference

### File Type Support

**Images:**
- `.jpg`, `.jpeg` - JPEG images
- `.png` - PNG images  
- `.gif` - GIF images
- `.webp` - WebP images
- `.avif` - AVIF images

**SVG Graphics:**
- `.svg` - Scalable Vector Graphics

**Fonts:**
- `.woff`, `.woff2` - Web Open Font Format
- `.ttf` - TrueType fonts
- `.otf` - OpenType fonts
- `.eot` - Embedded OpenType

**Videos:**
- `.mp4` - MPEG-4 video
- `.mov` - QuickTime video
- `.avi` - Audio Video Interleave
- `.wmv` - Windows Media Video
- `.flv` - Flash Video

**Audio:**
- `.mp3` - MPEG Audio Layer 3
- `.wav` - Waveform Audio
- `.ogg` - Ogg Vorbis
- `.m4a` - MPEG-4 Audio

**Documents:**
- `.doc`, `.docx` - Microsoft Word
- `.xls`, `.xlsx` - Microsoft Excel
- `.ppt`, `.pptx` - Microsoft PowerPoint
- `.txt` - Text files
- `.rtf` - Rich Text Format

**PDFs:**
- `.pdf` - Portable Document Format

### Database Schema

**Tables Created:**

`{prefix}_mif_media_inventory`
- Stores main inventory data
- File metadata and statistics
- Created/updated timestamps

`{prefix}_mif_usage_tracking`
- Tracks usage locations
- Post/page/widget associations
- Last scanned timestamp

**Data Stored:**
- Filename, size, type
- Upload date, dimensions
- Usage count, locations
- WordPress size data
- Custom metadata

**Data Retention:**
- Cleared on cache clear
- Rebuilt on full scan
- Updated on refresh scan
- Removed on plugin uninstall

### WordPress Hooks

**Actions:**
```php
// Before scan starts
do_action('mif_before_scan');

// After scan completes
do_action('mif_after_scan', $results);

// Before CSV export
do_action('mif_before_export', $data);
```

**Filters:**
```php
// Modify batch size
apply_filters('mif_batch_size', 10);

// Customize categories
apply_filters('mif_file_categories', $categories);

// Adjust export data
apply_filters('mif_export_data', $data);
```

### Performance Specifications

**Batch Processing:**
- Default: 10 files per batch
- Configurable: 1-100 files
- Timeout: 30 seconds per batch
- Resumable: Progress saved

**Memory Usage:**
- Baseline: ~32 MB
- Per file: ~10 KB
- Peak: ~64 MB (typical)
- Optimized for shared hosting

**Database Queries:**
- Batch inserts (efficient)
- Indexed lookups (fast)
- Optimized counting queries
- Minimal overhead

### Security Features

**Nonce Verification:**
- All AJAX requests verified
- Prevents CSRF attacks
- Expires after 24 hours

**Capability Checks:**
- Requires `manage_options`
- Administrator access only
- No front-end exposure

**Input Sanitization:**
- All inputs sanitized
- SQL injection prevention
- XSS attack prevention

**Output Escaping:**
- All outputs escaped
- Safe HTML rendering
- No raw data exposure

### System Requirements

**Minimum Requirements:**
```
WordPress: 5.0+
PHP: 7.4+
MySQL: 5.6+
Memory: 64 MB
Disk: 10 MB plugin size
```

**Recommended Requirements:**
```
WordPress: 6.4+
PHP: 8.0+
MySQL: 5.7+
Memory: 128 MB+
Disk: 50 MB free space
```

**Server Configuration:**
```
max_execution_time: 30+ seconds
post_max_size: 64 MB+
upload_max_filesize: 64 MB+
memory_limit: 128 MB+
```

### Version Compatibility

**WordPress Compatibility:**
- Minimum: 5.0
- Tested: Up to 6.7
- Multisite: Fully compatible

**PHP Compatibility:**
- Minimum: 7.4
- Recommended: 8.0+
- Tested: Up to 8.3

**Browser Compatibility:**
- Chrome: 90+
- Firefox: 88+
- Safari: 14+
- Edge: 90+

### Changelog Summary

**Version 4.1.0** (Current)
- Enhanced usage detection
- Improved table view sorting
- Performance optimizations
- Bug fixes

**Version 4.0.2**
- WordPress.org compliance
- Debug statement removal
- Code quality improvements

**Version 4.0.0**
- Major feature release
- Unused media detection
- Table view mode
- Advanced filtering

**Version 3.0.0**
- Forge header system
- File distribution chart
- Design system standardization

**Version 2.1.1**
- WordPress.org submission
- Compliance improvements

**Version 2.1.0**
- Complete documentation
- Code organization

**Version 2.0.0**
- Plugin conversion
- OOP architecture

---

## Glossary

**Batch Processing**
: Processing files in small groups rather than all at once, improving reliability and preventing timeouts.

**Card View**
: Visual display mode showing files as cards with thumbnails and metadata.

**CSV Export**
: Comma-Separated Values file format for data exchange with spreadsheet applications.

**Media Library**
: WordPress core feature for managing uploaded files, accessible via Media menu.

**Nonce**
: Number Used Once - WordPress security feature to prevent Cross-Site Request Forgery attacks.

**Read-Only**
: MIF only reads and analyzes files, never modifies, deletes, or optimizes them.

**Table View**
: Compact tabular display mode with sortable columns and pagination.

**Unused Media**
: Files in the media library not currently referenced in any posts, pages, or widgets.

**Usage Tracking**
: Feature that identifies where media files are used throughout the WordPress site.

**WordPress Size**
: Automatically generated image variations (thumbnail, medium, large) created by WordPress.

---

## About This Manual

**Version:** 4.1.0
**Last Updated:** November 2025
**Authors:** Jim R., Claude AI
**License:** GPL v2 or later

**Additional Resources:**
- Plugin Repository: [GitHub](https://github.com/Mij-Strebor/media-inventory-forge)

---

**Made with ❤️ for the WordPress Community**

© 2025 Media Inventory Forge. All rights reserved.