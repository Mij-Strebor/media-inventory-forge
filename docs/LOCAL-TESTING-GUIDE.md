# Media Inventory Forge - Local WordPress Testing Guide

**Plugin:** Media Inventory Forge (MIF)
**Development Location:** `E:\onedrive\projects\plugins\mif`
**Test Site Location:** `C:\Users\Owner\Local Sites\site\app\public\wp-content\plugins\media-inventory-forge`

---

## Setup Overview

Media Inventory Forge uses a **symbolic link** development workflow. This allows you to:
- Edit files in your Git repository (`E:\onedrive\projects\plugins\mif`)
- See changes instantly in your Local WordPress installation
- Test in a real WordPress environment
- Keep your development files under version control

---

## Local WordPress Site Details

**Site Name:** site
**Local URL:** http://site.local (or similar - check Local app)
**Plugin Path:** `C:\Users\Owner\Local Sites\site\app\public\wp-content\plugins\media-inventory-forge`
**Admin URL:** http://site.local/wp-admin

---

## Symbolic Link Setup

### Current Configuration

**Symbolic Link:**
```
Source (Development): E:\onedrive\projects\plugins\mif
Target (WordPress):    C:\Users\Owner\Local Sites\site\app\public\wp-content\plugins\media-inventory-forge
```

**Status:** ✅ Link active and working

### How the Symlink Was Created

**Command Used:**
```bash
ln -s "E:/onedrive/projects/plugins/mif" "C:/Users/Owner/Local Sites/site/app/public/wp-content/plugins/media-inventory-forge"
```

### Verifying the Symlink Works

**Test that changes sync:**
```bash
# Create test file in development folder
echo "test" > "E:/onedrive/projects/plugins/mif/.test-file"

# Check it appears in WordPress folder
cat "C:/Users/Owner/Local Sites/site/app/public/wp-content/plugins/media-inventory-forge/.test-file"

# Clean up
rm "E:/onedrive/projects/plugins/mif/.test-file"
```

If the file appears in both locations, the symlink is working correctly.

---

## Development Workflow

### 1. Activate Plugin in WordPress

1. Open Local app and start "site" site
2. Navigate to http://site.local/wp-admin
3. Go to **Plugins** → **Installed Plugins**
4. Find **Media Inventory Forge**
5. Click **Activate**

### 2. Access Plugin Interface

Navigate to: **Tools** → **Media Inventory** in WordPress admin

### 3. Edit Files in Development Location

```bash
# Open your editor in the development folder
cd E:/onedrive/projects/plugins/mif

# Make changes to any file
# Example: Edit the main plugin file
# notepad media-inventory-forge.php
# Or use VS Code, etc.
```

### 4. Test Changes in WordPress

1. Refresh the WordPress admin page
2. For PHP changes: May need to reload page or re-activate plugin
3. For CSS/JS changes: Hard refresh browser (Ctrl+F5 or Ctrl+Shift+R)
4. For template changes: Clear any page cache

### 5. Commit Changes to Git

```bash
cd E:/onedrive/projects/plugins/mif
git add .
git commit -m "Description of changes"
```

---

## Testing with Different Media Libraries

### Test Scenarios

**Small Library (< 100 files):**
- Quick initial testing
- Verify basic functionality
- Check UI rendering

**Medium Library (100-1000 files):**
- Test batch processing
- Verify progress tracking
- Check performance

**Large Library (1000+ files):**
- Test timeout handling
- Verify memory management
- Check for PHP errors

### Creating Test Media Libraries

**Upload Sample Files:**
```bash
# From WordPress admin:
# Media → Add New → Upload files

# Or use WP-CLI if available:
# wp media import path/to/test-files/*.jpg
```

**Import from External Source:**
1. Use WordPress Importer
2. Import sample content with media
3. Use demo content generators

---

## Common Testing Tasks

### Check for PHP Errors

**Enable Debug Mode:**
```php
// In wp-config.php (Local site):
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', false);
```

**View Debug Log:**
```bash
tail -f "C:/Users/Owner/Local Sites/site/app/public/wp-content/debug.log"
```

### Check for JavaScript Errors

1. Open browser DevTools (F12)
2. Go to Console tab
3. Refresh page
4. Look for errors (red messages)

### Test Scanning Functionality

**Small Batch Test:**
1. Set batch size to 5
2. Run scan
3. Verify progress bar updates
4. Check results display

**Large Batch Test:**
1. Set batch size to 50
2. Run scan on 500+ files
3. Monitor for timeouts
4. Verify completion

### Test CSV Export

1. Complete a scan
2. Click "Export CSV"
3. Verify download starts
4. Open in Excel/Google Sheets
5. Verify data accuracy

---

## Database Reset (if needed)

**Reset Plugin Data:**

If you need to start fresh with the plugin (no data currently stored, but for future reference):

```sql
-- Access database via Local's "Database" tab
-- Or use phpMyAdmin/Adminer

-- Check for plugin options
SELECT * FROM wp_options WHERE option_name LIKE '%media_inventory%';

-- Delete if needed (when implemented)
-- DELETE FROM wp_options WHERE option_name LIKE '%media_inventory%';
```

**Reset WordPress Media Library:**

```bash
# Delete all media (USE WITH CAUTION!)
# Backup first!

# Via WP-CLI (if available):
# wp media regenerate --yes
```

---

## Troubleshooting

### Plugin Not Appearing in WordPress

**Check Symlink:**
```bash
ls -la "C:/Users/Owner/Local Sites/site/app/public/wp-content/plugins/" | grep media
```

Should show `media-inventory-forge` directory.

**Recreate Symlink:**
```bash
# Remove old link/directory
rm -rf "C:/Users/Owner/Local Sites/site/app/public/wp-content/plugins/media-inventory-forge"

# Recreate symlink
ln -s "E:/onedrive/projects/plugins/mif" "C:/Users/Owner/Local Sites/site/app/public/wp-content/plugins/media-inventory-forge"
```

### Changes Not Showing in WordPress

**For PHP Changes:**
- Deactivate and reactivate plugin
- Clear any opcode cache
- Restart Local site

**For CSS/JS Changes:**
- Hard refresh browser (Ctrl+F5)
- Clear browser cache
- Check if assets are enqueued with version parameter

**For Template Changes:**
- Clear any WordPress page cache
- Check file permissions

### White Screen / Fatal Error

**Check Debug Log:**
```bash
tail -20 "C:/Users/Owner/Local Sites/site/app/public/wp-content/debug.log"
```

**Common Causes:**
- Syntax error in PHP
- Missing file or class
- Incompatible PHP version

**Recovery:**
```bash
# Deactivate plugin via database
# Access database and run:
# UPDATE wp_options SET option_value = '' WHERE option_name = 'active_plugins';
```

---

## Other Plugins in Test Environment

**Currently Linked:**
- **Fluid Font Forge**: `/e/onedrive/projects/plugins/fff`
- **Media Inventory Forge**: `E:/onedrive/projects/plugins/mif`

**Installed (not linked):**
- **Fluid Space Forge**: Installed directly (not symlinked)

**Note:** Be careful not to activate conflicting plugins during testing.

---

## Performance Monitoring

### Check PHP Memory Usage

**In wp-config.php:**
```php
define('WP_MEMORY_LIMIT', '256M');
define('WP_MAX_MEMORY_LIMIT', '512M');
```

**Monitor During Scan:**
```php
// Add to development code temporarily:
error_log('Memory usage: ' . memory_get_usage(true) / 1024 / 1024 . ' MB');
```

### Check Execution Time

```php
// Add to development code temporarily:
$start_time = microtime(true);
// ... code to test ...
$end_time = microtime(true);
error_log('Execution time: ' . ($end_time - $start_time) . ' seconds');
```

---

## Test Data Recommendations

### Recommended Test Media Mix

**Images:**
- 50-100 JPG files (various sizes)
- 20-30 PNG files
- 5-10 GIF files
- 5-10 WEBP files (if available)
- 3-5 SVG files

**Documents:**
- 10-20 PDF files
- 5-10 Word docs (if testing)

**Videos:**
- 3-5 MP4 files (various sizes)

**Audio:**
- 3-5 MP3 files

**Total:** Aim for 100-200 files for comprehensive testing

### Creating Dummy Files (if needed)

**Generate Test Images:**
```bash
# Use ImageMagick or online placeholder generators
# Example: https://placeholder.com/
# Download various sizes: 1920x1080, 1280x720, 800x600, etc.
```

---

## Pre-Release Testing Checklist

Before each release, test in Local WordPress:

- [ ] Fresh plugin activation works
- [ ] Scan completes without errors
- [ ] No PHP warnings/errors in debug log
- [ ] No JavaScript console errors
- [ ] CSV export downloads correctly
- [ ] Pie chart renders properly
- [ ] Progress bar displays accurately
- [ ] All categories display correctly
- [ ] Storage calculations accurate
- [ ] Deactivation/reactivation works
- [ ] No database errors
- [ ] Performance acceptable (< 30s for 100 files)

---

## Useful Local WordPress Commands

**Access via Local App:**
- **Open Site Shell**: Right-click site → "Open Site Shell"
- **Database Access**: Right-click site → "Database" tab → "Adminer"
- **Site Logs**: Right-click site → "Logs" tab

**Common WP-CLI Commands (if available):**
```bash
# List all plugins
wp plugin list

# Check plugin status
wp plugin status media-inventory-forge

# Activate/deactivate
wp plugin activate media-inventory-forge
wp plugin deactivate media-inventory-forge

# Check for errors
wp plugin verify-checksums media-inventory-forge
```

---

## Notes

- **Always test major changes in Local before committing**
- **Keep Local WordPress updated** (match production PHP/WP versions)
- **Use debug mode during development**
- **Clear cache when testing CSS/JS changes**
- **Test with different PHP versions** if possible
- **Document any Local-specific issues** encountered

---

**Last Updated:** 2025-11-04
**Symlink Status:** ✅ Active and verified
**Test Site:** Local by Flywheel - "site"
