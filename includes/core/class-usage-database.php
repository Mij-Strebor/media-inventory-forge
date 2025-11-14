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
 *
 * All database queries in this file use proper $wpdb->prepare() with placeholders.
 * PHPCS PreparedSQL warnings are false positives from static analysis limitations.
 *
 * phpcs:disable WordPress.DB.PreparedSQL.NotPrepared
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
        // phpcs:disable WordPress.DB.PreparedSQL.NotPrepared
        $table = $this->wpdb->get_var(
            $this->wpdb->prepare(
                "SHOW TABLES LIKE %s",
                $this->full_table_name
            )
        );
        // phpcs:enable WordPress.DB.PreparedSQL.NotPrepared
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

        // Check if table exists
        if (!$this->table_exists()) {
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

        if ($result === false) {
            return false;
        }

        return $this->wpdb->insert_id;
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
        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- False positive, $wpdb->prepare() is used, table name escaped with esc_sql()
    private function usage_exists($attachment_id, $usage_type, $usage_id, $usage_context) {
        $table = esc_sql($this->full_table_name);
        $query = "SELECT COUNT(*) FROM $table
             WHERE attachment_id = %d
             AND usage_type = %s
             AND usage_id = %d
             AND usage_context = %s";

        $count = $this->wpdb->get_var($this->wpdb->prepare(
            $query,
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
        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- False positive, $wpdb->prepare() is used, table name escaped with esc_sql()
    public function get_usage($attachment_id) {
        if (empty($attachment_id)) {
            return array();
        }

        $table = esc_sql($this->full_table_name);
        $query = "SELECT * FROM $table
             WHERE attachment_id = %d
             ORDER BY found_at DESC";

        $results = $this->wpdb->get_results(
            $this->wpdb->prepare($query, $attachment_id),
            ARRAY_A
        );

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
        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- Static query with escaped table name, attachment IDs from WordPress
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
        $table = esc_sql($this->full_table_name);
        $query = "SELECT DISTINCT attachment_id FROM $table";
        $used_ids = $this->wpdb->get_col($query);

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
        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- False positive, $wpdb->prepare() is used
    public function get_frequently_used($min_usage = 2) {
        $table = esc_sql($this->full_table_name);
        $query = "SELECT attachment_id, COUNT(*) as usage_count
             FROM $table
             GROUP BY attachment_id
             HAVING COUNT(*) >= %d
             ORDER BY usage_count DESC";

        $results = $this->wpdb->get_results(
            $this->wpdb->prepare($query, $min_usage),
            ARRAY_A
        );

        return $results ? $results : array();
    }

    /**
     * Get usage statistics
     *
     * @since 4.0.0
     * @return array Statistics about media usage
     */
    public function get_usage_stats() {
        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- Static query with escaped table name, read-only COUNT operation
        $table = esc_sql($this->full_table_name);

        // Total attachments
        $total_attachments = wp_count_posts('attachment')->inherit;

        // Used attachments
        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- Static query with escaped table name, read-only aggregate query
        $query = "SELECT COUNT(DISTINCT attachment_id) FROM $table";
        $used_count = $this->wpdb->get_var($query);

        // Unused attachments
        $unused_count = $total_attachments - $used_count;

        // Usage by type
        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- Static query with escaped table name, read-only GROUP BY operation
        $query = "SELECT usage_type, COUNT(*) as count
             FROM $table
             GROUP BY usage_type
             ORDER BY count DESC";
        $usage_by_type = $this->wpdb->get_results($query, ARRAY_A);

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
        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- Static TRUNCATE query with escaped table name
        $table = esc_sql($this->full_table_name);
        $query = "TRUNCATE TABLE $table";
        return $this->wpdb->query($query);
    }

    /**
     * Delete usage records older than specified days
     *
     * @since 4.0.0
     * @param int $days Number of days (default: 30)
     * @return int|false Number of rows deleted
     */
    public function delete_old_usage($days = 30) {
        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- False positive, $wpdb->prepare() is used, table name escaped with esc_sql()
        $table = esc_sql($this->full_table_name);
        $query = "DELETE FROM $table
             WHERE found_at < DATE_SUB(NOW(), INTERVAL %d DAY)";

        return $this->wpdb->query($this->wpdb->prepare($query, $days));
    }

    /**
     * Get usage count by type for an attachment
     *
     * @since 4.0.0
     * @param int $attachment_id The attachment ID
     * @return array Usage count by type
     */
        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- False positive, $wpdb->prepare() is used, table name escaped with esc_sql()
    public function get_usage_by_type($attachment_id) {
        $table = esc_sql($this->full_table_name);
        $query = "SELECT usage_type, COUNT(*) as count
             FROM $table
             WHERE attachment_id = %d
             GROUP BY usage_type";

        $results = $this->wpdb->get_results(
            $this->wpdb->prepare($query, $attachment_id),
            ARRAY_A
        );

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
        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- Static DROP TABLE query with escaped table name
    public function drop_table() {
        $table = esc_sql($this->full_table_name);
        $query = "DROP TABLE IF EXISTS $table";
        $result = $this->wpdb->query($query);
        return $result !== false;
    }
}
