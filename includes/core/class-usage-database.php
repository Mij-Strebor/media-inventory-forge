<?php
/**
 * Media Inventory Forge - Usage Database Class
 *
 * Handles all database operations for media usage tracking. This class manages
 * storing, retrieving, and analyzing where media files are used throughout the
 * WordPress installation.
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
 * MIF Usage Database Class
 *
 * Manages the custom database table for tracking media usage locations.
 * Provides methods for storing, retrieving, and analyzing usage data.
 *
 * @since 4.0.0
 */
class MIF_Usage_Database {

    /**
     * Database table name (without prefix)
     *
     * @var string
     */
    private $table_name = 'mif_usage';

    /**
     * Full table name (with prefix)
     *
     * @var string
     */
    private $full_table_name;

    /**
     * WordPress database object
     *
     * @var wpdb
     */
    private $wpdb;

    /**
     * Constructor
     *
     * @since 4.0.0
     */
    public function __construct() {
        global $wpdb;
        $this->wpdb = $wpdb;
        $this->full_table_name = $wpdb->prefix . $this->table_name;
    }

    /**
     * Create the usage tracking table
     *
     * Called on plugin activation. Creates the custom table for storing
     * media usage location data with proper indexes for performance.
     *
     * @since 4.0.0
     * @return bool True on success, false on failure
     */
    public function create_table() {
        $charset_collate = $this->wpdb->get_charset_collate();

        $sql = "CREATE TABLE {$this->full_table_name} (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            attachment_id bigint(20) unsigned NOT NULL,
            usage_type varchar(50) NOT NULL,
            usage_id bigint(20) unsigned DEFAULT 0,
            usage_context varchar(100) DEFAULT '',
            usage_data text DEFAULT NULL,
            found_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY attachment_id (attachment_id),
            KEY usage_type (usage_type),
            KEY usage_id (usage_id),
            KEY found_at (found_at)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);

        // Verify table was created
        return $this->table_exists();
    }

    /**
     * Check if the usage table exists
     *
     * @since 4.0.0
     * @return bool True if table exists, false otherwise
     */
    public function table_exists() {
        $table = $this->wpdb->get_var(
            $this->wpdb->prepare(
                "SHOW TABLES LIKE %s",
                $this->full_table_name
            )
        );
        return $table === $this->full_table_name;
    }

    /**
     * Store usage data for an attachment
     *
     * @since 4.0.0
     * @param int    $attachment_id The attachment ID
     * @param string $usage_type    Type of usage (post, page, widget, css, etc.)
     * @param int    $usage_id      ID of the content using the media (post ID, widget ID, etc.)
     * @param string $usage_context Context of usage (featured_image, content, background, etc.)
     * @param array  $usage_data    Additional data about the usage
     * @return int|false Insert ID on success, false on failure
     */
    public function store_usage($attachment_id, $usage_type, $usage_id = 0, $usage_context = '', $usage_data = array()) {
        // Validate required parameters
        if (empty($attachment_id) || empty($usage_type)) {
            return false;
        }

        // Check if this exact usage already exists
        if ($this->usage_exists($attachment_id, $usage_type, $usage_id, $usage_context)) {
            return true; // Already tracked, no need to duplicate
        }

        $data = array(
            'attachment_id' => absint($attachment_id),
            'usage_type' => sanitize_text_field($usage_type),
            'usage_id' => absint($usage_id),
            'usage_context' => sanitize_text_field($usage_context),
            'usage_data' => maybe_serialize($usage_data),
            'found_at' => current_time('mysql')
        );

        $format = array('%d', '%s', '%d', '%s', '%s', '%s');

        $result = $this->wpdb->insert($this->full_table_name, $data, $format);

        return $result ? $this->wpdb->insert_id : false;
    }

    /**
     * Check if a specific usage already exists
     *
     * @since 4.0.0
     * @param int    $attachment_id
     * @param string $usage_type
     * @param int    $usage_id
     * @param string $usage_context
     * @return bool
     */
    private function usage_exists($attachment_id, $usage_type, $usage_id, $usage_context) {
        $count = $this->wpdb->get_var($this->wpdb->prepare(
            "SELECT COUNT(*) FROM {$this->full_table_name}
             WHERE attachment_id = %d
             AND usage_type = %s
             AND usage_id = %d
             AND usage_context = %s",
            $attachment_id,
            $usage_type,
            $usage_id,
            $usage_context
        ));

        return $count > 0;
    }

    /**
     * Get all usage data for a specific attachment
     *
     * @since 4.0.0
     * @param int $attachment_id The attachment ID
     * @return array Array of usage records
     */
    public function get_usage($attachment_id) {
        if (empty($attachment_id)) {
            return array();
        }

        $results = $this->wpdb->get_results($this->wpdb->prepare(
            "SELECT * FROM {$this->full_table_name}
             WHERE attachment_id = %d
             ORDER BY found_at DESC",
            $attachment_id
        ), ARRAY_A);

        if (!$results) {
            return array();
        }

        // Unserialize usage_data
        foreach ($results as &$result) {
            $result['usage_data'] = maybe_unserialize($result['usage_data']);
        }

        return $results;
    }

    /**
     * Get usage summary for an attachment
     *
     * @since 4.0.0
     * @param int $attachment_id The attachment ID
     * @return array Summary with 'used', 'usage_count', 'locations'
     */
    public function get_usage_summary($attachment_id) {
        $usage = $this->get_usage($attachment_id);

        return array(
            'used' => !empty($usage),
            'usage_count' => count($usage),
            'locations' => $usage
        );
    }

    /**
     * Get all attachments that have no usage records
     *
     * @since 4.0.0
     * @param array $args Optional query arguments
     * @return array Array of attachment IDs with no usage
     */
    public function get_unused_media($args = array()) {
        $defaults = array(
            'post_type' => 'attachment',
            'post_status' => 'inherit',
            'posts_per_page' => -1,
            'fields' => 'ids'
        );

        $args = wp_parse_args($args, $defaults);
        $all_attachments = get_posts($args);

        if (empty($all_attachments)) {
            return array();
        }

        // Get attachment IDs that have usage
        $used_ids = $this->wpdb->get_col(
            "SELECT DISTINCT attachment_id FROM {$this->full_table_name}"
        );

        // Return attachments that are NOT in the used list
        $unused = array_diff($all_attachments, $used_ids);

        return array_values($unused); // Re-index array
    }

    /**
     * Get attachments used in multiple locations
     *
     * @since 4.0.0
     * @param int $min_usage Minimum number of uses (default: 2)
     * @return array Array of attachment IDs and usage counts
     */
    public function get_frequently_used($min_usage = 2) {
        $results = $this->wpdb->get_results($this->wpdb->prepare(
            "SELECT attachment_id, COUNT(*) as usage_count
             FROM {$this->full_table_name}
             GROUP BY attachment_id
             HAVING COUNT(*) >= %d
             ORDER BY usage_count DESC",
            $min_usage
        ), ARRAY_A);

        return $results ? $results : array();
    }

    /**
     * Get usage statistics
     *
     * @since 4.0.0
     * @return array Statistics about media usage
     */
    public function get_usage_stats() {
        // Total attachments
        $total_attachments = wp_count_posts('attachment')->inherit;

        // Used attachments
        $used_count = $this->wpdb->get_var(
            "SELECT COUNT(DISTINCT attachment_id) FROM {$this->full_table_name}"
        );

        // Unused attachments
        $unused_count = $total_attachments - $used_count;

        // Usage by type
        $usage_by_type = $this->wpdb->get_results(
            "SELECT usage_type, COUNT(*) as count
             FROM {$this->full_table_name}
             GROUP BY usage_type
             ORDER BY count DESC",
            ARRAY_A
        );

        return array(
            'total_attachments' => $total_attachments,
            'used_count' => $used_count,
            'unused_count' => $unused_count,
            'usage_by_type' => $usage_by_type,
            'last_scan' => get_option('mif_last_usage_scan', 'Never')
        );
    }

    /**
     * Clear all usage data for a specific attachment
     *
     * @since 4.0.0
     * @param int $attachment_id The attachment ID
     * @return int|false Number of rows deleted, or false on failure
     */
    public function clear_attachment_usage($attachment_id) {
        if (empty($attachment_id)) {
            return false;
        }

        return $this->wpdb->delete(
            $this->full_table_name,
            array('attachment_id' => absint($attachment_id)),
            array('%d')
        );
    }

    /**
     * Clear all usage data (for complete rescan)
     *
     * @since 4.0.0
     * @return int|false Number of rows deleted, or false on failure
     */
    public function clear_all_usage() {
        return $this->wpdb->query("TRUNCATE TABLE {$this->full_table_name}");
    }

    /**
     * Delete usage records older than specified days
     *
     * @since 4.0.0
     * @param int $days Number of days (default: 30)
     * @return int|false Number of rows deleted
     */
    public function delete_old_usage($days = 30) {
        return $this->wpdb->query($this->wpdb->prepare(
            "DELETE FROM {$this->full_table_name}
             WHERE found_at < DATE_SUB(NOW(), INTERVAL %d DAY)",
            $days
        ));
    }

    /**
     * Get usage count by type for an attachment
     *
     * @since 4.0.0
     * @param int $attachment_id The attachment ID
     * @return array Usage count by type
     */
    public function get_usage_by_type($attachment_id) {
        $results = $this->wpdb->get_results($this->wpdb->prepare(
            "SELECT usage_type, COUNT(*) as count
             FROM {$this->full_table_name}
             WHERE attachment_id = %d
             GROUP BY usage_type",
            $attachment_id
        ), ARRAY_A);

        $counts = array();
        foreach ($results as $result) {
            $counts[$result['usage_type']] = $result['count'];
        }

        return $counts;
    }

    /**
     * Drop the usage tracking table
     *
     * Called on plugin uninstall. Removes the custom table.
     *
     * @since 4.0.0
     * @return bool True on success, false on failure
     */
    public function drop_table() {
        $result = $this->wpdb->query("DROP TABLE IF EXISTS {$this->full_table_name}");
        return $result !== false;
    }
}
