<?php

/**
 * Main admin page template for Media Inventory Forge
 * 
 * @package MediaInventoryForge
 * @subpackage Templates
 * @since 2.0.0
 * 
 * Combines scan controls, results display, and about section.
 */

// Prevent direct access
defined('ABSPATH') || exit;
?>

<!-- Main Admin Page Template -->
<div class="wrap mif-wrap">
    <div class="mif-header-section">
        <h1 class="text-2xl font-bold mb-4">
            <?php echo esc_html(get_admin_page_title()); ?>
        </h1>
    </div>

    <!-- Version info outside header, above About panel -->
    <div class="mif-version-info">
        Version <?php echo esc_html(MIF_VERSION); ?>
    </div>
    
    <?php include MIF_PLUGIN_DIR . 'templates/admin/partials/about-section.php'; ?>
    
    <!-- Main Container -->
    <div class="media-inventory-container mif-container">
        <div class="mif-panel-compact">
            <!-- Two-column layout for Scan Controls and File Distribution -->
            <div class="mif-grid-2col">
                <?php include MIF_PLUGIN_DIR . 'templates/admin/partials/scan-controls.php'; ?>
                <?php include MIF_PLUGIN_DIR . 'templates/admin/partials/file-distribution.php'; ?>
            </div>

            <?php include MIF_PLUGIN_DIR . 'templates/admin/partials/results-section.php'; ?>
        </div>
    </div>

    <!-- Jim R Forge Community & Tools Panel -->
    <?php include MIF_PLUGIN_DIR . 'templates/admin/partials/community-panel.php'; ?>
</div>
