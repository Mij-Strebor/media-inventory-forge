<?php

/**
 * Media Inventory Forge Uninstall Handler
 *
 * Handles complete plugin uninstallation and cleanup. Removes all database tables,
 * options, transients, and user meta created by the plugin to ensure clean removal
 * and compliance with WordPress.org guidelines.
 *
 * Cleanup includes:
 * - Custom database table (wp_minvf_usage)
 * - Plugin options (minvf_activated_at, minvf_version, minvf_last_usage_scan)
 * - User transients (minvf_scan_results_{user_id})
 * - User meta (minvf_view_preference, minvf_last_scan_sources)
 *
 * @package MediaInventoryForge
 * @since 2.1.1
 * @version 5.0.2
 */

// If uninstall not called from WordPress, exit
if (!defined('WP_UNINSTALL_PLUGIN')) {
   exit;
}

// Access WordPress database
global $wpdb;

/* ==========================================================================
   1. DROP CUSTOM DATABASE TABLE
   ========================================================================== */

/**
 * Remove usage tracking table
 *
 * Drops the custom wp_minvf_usage table that stores media usage location data.
 */
$table_name = esc_sql($wpdb->prefix . 'minvf_usage');
// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Uninstall cleanup, table name is escaped
$wpdb->query("DROP TABLE IF EXISTS {$table_name}");

/* ==========================================================================
   2. DELETE PLUGIN OPTIONS
   ========================================================================== */

/**
 * Remove all plugin options from wp_options table
 *
 * Cleans up activation timestamp, version tracking, and last scan time.
 */
delete_option('minvf_activated_at');
delete_option('minvf_version');
delete_option('minvf_last_usage_scan');

/* ==========================================================================
   3. DELETE USER TRANSIENTS
   ========================================================================== */

/**
 * Remove scan result transients for all users
 *
 * Transients are stored with user-specific keys (minvf_scan_results_{user_id}).
 * We need to delete these for all users to prevent orphaned data.
 */
$users = get_users(array('fields' => 'ID'));
foreach ($users as $user_id) {
   delete_transient('minvf_scan_results_' . $user_id);
}

/* ==========================================================================
   4. DELETE USER META
   ========================================================================== */

/**
 * Remove all user meta created by the plugin
 *
 * Deletes view preferences and scan source filters for all users.
 */
// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Uninstall cleanup, most efficient method
$wpdb->query("DELETE FROM {$wpdb->usermeta} WHERE meta_key = 'minvf_view_preference'");
// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Uninstall cleanup, most efficient method
$wpdb->query("DELETE FROM {$wpdb->usermeta} WHERE meta_key = 'minvf_last_scan_sources'");

/* ==========================================================================
   5. CLEAR ANY CACHED DATA
   ========================================================================== */

/**
 * Flush WordPress object cache to ensure clean removal
 *
 * Removes any cached plugin data from memory caches.
 */
wp_cache_flush();

/* ==========================================================================
   END OF UNINSTALL CLEANUP
   ========================================================================== */
