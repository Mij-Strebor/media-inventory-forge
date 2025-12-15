<?php

/**
 * Plugin Name: Media Inventory Forge
 * Plugin URI: https://jimrforge.com/plugins/media-inventory-forge
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

/**
 * Media Inventory Forge - Main Plugin Bootstrap File
 *
 * This file serves as the primary entry point for the Media Inventory Forge plugin,
 * a comprehensive media library scanning and analysis tool designed for WordPress
 * developers and administrators. The plugin provides detailed insights into media
 * assets, file organization, and optimization opportunities.
 *
 * Key Features:
 * - Comprehensive media library scanning and analysis
 * - File type categorization and size optimization recommendations
 * - Detailed metadata extraction and reporting
 * - Professional admin interface with inventory management
 * - Extensible architecture with processor factory pattern
 * - Network multisite compatibility
 *
 * Architecture Overview:
 * - Core processing engine with specialized file processors
 * - Factory pattern for extensible file type handling
 * - Utility classes for common file operations
 * - Admin interface with controller pattern for separation of concerns
 * - Modular design enabling feature extension and customization
 *
 * Security Considerations:
 * - Direct access prevention through ABSPATH checks
 * - Proper capability checks in admin interfaces
 * - Sanitized input handling throughout processing pipeline
 * - Secure file access patterns with WordPress filesystem API
 *
 * Performance Features:
 * - Efficient batch processing for large media libraries
 * - Memory-conscious scanning algorithms
 * - Caching strategies for repeated operations
 * - Progressive loading for admin interface responsiveness
 *
 * @package    MediaInventoryForge
 * @subpackage Core
 * @since      1.0.0
 * @version    5.0.2
 * @author     Jim R Forge
 * @link       https://jimrforge.com/plugins/media-inventory-forge
 * @license    GPL-2.0-or-later
 *
 * @wordpress-plugin
 * @requires-php 7.4
 * @requires-wp  5.0
 * @tested-up-to 6.4
 * @network      true
 */

/* ==========================================================================
   SECURITY AND ACCESS CONTROL
   ========================================================================== */

/**
 * Prevent Direct File Access
 *
 * Security measure to prevent direct execution of this file outside of
 * WordPress context. This is a critical security practice for all WordPress
 * plugins to prevent unauthorized access and potential security vulnerabilities.
 */
if (!defined('ABSPATH')) {
    exit;
}

/* ==========================================================================
   PLUGIN CONSTANTS DEFINITION
   ========================================================================== */

/**
 * Define Core Plugin Constants
 *
 * These constants provide consistent access to plugin metadata and paths
 * throughout the application. Constants are defined with existence checks
 * to prevent redefinition errors during testing or multiple inclusions.
 */

/**
 * Plugin Version Constant
 *
 * Current version of the Media Inventory Forge plugin. Used for cache busting,
 * database migrations, and compatibility checks. Should be updated with each
 * release following semantic versioning principles.
 *
 * @var   string MIF_VERSION Current plugin version
 */
if (!defined('MIF_VERSION')) {
    define('MIF_VERSION', '5.0.2');
}

/**
 * Plugin Main File Constant
 *
 * Reference to the main plugin file for use in WordPress hooks and
 * plugin management functions. Required for proper plugin activation,
 * deactivation, and uninstall procedures.
 *
 * @var   string MIF_PLUGIN_FILE Absolute path to main plugin file
 */
if (!defined('MIF_PLUGIN_FILE')) {
    define('MIF_PLUGIN_FILE', __FILE__);
}

/**
 * Plugin Directory Path Constant
 *
 * Absolute filesystem path to the plugin directory. Used for including
 * files, accessing templates, and file system operations. Provides
 * consistent path resolution across different hosting environments.
 *
 * @var   string MIF_PLUGIN_DIR Absolute path to plugin directory
 */
if (!defined('MIF_PLUGIN_DIR')) {
    define('MIF_PLUGIN_DIR', plugin_dir_path(__FILE__));
}

/**
 * Plugin URL Constant
 *
 * Base URL for the plugin directory. Used for enqueueing assets (CSS, JS),
 * linking to plugin resources, and generating public-facing URLs.
 * Automatically handles SSL and WordPress subdirectory installations.
 *
 * @var   string MIF_PLUGIN_URL Base URL to plugin directory
 */
if (!defined('MIF_PLUGIN_URL')) {
    define('MIF_PLUGIN_URL', plugin_dir_url(__FILE__));
}
/* ==========================================================================
   DEPENDENCY MANAGEMENT AND CLASS LOADING
   ========================================================================== */

/**
 * Load File Utilities Class
 *
 * The File_Utils class provides common file system operations, validation,
 * and helper methods used throughout the plugin for file manipulation
 * and analysis.
 */
require_once MIF_PLUGIN_DIR . 'includes/utilities/class-file-utils.php';

/**
 * Load Media Type Information Class
 *
 * Provides media type-specific information including detection capabilities
 * and limitations for different file types. Used for user education about
 * what the usage scanner can and cannot detect.
 *
 * @since 4.0.0
 */
require_once MIF_PLUGIN_DIR . 'includes/utilities/class-media-type-info.php';

/**
 * Load File Processor Interface
 *
 * Defines the contract for all file processors, ensuring consistent
 * implementation across different file type handlers. This interface
 * enables polymorphic processing and extensibility.
 */
require_once MIF_PLUGIN_DIR . 'includes/core/interface-file-processor.php';

/**
 * Load Base File Processor Class
 *
 * Abstract base class providing common functionality for all file processors.
 * Implements shared processing logic and provides extension points for
 * specialized file type handling.
 */
require_once MIF_PLUGIN_DIR . 'includes/core/class-file-processor.php';

/**
 * Load Image Processor Class
 *
 * Specialized processor for image files including metadata extraction,
 * dimension analysis, optimization recommendations, and thumbnail generation
 * support. Handles common image formats (JPEG, PNG, GIF, WebP).
 */
require_once MIF_PLUGIN_DIR . 'includes/core/class-image-processor.php';

/**
 * Load Font Processor Class
 *
 * Specialized processor for font files including metadata extraction,
 * font family detection, and file analysis for various font formats.
 */
require_once MIF_PLUGIN_DIR . 'includes/core/class-font-processor.php';

/**
 * Load Processor Factory Class
 *
 * Factory pattern implementation for creating appropriate file processors
 * based on file type. Enables extensible architecture where new file
 * types can be supported by adding new processor classes.
 */
require_once MIF_PLUGIN_DIR . 'includes/core/class-processor-factory.php';

/**
 * Load Scanner Class
 *
 * Main scanning engine responsible for traversing directories, identifying
 * files, and coordinating processing through appropriate processors.
 * Implements efficient algorithms for large-scale media library analysis.
 */
require_once MIF_PLUGIN_DIR . 'includes/core/class-scanner.php';

/**
 * Load Usage Database Class
 *
 * Database operations for media usage tracking. Manages custom table for
 * storing and retrieving information about where media files are used
 * throughout the WordPress installation.
 *
 * @since 4.0.0
 */
require_once MIF_PLUGIN_DIR . 'includes/core/class-usage-database.php';

/**
 * Load Usage Scanner Class
 *
 * Scans WordPress content to detect media usage locations. Analyzes posts,
 * pages, widgets, theme customizer, CSS files, and page builders to build
 * comprehensive usage map for each media file.
 *
 * @since 4.0.0
 */
require_once MIF_PLUGIN_DIR . 'includes/core/class-usage-scanner.php';

/**
 * Load Admin Base Class
 *
 * Core admin functionality including menu registration, page setup,
 * asset enqueueing, and admin interface initialization. Provides
 * foundation for all administrative features.
 */
require_once MIF_PLUGIN_DIR . 'includes/admin/class-admin.php';

/**
 * Load Admin Controller Class
 *
 * Controller pattern implementation for handling admin requests,
 * processing form submissions, and managing admin interface state.
 * Separates presentation logic from business logic for maintainability.
 */
require_once MIF_PLUGIN_DIR . 'includes/admin/class-admin-controller.php';

/**
 * Load Table Builder Class
 *
 * Builds category-based HTML tables with expandable rows for
 * displaying media inventory in organized, interactive table format.
 *
 * @since 4.0.0
 */
require_once MIF_PLUGIN_DIR . 'includes/admin/class-table-builder.php';

/* ==========================================================================
   INTERNATIONALIZATION (I18N)
   ========================================================================== */

/**
 * Translation Support
 *
 * As of WordPress 4.6, translation loading for plugins hosted on WordPress.org
 * is handled automatically by WordPress. The load_plugin_textdomain() function
 * is no longer needed when using the standard text domain that matches the
 * plugin slug.
 *
 * Translation files are managed through translate.wordpress.org and loaded
 * automatically when available for the site's locale.
 *
 * Text domain: media-inventory-forge (matches plugin slug)
 *
 * @since 5.0.2
 */

/* ==========================================================================
   PLUGIN INITIALIZATION
   ========================================================================== */

/**
 * Initialize Admin Functionality
 *
 * Admin classes are only instantiated when in WordPress admin context
 * to optimize performance and prevent unnecessary loading on frontend.
 * Both admin classes are instantiated to provide complete administrative
 * functionality.
 *
 * Security Note: Admin instantiation is protected by WordPress's is_admin()
 * function, ensuring admin functionality is only available in appropriate
 * contexts with proper user capabilities.
 */
if (is_admin()) {
    /**
     * Initialize Main Admin Class
     *
     * Handles core admin functionality including menu registration,
     * page setup, and asset management. Provides the foundation
     * for all administrative interfaces.
     */
    new MIF_Admin();

    /**
     * Initialize Admin Controller
     *
     * Handles request processing, form submissions, and admin state
     * management. Implements controller pattern for separation of
     * concerns and maintainable admin interface architecture.
     */
    new MIF_Admin_Controller();
}

/* ==========================================================================
   PLUGIN ACTIVATION, DEACTIVATION, AND UNINSTALL HOOKS
   ========================================================================== */

/**
 * Plugin Lifecycle Management
 *
 * WordPress provides hooks for plugin activation, deactivation, and uninstall
 * events. These hooks manage database setup, cleanup, and complete removal.
 *
 * Current Implementation:
 * ✅ Activation hook - Creates database tables and sets initial options
 * ⚠️ Deactivation hook - Not implemented (optional for this plugin)
 * ✅ Uninstall hook - Handled via uninstall.php (WordPress best practice)
 *
 * @since 4.0.0
 * @version 5.0.2
 */

/**
 * Plugin Activation Callback
 *
 * Called when the plugin is activated. Creates database tables and sets
 * default options for proper plugin initialization.
 *
 * Actions performed:
 * - Creates wp_mif_usage custom table for media usage tracking
 * - Sets activation timestamp for plugin lifecycle tracking
 * - Stores current plugin version for future upgrade checks
 *
 * @since 4.0.0
 * @return void
 */
function mif_activate_plugin()
{
    // Create usage tracking table
    $usage_db = new MIF_Usage_Database();
    $usage_db->create_table();

    // Set activation timestamp
    add_option('mif_activated_at', current_time('mysql'));
    add_option('mif_version', MIF_VERSION);
}

/**
 * Register Plugin Activation Hook
 *
 * Registers the activation callback to run when the plugin is activated.
 * This ensures proper database setup and option initialization.
 *
 * @since 4.0.0
 */
register_activation_hook(MIF_PLUGIN_FILE, 'mif_activate_plugin');

/**
 * Plugin Deactivation Hook
 *
 * Deactivation hook is intentionally NOT implemented for this plugin.
 * The plugin does not use scheduled events or temporary data that requires
 * cleanup on deactivation. All persistent data is removed via uninstall.php
 * when the plugin is deleted.
 *
 * @since 5.0.2
 * @note Deactivation != Deletion. Use uninstall.php for complete cleanup.
 */
// Deactivation hook intentionally not registered

/**
 * Plugin Uninstall Hook
 *
 * Uninstall cleanup is handled via uninstall.php (WordPress best practice).
 * The uninstall.php file is automatically executed when the plugin is deleted
 * from the WordPress admin interface.
 *
 * Cleanup performed in uninstall.php:
 * ✅ Drops wp_mif_usage custom table
 * ✅ Deletes all plugin options (mif_*)
 * ✅ Removes all user transients
 * ✅ Removes all user meta data
 * ✅ Flushes WordPress cache
 *
 * @since 4.0.0
 * @version 5.0.2
 * @see uninstall.php Complete uninstall implementation
 * @note Using uninstall.php instead of register_uninstall_hook() per WordPress guidelines
 */
// Uninstall handled by uninstall.php

/* ==========================================================================
   END OF PLUGIN BOOTSTRAP
   ========================================================================== */