# Media Inventory Forge v4.0.0 - Phase 1 Architecture

**Version:** 4.0.0
**Date:** 2025-11-05
**Status:** Planning
**Timeline Estimate:** 1-2 weeks (optimistic)

---

## Overview

Phase 1 implements three major feature sets:
1. **Unused Media Detection & Usage Tracking**
2. **Table View Mode with Sorting**
3. **Advanced Filtering System**

This document outlines the technical architecture, database schema, file structure, and implementation approach.

---

## Table of Contents

1. [Feature 1: Unused Media Detection](#feature-1-unused-media-detection)
2. [Feature 2: Table View Mode](#feature-2-table-view-mode)
3. [Feature 3: Filtering System](#feature-3-filtering-system)
4. [Database Schema](#database-schema)
5. [File Structure](#file-structure)
6. [Implementation Order](#implementation-order)
7. [Testing Strategy](#testing-strategy)

---

## Feature 1: Unused Media Detection

### Core Functionality

**Goal:** Scan WordPress content to find where media is used and identify unused media.

### Scanning Targets

**WordPress Core Content:**
```php
// Posts and Pages
- post_content (look for attachment URLs and IDs)
- post_meta (especially _thumbnail_id for featured images)

// Custom Post Types
- Same as posts/pages
- Include all public post types

// Widgets
- Scan widget data in options table
- Look for image widgets, gallery widgets

// Menus
- Scan nav_menu_item posts
- Check for image menu items

// Theme Customizer
- theme_mods_{theme_name} option
- custom_logo, header_image, background_image
```

**Page Builders (If Detected):**
```php
// Elementor
- _elementor_data post meta
- Parse JSON for image URLs/IDs

// WPBakery
- Look for [vc_single_image] shortcodes
- Parse post_content for builder data

// Gutenberg Blocks
- Parse block comments in post_content
- wp:image, wp:gallery, wp:cover, wp:media-text blocks
```

**Advanced Scanning:**
```php
// Shortcodes
- [gallery ids="1,2,3"]
- [caption] with img tags
- Custom shortcodes with image parameters

// ACF Fields (If detected)
- Scan postmeta for ACF image fields
- field_* meta keys with attachment IDs

// WYSIWYG Content
- <img src="..."> tags in content
- Background images in style attributes
```

### Implementation Classes

**New Class: `MIF_Usage_Scanner`**

Location: `includes/core/class-usage-scanner.php`

```php
class MIF_Usage_Scanner {
    /**
     * Scan all content for media usage
     *
     * @param int $attachment_id Optional specific attachment to scan for
     * @return array Usage data
     */
    public function scan_media_usage($attachment_id = null);

    /**
     * Scan posts and pages
     */
    private function scan_posts();

    /**
     * Scan featured images
     */
    private function scan_featured_images();

    /**
     * Scan widgets
     */
    private function scan_widgets();

    /**
     * Scan theme customizer
     */
    private function scan_theme_mods();

    /**
     * Scan Gutenberg blocks
     */
    private function scan_gutenberg_blocks($content);

    /**
     * Scan shortcodes
     */
    private function scan_shortcodes($content);

    /**
     * Detect and scan page builders
     */
    private function scan_page_builders();

    /**
     * Get attachment ID from URL
     */
    private function url_to_attachment_id($url);
}
```

**New Class: `MIF_Usage_Database`**

Location: `includes/core/class-usage-database.php`

```php
class MIF_Usage_Database {
    /**
     * Store usage data for an attachment
     */
    public function store_usage($attachment_id, $usage_data);

    /**
     * Get usage data for an attachment
     */
    public function get_usage($attachment_id);

    /**
     * Get all unused media
     */
    public function get_unused_media();

    /**
     * Clear all usage data (for rescan)
     */
    public function clear_usage_data();

    /**
     * Get usage statistics
     */
    public function get_usage_stats();
}
```

### Data Structure

**Usage Data Array:**
```php
[
    'attachment_id' => 123,
    'used' => true,
    'usage_count' => 3,
    'locations' => [
        [
            'type' => 'post',           // post, page, widget, customizer, shortcode
            'id' => 456,                // Post ID or widget ID
            'title' => 'Welcome Post',  // Post title or widget name
            'context' => 'featured_image', // featured_image, content, gallery, etc.
            'edit_url' => 'post.php?post=456&action=edit',
            'view_url' => 'https://example.com/welcome',
            'found_at' => '2025-11-05 10:30:00'
        ],
        [
            'type' => 'page',
            'id' => 789,
            'title' => 'About Us',
            'context' => 'content',
            'edit_url' => 'post.php?post=789&action=edit',
            'view_url' => 'https://example.com/about',
            'found_at' => '2025-11-05 10:30:01'
        ]
    ],
    'last_scanned' => '2025-11-05 10:30:05'
]
```

### UI Components

**Usage Status Badge:**
```html
<span class="mif-usage-badge mif-usage-used">
    Used in 3 places
</span>

<span class="mif-usage-badge mif-usage-unused">
    ⚠️ Not Used
</span>
```

**Usage Details Dropdown:**
```html
<div class="mif-usage-details">
    <button class="mif-toggle-usage">
        Show usage locations ▼
    </button>
    <div class="mif-usage-list" style="display:none;">
        <ul>
            <li>
                <strong>Post:</strong> Welcome Post
                <span class="context">(Featured Image)</span>
                <a href="post.php?post=456&action=edit">Edit</a>
                <a href="https://example.com/welcome">View</a>
            </li>
            <li>
                <strong>Page:</strong> About Us
                <span class="context">(Content)</span>
                <a href="post.php?post=789&action=edit">Edit</a>
                <a href="https://example.com/about">View</a>
            </li>
        </ul>
    </div>
</div>
```

**Filter for Unused Media:**
```html
<div class="mif-usage-filter">
    <label>
        <input type="checkbox" id="mif-filter-unused">
        Show only unused media
    </label>
</div>
```

---

## Feature 2: Table View Mode

### Core Functionality

**Goal:** Provide an alternative tabular view with sortable columns.

### View Toggle

**Settings Storage:**
```php
// Store in user meta
update_user_meta(get_current_user_id(), 'mif_view_mode', 'table'); // or 'card'
update_user_meta(get_current_user_id(), 'mif_sort_column', 'name');
update_user_meta(get_current_user_id(), 'mif_sort_direction', 'asc');
```

**UI Toggle:**
```html
<div class="mif-view-toggle">
    <button class="mif-view-btn active" data-view="card">
        <span class="dashicons dashicons-grid-view"></span> Card View
    </button>
    <button class="mif-view-btn" data-view="table">
        <span class="dashicons dashicons-list-view"></span> Table View
    </button>
</div>
```

### Table Structure

**Using WordPress `WP_List_Table` Class:**

Location: `includes/admin/class-media-list-table.php`

```php
class MIF_Media_List_Table extends WP_List_Table {

    public function get_columns() {
        return [
            'cb' => '<input type="checkbox" />',
            'thumbnail' => 'Thumbnail',
            'file_name' => 'File Name',
            'type' => 'Type',
            'size' => 'Size',
            'dimensions' => 'Dimensions',
            'upload_date' => 'Upload Date',
            'usage' => 'Usage',
            'actions' => 'Actions'
        ];
    }

    public function get_sortable_columns() {
        return [
            'file_name' => ['file_name', false],
            'type' => ['type', false],
            'size' => ['size', false],
            'upload_date' => ['upload_date', false],
            'usage' => ['usage', false]
        ];
    }

    public function prepare_items() {
        // Get media data
        // Apply sorting
        // Apply pagination
        // Set $this->items
    }

    public function column_default($item, $column_name) {
        // Render column content
    }

    public function column_thumbnail($item) {
        // Show thumbnail
    }

    public function column_usage($item) {
        // Show usage badge and details
    }
}
```

**Table HTML:**
```html
<table class="wp-list-table widefat fixed striped mif-media-table">
    <thead>
        <tr>
            <th><input type="checkbox" /></th>
            <th>Thumbnail</th>
            <th class="sortable asc">
                <a href="#" data-sort="name">
                    File Name
                    <span class="sorting-indicator"></span>
                </a>
            </th>
            <th class="sortable">
                <a href="#" data-sort="size">
                    Size
                    <span class="sorting-indicator"></span>
                </a>
            </th>
            <th>Type</th>
            <th>Usage</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <!-- Rows via AJAX -->
    </tbody>
</table>
```

### Pagination

**Pagination Controls:**
```html
<div class="tablenav bottom">
    <div class="alignleft actions">
        <label>Items per page:
            <select id="mif-items-per-page">
                <option value="25">25</option>
                <option value="50" selected>50</option>
                <option value="100">100</option>
                <option value="500">500</option>
            </select>
        </label>
    </div>
    <div class="tablenav-pages">
        <span class="displaying-num">150 items</span>
        <span class="pagination-links">
            <a class="first-page">«</a>
            <a class="prev-page">‹</a>
            <span class="paging-input">
                <input type="text" value="1" size="2"> of
                <span class="total-pages">3</span>
            </span>
            <a class="next-page">›</a>
            <a class="last-page">»</a>
        </span>
    </div>
</div>
```

### AJAX Sorting

**JavaScript:**
```javascript
// Sort table via AJAX
jQuery('.mif-media-table th.sortable a').on('click', function(e) {
    e.preventDefault();
    var column = jQuery(this).data('sort');
    var direction = jQuery(this).parent().hasClass('asc') ? 'desc' : 'asc';

    mif_load_table_data({
        sort_column: column,
        sort_direction: direction,
        page: 1
    });
});
```

**AJAX Handler:**
```php
add_action('wp_ajax_mif_get_table_data', 'mif_ajax_get_table_data');

function mif_ajax_get_table_data() {
    check_ajax_referer('mif_ajax_nonce', 'nonce');

    $sort_column = sanitize_text_field($_POST['sort_column']);
    $sort_direction = sanitize_text_field($_POST['sort_direction']);
    $page = intval($_POST['page']);
    $per_page = intval($_POST['per_page']);

    // Get sorted, paginated data
    $table = new MIF_Media_List_Table();
    $table->prepare_items();

    ob_start();
    $table->display();
    $html = ob_get_clean();

    wp_send_json_success(['html' => $html]);
}
```

---

## Feature 3: Filtering System

### Filter UI

**Filter Bar:**
```html
<div class="mif-filter-bar">
    <div class="mif-filter-group">
        <label>Type:</label>
        <select id="mif-filter-type">
            <option value="">All Types</option>
            <option value="image">Images</option>
            <option value="video">Videos</option>
            <option value="audio">Audio</option>
            <option value="document">Documents</option>
            <option value="pdf">PDF</option>
            <option value="svg">SVG</option>
        </select>
    </div>

    <div class="mif-filter-group">
        <label>Size:</label>
        <select id="mif-filter-size">
            <option value="">All Sizes</option>
            <option value="small">Small (< 100 KB)</option>
            <option value="medium">Medium (100 KB - 1 MB)</option>
            <option value="large">Large (1 MB - 5 MB)</option>
            <option value="very-large">Very Large (> 5 MB)</option>
        </select>
    </div>

    <div class="mif-filter-group">
        <label>Usage:</label>
        <select id="mif-filter-usage">
            <option value="">All Media</option>
            <option value="used">Used</option>
            <option value="unused">Unused</option>
            <option value="used-multiple">Used 2+ times</option>
            <option value="used-frequently">Used 5+ times</option>
        </select>
    </div>

    <div class="mif-filter-group">
        <label>Upload Date:</label>
        <select id="mif-filter-date">
            <option value="">All Dates</option>
            <option value="30days">Last 30 days</option>
            <option value="90days">Last 90 days</option>
            <option value="1year">Last Year</option>
            <option value="old">Older than 1 year</option>
            <option value="custom">Custom Range...</option>
        </select>
    </div>

    <div class="mif-filter-actions">
        <button id="mif-apply-filters" class="button button-primary">
            Apply Filters
        </button>
        <button id="mif-clear-filters" class="button">
            Clear
        </button>
    </div>
</div>

<div id="mif-custom-date-range" style="display:none;">
    <label>From: <input type="date" id="mif-date-from"></label>
    <label>To: <input type="date" id="mif-date-to"></label>
</div>
```

### Filter Logic

**JavaScript Filter Handler:**
```javascript
jQuery('#mif-apply-filters').on('click', function() {
    var filters = {
        type: jQuery('#mif-filter-type').val(),
        size: jQuery('#mif-filter-size').val(),
        usage: jQuery('#mif-filter-usage').val(),
        date: jQuery('#mif-filter-date').val(),
        date_from: jQuery('#mif-date-from').val(),
        date_to: jQuery('#mif-date-to').val()
    };

    mif_apply_filters(filters);
});

function mif_apply_filters(filters) {
    jQuery.ajax({
        url: ajaxurl,
        type: 'POST',
        data: {
            action: 'mif_filter_media',
            nonce: mif_ajax.nonce,
            filters: filters
        },
        success: function(response) {
            // Update display with filtered results
            jQuery('.mif-results-container').html(response.data.html);
        }
    });
}
```

**PHP Filter Handler:**
```php
add_action('wp_ajax_mif_filter_media', 'mif_ajax_filter_media');

function mif_ajax_filter_media() {
    check_ajax_referer('mif_ajax_nonce', 'nonce');

    $filters = $_POST['filters'];

    $args = [
        'post_type' => 'attachment',
        'post_status' => 'inherit',
        'posts_per_page' => -1
    ];

    // Apply type filter
    if (!empty($filters['type'])) {
        $args['post_mime_type'] = mif_get_mime_type_for_filter($filters['type']);
    }

    // Apply date filter
    if (!empty($filters['date'])) {
        $args['date_query'] = mif_get_date_query($filters['date'], $filters['date_from'], $filters['date_to']);
    }

    $query = new WP_Query($args);
    $attachments = $query->posts;

    // Apply size filter (post-query)
    if (!empty($filters['size'])) {
        $attachments = array_filter($attachments, function($att) use ($filters) {
            return mif_matches_size_filter($att, $filters['size']);
        });
    }

    // Apply usage filter (post-query)
    if (!empty($filters['usage'])) {
        $usage_db = new MIF_Usage_Database();
        $attachments = array_filter($attachments, function($att) use ($filters, $usage_db) {
            return mif_matches_usage_filter($att, $filters['usage'], $usage_db);
        });
    }

    // Render filtered results
    ob_start();
    mif_render_media_display($attachments);
    $html = ob_get_clean();

    wp_send_json_success([
        'html' => $html,
        'count' => count($attachments)
    ]);
}
```

---

## Database Schema

### Usage Tracking Table

**Table Name:** `{prefix}_mif_usage`

**Schema:**
```sql
CREATE TABLE {prefix}_mif_usage (
    id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
    attachment_id bigint(20) unsigned NOT NULL,
    usage_type varchar(50) NOT NULL,        -- post, page, widget, customizer, shortcode
    usage_id bigint(20) unsigned DEFAULT 0, -- Post ID, Widget ID, etc.
    usage_context varchar(100) DEFAULT '',  -- featured_image, content, gallery, etc.
    found_at datetime DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY attachment_id (attachment_id),
    KEY usage_type (usage_type),
    KEY usage_id (usage_id),
    KEY found_at (found_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**Create Table Function:**
```php
function mif_create_usage_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'mif_usage';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (...)";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}

register_activation_hook(MIF_PLUGIN_FILE, 'mif_create_usage_table');
```

### User Preferences

**Stored in User Meta:**
```php
// View mode
update_user_meta($user_id, 'mif_view_mode', 'table');

// Sort preferences
update_user_meta($user_id, 'mif_sort_column', 'name');
update_user_meta($user_id, 'mif_sort_direction', 'asc');

// Pagination
update_user_meta($user_id, 'mif_items_per_page', 50);

// Filter presets (for future Pro version)
update_user_meta($user_id, 'mif_filter_presets', [
    'unused_large_images' => [
        'type' => 'image',
        'size' => 'large',
        'usage' => 'unused'
    ]
]);
```

---

## File Structure

```
media-inventory-forge/
├── includes/
│   ├── core/
│   │   ├── class-scanner.php (existing - update)
│   │   ├── class-file-processor.php (existing)
│   │   ├── class-usage-scanner.php (NEW)
│   │   ├── class-usage-database.php (NEW)
│   │   └── class-filter-engine.php (NEW)
│   │
│   ├── admin/
│   │   ├── class-admin.php (existing - update)
│   │   ├── class-media-list-table.php (NEW)
│   │   └── class-ajax-handlers.php (NEW)
│   │
│   └── utilities/
│       ├── class-file-utils.php (existing)
│       └── class-url-utils.php (NEW)
│
├── assets/
│   ├── css/
│   │   ├── admin.css (existing - update)
│   │   ├── table-view.css (NEW)
│   │   └── filters.css (NEW)
│   │
│   └── js/
│       ├── admin.js (existing - update)
│       ├── table-view.js (NEW)
│       ├── filters.js (NEW)
│       └── usage-display.js (NEW)
│
├── templates/
│   └── admin/
│       ├── main-interface.php (existing - update)
│       ├── table-view.php (NEW)
│       ├── card-view.php (NEW - extract from main-interface)
│       ├── filter-bar.php (NEW)
│       └── partials/
│           ├── usage-badge.php (NEW)
│           └── usage-details.php (NEW)
│
└── media-inventory-forge.php (update with new classes)
```

---

## Implementation Order

### Week 1: Core Infrastructure

**Day 1-2: Database & Usage Scanner**
- Create database table
- Implement `MIF_Usage_Database` class
- Basic `MIF_Usage_Scanner` class structure
- Scan posts and pages only (simplest case)

**Day 3-4: Extended Scanning**
- Featured images scanning
- Widget scanning
- Theme customizer scanning
- Gutenberg blocks parsing
- Shortcode detection

**Day 5: Testing & Refinement**
- Test on various site configurations
- Performance testing with large sites
- Bug fixes and optimization

### Week 2: UI & Filtering

**Day 6-7: Table View**
- Create `MIF_Media_List_Table` class
- Implement table rendering
- Add sorting functionality
- Add pagination
- View toggle between card and table

**Day 8-9: Filtering System**
- Create filter UI
- Implement filter logic
- AJAX filter updates
- Combine filters

**Day 10: Integration & Testing**
- Integrate all features
- Update existing scanner to trigger usage scan
- Comprehensive testing
- Performance optimization
- Bug fixes

**Day 11-12: Polish & Documentation**
- CSS refinements
- JavaScript optimization
- User experience improvements
- Update documentation
- Create user guide

---

## Testing Strategy

### Unit Tests

**Usage Scanner Tests:**
```php
test_scan_posts_with_images()
test_scan_featured_images()
test_scan_widgets()
test_scan_gutenberg_blocks()
test_url_to_attachment_id()
```

**Usage Database Tests:**
```php
test_store_usage()
test_get_usage()
test_get_unused_media()
test_clear_usage_data()
```

**Filter Engine Tests:**
```php
test_filter_by_type()
test_filter_by_size()
test_filter_by_usage()
test_filter_by_date()
test_combined_filters()
```

### Integration Tests

**Full Workflow Test:**
1. Create test site with known media usage
2. Run usage scan
3. Verify correct usage detection
4. Test filtering
5. Test table view
6. Test sorting
7. Verify performance with 1000+ media items

### Manual Testing Checklist

**Browser Testing:**
- Chrome (latest)
- Firefox (latest)
- Safari (latest)
- Edge (latest)

**WordPress Versions:**
- 5.0 (minimum supported)
- 6.0
- 6.7 (latest)

**Site Configurations:**
- Fresh WordPress install
- Site with Gutenberg
- Site with Classic Editor
- Site with Elementor
- Site with WPBakery
- Multisite installation

**Media Library Sizes:**
- < 100 files
- 100-1000 files
- 1000+ files

---

## Performance Considerations

### Optimization Strategies

**Batch Processing:**
```php
// Scan in batches to avoid timeouts
function mif_scan_usage_batch($offset = 0, $limit = 100) {
    $args = [
        'post_type' => 'attachment',
        'posts_per_page' => $limit,
        'offset' => $offset
    ];

    $query = new WP_Query($args);
    $scanner = new MIF_Usage_Scanner();

    foreach ($query->posts as $attachment) {
        $scanner->scan_media_usage($attachment->ID);
    }

    return $query->found_posts > ($offset + $limit);
}
```

**Caching:**
```php
// Cache usage data for 1 hour
$usage = wp_cache_get('mif_usage_' . $attachment_id, 'mif');
if (false === $usage) {
    $usage_db = new MIF_Usage_Database();
    $usage = $usage_db->get_usage($attachment_id);
    wp_cache_set('mif_usage_' . $attachment_id, $usage, 'mif', HOUR_IN_SECONDS);
}
```

**Database Indexing:**
- Index on `attachment_id` for fast lookups
- Index on `usage_type` for filtered queries
- Index on `found_at` for date filtering

**AJAX Optimization:**
- Load only visible data (pagination)
- Debounce filter inputs
- Use transients for frequently accessed data

---

## Security Considerations

**User Capabilities:**
```php
// Only administrators can access
if (!current_user_can('manage_options')) {
    wp_die('Unauthorized');
}
```

**Nonce Verification:**
```php
// All AJAX requests
check_ajax_referer('mif_ajax_nonce', 'nonce');
```

**Input Sanitization:**
```php
$type = sanitize_text_field($_POST['type']);
$size = sanitize_text_field($_POST['size']);
$attachment_id = absint($_POST['attachment_id']);
```

**SQL Injection Prevention:**
```php
// Use $wpdb->prepare for all queries
$wpdb->query($wpdb->prepare(
    "INSERT INTO {$table} (attachment_id, usage_type) VALUES (%d, %s)",
    $attachment_id, $usage_type
));
```

---

## Next Steps

1. ✅ **Version 4.0.0 updated** (all 6 locations)
2. ✅ **Architecture documented** (this file)
3. ⏳ **Create feature branch:** `feature/unused-media-detection`
4. ⏳ **Begin implementation** (Day 1: Database & basic scanner)

---

**Status:** Ready for implementation
**Reviewed:** 2025-11-05
**Approved:** Pending
