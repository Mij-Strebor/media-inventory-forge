<?php
/**
 * Media Inventory Forge - Usage Scanner Class
 *
 * Scans WordPress content to detect where media files are being used. Analyzes
 * posts, pages, custom post types, widgets, theme customizer settings, CSS files,
 * and page builders to build a comprehensive usage map for each media file.
 *
 * @package    MediaInventoryForge
 * @subpackage Core
 * @since      4.0.0
 * @version    4.0.0
 */

// Prevent direct file access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * MIF Usage Scanner Class
 *
 * Provides methods for scanning various WordPress content areas to detect
 * media usage. Supports posts, pages, widgets, CSS, page builders, and more.
 *
 * @since 4.0.0
 */
class MIF_Usage_Scanner {

    /**
     * Usage database instance
     *
     * @var MIF_Usage_Database
     */
    private $usage_db;

    /**
     * Batch size for processing
     *
     * @var int
     */
    private $batch_size = 100;

    /**
     * Current scan progress
     *
     * @var array
     */
    private $progress = array();

    /**
     * Constructor
     *
     * @since 4.0.0
     */
    public function __construct() {
        $this->usage_db = new MIF_Usage_Database();
    }

    /**
     * Scan all media usage
     *
     * Main entry point for scanning. Scans all content types and builds
     * complete usage map for all attachments.
     *
     * @since 4.0.0
     * @param array $options Scanning options
     * @return array Scan results
     */
    public function scan_all_usage($options = array()) {
        $defaults = array(
            'clear_existing' => true,
            'batch_size' => $this->batch_size
        );

        $options = wp_parse_args($options, $defaults);

        // Clear existing data if requested
        if ($options['clear_existing']) {
            $this->usage_db->clear_all_usage();
        }

        // Initialize progress tracking
        $this->progress = array(
            'started_at' => current_time('mysql'),
            'posts_scanned' => 0,
            'widgets_scanned' => 0,
            'css_files_scanned' => 0,
            'usage_found' => 0
        );

        // Scan different content types
        $this->scan_posts_and_pages();
        $this->scan_featured_images();
        $this->scan_widgets();
        $this->scan_theme_customizer();
        $this->scan_css_files();

        // Scan page builders if active
        $this->scan_page_builders();

        // Update last scan time
        update_option('mif_last_usage_scan', current_time('mysql'));

        $this->progress['completed_at'] = current_time('mysql');

        return $this->progress;
    }

    /**
     * Scan posts and pages content
     *
     * Scans post_content for images, videos, PDFs, and other media references.
     * Detects <img> tags, Gutenberg blocks, shortcodes, and direct links.
     *
     * @since 4.0.0
     * @return int Number of posts scanned
     */
    private function scan_posts_and_pages() {
        $args = array(
            'post_type' => 'any',
            'post_status' => 'publish',
            'posts_per_page' => $this->batch_size,
            'paged' => 1,
            'no_found_rows' => false
        );

        $total_scanned = 0;

        do {
            $query = new WP_Query($args);

            if (!$query->have_posts()) {
                break;
            }

            while ($query->have_posts()) {
                $query->the_post();
                $post_id = get_the_ID();
                $content = get_the_content();

                // Scan content for media
                $this->scan_content_for_media($post_id, $content, 'post');

                $total_scanned++;
            }

            wp_reset_postdata();
            $args['paged']++;

        } while ($query->max_num_pages >= $args['paged']);

        $this->progress['posts_scanned'] = $total_scanned;

        return $total_scanned;
    }

    /**
     * Scan content for media references
     *
     * Analyzes HTML content for various media references including img tags,
     * Gutenberg blocks, shortcodes, and direct links to media files.
     *
     * @since 4.0.0
     * @param int    $content_id   Post/page ID
     * @param string $content      HTML content to scan
     * @param string $content_type Type of content (post, page, widget, etc.)
     * @return int Number of media items found
     */
    private function scan_content_for_media($content_id, $content, $content_type = 'post') {
        $found_count = 0;

        // 1. Scan for <img> tags with attachment IDs or URLs
        $found_count += $this->scan_img_tags($content_id, $content, $content_type);

        // 2. Scan for Gutenberg image blocks
        $found_count += $this->scan_gutenberg_blocks($content_id, $content, $content_type);

        // 3. Scan for gallery shortcodes
        $found_count += $this->scan_gallery_shortcodes($content_id, $content, $content_type);

        // 4. Scan for direct media links (PDFs, videos, etc.)
        $found_count += $this->scan_media_links($content_id, $content, $content_type);

        // 5. Scan for audio/video shortcodes
        $found_count += $this->scan_av_shortcodes($content_id, $content, $content_type);

        return $found_count;
    }

    /**
     * Scan for <img> tags in content
     *
     * Extracts attachment IDs from wp-image-XXX classes and converts
     * URLs to attachment IDs where possible.
     *
     * @since 4.0.0
     * @param int    $content_id
     * @param string $content
     * @param string $content_type
     * @return int
     */
    private function scan_img_tags($content_id, $content, $content_type) {
        $found_count = 0;

        // Match all <img> tags
        preg_match_all('/<img[^>]+>/i', $content, $img_tags);

        foreach ($img_tags[0] as $img_tag) {
            $attachment_id = null;

            // Try to get attachment ID from wp-image-XXX class
            if (preg_match('/wp-image-(\d+)/i', $img_tag, $class_id)) {
                $attachment_id = intval($class_id[1]);
            }
            // Try to get attachment ID from data-id attribute
            elseif (preg_match('/data-id=["\'](\d+)["\']/i', $img_tag, $data_id)) {
                $attachment_id = intval($data_id[1]);
            }
            // Try to extract URL and convert to attachment ID
            elseif (preg_match('/src=["\']([^"\']+)["\']/i', $img_tag, $src)) {
                $attachment_id = $this->url_to_attachment_id($src[1]);
            }

            if ($attachment_id) {
                $metadata = $this->build_usage_metadata($content_type, $content_id, 'content_image');
                $this->usage_db->store_usage(
                    $attachment_id,
                    $content_type,
                    $content_id,
                    'content_image',
                    $metadata
                );
                $found_count++;
                $this->progress['usage_found']++;
            }
        }

        return $found_count;
    }

    /**
     * Scan for Gutenberg blocks
     *
     * Parses Gutenberg block comments and extracts attachment IDs
     * from image, gallery, cover, and media-text blocks.
     *
     * @since 4.0.0
     * @param int    $content_id
     * @param string $content
     * @param string $content_type
     * @return int
     */
    private function scan_gutenberg_blocks($content_id, $content, $content_type) {
        $found_count = 0;

        // Match Gutenberg block comments
        preg_match_all('/<!-- wp:([^\s]+)\s+({[^}]+})\s*-->/i', $content, $blocks, PREG_SET_ORDER);

        foreach ($blocks as $block) {
            $block_name = $block[1];
            $block_attrs = $block[2];

            // Parse JSON attributes
            $attrs = json_decode($block_attrs, true);

            if (!$attrs) {
                continue;
            }

            $attachment_id = null;
            $context = 'gutenberg_' . str_replace('/', '_', $block_name);

            // wp:image block
            if ($block_name === 'image' && isset($attrs['id'])) {
                $attachment_id = intval($attrs['id']);
            }
            // wp:cover block
            elseif ($block_name === 'cover' && isset($attrs['id'])) {
                $attachment_id = intval($attrs['id']);
            }
            // wp:media-text block
            elseif ($block_name === 'media-text' && isset($attrs['mediaId'])) {
                $attachment_id = intval($attrs['mediaId']);
            }
            // wp:video or wp:audio
            elseif (in_array($block_name, array('video', 'audio')) && isset($attrs['id'])) {
                $attachment_id = intval($attrs['id']);
            }
            // wp:gallery block (has multiple IDs)
            elseif ($block_name === 'gallery' && isset($attrs['ids']) && is_array($attrs['ids'])) {
                foreach ($attrs['ids'] as $id) {
                    $metadata = $this->build_usage_metadata($content_type, $content_id, 'gutenberg_gallery');
                    $this->usage_db->store_usage(
                        intval($id),
                        $content_type,
                        $content_id,
                        'gutenberg_gallery',
                        $metadata
                    );
                    $found_count++;
                    $this->progress['usage_found']++;
                }
                continue; // Skip the single ID logic below
            }

            if ($attachment_id) {
                $metadata = $this->build_usage_metadata($content_type, $content_id, $context);
                $this->usage_db->store_usage(
                    $attachment_id,
                    $content_type,
                    $content_id,
                    $context,
                    $metadata
                );
                $found_count++;
                $this->progress['usage_found']++;
            }
        }

        return $found_count;
    }

    /**
     * Scan for [gallery] shortcodes
     *
     * Extracts attachment IDs from gallery shortcode attributes.
     *
     * @since 4.0.0
     * @param int    $content_id
     * @param string $content
     * @param string $content_type
     * @return int
     */
    private function scan_gallery_shortcodes($content_id, $content, $content_type) {
        $found_count = 0;

        // Match [gallery] shortcodes
        preg_match_all('/\[gallery[^\]]*ids=["\']([^"\']+)["\']/i', $content, $galleries);

        foreach ($galleries[1] as $ids_string) {
            $ids = array_map('intval', explode(',', $ids_string));

            foreach ($ids as $attachment_id) {
                if ($attachment_id > 0) {
                    $metadata = $this->build_usage_metadata($content_type, $content_id, 'shortcode_gallery');
                    $this->usage_db->store_usage(
                        $attachment_id,
                        $content_type,
                        $content_id,
                        'shortcode_gallery',
                        $metadata
                    );
                    $found_count++;
                    $this->progress['usage_found']++;
                }
            }
        }

        return $found_count;
    }

    /**
     * Scan for direct media links (PDFs, videos, etc.)
     *
     * Finds <a> tags linking to media files.
     *
     * @since 4.0.0
     * @param int    $content_id
     * @param string $content
     * @param string $content_type
     * @return int
     */
    private function scan_media_links($content_id, $content, $content_type) {
        $found_count = 0;

        // Match <a> tags with href to uploads directory
        preg_match_all('/<a[^>]+href=["\']([^"\']*\/wp-content\/uploads\/[^"\']+\.(pdf|zip|doc|docx|xls|xlsx|ppt|pptx|mp4|mov|avi|mp3|wav))["\']/i', $content, $links);

        foreach ($links[1] as $url) {
            $attachment_id = $this->url_to_attachment_id($url);

            if ($attachment_id) {
                $metadata = $this->build_usage_metadata($content_type, $content_id, 'linked_media');
                $this->usage_db->store_usage(
                    $attachment_id,
                    $content_type,
                    $content_id,
                    'linked_media',
                    $metadata
                );
                $found_count++;
                $this->progress['usage_found']++;
            }
        }

        return $found_count;
    }

    /**
     * Scan for [audio] and [video] shortcodes
     *
     * @since 4.0.0
     * @param int    $content_id
     * @param string $content
     * @param string $content_type
     * @return int
     */
    private function scan_av_shortcodes($content_id, $content, $content_type) {
        $found_count = 0;

        // Match [audio] and [video] shortcodes with src or mp3/mp4 attributes
        preg_match_all('/\[(audio|video)[^\]]*(?:src|mp3|mp4)=["\']([^"\']+)["\']/i', $content, $av_shortcodes, PREG_SET_ORDER);

        foreach ($av_shortcodes as $shortcode) {
            $url = $shortcode[2];
            $attachment_id = $this->url_to_attachment_id($url);

            if ($attachment_id) {
                $context = 'shortcode_' . $shortcode[1];
                $metadata = $this->build_usage_metadata($content_type, $content_id, $context);
                $this->usage_db->store_usage(
                    $attachment_id,
                    $content_type,
                    $content_id,
                    $context,
                    $metadata
                );
                $found_count++;
                $this->progress['usage_found']++;
            }
        }

        return $found_count;
    }

    /**
     * Scan featured images (post thumbnails)
     *
     * Checks _thumbnail_id meta for all posts to find featured images.
     *
     * @since 4.0.0
     * @return int Number of featured images found
     */
    private function scan_featured_images() {
        global $wpdb;
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Read-only query for featured image detection, no caching needed for scan operation

        $results = $wpdb->get_results(
            "SELECT post_id, meta_value as attachment_id
             FROM {$wpdb->postmeta}
             WHERE meta_key = '_thumbnail_id'
             AND meta_value > 0"
        );

        $found_count = 0;

        foreach ($results as $row) {
            $metadata = $this->build_usage_metadata('post', intval($row->post_id), 'featured_image');
            $this->usage_db->store_usage(
                intval($row->attachment_id),
                'post',
                intval($row->post_id),
                'featured_image',
                $metadata
            );
            $found_count++;
            $this->progress['usage_found']++;
        }

        return $found_count;
    }

    /**
     * Scan widgets for media
     *
     * Scans widget data stored in options table for image widgets,
     * custom HTML widgets with images, etc.
     *
     * @since 4.0.0
     * @return int Number of widgets scanned
     */
    private function scan_widgets() {
        global $wpdb;

        $widget_count = 0;

            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Read-only query for background image detection, no caching needed for scan operation
        // Get all widget options
        $widget_options = $wpdb->get_results(
            "SELECT option_name, option_value
             FROM {$wpdb->options}
             WHERE option_name LIKE 'widget_%'"
        );

        foreach ($widget_options as $option) {
            $widgets = maybe_unserialize($option->option_value);

            if (!is_array($widgets)) {
                continue;
            }

            foreach ($widgets as $widget_id => $widget_data) {
                if (!is_array($widget_data) || $widget_id === '_multiwidget') {
                    continue;
                }

                // Scan widget data
                $this->scan_widget_data($option->option_name, $widget_id, $widget_data);
                $widget_count++;
            }
        }

        $this->progress['widgets_scanned'] = $widget_count;

        return $widget_count;
    }

    /**
     * Scan individual widget data for media
     *
     * @since 4.0.0
     * @param string $widget_type
     * @param mixed  $widget_id
     * @param array  $widget_data
     * @return int
     */
    private function scan_widget_data($widget_type, $widget_id, $widget_data) {
        $found_count = 0;
        $widget_identifier = $widget_type . '_' . $widget_id;

        // Check for attachment_id field (common in media widgets)
        if (isset($widget_data['attachment_id']) && $widget_data['attachment_id'] > 0) {
            $metadata = $this->build_usage_metadata('widget', 0, 'widget_' . $widget_type, array('widget_id' => $widget_identifier));
            $this->usage_db->store_usage(
                intval($widget_data['attachment_id']),
                'widget',
                0,
                'widget_' . $widget_type,
                $metadata
            );
            $found_count++;
            $this->progress['usage_found']++;
        }

        // Check for image_id field
        if (isset($widget_data['image_id']) && $widget_data['image_id'] > 0) {
            $metadata = $this->build_usage_metadata('widget', 0, 'widget_' . $widget_type, array('widget_id' => $widget_identifier));
            $this->usage_db->store_usage(
                intval($widget_data['image_id']),
                'widget',
                0,
                'widget_' . $widget_type,
                $metadata
            );
            $found_count++;
            $this->progress['usage_found']++;
        }

        // Scan text/HTML content in widgets
        if (isset($widget_data['text'])) {
            $found_count += $this->scan_content_for_media(0, $widget_data['text'], 'widget');
        }

        return $found_count;
    }

    /**
     * Scan theme customizer settings
     *
     * Scans theme mods for custom logo, header image, background image, etc.
     *
     * @since 4.0.0
     * @return int Number of customizer images found
     */
    private function scan_theme_customizer() {
        $found_count = 0;

        $theme_slug = get_option('stylesheet');
        $theme_mods = get_option('theme_mods_' . $theme_slug);

        if (!is_array($theme_mods)) {
            return 0;
        }

        // Custom logo
        if (isset($theme_mods['custom_logo']) && $theme_mods['custom_logo'] > 0) {
            $metadata = $this->build_usage_metadata('customizer', 0, 'custom_logo');
            $this->usage_db->store_usage(
                intval($theme_mods['custom_logo']),
                'customizer',
                0,
                'custom_logo',
                $metadata
            );
            $found_count++;
            $this->progress['usage_found']++;
        }

        // Header image
        if (isset($theme_mods['header_image']) && !empty($theme_mods['header_image'])) {
            $url = $theme_mods['header_image'];
            if (filter_var($url, FILTER_VALIDATE_URL)) {
                $attachment_id = $this->url_to_attachment_id($url);
                if ($attachment_id) {
                    $metadata = $this->build_usage_metadata('customizer', 0, 'header_image');
                    $this->usage_db->store_usage($attachment_id, 'customizer', 0, 'header_image', $metadata);
                    $found_count++;
                    $this->progress['usage_found']++;
                }
            }
        }

        // Background image
        if (isset($theme_mods['background_image']) && !empty($theme_mods['background_image'])) {
            $url = $theme_mods['background_image'];
            if (filter_var($url, FILTER_VALIDATE_URL)) {
                $attachment_id = $this->url_to_attachment_id($url);
                if ($attachment_id) {
                    $metadata = $this->build_usage_metadata('customizer', 0, 'background_image');
                    $this->usage_db->store_usage($attachment_id, 'customizer', 0, 'background_image', $metadata);
                    $found_count++;
                    $this->progress['usage_found']++;
                }
            }
        }

        return $found_count;
    }

    /**
     * Scan CSS files for media references
     *
     * Scans theme stylesheets and enqueued CSS for background-image,
     * list-style-image, and content: url() references.
     *
     * @since 4.0.0
     * @return int Number of CSS files scanned
     */
    private function scan_css_files() {
        $files_scanned = 0;

        // 1. Scan theme CSS files
        $files_scanned += $this->scan_theme_css();

        // 2. Scan enqueued stylesheets
        $files_scanned += $this->scan_enqueued_css();

        // 3. Scan custom CSS from customizer
        $this->scan_custom_css();

        $this->progress['css_files_scanned'] = $files_scanned;

        return $files_scanned;
    }

    /**
     * Scan theme CSS files
     *
     * @since 4.0.0
     * @return int
     */
    private function scan_theme_css() {
        $theme_root = get_stylesheet_directory();
        $css_files = glob($theme_root . '/*.css');

        // Also check css/ subdirectory if it exists
        if (is_dir($theme_root . '/css')) {
            $css_files = array_merge($css_files, glob($theme_root . '/css/*.css'));
        }

        $files_scanned = 0;

        foreach ($css_files as $css_file) {
            if (is_readable($css_file)) {
                $css_content = file_get_contents($css_file);
                $this->scan_css_content($css_content, basename($css_file));
                $files_scanned++;
            }
        }

        return $files_scanned;
    }

    /**
     * Scan enqueued stylesheets
     *
     * @since 4.0.0
     * @return int
     */
    private function scan_enqueued_css() {
        global $wp_styles;

        if (!$wp_styles || !is_object($wp_styles)) {
            return 0;
        }

        $files_scanned = 0;

        foreach ($wp_styles->registered as $handle => $style) {
            if (empty($style->src)) {
                continue;
            }

            // Convert URL to file path
            $css_url = $style->src;
            $css_path = $this->url_to_path($css_url);

            if ($css_path && is_readable($css_path)) {
                $css_content = file_get_contents($css_path);
                $this->scan_css_content($css_content, $handle);
                $files_scanned++;
            }
        }

        return $files_scanned;
    }

    /**
     * Scan custom CSS from theme customizer
     *
     * @since 4.0.0
     * @return int
     */
    private function scan_custom_css() {
        $custom_css = wp_get_custom_css();

        if (!empty($custom_css)) {
            $this->scan_css_content($custom_css, 'custom_css');
            return 1;
        }

        return 0;
    }

    /**
     * Scan CSS content for media URLs
     *
     * Extracts URLs from url() declarations in CSS.
     *
     * @since 4.0.0
     * @param string $css_content
     * @param string $source_file
     * @return int
     */
    private function scan_css_content($css_content, $source_file) {
        $found_count = 0;

        // Match url() declarations
        // Matches: url('...'), url("..."), url(...)
        preg_match_all('/url\s*\(\s*[\'"]?([^\'"()]+)[\'"]?\s*\)/i', $css_content, $urls);

        foreach ($urls[1] as $url) {
            // Skip data URIs, absolute external URLs, and relative paths that aren't in uploads
            if (strpos($url, 'data:') === 0 ||
                strpos($url, 'http://') === 0 ||
                strpos($url, 'https://') === 0 ||
                strpos($url, '//') === 0) {
                // If it's an absolute URL, check if it's from this site's uploads
                if (strpos($url, '/wp-content/uploads/') !== false) {
                    $attachment_id = $this->url_to_attachment_id($url);
                } else {
                    continue;
                }
            } else {
                // Relative URL - try to resolve it
                $attachment_id = $this->url_to_attachment_id($url);
            }

            if ($attachment_id) {
                $metadata = $this->build_usage_metadata('css', 0, 'css_background', array('file' => $source_file, 'url' => $url));
                $this->usage_db->store_usage(
                    $attachment_id,
                    'css',
                    0,
                    'css_background',
                    $metadata
                );
                $found_count++;
                $this->progress['usage_found']++;
            }
        }

        return $found_count;
    }

    /**
     * Scan page builders for media usage
     *
     * Detects active page builders (Elementor, WPBakery, Bricks, etc.) and
     * scans their data structures for media references.
     *
     * @since 4.0.0
     * @return int Number of builder pages scanned
     */
    private function scan_page_builders() {
        $builders_scanned = 0;

        // Detect which builders are active
        $active_builders = $this->detect_active_builders();

        // Scan each active builder
        if (in_array('elementor', $active_builders)) {
            $builders_scanned += $this->scan_elementor_data();
        }

        // Future: Add WPBakery, Divi, Bricks support here

        return $builders_scanned;
    }

    /**
     * Detect which page builders are active
     *
     * @since 4.0.0
     * @return array List of active builder slugs
     */
    private function detect_active_builders() {
        $active_builders = array();

        // Check Elementor
        if (did_action('elementor/loaded') || defined('ELEMENTOR_VERSION')) {
            $active_builders[] = 'elementor';
        }

        // Check WPBakery (Visual Composer)
        if (defined('WPB_VC_VERSION')) {
            $active_builders[] = 'wpbakery';
        }

        // Check Divi
        if (defined('ET_BUILDER_VERSION')) {
            $active_builders[] = 'divi';
        }

        // Check Bricks
        if (defined('BRICKS_VERSION')) {
            $active_builders[] = 'bricks';
        }

        return $active_builders;
    }

    /**
     * Scan Elementor pages for media usage
     *
     * Elementor stores page data in postmeta as JSON. This method parses
     * the Elementor data structure to find images in various widget types.
     *
     * WHERE ELEMENTOR AFFECTS DATA COLLECTION:
     * - Storage: wp_postmeta with key '_elementor_data'
     * - Format: JSON array of elements/widgets
     * - Bypasses normal post_content completely!
     *
     * @since 4.0.0
     * @return int Number of Elementor pages scanned
     */
    private function scan_elementor_data() {
        global $wpdb;

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Read-only query for page builder meta detection, no caching needed for scan operation
        // Query all posts that have Elementor data
        $results = $wpdb->get_results(
            "SELECT post_id, meta_value
             FROM {$wpdb->postmeta}
             WHERE meta_key = '_elementor_data'
             AND meta_value != ''"
        );

        if (empty($results)) {
            return 0;
        }

        $pages_scanned = 0;

        foreach ($results as $row) {
            $post_id = intval($row->post_id);

            // Parse Elementor JSON data
            $elementor_data = json_decode($row->meta_value, true);

            if (!is_array($elementor_data)) {
                continue;
            }

            // Recursively scan Elementor elements for images
            $this->scan_elementor_elements($elementor_data, $post_id);

            $pages_scanned++;
        }

        return $pages_scanned;
    }

    /**
     * Recursively scan Elementor elements for media
     *
     * Elementor uses a nested structure where elements can contain other elements.
     * This method recursively traverses the structure looking for media references.
     *
     * ELEMENTOR STRUCTURE EXAMPLE:
     * [
     *   {
     *     "elType": "section",
     *     "elements": [
     *       {
     *         "elType": "column",
     *         "elements": [
     *           {
     *             "elType": "widget",
     *             "widgetType": "image",
     *             "settings": {
     *               "image": {"id": 123, "url": "..."}
     *             }
     *           }
     *         ]
     *       }
     *     ]
     *   }
     * ]
     *
     * @since 4.0.0
     * @param array $elements Elementor elements array
     * @param int   $post_id  Post ID
     * @return int Number of media items found
     */
    private function scan_elementor_elements($elements, $post_id) {
        $found_count = 0;

        if (!is_array($elements)) {
            return 0;
        }

        foreach ($elements as $element) {
            if (!is_array($element)) {
                continue;
            }

            // Check if this is a widget with settings
            if (isset($element['widgetType']) && isset($element['settings'])) {
                $found_count += $this->scan_elementor_widget($element, $post_id);
            }

            // Recursively scan child elements
            if (isset($element['elements']) && is_array($element['elements'])) {
                $found_count += $this->scan_elementor_elements($element['elements'], $post_id);
            }
        }

        return $found_count;
    }

    /**
     * Scan an individual Elementor widget for media
     *
     * Different widget types store images differently:
     * - image: settings.image.id
     * - gallery: settings.gallery[].id
     * - video: settings.poster.id (video thumbnail)
     * - Background images: settings.background_image.id
     * - And many more...
     *
     * @since 4.0.0
     * @param array $widget  Widget data
     * @param int   $post_id Post ID
     * @return int Number of media items found
     */
    private function scan_elementor_widget($widget, $post_id) {
        $found_count = 0;
        $widget_type = isset($widget['widgetType']) ? $widget['widgetType'] : '';
        $settings = isset($widget['settings']) ? $widget['settings'] : array();

        if (empty($settings)) {
            return 0;
        }

        // IMAGE WIDGET: Most common Elementor widget
        if ($widget_type === 'image' && isset($settings['image']['id'])) {
            $attachment_id = intval($settings['image']['id']);
            if ($attachment_id > 0) {
                $metadata = $this->build_usage_metadata('page_builder', $post_id, 'elementor_image', array('builder' => 'elementor'));
                $this->usage_db->store_usage(
                    $attachment_id,
                    'page_builder',
                    $post_id,
                    'elementor_image',
                    $metadata
                );
                $found_count++;
                $this->progress['usage_found']++;
            }
        }

        // GALLERY WIDGET: Array of images
        if ($widget_type === 'gallery' && isset($settings['gallery']) && is_array($settings['gallery'])) {
            foreach ($settings['gallery'] as $gallery_item) {
                if (isset($gallery_item['id'])) {
                    $attachment_id = intval($gallery_item['id']);
                    if ($attachment_id > 0) {
                        $metadata = $this->build_usage_metadata('page_builder', $post_id, 'elementor_gallery', array('builder' => 'elementor'));
                        $this->usage_db->store_usage(
                            $attachment_id,
                            'page_builder',
                            $post_id,
                            'elementor_gallery',
                            $metadata
                        );
                        $found_count++;
                        $this->progress['usage_found']++;
                    }
                }
            }
        }

        // IMAGE CAROUSEL: Another gallery type
        if ($widget_type === 'image-carousel' && isset($settings['carousel']) && is_array($settings['carousel'])) {
            foreach ($settings['carousel'] as $carousel_item) {
                if (isset($carousel_item['id'])) {
                    $attachment_id = intval($carousel_item['id']);
                    if ($attachment_id > 0) {
                        $metadata = $this->build_usage_metadata('page_builder', $post_id, 'elementor_carousel', array('builder' => 'elementor'));
                        $this->usage_db->store_usage(
                            $attachment_id,
                            'page_builder',
                            $post_id,
                            'elementor_carousel',
                            $metadata
                        );
                        $found_count++;
                        $this->progress['usage_found']++;
                    }
                }
            }
        }

        // VIDEO WIDGET: Poster/thumbnail image
        if ($widget_type === 'video' && isset($settings['poster']['id'])) {
            $attachment_id = intval($settings['poster']['id']);
            if ($attachment_id > 0) {
                $metadata = $this->build_usage_metadata('page_builder', $post_id, 'elementor_video_poster', array('builder' => 'elementor'));
                $this->usage_db->store_usage(
                    $attachment_id,
                    'page_builder',
                    $post_id,
                    'elementor_video_poster',
                    $metadata
                );
                $found_count++;
                $this->progress['usage_found']++;
            }
        }

        // BACKGROUND IMAGES: Can be on ANY widget/section/column
        // Check for background_image in settings
        if (isset($settings['background_image']['id'])) {
            $attachment_id = intval($settings['background_image']['id']);
            if ($attachment_id > 0) {
                $metadata = $this->build_usage_metadata('page_builder', $post_id, 'elementor_background', array('builder' => 'elementor'));
                $this->usage_db->store_usage(
                    $attachment_id,
                    'page_builder',
                    $post_id,
                    'elementor_background',
                    $metadata
                );
                $found_count++;
                $this->progress['usage_found']++;
            }
        }

        // BACKGROUND OVERLAY: Another background type
        if (isset($settings['background_overlay_image']['id'])) {
            $attachment_id = intval($settings['background_overlay_image']['id']);
            if ($attachment_id > 0) {
                $metadata = $this->build_usage_metadata('page_builder', $post_id, 'elementor_background_overlay', array('builder' => 'elementor'));
                $this->usage_db->store_usage(
                    $attachment_id,
                    'page_builder',
                    $post_id,
                    'elementor_background_overlay',
                    $metadata
                );
                $found_count++;
                $this->progress['usage_found']++;
            }
        }

        return $found_count;
    }

    /**
     * Build enhanced metadata with view/edit URLs and contextual information
     *
     * Creates a comprehensive metadata array for usage tracking that includes
     * view URLs, edit URLs, titles, scope information, and contextual notes.
     *
     * @since 4.0.0
     * @param string $usage_type    Type of usage (post, page, widget, customizer, css)
     * @param int    $usage_id      ID of the content (post ID, widget ID, etc.)
     * @param string $usage_context Context (featured_image, content, etc.)
     * @param array  $extra_data    Additional data specific to the usage type
     * @return array Enhanced metadata array
     */
    private function build_usage_metadata($usage_type, $usage_id, $usage_context, $extra_data = array()) {
        $metadata = array(
            'title' => '',
            'view_url' => '',
            'edit_url' => '',
            'primary_action' => 'view',
            'scope' => 'single',
            'notes' => ''
        );

        switch ($usage_type) {
            case 'post':
            case 'page':
                if ($usage_id > 0) {
                    $post = get_post($usage_id);
                    if ($post) {
                        $metadata['title'] = get_the_title($usage_id);
                        $metadata['view_url'] = get_permalink($usage_id);
                        $metadata['edit_url'] = admin_url('post.php?post=' . $usage_id . '&action=edit');
                        $metadata['scope'] = 'single';

                        // Context-specific notes
                        if ($usage_context === 'featured_image') {
                            $metadata['notes'] = 'Featured Image';
                        } elseif ($usage_context === 'content_image') {
                            $metadata['notes'] = 'In Content';
                        } elseif (strpos($usage_context, 'gutenberg_') === 0) {
                            $metadata['notes'] = 'Gutenberg Block';
                        } elseif (strpos($usage_context, 'shortcode_') === 0) {
                            $metadata['notes'] = 'Shortcode';
                        }
                    }
                }
                break;

            case 'widget':
                $metadata['primary_action'] = 'edit';
                $metadata['scope'] = 'multiple';
                $metadata['view_url'] = home_url('/');
                $metadata['edit_url'] = admin_url('widgets.php');

                if (isset($extra_data['widget_id'])) {
                    $metadata['title'] = $extra_data['widget_id'];
                    $metadata['notes'] = 'Appears in widget areas across the site';
                } else {
                    $metadata['title'] = 'Widget';
                    $metadata['notes'] = 'Widget usage';
                }
                break;

            case 'customizer':
                $metadata['primary_action'] = 'view';
                $metadata['scope'] = 'global';
                $metadata['view_url'] = home_url('/');
                $metadata['edit_url'] = admin_url('customize.php');

                if ($usage_context === 'custom_logo') {
                    $metadata['title'] = 'Site Logo';
                    $metadata['notes'] = 'Site-wide logo in customizer';
                } elseif ($usage_context === 'header_image') {
                    $metadata['title'] = 'Header Image';
                    $metadata['notes'] = 'Site-wide header image';
                } elseif ($usage_context === 'background_image') {
                    $metadata['title'] = 'Background Image';
                    $metadata['notes'] = 'Site-wide background image';
                }
                break;

            case 'css':
                $metadata['primary_action'] = 'view';
                $metadata['scope'] = 'global';
                $metadata['view_url'] = home_url('/');
                $metadata['edit_url'] = '';

                if (isset($extra_data['file'])) {
                    $file_name = basename($extra_data['file']);
                    $metadata['title'] = 'CSS: ' . $file_name;
                    $metadata['notes'] = 'Background image in stylesheet';
                } else {
                    $metadata['title'] = 'CSS Background';
                    $metadata['notes'] = 'Used in CSS file';
                }
                break;

            case 'page_builder':
                if ($usage_id > 0) {
                    $post = get_post($usage_id);
                    if ($post) {
                        $metadata['title'] = get_the_title($usage_id);
                        $metadata['view_url'] = get_permalink($usage_id);
                        $metadata['scope'] = 'single';

                        // Set builder-specific edit URLs
                        if (isset($extra_data['builder'])) {
                            $builder = $extra_data['builder'];

                            if ($builder === 'elementor') {
                                $metadata['edit_url'] = admin_url('post.php?post=' . $usage_id . '&action=elementor');
                                $metadata['notes'] = 'Elementor Page Builder';
                            } elseif ($builder === 'wpbakery') {
                                $metadata['edit_url'] = admin_url('post.php?post=' . $usage_id . '&action=edit');
                                $metadata['notes'] = 'WPBakery Page Builder';
                            } else {
                                $metadata['edit_url'] = admin_url('post.php?post=' . $usage_id . '&action=edit');
                                $metadata['notes'] = 'Page Builder';
                            }
                        } else {
                            $metadata['edit_url'] = admin_url('post.php?post=' . $usage_id . '&action=edit');
                            $metadata['notes'] = 'Page Builder';
                        }
                    }
                }
                break;

            default:
                // Generic fallback
                $metadata['title'] = ucfirst($usage_type);
                $metadata['notes'] = 'Used in ' . $usage_type;
                break;
        }

        return $metadata;
    }

    /**
     * Convert URL to attachment ID
     *
     * Attempts to find the attachment ID for a given URL.
     *
     * @since 4.0.0
     * @param string $url Media URL
     * @return int|false Attachment ID or false if not found
     */
    private function url_to_attachment_id($url) {
        // Clean up URL
        $url = trim($url);

        // WordPress has a built-in function for this
        $attachment_id = attachment_url_to_postid($url);

        if ($attachment_id > 0) {
            return $attachment_id;
        }

        // Try stripping size suffix (-150x150, -300x200, etc.) and retry
        $url_without_size = preg_replace('/-\d+x\d+(\.[a-z]{3,4})$/i', '$1', $url);

        if ($url_without_size !== $url) {
            $attachment_id = attachment_url_to_postid($url_without_size);
            if ($attachment_id > 0) {
                return $attachment_id;
            }
        }

        return false;
    }

    /**
     * Convert URL to file path
     *
     * @since 4.0.0
     * @param string $url
     * @return string|false
     */
    private function url_to_path($url) {
        // Remove protocol and domain
        $path = wp_parse_url($url, PHP_URL_PATH);

        if (!$path) {
            return false;
        }

        // Convert to absolute path
        $uploads_dir = wp_upload_dir();
        $base_url = $uploads_dir['baseurl'];
        $base_dir = $uploads_dir['basedir'];

        if (strpos($url, $base_url) === 0) {
            return str_replace($base_url, $base_dir, $url);
        }

        // Try as relative to ABSPATH
        $file_path = ABSPATH . ltrim($path, '/');

        if (file_exists($file_path)) {
            return $file_path;
        }

        return false;
    }

    /**
     * Get scan progress
     *
     * @since 4.0.0
     * @return array Current scan progress
     */
    public function get_progress() {
        return $this->progress;
    }
}
