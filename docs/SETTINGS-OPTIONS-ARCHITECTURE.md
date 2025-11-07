# Settings & Options Architecture - Media Inventory Forge

**Version:** 4.0.0
**Date:** 2025-11-06
**Purpose:** Design for plugin settings/options system

---

## Overview

Users need control over:
1. **What to scan** (media types, sources)
2. **What to display** (categories, filters)
3. **How to scan** (batch size, timeout)
4. **What to exclude** (specific folders, file types)

---

## Settings Page Structure

### Main Settings Page
**Location:** `Settings → Media Inventory Forge`

**Page Sections:**
1. Scanning Options
2. Display Options
3. Media Type Filters
4. Source Filters
5. Exclusions
6. Performance Settings
7. Advanced Options

---

## 1. Scanning Options

### What to Control

**Media Types to Scan:**
```php
[
    'images' => true,       // JPG, PNG, GIF, WEBP
    'svg' => true,          // SVG files
    'pdf' => true,          // PDF documents
    'documents' => true,    // DOC, XLS, PPT
    'archives' => true,     // ZIP, RAR, TAR
    'fonts' => true,        // TTF, WOFF, OTF
    'audio' => false,       // MP3, WAV (off by default)
    'video' => false,       // MP4, MOV (off by default - performance)
]
```

**Sources to Scan:**
```php
[
    'media_library' => true,   // WordPress Media Library
    'themes' => true,          // Theme directories
    'plugins' => false,        // Plugin directories (usually not needed)
    'uploads_root' => true,    // Root uploads folder
]
```

**Usage Detection Scope:**
```php
[
    'posts_pages' => true,     // Standard posts/pages
    'custom_post_types' => true, // All CPTs
    'widgets' => true,         // Widget areas
    'customizer' => true,      // Theme customizer
    'elementor' => true,       // Elementor pages
    'css_files' => true,       // CSS background images
    'acf_fields' => false,     // ACF (when implemented)
    'woocommerce' => false,    // WooCommerce (when implemented)
]
```

### UI Design

```html
<div class="mif-settings-section">
    <h2>📋 Scanning Options</h2>

    <div class="mif-setting-group">
        <h3>Media Types to Scan</h3>
        <label>
            <input type="checkbox" name="mif_scan_types[images]" checked>
            <span class="dashicons dashicons-images-alt2"></span>
            Images (JPG, PNG, GIF, WEBP)
        </label>
        <label>
            <input type="checkbox" name="mif_scan_types[svg]" checked>
            <span class="dashicons dashicons-format-image"></span>
            SVG Files
        </label>
        <label>
            <input type="checkbox" name="mif_scan_types[pdf]" checked>
            <span class="dashicons dashicons-media-document"></span>
            PDF Documents
        </label>
        <label>
            <input type="checkbox" name="mif_scan_types[documents]" checked>
            <span class="dashicons dashicons-media-text"></span>
            Office Documents (DOC, XLS, PPT)
        </label>
        <label>
            <input type="checkbox" name="mif_scan_types[archives]" checked>
            <span class="dashicons dashicons-media-archive"></span>
            Archives (ZIP, RAR, TAR)
        </label>
        <label>
            <input type="checkbox" name="mif_scan_types[fonts]" checked>
            <span class="dashicons dashicons-editor-textcolor"></span>
            Fonts (TTF, WOFF, OTF)
        </label>
        <label>
            <input type="checkbox" name="mif_scan_types[audio]">
            <span class="dashicons dashicons-media-audio"></span>
            Audio Files (MP3, WAV)
            <span class="mif-note">May slow down scanning</span>
        </label>
        <label>
            <input type="checkbox" name="mif_scan_types[video]">
            <span class="dashicons dashicons-media-video"></span>
            Video Files (MP4, MOV)
            <span class="mif-note">May significantly slow down scanning</span>
        </label>
    </div>

    <div class="mif-setting-group">
        <h3>Sources to Scan</h3>
        <label>
            <input type="checkbox" name="mif_scan_sources[media_library]" checked disabled>
            <strong>Media Library</strong>
            <span class="mif-note">(Always enabled)</span>
        </label>
        <label>
            <input type="checkbox" name="mif_scan_sources[themes]" checked>
            Theme Directories
            <span class="mif-help" title="Scans /wp-content/themes/ for media files">?</span>
        </label>
        <label>
            <input type="checkbox" name="mif_scan_sources[plugins]">
            Plugin Directories
            <span class="mif-help" title="Usually not needed - plugin assets are rarely unused">?</span>
        </label>
    </div>

    <div class="mif-setting-group">
        <h3>Usage Detection Scope</h3>
        <label>
            <input type="checkbox" name="mif_usage_scope[posts_pages]" checked disabled>
            <strong>Posts & Pages</strong>
            <span class="mif-note">(Always enabled)</span>
        </label>
        <label>
            <input type="checkbox" name="mif_usage_scope[custom_post_types]" checked>
            Custom Post Types
        </label>
        <label>
            <input type="checkbox" name="mif_usage_scope[widgets]" checked>
            Widgets
        </label>
        <label>
            <input type="checkbox" name="mif_usage_scope[customizer]" checked>
            Theme Customizer
        </label>
        <label>
            <input type="checkbox" name="mif_usage_scope[elementor]" checked>
            Elementor Pages
        </label>
        <label>
            <input type="checkbox" name="mif_usage_scope[css_files]" checked>
            CSS Background Images
            <span class="mif-help" title="Scans CSS files for background-image declarations">?</span>
        </label>
    </div>
</div>
```

---

## 2. Display Options

### What to Control

**Default View Mode:**
```php
[
    'default_view' => 'card',  // 'card' or 'table'
]
```

**Category Display Order:**
```php
[
    'category_order' => ['Fonts', 'SVG', 'Images', 'Videos', 'Audio', 'PDFs', 'Documents', 'Archives', 'Other'],
    'collapse_categories' => false,  // Start with categories expanded
]
```

**Card Display Options:**
```php
[
    'show_thumbnails' => true,
    'show_file_counts' => true,
    'show_sizes' => true,
    'show_dimensions' => true,
    'show_source_badges' => true,
]
```

### UI Design

```html
<div class="mif-settings-section">
    <h2>🎨 Display Options</h2>

    <div class="mif-setting-group">
        <h3>Default View Mode</h3>
        <label>
            <input type="radio" name="mif_default_view" value="card" checked>
            <span class="dashicons dashicons-grid-view"></span>
            Card View
        </label>
        <label>
            <input type="radio" name="mif_default_view" value="table">
            <span class="dashicons dashicons-list-view"></span>
            Table View
        </label>
    </div>

    <div class="mif-setting-group">
        <h3>Category Display</h3>
        <label>
            <input type="checkbox" name="mif_collapse_categories">
            Start with categories collapsed
        </label>
        <div class="mif-sortable-list">
            <p><strong>Category Display Order:</strong> (Drag to reorder)</p>
            <ul id="mif-category-order" class="sortable">
                <li data-category="fonts"><span class="dashicons dashicons-menu"></span> Fonts</li>
                <li data-category="svg"><span class="dashicons dashicons-menu"></span> SVG</li>
                <li data-category="images"><span class="dashicons dashicons-menu"></span> Images</li>
                <li data-category="pdfs"><span class="dashicons dashicons-menu"></span> PDFs</li>
                <li data-category="documents"><span class="dashicons dashicons-menu"></span> Documents</li>
                <li data-category="archives"><span class="dashicons dashicons-menu"></span> Archives</li>
            </ul>
        </div>
    </div>

    <div class="mif-setting-group">
        <h3>Card Display Options</h3>
        <label>
            <input type="checkbox" name="mif_show_thumbnails" checked>
            Show thumbnails
        </label>
        <label>
            <input type="checkbox" name="mif_show_file_counts" checked>
            Show file counts (e.g., "3 files")
        </label>
        <label>
            <input type="checkbox" name="mif_show_sizes" checked>
            Show file sizes
        </label>
        <label>
            <input type="checkbox" name="mif_show_dimensions" checked>
            Show dimensions (for images)
        </label>
        <label>
            <input type="checkbox" name="mif_show_source_badges" checked>
            Show source badges (Media Library / Theme)
        </label>
    </div>
</div>
```

---

## 3. Exclusions

### What to Control

**Excluded Directories:**
```php
[
    'excluded_dirs' => [
        'wp-content/uploads/elementor/css',  // Generated CSS
        'wp-content/uploads/cache',          // Cache files
        'wp-content/uploads/backups',        // Backup files
    ]
]
```

**Excluded File Patterns:**
```php
[
    'excluded_patterns' => [
        '*-150x150.*',   // Exclude thumbnail sizes
        '*-300x300.*',
        '*.tmp',         // Temporary files
        '*.bak',         // Backup files
    ]
]
```

**Minimum File Size:**
```php
[
    'min_file_size' => 0,      // Bytes (0 = no minimum)
    'max_file_size' => 0,      // Bytes (0 = no maximum)
]
```

### UI Design

```html
<div class="mif-settings-section">
    <h2>🚫 Exclusions</h2>

    <div class="mif-setting-group">
        <h3>Excluded Directories</h3>
        <p class="description">Files in these directories will not be scanned. One per line.</p>
        <textarea name="mif_excluded_dirs" rows="5" class="large-text code">
wp-content/uploads/elementor/css
wp-content/uploads/cache
wp-content/uploads/backups
        </textarea>
    </div>

    <div class="mif-setting-group">
        <h3>Excluded File Patterns</h3>
        <p class="description">Use wildcards (*). One pattern per line.</p>
        <textarea name="mif_excluded_patterns" rows="5" class="large-text code">
*-150x150.*
*-300x300.*
*.tmp
*.bak
        </textarea>
    </div>

    <div class="mif-setting-group">
        <h3>File Size Filters</h3>
        <label>
            Minimum file size (KB):
            <input type="number" name="mif_min_file_size" value="0" min="0">
            <span class="description">0 = no minimum</span>
        </label>
        <br>
        <label>
            Maximum file size (MB):
            <input type="number" name="mif_max_file_size" value="0" min="0">
            <span class="description">0 = no maximum</span>
        </label>
    </div>
</div>
```

---

## 4. Performance Settings

### What to Control

**Batch Processing:**
```php
[
    'batch_size' => 50,          // Process 50 items at a time
    'batch_delay' => 100,        // 100ms delay between batches
    'max_execution_time' => 120, // 2 minutes per batch
]
```

**Caching:**
```php
[
    'cache_results' => true,
    'cache_duration' => 3600,    // 1 hour
]
```

### UI Design

```html
<div class="mif-settings-section">
    <h2>⚡ Performance Settings</h2>

    <div class="mif-setting-group">
        <h3>Batch Processing</h3>
        <label>
            Items per batch:
            <input type="number" name="mif_batch_size" value="50" min="10" max="200">
            <span class="description">Recommended: 50-100</span>
        </label>
        <br>
        <label>
            Delay between batches (ms):
            <input type="number" name="mif_batch_delay" value="100" min="0" max="1000">
            <span class="description">Recommended: 100-200</span>
        </label>
    </div>

    <div class="mif-setting-group">
        <h3>Caching</h3>
        <label>
            <input type="checkbox" name="mif_cache_results" checked>
            Enable result caching
        </label>
        <br>
        <label>
            Cache duration (hours):
            <input type="number" name="mif_cache_duration" value="1" min="1" max="24">
        </label>
    </div>

    <div class="mif-setting-group">
        <h3>Server Limits</h3>
        <div class="mif-server-info">
            <p><strong>Current PHP Settings:</strong></p>
            <ul>
                <li>Max execution time: <?php echo ini_get('max_execution_time'); ?>s</li>
                <li>Memory limit: <?php echo ini_get('memory_limit'); ?></li>
                <li>Upload max filesize: <?php echo ini_get('upload_max_filesize'); ?></li>
            </ul>
        </div>
    </div>
</div>
```

---

## 5. Advanced Options

### What to Control

**Debug Mode:**
```php
[
    'debug_mode' => false,
    'log_queries' => false,
    'show_scan_times' => false,
]
```

**Data Cleanup:**
```php
[
    'delete_data_on_deactivate' => false,  // Keep data when plugin deactivated
    'clear_usage_on_rescan' => true,       // Clear old usage data before new scan
]
```

### UI Design

```html
<div class="mif-settings-section">
    <h2>🔧 Advanced Options</h2>

    <div class="mif-setting-group">
        <h3>Debug Settings</h3>
        <label>
            <input type="checkbox" name="mif_debug_mode">
            Enable debug mode
            <span class="mif-help" title="Shows detailed error messages and logs">?</span>
        </label>
        <br>
        <label>
            <input type="checkbox" name="mif_log_queries">
            Log database queries
        </label>
        <br>
        <label>
            <input type="checkbox" name="mif_show_scan_times">
            Show scan timing information
        </label>
    </div>

    <div class="mif-setting-group">
        <h3>Data Management</h3>
        <label>
            <input type="checkbox" name="mif_delete_data_on_deactivate">
            Delete all data when plugin is deactivated
            <span class="mif-warning">⚠️ Cannot be undone!</span>
        </label>
        <br>
        <label>
            <input type="checkbox" name="mif_clear_usage_on_rescan" checked>
            Clear old usage data before scanning
        </label>
    </div>

    <div class="mif-setting-group">
        <h3>Reset Options</h3>
        <button type="button" class="button button-secondary" id="mif-reset-settings">
            Reset to Default Settings
        </button>
        <button type="button" class="button button-danger" id="mif-clear-all-data">
            Clear All Scan Data
        </button>
        <p class="description">⚠️ These actions cannot be undone!</p>
    </div>
</div>
```

---

## Implementation Classes

### Settings Manager Class

**Location:** `includes/admin/class-settings-manager.php`

```php
class MIF_Settings_Manager {

    private $option_name = 'mif_settings';

    /**
     * Get setting value
     */
    public function get($key, $default = null) {
        $settings = get_option($this->option_name, []);
        return isset($settings[$key]) ? $settings[$key] : $default;
    }

    /**
     * Set setting value
     */
    public function set($key, $value) {
        $settings = get_option($this->option_name, []);
        $settings[$key] = $value;
        update_option($this->option_name, $settings);
    }

    /**
     * Get all settings
     */
    public function get_all() {
        return wp_parse_args(
            get_option($this->option_name, []),
            $this->get_defaults()
        );
    }

    /**
     * Default settings
     */
    private function get_defaults() {
        return [
            // Scanning
            'scan_types' => [
                'images' => true,
                'svg' => true,
                'pdf' => true,
                'documents' => true,
                'archives' => true,
                'fonts' => true,
                'audio' => false,
                'video' => false,
            ],
            'scan_sources' => [
                'media_library' => true,
                'themes' => true,
                'plugins' => false,
            ],
            'usage_scope' => [
                'posts_pages' => true,
                'custom_post_types' => true,
                'widgets' => true,
                'customizer' => true,
                'elementor' => true,
                'css_files' => true,
            ],

            // Display
            'default_view' => 'card',
            'collapse_categories' => false,
            'show_thumbnails' => true,
            'show_file_counts' => true,
            'show_sizes' => true,
            'show_dimensions' => true,
            'show_source_badges' => true,

            // Exclusions
            'excluded_dirs' => [],
            'excluded_patterns' => [],
            'min_file_size' => 0,
            'max_file_size' => 0,

            // Performance
            'batch_size' => 50,
            'batch_delay' => 100,
            'cache_results' => true,
            'cache_duration' => 3600,

            // Advanced
            'debug_mode' => false,
            'log_queries' => false,
            'show_scan_times' => false,
            'delete_data_on_deactivate' => false,
            'clear_usage_on_rescan' => true,
        ];
    }

    /**
     * Reset to defaults
     */
    public function reset() {
        update_option($this->option_name, $this->get_defaults());
    }

    /**
     * Check if media type should be scanned
     */
    public function should_scan_type($type) {
        $scan_types = $this->get('scan_types', []);
        return isset($scan_types[$type]) && $scan_types[$type];
    }

    /**
     * Check if source should be scanned
     */
    public function should_scan_source($source) {
        $scan_sources = $this->get('scan_sources', []);
        return isset($scan_sources[$source]) && $scan_sources[$source];
    }

    /**
     * Check if directory is excluded
     */
    public function is_directory_excluded($dir) {
        $excluded = $this->get('excluded_dirs', []);
        foreach ($excluded as $pattern) {
            if (fnmatch($pattern, $dir)) {
                return true;
            }
        }
        return false;
    }
}
```

---

## Integration with Scanner

**Update Scanner to Respect Settings:**

```php
// In MIF_Scanner class
public function scan_batch($offset) {
    $settings = new MIF_Settings_Manager();

    // Skip if audio not enabled
    if (!$settings->should_scan_type('audio')) {
        // Filter out audio files
    }

    // Skip excluded directories
    if ($settings->is_directory_excluded($file_path)) {
        continue;
    }

    // Apply file size filters
    $min_size = $settings->get('min_file_size', 0) * 1024; // Convert KB to bytes
    if ($file_size < $min_size) {
        continue;
    }
}
```

---

## Priority for Implementation

### Phase 1 (Essential - With Table View)
1. ✅ Basic settings framework
2. ✅ Media type filters (images, pdf, fonts, etc.)
3. ✅ Source filters (Media Library, Themes)
4. [ ] Save/load settings from options table

### Phase 2 (Nice to Have)
5. [ ] Exclusion patterns
6. [ ] Performance settings UI
7. [ ] Category display order
8. [ ] Advanced debug options

### Phase 3 (Future)
9. [ ] Export/Import settings
10. [ ] Settings presets
11. [ ] Per-user settings

---

**Status:** Architecture defined, ready for implementation
**Next Step:** Build settings page alongside table view
**Timeline:** 1 day for basic settings, can expand later
