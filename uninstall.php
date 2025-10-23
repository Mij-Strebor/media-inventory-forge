<?php

/**
 * Media Inventory Forge Uninstall Handler
 * 
 * Handles plugin uninstallation and cleanup. Currently, Media Inventory Forge
 * does not store any persistent data (no database tables, no options, no transients),
 * so this file exists primarily to meet WordPress.org submission requirements.
 * 
 * Future versions may store settings or cached data that would require cleanup here.
 * 
 * @package MediaInventoryForge
 * @since 2.1.1
 */

// If uninstall not called from WordPress, exit
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Plugin does not currently store any data in the database
// No options, no transients, no custom tables to clean up

// If future versions add persistent storage, cleanup would go here:
// delete_option('mif_settings');
// delete_transient('mif_cache');
// $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}mif_data");