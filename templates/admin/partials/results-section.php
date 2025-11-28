<?php

/**
 * Results section template
 * @package MediaInventoryForge
 * @subpackage Templates
 * @since 2.0.0
 *
 * Provides the results display area for scan outputs.
 */

// Prevent direct access
defined('ABSPATH') || exit;
?>

<!-- Results Section -->
<div class="mif-panel">
    <h2 class="mif-section-heading">Inventory Results</h2>

    <!-- Card View Container -->
    <div id="mif-card-view" class="mif-view-container">
        <div id="results-container" class="mif-results-placeholder">
            <div class="mif-empty-state">
                Click "start scan" to begin inventory scanning.
            </div>
        </div>
    </div>

    <!-- Table View Container -->
    <div id="mif-table-view" class="mif-view-container" style="display: none;"></div>
</div>
