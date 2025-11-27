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
    <h2 style="margin-bottom: 16px;">File Distribution</h2>

    <!-- Pie Chart Container -->
    <div id="file-distribution-chart" style="min-height: 300px; display: flex; flex-direction: column; align-items: center; justify-content: center;">
        <div style="text-align: center; padding: 40px; color: var(--clr-txt); font-style: italic;">
            Run a scan to see file distribution
        </div>
    </div>
</div>
