<?php
/**
 * Media Type Information Class
 *
 * Provides media type-specific information including detection capabilities,
 * limitations, and special notes for different file types.
 *
 * @package    MediaInventoryForge
 * @subpackage Utilities
 * @since      4.0.0
 * @version    4.0.0
 */

// Prevent direct file access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * MIF Media Type Info Class
 *
 * Centralizes media type information for consistent display across the plugin.
 *
 * @since 4.0.0
 */
class MIF_Media_Type_Info {

    /**
     * Get information for a specific media type
     *
     * @since 4.0.0
     * @param string $type Media type (image, pdf, video, etc.)
     * @return array Media type information
     */
    public static function get_type_info($type) {
        $all_types = self::get_all_types();
        return isset($all_types[$type]) ? $all_types[$type] : self::get_default_info();
    }

    /**
     * Get all media type definitions
     *
     * @since 4.0.0
     * @return array All media type information
     */
    public static function get_all_types() {
        return array(
            'image' => array(
                'type' => 'image',
                'title' => 'Images (JPG, PNG, GIF, WEBP)',
                'icon' => '📷',
                'can_detect' => array(
                    'Featured images for posts and pages',
                    'Images in post/page content (<img> tags)',
                    'Gutenberg image, cover, and media-text blocks',
                    'Gallery attachments and shortcodes',
                    'Widget images (Image widget, Custom HTML)',
                    'Theme customizer (site logo, header, background)',
                    'Page builder images (Elementor, WPBakery, etc.)',
                    'CSS background-image declarations'
                ),
                'might_miss' => array(
                    'Hardcoded URLs in theme template files (.php)',
                    'JavaScript-loaded images and lazy loaders',
                    'Images used via REST API by external apps',
                    'Email templates or third-party integrations'
                ),
                'special_note' => 'Images marked as "unused" should be manually verified before deletion. CSS scanning reduces false positives significantly.'
            ),

            'pdf' => array(
                'type' => 'pdf',
                'title' => 'PDF Documents',
                'icon' => '📄',
                'can_detect' => array(
                    'Linked in post/page content (<a> tags)',
                    'Gutenberg file blocks (wp:file)',
                    'Download buttons in page builders',
                    'ACF file fields (if ACF plugin detected)'
                ),
                'might_miss' => array(
                    'Download manager plugins with custom delivery',
                    'PDFs served via PHP download scripts',
                    'Email attachments and form submissions',
                    'JavaScript download handlers'
                ),
                'special_note' => 'PDFs are often used in forms, email automation, and download managers. Double-check before removing.'
            ),

            'video' => array(
                'type' => 'video',
                'title' => 'Videos (MP4, MOV, AVI, WEBM)',
                'icon' => '🎬',
                'can_detect' => array(
                    'Gutenberg video blocks (wp:video)',
                    'Embedded with <video> HTML5 tags',
                    'Video shortcodes [video src="..."]',
                    'Page builder video widgets',
                    'Background videos in hero sections'
                ),
                'might_miss' => array(
                    'JavaScript video players and libraries',
                    'Streaming manifests (HLS, DASH)',
                    'Videos in LMS or membership plugins',
                    'Third-party video embeds'
                ),
                'special_note' => 'Video files are large. Even if unused, verify they\'re not needed for future content before removal.'
            ),

            'audio' => array(
                'type' => 'audio',
                'title' => 'Audio Files (MP3, WAV, OGG)',
                'icon' => '🎵',
                'can_detect' => array(
                    'Gutenberg audio blocks (wp:audio)',
                    'Embedded with <audio> HTML5 tags',
                    'Audio shortcodes [audio src="..."]',
                    'Page builder audio widgets',
                    'Podcast episodes in content'
                ),
                'might_miss' => array(
                    'JavaScript audio players',
                    'Podcast hosting plugins with custom delivery',
                    'Audio in third-party plugins'
                ),
                'special_note' => 'Audio files may be used in podcasting plugins. Verify before removal.'
            ),

            'svg' => array(
                'type' => 'svg',
                'title' => 'SVG Files',
                'icon' => '🎨',
                'can_detect' => array(
                    'Inline SVG in content',
                    'Linked SVG files (<img src="...">)',
                    'Theme customizer icons',
                    'CSS background-image SVGs',
                    'Gutenberg blocks with SVG images'
                ),
                'might_miss' => array(
                    'SVG sprites loaded via JavaScript',
                    'Hardcoded in theme template files',
                    'Icon libraries and web fonts',
                    'SVG symbols referenced by ID'
                ),
                'special_note' => 'SVGs are often used as icons throughout themes. Verify usage before deletion to avoid broken layouts.'
            ),

            'font' => array(
                'type' => 'font',
                'title' => 'Font Files (TTF, OTF, WOFF, WOFF2)',
                'icon' => '🔤',
                'can_detect' => array(
                    '@font-face declarations in stylesheets',
                    'Font files referenced in CSS',
                    'Theme CSS font loading'
                ),
                'might_miss' => array(
                    'Fonts loaded via JavaScript',
                    'Third-party font loaders',
                    'Web font services (Google Fonts, TypeKit)'
                ),
                'special_note' => 'Unused fonts can safely be removed to improve page load speed. Most sites only need 2-4 font files.'
            ),

            'document' => array(
                'type' => 'document',
                'title' => 'Documents (DOC, DOCX, XLS, PPT, etc.)',
                'icon' => '📑',
                'can_detect' => array(
                    'Linked in post/page content',
                    'Gutenberg file blocks',
                    'Download buttons and links',
                    'ACF file fields'
                ),
                'might_miss' => array(
                    'Document management plugins',
                    'Intranet systems',
                    'Form submission handlers',
                    'Email automation systems'
                ),
                'special_note' => 'Documents may be used in internal systems or forms. Verify before removal.'
            )
        );
    }

    /**
     * Get default/fallback type information
     *
     * @since 4.0.0
     * @return array Default type info
     */
    private static function get_default_info() {
        return array(
            'type' => 'unknown',
            'title' => 'Other Media',
            'icon' => '📎',
            'can_detect' => array(
                'Linked in post/page content',
                'Attached to posts/pages'
            ),
            'might_miss' => array(
                'Custom plugin implementations',
                'Third-party integrations'
            ),
            'special_note' => 'Verify usage before removal.'
        );
    }

    /**
     * Get media type from MIME type
     *
     * @since 4.0.0
     * @param string $mime_type WordPress MIME type
     * @return string Media type category
     */
    public static function get_type_from_mime($mime_type) {
        if (strpos($mime_type, 'image/') === 0) {
            if ($mime_type === 'image/svg+xml') {
                return 'svg';
            }
            return 'image';
        }

        if ($mime_type === 'application/pdf') {
            return 'pdf';
        }

        if (strpos($mime_type, 'video/') === 0) {
            return 'video';
        }

        if (strpos($mime_type, 'audio/') === 0) {
            return 'audio';
        }

        if (in_array($mime_type, array('font/ttf', 'font/otf', 'font/woff', 'font/woff2', 'application/x-font-ttf', 'application/x-font-otf', 'application/font-woff', 'application/font-woff2'))) {
            return 'font';
        }

        if (in_array($mime_type, array(
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'application/vnd.ms-powerpoint',
            'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'text/plain'
        ))) {
            return 'document';
        }

        return 'other';
    }

    /**
     * Render media type explanation
     *
     * @since 4.0.0
     * @param string $type  Media type
     * @param int    $count Total count
     * @param array  $stats Usage statistics
     * @return void
     */
    public static function render_explanation($type, $count = 0, $stats = array()) {
        $info = self::get_type_info($type);

        // Merge info with passed parameters
        $info['count'] = $count;
        $info['stats'] = $stats;

        // Extract variables for template
        extract($info);

        // Load template
        $template_path = MIF_PLUGIN_DIR . 'templates/admin/partials/media-type-explanation.php';

        if (file_exists($template_path)) {
            include $template_path;
        }
    }
}
