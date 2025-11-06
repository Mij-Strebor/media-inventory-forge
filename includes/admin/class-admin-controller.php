<?php

/**
 * Admin controller for Media Inventory Forge
 * 
 * @package MediaInventoryForge
 * @subpackage Admin
 * @since 2.0.0
 */

defined('ABSPATH') || exit;

/**
 * Class MIF_Admin_Controller
 * 
 * Handles admin menu, AJAX requests for scanning and exporting media inventory.
 * Relies on MIF_Scanner and MIF_File_Processor for core functionality.
 */
class MIF_Admin_Controller
{
    /**
     * Constructor
     * 
     * Initializes admin functionality with proper hook priorities
     * to ensure compatibility with shared menu systems.
     */
    public function __construct()
    {
        // Use later priority to ensure other menu systems are ready
        add_action('admin_menu', [$this, 'add_admin_menu'], 15);
        add_action('wp_ajax_media_inventory_scan', [$this, 'ajax_scan']);
        add_action('wp_ajax_media_inventory_export', [$this, 'ajax_export']);
        add_action('wp_ajax_media_inventory_scan_usage', [$this, 'ajax_scan_usage']);
        add_action('wp_ajax_media_inventory_get_usage', [$this, 'ajax_get_usage']);
        add_action('wp_ajax_media_inventory_create_table', [$this, 'ajax_create_table']);
    }

    /**
     * Add admin menu to WordPress Tools section
     * 
     * Creates a standalone menu item under Tools for Media Inventory Forge,
     * providing independent access without requiring external menu systems.
     */
    public function add_admin_menu()
    {
        // Add to WordPress Tools menu
        add_management_page(
            'Media Inventory Forge',           // Page title
            'Media Inventory Forge',           // Menu title
            'manage_options',                  // Capability
            'media-inventory-forge',           // Menu slug
            [$this, 'admin_page']             // Callback function
        );
    }

    /**
     * AJAX handler for scanning media
     * 
     * Processes media library scanning requests in batches with
     * comprehensive error handling and security validation.
     */
    public function ajax_scan()
    {
        check_ajax_referer('media_inventory_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Permission denied');
            return;
        }

        $offset = isset($_POST['offset']) ? intval($_POST['offset']) : 0;
        $batch_size = isset($_POST['batch_size']) ? intval($_POST['batch_size']) : 10;

        if ($offset < 0) {
            wp_send_json_error('Invalid offset parameter');
            return;
        }

        if ($batch_size < 1 || $batch_size > 50) {
            wp_send_json_error('Invalid batch size parameter');
            return;
        }

        try {
            $scanner = new MIF_Scanner($batch_size);
            $result = $scanner->scan_batch($offset);

            if (defined('WP_DEBUG') && WP_DEBUG) {
                $result['debug'] = [
                    'memory_usage' => memory_get_usage(true),
                    'memory_peak' => memory_get_peak_usage(true)
                ];
            }

            wp_send_json_success($result);
        } catch (Exception $e) {
            wp_send_json_error('Scan failed: ' . $e->getMessage());
        }
    }

    /**
     * AJAX handler for exporting inventory data
     * 
     * Generates and downloads CSV export of media inventory data
     * with comprehensive file details and metadata.
     */
    public function ajax_export()
    {
        check_ajax_referer('media_inventory_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_die('Permission denied');
        }

        $inventory_data = isset($_POST['inventory_data']) ? json_decode(stripslashes(sanitize_textarea_field(wp_unslash($_POST['inventory_data']))), true) : null;

        if (empty($inventory_data)) {
            wp_die('No data to export');
        }

        // Suppress fclose warning by using alternative CSV approach
        ob_start();

        $csv_content = "ID,Title,Category,Extension,MIME Type,Dimensions,Thumbnail URL,Font Family,File Count,Total Size,Total Size (Formatted),File Details\n";

        foreach ($inventory_data as $item) {
            $file_details = [];
            foreach ($item['files'] as $file) {
                $details = $file['filename'] . ' (' . $file['type'] . ')';
                if (!empty($file['dimensions'])) {
                    $details .= ' - ' . $file['dimensions'];
                }
                $details .= ' - ' . MIF_File_Utils::format_bytes($file['size']);
                $file_details[] = $details;
            }

            $csv_content .= sprintf(
                '"%s","%s","%s","%s","%s","%s","%s","%s","%s","%s","%s","%s"' . "\n",
                str_replace('"', '""', $item['id']),
                str_replace('"', '""', $item['title']),
                str_replace('"', '""', $item['category']),
                str_replace('"', '""', $item['extension']),
                str_replace('"', '""', $item['mime_type']),
                str_replace('"', '""', $item['dimensions'] ?? ''),
                str_replace('"', '""', $item['thumbnail_url'] ?? ''),
                str_replace('"', '""', $item['font_family'] ?? ''),
                str_replace('"', '""', $item['file_count']),
                str_replace('"', '""', $item['total_size']),
                str_replace('"', '""', MIF_File_Utils::format_bytes($item['total_size'])),
                str_replace('"', '""', implode(' | ', $file_details))
            );
        }

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="media-inventory-' . gmdate('Y-m-d-H-i-s') . '.csv"');
        header('Pragma: no-cache');
        header('Expires: 0');

        // WordPress requires escaping but CSV content is pre-formatted data
        // Use nocache_headers() and direct output for file downloads
        nocache_headers();
        // CSV content is pre-formatted binary data for file download
        // All user input was sanitized during JSON decode (line 61-62)
        // Escaping would corrupt the CSV file format and prevent proper download
        // This is the WordPress-approved pattern for file downloads
        echo $csv_content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- CSV binary file download
        exit;
    }

    /**
     * AJAX handler for scanning media usage
     *
     * Scans WordPress content to find where media files are being used.
     * Detects usage in posts, pages, widgets, customizer, CSS, and page builders.
     *
     * @since 4.0.0
     */
    public function ajax_scan_usage()
    {
        check_ajax_referer('media_inventory_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Permission denied');
            return;
        }

        try {
            $scanner = new MIF_Usage_Scanner();
            $result = $scanner->scan_all_usage();

            // Get usage statistics
            $usage_db = new MIF_Usage_Database();
            $stats = $usage_db->get_usage_stats();

            wp_send_json_success([
                'progress' => $result,
                'stats' => $stats,
                'message' => 'Usage scan completed successfully!'
            ]);
        } catch (Exception $e) {
            wp_send_json_error('Usage scan failed: ' . $e->getMessage());
        }
    }

    /**
     * AJAX handler for getting usage data
     *
     * Returns all usage records from the database for display/debugging.
     *
     * @since 4.0.0
     */
    public function ajax_get_usage()
    {
        check_ajax_referer('media_inventory_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Permission denied');
            return;
        }

        try {
            global $wpdb;
            $table_name = $wpdb->prefix . 'mif_usage';

            $results = $wpdb->get_results(
                "SELECT * FROM {$table_name} ORDER BY found_at DESC LIMIT 50",
                ARRAY_A
            );

            // Unserialize usage_data for easier reading
            foreach ($results as &$row) {
                if (isset($row['usage_data'])) {
                    $row['usage_data'] = maybe_unserialize($row['usage_data']);
                }
            }

            wp_send_json_success([
                'count' => count($results),
                'data' => $results
            ]);
        } catch (Exception $e) {
            wp_send_json_error('Failed to get usage data: ' . $e->getMessage());
        }
    }

    /**
     * AJAX handler for creating usage table
     *
     * Creates the wp_mif_usage table if it doesn't exist.
     *
     * @since 4.0.0
     */
    public function ajax_create_table()
    {
        check_ajax_referer('media_inventory_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Permission denied');
            return;
        }

        try {
            $usage_db = new MIF_Usage_Database();
            $result = $usage_db->create_table();

            if ($result) {
                wp_send_json_success([
                    'message' => 'Table created successfully!',
                    'table_exists' => true
                ]);
            } else {
                wp_send_json_error('Failed to create table');
            }
        } catch (Exception $e) {
            wp_send_json_error('Error: ' . $e->getMessage());
        }
    }

    /**
     * Render admin page
     *
     * Loads and displays the main Media Inventory Forge admin interface
     * with scan controls, progress tracking, and results display.
     */
    public function admin_page()
    {
        include MIF_PLUGIN_DIR . 'templates/admin/main-page.php';
    }
}
