# Test Media Setup Guide - Unusual WordPress Usage Scenarios

**Version:** 4.0.0
**Date:** 2025-11-06
**Purpose:** How to set up media files in unusual WordPress locations for comprehensive testing

---

## Overview

This guide shows you how to create test scenarios where media appears in "unusual" places that users might not expect - Custom Post Types, ACF fields, WooCommerce products, custom shortcodes, etc.

---

## 1. Custom Post Types

### What They Are
Custom Post Types (CPT) are like posts/pages but for specific content types: Portfolio Items, Testimonials, Products, Events, Team Members, etc.

### How to Create Test CPT

**Option A: Using a Plugin**
1. Install **Custom Post Type UI** plugin
2. Go to CPT UI → Add/Edit Post Types
3. Create "Portfolio" post type
4. Add posts with featured images and gallery

**Option B: Code in functions.php**
```php
// Add to theme's functions.php
function mif_test_custom_post_types() {
    register_post_type('portfolio', array(
        'labels' => array(
            'name' => 'Portfolio Items',
            'singular_name' => 'Portfolio Item'
        ),
        'public' => true,
        'has_archive' => true,
        'supports' => array('title', 'editor', 'thumbnail', 'custom-fields'),
        'show_in_rest' => true, // For Gutenberg
    ));
}
add_action('init', 'mif_test_custom_post_types');
```

### Media Usage in CPT to Test

**Featured Images:**
```php
// MIF should detect: _thumbnail_id meta for CPT
// Same as posts/pages - already working!
```

**Content Images:**
```html
<!-- In portfolio post content -->
<img class="wp-image-123" src="...">
<!-- MIF should detect: Already working via post_content scan -->
```

**Gallery Shortcodes:**
```
[gallery ids="1,2,3,4,5"]
<!-- MIF should detect: Already working! -->
```

**Test Checklist:**
- [ ] Create Portfolio CPT
- [ ] Add 3 portfolio items with featured images
- [ ] Add gallery to one portfolio item
- [ ] Run MIF usage scan
- [ ] Verify: All portfolio images show usage

---

## 2. Advanced Custom Fields (ACF)

### What It Is
ACF lets you add custom fields to posts - image fields, gallery fields, file fields.

### How to Set Up Test

**Install ACF Free Plugin:**
1. Install "Advanced Custom Fields" from WordPress.org
2. Create Field Group: "Product Details"
3. Add fields:
   - Image field: "Product Photo"
   - Gallery field: "Product Gallery"
   - File field: "Product Manual" (PDF)

**Assign to Post Type:**
- Location: Post Type = Post (or your CPT)

### Media Storage Format

**ACF stores attachment IDs in postmeta:**
```sql
-- Single image field
meta_key: product_photo
meta_value: 123

-- Gallery field (serialized array)
meta_key: product_gallery
meta_value: a:3:{i:0;i:45;i:1;i:46;i:2;i:47;}
```

### How MIF Should Detect ACF

**Current Status:** MIF doesn't scan ACF fields yet

**Implementation Needed:**
```php
// In MIF_Usage_Scanner class
private function scan_acf_fields() {
    global $wpdb;

    // Get all postmeta that might be ACF image fields
    $results = $wpdb->get_results("
        SELECT post_id, meta_key, meta_value
        FROM {$wpdb->postmeta}
        WHERE meta_key LIKE 'field_%'
        OR meta_key IN (
            SELECT meta_value FROM {$wpdb->postmeta}
            WHERE meta_key = '_field_%'
        )
    ");

    foreach ($results as $row) {
        // Check if value is numeric (single image)
        if (is_numeric($row->meta_value)) {
            $attachment_id = intval($row->meta_value);
            if (wp_attachment_is_image($attachment_id)) {
                // Store usage
            }
        }

        // Check if value is serialized array (gallery)
        $unserialized = @unserialize($row->meta_value);
        if (is_array($unserialized)) {
            foreach ($unserialized as $id) {
                if (is_numeric($id) && wp_attachment_is_image($id)) {
                    // Store usage
                }
            }
        }
    }
}
```

### Test Checklist
- [ ] Install ACF Free
- [ ] Create image field on Posts
- [ ] Add image to custom field
- [ ] Verify: MIF detects ACF image usage (when implemented)

---

## 3. WooCommerce Products

### What It Is
WooCommerce adds "Product" custom post type with product images, galleries, downloadable files.

### Media Usage in WooCommerce

**Product Featured Image:**
```php
// Same as regular featured image
// MIF already detects this!
```

**Product Gallery:**
```php
// Stored in postmeta as serialized array
meta_key: _product_image_gallery
meta_value: "45,46,47,48"  // Comma-separated IDs
```

**Downloadable Files:**
```php
// Stored in postmeta as serialized array
meta_key: _downloadable_files
meta_value: array(
    array(
        'name' => 'Product Manual',
        'file' => 'https://site.com/wp-content/uploads/2025/11/manual.pdf'
    )
)
```

### How to Test

**Install WooCommerce:**
1. Install WooCommerce plugin
2. Complete setup wizard
3. Add Product
4. Set featured image
5. Add product gallery (2-3 images)
6. Add downloadable file (PDF)

### Implementation Needed

```php
// In MIF_Usage_Scanner
private function scan_woocommerce_products() {
    if (!function_exists('WC')) {
        return; // WooCommerce not active
    }

    global $wpdb;

    // Product galleries
    $galleries = $wpdb->get_results("
        SELECT post_id, meta_value
        FROM {$wpdb->postmeta}
        WHERE meta_key = '_product_image_gallery'
        AND meta_value != ''
    ");

    foreach ($galleries as $row) {
        $ids = explode(',', $row->meta_value);
        foreach ($ids as $attachment_id) {
            // Store usage
        }
    }
}
```

### Test Checklist
- [ ] Install WooCommerce
- [ ] Create 2 products with galleries
- [ ] Add downloadable PDF to one product
- [ ] Verify: MIF detects all product media

---

## 4. Custom Shortcodes

### What They Are
Custom shortcodes created by themes/plugins that output media.

### Example Custom Shortcode

**Theme creates shortcode:**
```php
// In theme functions.php
function custom_team_member_shortcode($atts) {
    $atts = shortcode_atts(array(
        'image_id' => '',
        'name' => '',
    ), $atts);

    $image_url = wp_get_attachment_url($atts['image_id']);

    return '<div class="team-member">
        <img src="' . $image_url . '" alt="' . $atts['name'] . '">
        <h3>' . $atts['name'] . '</h3>
    </div>';
}
add_shortcode('team_member', 'custom_team_member_shortcode');
```

**Used in content:**
```
[team_member image_id="123" name="John Smith"]
```

### How MIF Should Detect

**Pattern Matching in Content:**
```php
// In MIF_Usage_Scanner
private function scan_custom_shortcodes($content) {
    // Look for shortcodes with image_id or attachment_id parameters
    $pattern = '/\[([\w_-]+)[^\]]*(?:image_id|attachment_id|img_id)=["\']?(\d+)/';
    preg_match_all($pattern, $content, $matches);

    if (!empty($matches[2])) {
        foreach ($matches[2] as $attachment_id) {
            // Store usage
        }
    }
}
```

### Test Checklist
- [ ] Add custom shortcode to functions.php
- [ ] Use shortcode in post with image ID
- [ ] Verify: MIF detects shortcode image usage

---

## 5. Menu Items with Images

### What It Is
WordPress menus can have custom fields, including images.

### How to Set Up

**Enable Custom Fields in Menus:**
```php
// Add to functions.php
add_filter('wp_nav_menu_objects', 'add_menu_image_support');
function add_menu_image_support($items) {
    foreach ($items as $item) {
        $image_id = get_post_meta($item->ID, '_menu_item_image', true);
        if ($image_id) {
            $item->thumbnail = wp_get_attachment_image($image_id, 'thumbnail');
        }
    }
    return $items;
}
```

### Test Checklist
- [ ] Add custom field to menu item
- [ ] Store image ID in `_menu_item_image` meta
- [ ] Verify: MIF detects menu item images

---

## 6. Dynamic Sidebars with Image Widgets

### What It Is
Widget areas that contain Image widgets with media.

### How to Test

**Add Image Widgets:**
1. Appearance → Widgets
2. Add "Image" widget to sidebar
3. Select media from library
4. Save widget

**Widget Data Storage:**
```php
// In options table
option_name: widget_media_image
option_value: array(
    2 => array(
        'attachment_id' => 123,
        'url' => '...',
        'title' => 'Widget Image'
    )
)
```

**Current Status:** MIF already scans widgets! ✅

### Test Checklist
- [ ] Add 2 Image widgets to different sidebars
- [ ] Run MIF scan
- [ ] Verify: Widget images show usage

---

## 7. User Profile Images (Not Standard WP)

### What It Is
Plugins like "Simple Local Avatars" or "WP User Avatar" let users upload profile images.

### Storage Format
```php
// In usermeta table
meta_key: simple_local_avatar
meta_value: 123  // attachment ID
```

### Implementation Needed

```php
// In MIF_Usage_Scanner
private function scan_user_avatars() {
    global $wpdb;

    $avatars = $wpdb->get_results("
        SELECT user_id, meta_value as attachment_id
        FROM {$wpdb->usermeta}
        WHERE meta_key IN (
            'simple_local_avatar',
            'wp_user_avatar',
            'basic_user_avatars'
        )
    ");

    foreach ($avatars as $row) {
        // Store usage with type='user_avatar'
    }
}
```

### Test Checklist
- [ ] Install "Simple Local Avatars" plugin
- [ ] Upload avatar for test user
- [ ] Verify: MIF detects avatar usage

---

## 8. Email Templates (HTML emails)

### What They Are
Plugins like WooCommerce, Newsletter, or MailPoet create HTML email templates with images.

### Storage Locations

**WooCommerce Email Templates:**
- Location: `/wp-content/themes/your-theme/woocommerce/emails/`
- Images hardcoded in PHP: `<img src="<?php echo get_template_directory_uri(); ?>/images/logo.png">`

**Newsletter Plugin:**
- Stored in custom table or post type
- Images embedded as `<img src="...">`

### Detection Challenge
**Problem:** Emails stored outside standard post content

**Possible Solution:**
```php
// Scan custom tables for newsletter plugins
private function scan_newsletter_emails() {
    global $wpdb;

    // Check if Newsletter plugin is active
    if (defined('NEWSLETTER_VERSION')) {
        $results = $wpdb->get_results("
            SELECT message FROM {$wpdb->prefix}newsletter
            WHERE message LIKE '%<img%'
        ");

        foreach ($results as $row) {
            // Parse HTML for wp-image-XXX or URLs
        }
    }
}
```

### Test Checklist
- [ ] Install Newsletter plugin
- [ ] Create newsletter with images
- [ ] Check if MIF can detect (likely won't without custom code)

---

## 9. Custom Database Tables (Theme/Plugin Specific)

### What It Is
Some themes/plugins store data in custom database tables instead of post content.

### Example: Real Estate Plugin

**Custom Table Structure:**
```sql
CREATE TABLE wp_properties (
    id INT PRIMARY KEY,
    title VARCHAR(255),
    featured_image_id INT,
    gallery_images TEXT,  -- Serialized array of IDs
    floor_plan_id INT
);
```

### Detection Strategy

**Create Optional Hooks:**
```php
// Allow third-party plugins/themes to report their media usage
do_action('mif_scan_custom_locations', $scanner);

// Theme can hook in:
add_action('mif_scan_custom_locations', function($scanner) {
    global $wpdb;
    $properties = $wpdb->get_results("SELECT * FROM wp_properties");

    foreach ($properties as $prop) {
        // Report featured image
        $scanner->record_usage($prop->featured_image_id, 'custom_property', $prop->id);

        // Report gallery
        $gallery = unserialize($prop->gallery_images);
        foreach ($gallery as $img_id) {
            $scanner->record_usage($img_id, 'custom_property_gallery', $prop->id);
        }
    }
});
```

---

## 10. REST API / Headless WordPress

### What It Is
WordPress used as backend API, frontend is React/Vue/Next.js

### Usage Pattern
```javascript
// Frontend fetches media via REST API
fetch('/wp-json/wp/v2/media/123')
```

### Detection Status
**MIF can detect:** If media is attached to posts, pages, or standard WP locations
**MIF cannot detect:** If frontend hardcodes media URLs or uses external asset management

---

## Testing Priority Order

### Must Test (High Priority)
1. ✅ Standard posts with images (working)
2. ✅ Featured images (working)
3. ✅ Gutenberg blocks (working)
4. ✅ Widgets (working)
5. ✅ Theme Customizer (working)
6. ✅ Elementor (working)
7. [ ] Custom Post Types (should work, needs verification)
8. [ ] WooCommerce Products

### Should Test (Medium Priority)
9. [ ] ACF fields (needs implementation)
10. [ ] Custom shortcodes
11. [ ] Menu images

### Nice to Test (Low Priority)
12. [ ] User avatars
13. [ ] Newsletter emails
14. [ ] Custom database tables

---

## Quick Test Site Setup Script

**Create a comprehensive test site in 30 minutes:**

```bash
# 1. Fresh WordPress install via Local

# 2. Install these plugins:
# - Advanced Custom Fields (Free)
# - WooCommerce
# - Custom Post Type UI
# - Elementor (Free)

# 3. Create content:
# - 5 regular posts with images
# - 3 pages with galleries
# - 2 custom post types (Portfolio)
# - 2 WooCommerce products
# - 1 post with ACF image field
# - 3 Image widgets in sidebar

# 4. Run MIF scan

# 5. Manually verify each usage is detected
```

---

## Detection Coverage Report Template

After testing, create a report:

```markdown
# MIF Detection Coverage Report

**Test Date:** 2025-11-XX
**MIF Version:** 4.0.0
**WordPress Version:** 6.4

## Results Summary

| Location Type | Test Cases | Detected | Not Detected | Coverage % |
|--------------|------------|----------|--------------|------------|
| Standard Posts | 10 | 10 | 0 | 100% |
| Custom Post Types | 5 | 5 | 0 | 100% |
| WooCommerce | 3 | 2 | 1 | 67% |
| ACF Fields | 4 | 0 | 4 | 0% |
| Widgets | 3 | 3 | 0 | 100% |

## Known Limitations
- ACF fields not yet supported
- WooCommerce downloadable files not detected
- Newsletter emails not scanned

## Recommended Next Steps
1. Implement ACF detection
2. Add WooCommerce gallery support
```

---

**Status:** Ready for comprehensive testing
**Next Session:** Use this guide to create diverse test scenarios
**Goal:** Verify MIF detects media in all common WordPress usage patterns
