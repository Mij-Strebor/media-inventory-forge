<?php

/**
 * Admin interface functionality for Media Inventory Forge
 * 
 * @package MediaInventoryForge
 * @subpackage Admin
 * @since 2.0.0
 * 
 * Handles enqueuing admin scripts/styles and localizing script data.
 */

// Prevent direct access
defined('ABSPATH') || exit;

/**
 * Class MINVF_Admin
 * 
 * Handles admin-specific functionality such as enqueuing scripts and styles,
 * localizing script data, and managing admin page interactions.
 */
class MINVF_Admin
{

    /**
     * Constructor
     */
    public function __construct()
    {
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_assets']);
    }

    /**
     * Enqueue admin assets (CSS and JavaScript)
     */
    public function enqueue_admin_assets($hook)
    {
        // Only load assets on our plugin pages
        if (strpos($hook, 'media-inventory') === false && strpos($hook, 'j-forge') === false) {
            return;
        }

        // Enqueue admin CSS
        wp_enqueue_style(
            'minvf-admin-css',
            MINVF_PLUGIN_URL . 'assets/css/admin.css',
            [],
            MINVF_VERSION
        );

        // Enqueue integrated forge header CSS
        wp_enqueue_style(
            'minvf-forge-header-css',
            MINVF_PLUGIN_URL . 'assets/css/forge-header.css',
            ['minvf-admin-css'],
            MINVF_VERSION
        );

        // Enqueue table view CSS
        wp_enqueue_style(
            'minvf-table-view-css',
            MINVF_PLUGIN_URL . 'assets/css/table-view.css',
            ['minvf-admin-css'],
            MINVF_VERSION
        );

        // Enqueue admin JavaScript
        wp_enqueue_script(
            'minvf-admin-js',
            MINVF_PLUGIN_URL . 'assets/js/admin.js',
            ['jquery'], // Dependencies
            MINVF_VERSION,
            true // Load in footer
        );

        // Enqueue table view JavaScript
        wp_enqueue_script(
            'minvf-table-view-js',
            MINVF_PLUGIN_URL . 'assets/js/table-view.js',
            ['jquery', 'minvf-admin-js'], // Dependencies
            MINVF_VERSION,
            true // Load in footer
        );

        // Localize script data (pass PHP data to JavaScript)
        wp_localize_script('minvf-admin-js', 'minvfData', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce(MINVF_Admin_Controller::NONCE_ACTION),
            'viewPreference' => get_user_meta(get_current_user_id(), 'minvf_view_preference', true) ?: 'card',
            'categoryOrder' => MINVF_Table_Builder::$category_order,
            'strings' => [
                'scanComplete' => __('Scan completed successfully!', 'media-inventory-forge'),
                'scanError' => __('An error occurred during scanning.', 'media-inventory-forge'),
                'confirmClear' => __('Are you sure you want to clear all results?', 'media-inventory-forge'),
            ]
        ]);
    }
}
