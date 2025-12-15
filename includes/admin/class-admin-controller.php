<?php

/**
 * Admin controller for Media Inventory Forge
 * 
 * @package MediaInventoryForge
 * @subpackage Admin
 * @since 2.0.0
 *
 * phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
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

        // Usage tracking AJAX handlers (backend kept, UI removed for v4.0.0)
        // Proper integration planned for v4.1.0
        add_action('wp_ajax_media_inventory_scan_usage', [$this, 'ajax_scan_usage']);
        add_action('wp_ajax_media_inventory_get_usage', [$this, 'ajax_get_usage']);
        add_action('wp_ajax_media_inventory_create_table', [$this, 'ajax_create_table']);

        // Table view AJAX handlers
        add_action('wp_ajax_mif_get_table_view', [$this, 'ajax_get_table_view']);
        add_action('wp_ajax_mif_save_view_preference', [$this, 'ajax_save_view_preference']);
        add_action('wp_ajax_mif_save_scan_results', [$this, 'ajax_save_scan_results']);
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

        // Get source filters
        $sources = isset($_POST['sources']) && is_array($_POST['sources'])
            ? array_map('sanitize_text_field', wp_unslash($_POST['sources']))
            : ['media-library']; // Default to media library only

        if ($offset < 0) {
            wp_send_json_error('Invalid offset parameter');
            return;
        }

        if ($batch_size < 1 || $batch_size > 50) {
            wp_send_json_error('Invalid batch size parameter');
            return;
        }

        try {
            // Store source filters for use by table view
            update_user_meta(get_current_user_id(), 'mif_last_scan_sources', $sources);

            $scanner = new MIF_Scanner($batch_size);
            $scanner->set_source_filters($sources);
            $result = $scanner->scan_batch($offset);

            if (defined('WP_DEBUG') && WP_DEBUG) {
                $result['debug'] = [
                    'memory_usage' => memory_get_usage(true),
                    'memory_peak' => memory_get_peak_usage(true),
                    'sources' => $sources
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

        // Clean the output buffer (closes ob_start)
        ob_end_clean();

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
            $table_name = esc_sql($wpdb->prefix . 'mif_usage');

            // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Static query with escaped table name, admin-only debug view
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
     * AJAX handler for getting table view
     *
     * Returns HTML for the table view with expandable rows
     *
     * @since 4.0.0
     */
    public function ajax_get_table_view()
    {
        check_ajax_referer('media_inventory_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Permission denied');
            return;
        }

        try {
            // Create table builder
            $table_builder = new MIF_Table_Builder();
            $html = $table_builder->build_tables();

            wp_send_json_success(['html' => $html]);
        } catch (Exception $e) {
            wp_send_json_error('Error loading table view: ' . $e->getMessage());
        }
    }

    /**
     * AJAX handler for saving view preference
     *
     * Saves user's preferred view mode (card or table) to user meta
     *
     * @since 4.0.0
     */
    public function ajax_save_view_preference()
    {
        check_ajax_referer('media_inventory_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Permission denied');
            return;
        }

        $view = isset($_POST['view']) ? sanitize_text_field(wp_unslash($_POST['view'])) : 'card';

        if (!in_array($view, ['card', 'table'], true)) {
            wp_send_json_error('Invalid view type');
            return;
        }

        update_user_meta(get_current_user_id(), 'mif_view_preference', $view);

        wp_send_json_success(['message' => 'Preference saved']);
    }

    /**
     * AJAX handler for saving scan results
     *
     * Saves the complete scan results for use by table view
     *
     * @since 4.0.0
     */
    public function ajax_save_scan_results()
    {
        check_ajax_referer('media_inventory_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Permission denied');
            return;
        }

        // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Sanitized after JSON decode
        $scan_data_raw = isset($_POST['scan_data']) ? wp_unslash($_POST['scan_data']) : '';

        if (empty($scan_data_raw)) {
            wp_send_json_error('No scan data provided');
            return;
        }

        // Decode and validate JSON format
        $scan_data_decoded = json_decode($scan_data_raw, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            wp_send_json_error('Invalid scan data format');
            return;
        }

        // Recursively sanitize all array values
        $scan_data_sanitized = $this->mif_sanitize_scan_data($scan_data_decoded);

        // Re-encode sanitized data for storage
        $scan_data_clean = wp_json_encode($scan_data_sanitized);

        // Store sanitized scan results in transient (24 hour expiration)
        $user_id = get_current_user_id();
        set_transient('mif_scan_results_' . $user_id, $scan_data_clean, DAY_IN_SECONDS);

        wp_send_json_success(['message' => 'Scan results saved', 'data_length' => strlen($scan_data_clean)]);
    }

    /**
     * Recursively sanitize scan data array
     *
     * Sanitizes all string values in the scan data array using appropriate
     * WordPress sanitization functions based on data type.
     *
     * @since 5.0.2
     * @param mixed $data Data to sanitize (array, string, int, etc.)
     * @return mixed Sanitized data
     */
    private function mif_sanitize_scan_data($data)
    {
        // Handle arrays recursively
        if (is_array($data)) {
            $sanitized = [];
            foreach ($data as $key => $value) {
                $sanitized_key = sanitize_key($key);
                $sanitized[$sanitized_key] = $this->mif_sanitize_scan_data($value);
            }
            return $sanitized;
        }

        // Handle strings - use text_field for most data
        if (is_string($data)) {
            return sanitize_text_field($data);
        }

        // Handle integers
        if (is_int($data)) {
            return intval($data);
        }

        // Handle floats
        if (is_float($data)) {
            return floatval($data);
        }

        // Handle booleans
        if (is_bool($data)) {
            return (bool) $data;
        }

        // Return null for anything else
        return null;
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
