# Table View Quick Start Guide

**Priority:** HIGH - User wants this tomorrow
**Estimated Time:** 6-8 hours
**Complexity:** Medium (WordPress has good examples)

---

## What We're Building

A sortable, paginated table view as an alternative to the current card view.

**Features:**
- ✅ Toggle between Card View and Table View
- ✅ Sortable columns (Name, Type, Size, Date, Usage)
- ✅ Pagination (25, 50, 100, 500 items per page)
- ✅ AJAX sorting and filtering
- ✅ Same data as card view, different presentation

---

## Implementation Steps

### Step 1: Create Table Class (2 hours)

**File:** `includes/admin/class-media-list-table.php`

**Base on WordPress `WP_List_Table`:**
```php
class MIF_Media_List_Table extends WP_List_Table {

    public function __construct() {
        parent::__construct([
            'singular' => 'media_item',
            'plural'   => 'media_items',
            'ajax'     => true
        ]);
    }

    public function get_columns() {
        return [
            'cb'          => '<input type="checkbox" />',
            'thumbnail'   => 'Preview',
            'title'       => 'Title',
            'type'        => 'Type',
            'source'      => 'Source',
            'files'       => 'Files',
            'size'        => 'Size',
            'dimensions'  => 'Dimensions',
            'usage'       => 'Usage'
        ];
    }

    public function get_sortable_columns() {
        return [
            'title'      => ['title', false],
            'type'       => ['type', false],
            'size'       => ['size', false],
            'files'      => ['files', false]
        ];
    }

    public function prepare_items() {
        // Get data from scanner
        // Apply sorting
        // Apply pagination
        // Set $this->items
    }

    public function column_thumbnail($item) {
        // Render thumbnail
    }

    public function column_usage($item) {
        // Render usage badge
    }
}
```

**Key Methods to Implement:**
- `get_columns()` - Define columns
- `get_sortable_columns()` - Which columns sort
- `prepare_items()` - Get and prepare data
- `column_default()` - Default column rendering
- `column_thumbnail()` - Custom thumbnail display
- `column_usage()` - Custom usage display

---

### Step 2: Create Template (1 hour)

**File:** `templates/admin/table-view.php`

```php
<?php
// Prevent direct access
defined('ABSPATH') || exit;

// Create table instance
$list_table = new MIF_Media_List_Table();
$list_table->prepare_items();
?>

<div class="wrap">
    <form method="get">
        <?php $list_table->display(); ?>
    </form>
</div>
```

---

### Step 3: Add View Toggle (1 hour)

**File:** `templates/admin/main-page.php` (modify)

```html
<!-- Add at top of results section -->
<div class="mif-view-controls" style="margin-bottom: 16px;">
    <div class="mif-view-toggle">
        <button class="mif-view-btn active" data-view="card">
            <span class="dashicons dashicons-grid-view"></span>
            Card View
        </button>
        <button class="mif-view-btn" data-view="table">
            <span class="dashicons dashicons-list-view"></span>
            Table View
        </button>
    </div>
</div>

<div id="mif-card-view" class="mif-view-container">
    <!-- Existing card view code -->
</div>

<div id="mif-table-view" class="mif-view-container" style="display: none;">
    <!-- Table view will load here via AJAX -->
</div>
```

---

### Step 4: Add JavaScript (2 hours)

**File:** `assets/js/table-view.js` (NEW)

```javascript
jQuery(document).ready(function($) {

    // View toggle
    $('.mif-view-btn').on('click', function() {
        var view = $(this).data('view');

        // Update buttons
        $('.mif-view-btn').removeClass('active');
        $(this).addClass('active');

        // Switch views
        if (view === 'card') {
            $('#mif-card-view').show();
            $('#mif-table-view').hide();
        } else {
            $('#mif-card-view').hide();
            $('#mif-table-view').show();

            // Load table if not already loaded
            if ($('#mif-table-view').is(':empty')) {
                loadTableView();
            }
        }

        // Save preference
        $.post(ajaxurl, {
            action: 'mif_save_view_preference',
            view: view,
            nonce: mifData.nonce
        });
    });

    // Load table data
    function loadTableView(page, orderby, order) {
        $('#mif-table-view').html('<div class="mif-loading">Loading table...</div>');

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'mif_get_table_view',
                nonce: mifData.nonce,
                page: page || 1,
                orderby: orderby || 'title',
                order: order || 'asc'
            },
            success: function(response) {
                if (response.success) {
                    $('#mif-table-view').html(response.data.html);
                    attachTableHandlers();
                } else {
                    $('#mif-table-view').html('<div class="error">Error loading table</div>');
                }
            }
        });
    }

    // Attach event handlers to table
    function attachTableHandlers() {
        // Sorting
        $('#mif-table-view .sortable a').on('click', function(e) {
            e.preventDefault();
            var orderby = $(this).data('orderby');
            var order = $(this).parent().hasClass('asc') ? 'desc' : 'asc';
            loadTableView(1, orderby, order);
        });

        // Pagination
        $('#mif-table-view .pagination-links a').on('click', function(e) {
            e.preventDefault();
            var page = $(this).data('page');
            loadTableView(page);
        });
    }
});
```

---

### Step 5: Add AJAX Handlers (1 hour)

**File:** `includes/admin/class-admin-controller.php` (modify)

```php
// Add to constructor
add_action('wp_ajax_mif_get_table_view', [$this, 'ajax_get_table_view']);
add_action('wp_ajax_mif_save_view_preference', [$this, 'ajax_save_view_preference']);

// AJAX handler for table view
public function ajax_get_table_view() {
    check_ajax_referer('media_inventory_nonce', 'nonce');

    if (!current_user_can('manage_options')) {
        wp_send_json_error('Permission denied');
        return;
    }

    try {
        // Create table instance
        $list_table = new MIF_Media_List_Table();
        $list_table->prepare_items();

        // Render table
        ob_start();
        $list_table->display();
        $html = ob_get_clean();

        wp_send_json_success(['html' => $html]);
    } catch (Exception $e) {
        wp_send_json_error('Error: ' . $e->getMessage());
    }
}

// Save user's view preference
public function ajax_save_view_preference() {
    check_ajax_referer('media_inventory_nonce', 'nonce');

    $view = sanitize_text_field($_POST['view']);
    update_user_meta(get_current_user_id(), 'mif_view_preference', $view);

    wp_send_json_success();
}
```

---

### Step 6: Add Styling (1 hour)

**File:** `assets/css/table-view.css` (NEW)

```css
/* View Toggle */
.mif-view-toggle {
    display: inline-flex;
    border: 1px solid #ddd;
    border-radius: 4px;
    overflow: hidden;
}

.mif-view-btn {
    padding: 8px 16px;
    background: white;
    border: none;
    border-right: 1px solid #ddd;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 14px;
    transition: all 0.2s;
}

.mif-view-btn:last-child {
    border-right: none;
}

.mif-view-btn:hover {
    background: #f5f5f5;
}

.mif-view-btn.active {
    background: #2271b1;
    color: white;
}

/* Table Styling */
.mif-media-table {
    width: 100%;
    margin-top: 16px;
}

.mif-media-table th.sortable a {
    text-decoration: none;
    color: inherit;
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.mif-media-table th.sortable a:hover {
    color: #2271b1;
}

.mif-media-table th.asc .sorting-indicator::after {
    content: "↑";
}

.mif-media-table th.desc .sorting-indicator::after {
    content: "↓";
}

.mif-media-table .column-thumbnail img {
    width: 60px;
    height: 60px;
    object-fit: cover;
    border-radius: 4px;
}

.mif-media-table .column-usage .usage-badge {
    display: inline-block;
    padding: 4px 8px;
    border-radius: 3px;
    font-size: 12px;
    font-weight: 600;
}

.mif-media-table .usage-badge.used {
    background: #d4edda;
    color: #155724;
}

.mif-media-table .usage-badge.unused {
    background: #f8d7da;
    color: #721c24;
}
```

---

## Data Flow

### Getting Data for Table

```php
// In MIF_Media_List_Table::prepare_items()

// Get scanner instance
$scanner = new MIF_Scanner();

// Run scan (or use cached results)
$scan_results = $scanner->scan_batch(0);
$items = $scan_results['data'];

// Apply sorting
$orderby = $_GET['orderby'] ?? 'title';
$order = $_GET['order'] ?? 'asc';
$items = $this->sort_items($items, $orderby, $order);

// Apply pagination
$per_page = 50;
$current_page = $this->get_pagenum();
$total_items = count($items);

$this->set_pagination_args([
    'total_items' => $total_items,
    'per_page'    => $per_page,
    'total_pages' => ceil($total_items / $per_page)
]);

$this->items = array_slice($items, ($current_page - 1) * $per_page, $per_page);
```

---

## Testing Checklist

### Functionality
- [ ] View toggle switches between card and table
- [ ] Table displays all media items
- [ ] Columns are sortable (click to sort)
- [ ] Pagination works (next, prev, page numbers)
- [ ] User preference is saved
- [ ] Data matches card view

### UI/UX
- [ ] Table is responsive
- [ ] Thumbnails display correctly
- [ ] Usage badges show correctly
- [ ] Sorting indicators clear
- [ ] Loading states smooth

### Performance
- [ ] Table loads quickly
- [ ] Sorting is instant
- [ ] Pagination is smooth
- [ ] No memory issues with large datasets

---

## Common WordPress Table Examples

**Look at these for reference:**
1. Media Library list view (`wp-admin/upload.php?mode=list`)
2. Posts list (`wp-admin/edit.php`)
3. Users list (`wp-admin/users.php`)

**WordPress Codex:**
- `WP_List_Table` class reference
- Pagination documentation
- AJAX in admin documentation

---

## Troubleshooting Tips

### Table not displaying
- Check if `WP_List_Table` class is loaded
- Verify `prepare_items()` is called
- Check for PHP errors in browser console

### Sorting not working
- Verify column is in `get_sortable_columns()`
- Check `orderby` and `order` parameters
- Ensure sort function handles all data types

### AJAX errors
- Check nonce verification
- Verify action hook is registered
- Check browser console for JS errors

---

## Quick Reference Commands

```bash
# Create new files
touch includes/admin/class-media-list-table.php
touch templates/admin/table-view.php
touch assets/css/table-view.css
touch assets/js/table-view.js

# Test locally
# 1. Refresh WordPress admin
# 2. Go to Media Inventory Forge page
# 3. Click "Table View" button
# 4. Verify table displays
# 5. Test sorting
# 6. Test pagination
```

---

**Status:** Ready to implement!
**Next Session:** Start with Step 1 (Table Class)
**Goal:** Working table view by end of session
