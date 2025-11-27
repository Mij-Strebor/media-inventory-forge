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
    <h2 style="margin-bottom: 16px;">Inventory Results</h2>

    <!-- Card View Container -->
    <div id="mif-card-view" class="mif-view-container">
        <div id="results-container" style="min-height: 120px;">
            <div style="text-align: center; padding: 40px; color: var(--clr-txt); font-style: italic;">
                Click "start scan" to begin inventory scanning.
            </div>
        </div>
    </div>

    <!-- Table View Container -->
    <div id="mif-table-view" class="mif-view-container" style="display: none;"></div>
</div>