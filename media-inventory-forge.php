<?php

/**
 * Plugin Name: Media Inventory Forge
 * Plugin URI: https://github.com/Mij-Strebor/media-inventory-forge
 * Description: Professional media library scanner and analyzer for WordPress developers
 * Version: 5.0.2
 * Author: Jim R Forge
 * Author URI: https://jimrforge.com
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: media-inventory-forge
 * Requires at least: 5.0
 * Tested up to: 6.9
 * Requires PHP: 7.4
 * Network: true
 */

defined('ABSPATH') || exit;

/* ==========================================================================
   PLUGIN CONSTANTS
   ========================================================================== */

define('MINVF_VERSION',     '5.0.2');
define('MINVF_PLUGIN_FILE', __FILE__);
define('MINVF_PLUGIN_DIR',  plugin_dir_path(__FILE__));
define('MINVF_PLUGIN_URL',  plugin_dir_url(__FILE__));

/* ==========================================================================
   CLASS LOADING
   ========================================================================== */

require_once MINVF_PLUGIN_DIR . 'includes/utilities/class-file-utils.php';
require_once MINVF_PLUGIN_DIR . 'includes/utilities/class-media-type-info.php';
require_once MINVF_PLUGIN_DIR . 'includes/core/interface-file-processor.php';
require_once MINVF_PLUGIN_DIR . 'includes/core/class-file-processor.php';
require_once MINVF_PLUGIN_DIR . 'includes/core/class-image-processor.php';
require_once MINVF_PLUGIN_DIR . 'includes/core/class-font-processor.php';
require_once MINVF_PLUGIN_DIR . 'includes/core/class-processor-factory.php';
require_once MINVF_PLUGIN_DIR . 'includes/core/class-scanner.php';
require_once MINVF_PLUGIN_DIR . 'includes/core/class-usage-database.php';
require_once MINVF_PLUGIN_DIR . 'includes/core/class-usage-scanner.php';
require_once MINVF_PLUGIN_DIR . 'includes/admin/class-admin.php';
require_once MINVF_PLUGIN_DIR . 'includes/admin/class-admin-controller.php';
require_once MINVF_PLUGIN_DIR . 'includes/admin/class-table-builder.php';
require_once MINVF_PLUGIN_DIR . 'includes/admin/class-media-list-table.php';

/* ==========================================================================
   PLUGIN INITIALIZATION
   ========================================================================== */

if (is_admin()) {
    new MINVF_Admin();
    new MINVF_Admin_Controller();
}

/* ==========================================================================
   ACTIVATION HOOK
   ========================================================================== */

function minvf_activate_plugin()
{
    $usage_db = new MINVF_Usage_Database();
    $usage_db->create_table();

    add_option('minvf_activated_at', current_time('mysql'));
    add_option('minvf_version', MINVF_VERSION);
}

register_activation_hook(MINVF_PLUGIN_FILE, 'minvf_activate_plugin');

// Deactivation hook intentionally not registered — no scheduled events or temp data to clean up.
// Uninstall is handled by uninstall.php.
