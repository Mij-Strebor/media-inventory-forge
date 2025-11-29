# Media Inventory Forge - Quick Start Guide

![Media Inventory Forge](https://img.shields.io/badge/version-4.1.0-blue.svg) ![WordPress](https://img.shields.io/badge/WordPress-5.0%2B-blue.svg)

**Get scanning your WordPress media library in under 2 minutes**

---

## What is Media Inventory Forge?

Media Inventory Forge (MIF) is a **read-only analysis tool** that scans your WordPress media library to provide comprehensive reports on file types, storage usage, and media locations. It helps you understand what you have, where storage is being consumed, and which files may need attention.

**Important:** MIF only scans and reports—it does not modify, optimize, compress, resize, or delete any files.

---

## Installation (3 Steps)

### Step 1: Download & Upload
1. Download the plugin from [GitHub Releases](https://github.com/Mij-Strebor/media-inventory-forge/releases)
2. In WordPress admin, go to **Plugins → Add New → Upload Plugin**
3. Select the downloaded ZIP file and click **Install Now**

### Step 2: Activate
Click **Activate Plugin** after installation completes

### Step 3: Access
Navigate to **Tools → Media Inventory Forge** in your WordPress admin sidebar

---

## Your First Scan (4 Simple Steps)

### Step 1: Start the Scan
1. On the Media Inventory page, you'll see the **Scan Controls** panel
2. Default settings are optimized for most sites:
   - **Batch Size:** 10 files per request
   - **Timeout:** 30 seconds per batch
   - **Sources:** Media Library (recommended for first scan)
3. Click the **Start Scan** button

### Step 2: Watch Progress
- Real-time progress bar shows scan status
- File count and completion percentage update automatically
- Scan processes in batches to avoid timeouts on large libraries
- **Tip:** Don't navigate away during the scan

### Step 3: Review Results
After scan completion, you'll see:
- **Storage Summary** with total usage by category
- **File Distribution** pie chart showing storage breakdown
- **WordPress Size Analysis** (thumbnails, medium, large, etc.)
- **Category Sections** for Images, PDFs, Videos, Audio, Documents, SVG, and Fonts

### Step 4: Explore Your Data
- Click category headers to expand/collapse details
- View individual file information including dimensions, formats, and sizes
- Switch between **Card View** and **Table View** for different perspectives
- Use filters to find specific files (type, size, date, usage status)

---

## Understanding Your Results

### Storage Summary Panel
Shows total storage usage broken down by file category:
```
Total Storage: 127.4 MB across 1,247 files

Categories:
• Images: 89.3 MB (70%) - 892 files
• Documents: 24.1 MB (19%) - 156 files  
• Videos: 12.8 MB (10%) - 89 files
• Other: 1.2 MB (1%) - 110 files
```

### File Distribution Chart
Interactive pie chart visualizes storage by file type with color-coded legend. Each slice represents a category's percentage of total storage.

### WordPress Size Categories
For images, MIF categorizes by WordPress-generated sizes:
- **Thumbnails** (≤150px): Small preview images
- **Small** (151-300px): Gallery thumbnails
- **Medium** (301-768px): Standard content images
- **Large** (769-1024px): Featured images
- **Extra Large** (1025-1536px): Hero images
- **Super Large** (>1536px): High-resolution originals

---

## View Modes

### Card View (Default)
- Visual cards for each media item
- Thumbnail previews for images
- Quick-glance file information
- Ideal for browsing and visual identification

**How to use:**
1. Click the **Card View** button at the top
2. Scroll through categorized sections
3. Expand category sections to see all files

### Table View
- Compact tabular format
- Sortable columns (click headers)
- More files visible at once
- Better for data analysis

**How to use:**
1. Click the **Table View** button at the top
2. Click column headers to sort (Title, Files, Total Size)
3. Click again to reverse sort order
4. Use pagination controls for large libraries

---

## Filtering Your Results

MIF includes powerful filtering to help you find specific files:

### Filter by Type
- Images, PDFs, Videos, Audio, Documents, SVG, Fonts
- Check multiple types to combine results

### Filter by Size
- Small (<100KB)
- Medium (100KB - 1MB)
- Large (1MB - 10MB)
- Very Large (>10MB)

### Filter by Usage Status
- Used (appears in content)
- Unused (not found in posts/pages)
- Used 2+ times
- Used 5+ times

### Filter by Upload Date
- Last 30 days
- Last 90 days
- Last year
- Older than 1 year
- Custom date range

**Tip:** Combine filters for precise searches. Example: "Show unused images larger than 1MB uploaded over a year ago"

---

## Exporting Data

### CSV Export
1. Complete a scan
2. Click the **Export CSV** button in the Scan Controls panel
3. File downloads automatically as `media-inventory-YYYY-MM-DD-HH-MM-SS.csv`

### What's Included in CSV
- Media ID and Title
- Category and File Type
- MIME Type and Dimensions
- File Count and Total Size
- Complete file details for all variations
- Thumbnail URLs (for images)
- Font family information (for fonts)

**Use Case:** Import into Excel or Google Sheets for detailed analysis, sorting, and reporting

---

## Common First-Time Questions

### Q: How long will my first scan take?
**A:** Depends on library size:
- 500 files: ~2-3 minutes
- 1,000 files: ~5-7 minutes
- 5,000 files: ~20-30 minutes
- 10,000+ files: ~45-60 minutes

**Tip:** Larger libraries may require increasing batch size or PHP timeout settings

### Q: Can I scan while users are on the site?
**A:** Yes, scanning is read-only and doesn't affect site performance or user experience.

### Q: Will this delete unused files?
**A:** No. MIF only identifies and reports. You must manually review and delete files through WordPress Media Library.

### Q: What does "unused" mean?
**A:** MIF scans posts, pages, widgets, theme customizer, and page builders to find where media appears. "Unused" means not detected in these locations. Some edge cases (like hardcoded URLs in templates) may not be detected.

### Q: Can I scan specific folders?
**A:** MIF scans the entire WordPress Media Library by default. You can filter results after scanning using the filtering controls.

### Q: Do I need to rescan regularly?
**A:** Only when your media library changes significantly. Scan results remain available until you run a new scan.

---

## Quick Tips for Best Results

### Before Scanning
- Ensure adequate server resources (recommended: 256MB PHP memory limit)
- Close other intensive admin tasks
- For very large libraries (10,000+ files), consider increasing batch size to 15-20

### During Scanning
- Keep the browser tab open and active
- Don't navigate away from the page
- Monitor for timeout errors (if they occur, reduce batch size)

### After Scanning
- Save the CSV export for records
- Review "Unused" files carefully before deletion
- Check large files (>1MB) for optimization opportunities
- Identify unnecessary WordPress size variations

### Performance Issues?
1. Reduce batch size to 5 files
2. Increase PHP max_execution_time to 60 seconds
3. Clear WordPress transients/cache
4. Scan during low-traffic periods

---

## Next Steps

### Planning Media Cleanup
1. Export CSV for offline analysis
2. Filter for unused files
3. Sort by size to find storage hogs
4. Review before deleting anything

### Identifying Optimization Opportunities
MIF shows you:
- Large uncompressed files (consider compression tools)
- Unnecessary WordPress thumbnail sizes (can be disabled)
- Legacy file formats (PNG → WebP conversion candidates)
- Duplicate or similar images

### Regular Maintenance
- Run quarterly scans to track library growth
- Review "unused" files before major updates
- Monitor storage trends over time
- Clean up after content migrations

---

## Getting Help

### Community Support
- **GitHub Issues:** [Report bugs or request features](https://github.com/Mij-Strebor/media-inventory-forge/issues)
- **GitHub Discussions:** [Community Q&A](https://github.com/Mij-Strebor/media-inventory-forge/discussions)

### Documentation
- Full User Manual (included with plugin)
- [README on GitHub](https://github.com/Mij-Strebor/media-inventory-forge)
- [WordPress.org Plugin Page](https://wordpress.org/plugins/media-inventory-forge/) (coming soon)

### Professional Services
For custom development or consulting: [JimRWeb.com](https://jimrweb.com)

---

## Keyboard Shortcuts

| Action | Shortcut |
|--------|----------|
| Toggle Card/Table View | `V` |
| Clear All Filters | `C` |
| Export CSV | `E` |
| Collapse All Categories | `Shift + -` |
| Expand All Categories | `Shift + +` |

*Note: Shortcuts work when focus is on the main content area*

---

**You're Ready to Go!**

Start by running your first scan, then explore the powerful filtering and analysis features. Remember: MIF is a read-only tool that empowers you with knowledge—what you do with that knowledge is entirely up to you.

---

**Version:** 4.1.0 | **Last Updated:** November 2024 | **License:** GPL v2+

*Made with ❤️ for the WordPress Community by [Jim R Forge](https://jimrforge.com)*