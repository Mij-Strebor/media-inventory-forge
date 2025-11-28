<?php

/**
 * File Distribution panel template
 * @package MediaInventoryForge
 * @subpackage Templates
 * @since 3.0.0
 *
 * Displays a pie chart showing the distribution of file types by storage size.
 */

// Prevent direct access
defined('ABSPATH') || exit;
?>

<!-- File Distribution Panel -->
<div class="mif-panel">
    <h2 class="mif-section-heading">File Distribution</h2>

    <!-- Pie Chart Container -->
    <div id="file-distribution-chart" class="mif-chart-wrapper">
        <div class="mif-empty-state">
            Run a scan to see file distribution
        </div>
    </div>
</div>
