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
                $this->usage_db->store_usage(
                    $attachment_id,
                    $content_type,
                    $content_id,
                    'content_image',
                    array('tag' => 'img')
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
                    $this->usage_db->store_usage(
                        intval($id),
                        $content_type,
                        $content_id,
                        'gutenberg_gallery'
                    );
                    $found_count++;
                    $this->progress['usage_found']++;
                }
                continue; // Skip the single ID logic below
            }

            if ($attachment_id) {
                $this->usage_db->store_usage(
                    $attachment_id,
                    $content_type,
                    $content_id,
                    $context
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
                    $this->usage_db->store_usage(
                        $attachment_id,
                        $content_type,
                        $content_id,
                        'shortcode_gallery'
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
                $this->usage_db->store_usage(
                    $attachment_id,
                    $content_type,
                    $content_id,
                    'linked_media'
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
                $this->usage_db->store_usage(
                    $attachment_id,
                    $content_type,
                    $content_id,
                    'shortcode_' . $shortcode[1]
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

        $results = $wpdb->get_results(
            "SELECT post_id, meta_value as attachment_id
             FROM {$wpdb->postmeta}
             WHERE meta_key = '_thumbnail_id'
             AND meta_value > 0"
        );

        $found_count = 0;

        foreach ($results as $row) {
            $this->usage_db->store_usage(
                intval($row->attachment_id),
                'post',
                intval($row->post_id),
                'featured_image'
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
            $this->usage_db->store_usage(
                intval($widget_data['attachment_id']),
                'widget',
                0,
                'widget_' . $widget_type,
                array('widget_id' => $widget_identifier)
            );
            $found_count++;
            $this->progress['usage_found']++;
        }

        // Check for image_id field
        if (isset($widget_data['image_id']) && $widget_data['image_id'] > 0) {
            $this->usage_db->store_usage(
                intval($widget_data['image_id']),
                'widget',
                0,
                'widget_' . $widget_type,
                array('widget_id' => $widget_identifier)
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
            $this->usage_db->store_usage(
                intval($theme_mods['custom_logo']),
                'customizer',
                0,
                'custom_logo'
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
                    $this->usage_db->store_usage($attachment_id, 'customizer', 0, 'header_image');
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
                    $this->usage_db->store_usage($attachment_id, 'customizer', 0, 'background_image');
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
                $this->usage_db->store_usage(
                    $attachment_id,
                    'css',
                    0,
                    'css_background',
                    array('file' => $source_file, 'url' => $url)
                );
                $found_count++;
                $this->progress['usage_found']++;
            }
        }

        return $found_count;
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
        $path = parse_url($url, PHP_URL_PATH);

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
