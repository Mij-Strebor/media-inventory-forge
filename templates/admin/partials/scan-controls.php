<?php

/**
 * Scan controls template
 * @package MediaInventoryForge
 * @subpackage Templates
 * @since 2.0.0
 *
 * Provides the scan control buttons, progress bar, and summary stats section.
 */

// Prevent direct access
defined('ABSPATH') || exit;
?>

<!-- Controls Section -->
<div class="mif-panel">
    <h2 class="mif-section-heading">Scan Controls</h2>

    <!-- Image Display Mode Selection -->
    <div class="mif-control-group">
        <label class="mif-control-label" style="display: block;">
            <span class="mif-control-label-text">Image Display Mode:</span>
        </label>
        <div class="mif-display-mode-group">
            <label class="mif-mode-option">
                <input type="radio" name="mif-display-mode" id="mif-display-card" value="card" checked />
                <span class="dashicons dashicons-grid-view mif-mode-icon"></span>
                Card View
            </label>
            <label class="mif-mode-option">
                <input type="radio" name="mif-display-mode" id="mif-display-table" value="table" />
                <span class="dashicons dashicons-list-view mif-mode-icon"></span>
                Table View
            </label>
        </div>
    </div>

    <!-- Source Filters -->
    <div class="mif-control-group">
        <label class="mif-control-label">
            <input type="checkbox" id="mif-toggle-all-sources" />
            <span class="mif-control-label-text">Scan Sources:</span>
        </label>
        <div class="mif-source-grid">
            <label class="mif-source-option">
                <input type="checkbox" name="mif-source-filter" class="mif-source-filter" value="media-library" checked />
                <strong>Media Library</strong>
                <span class="mif-source-meta">(Primary)</span>
            </label>
            <label class="mif-source-option">
                <input type="checkbox" name="mif-source-filter" class="mif-source-filter" value="theme" />
                Active Theme
            </label>
            <label class="mif-source-option">
                <input type="checkbox" name="mif-source-filter" class="mif-source-filter" value="parent-theme" />
                Parent Theme
            </label>
            <label class="mif-source-option">
                <input type="checkbox" name="mif-source-filter" class="mif-source-filter" value="plugins" />
                Plugins
            </label>
            <label class="mif-source-option">
                <input type="checkbox" name="mif-source-filter" class="mif-source-filter" value="wordpress-core" />
                WordPress Core
            </label>
            <label class="mif-source-option">
                <input type="checkbox" name="mif-source-filter" class="mif-source-filter" value="uploads" />
                Uploads Directory
            </label>
        </div>
    </div>

    <div class="mif-button-group">
        <button id="start-scan" class="mif-btn">start scan</button>
        <button id="stop-scan" class="mif-btn mif-btn-danger" style="display: none;">stop scan</button>
        <button id="export-csv" class="mif-btn" style="display: none;">export csv</button>
    </div>

    <div id="scan-progress" style="display: none;">
        <div class="mif-progress-wrapper">
            <strong class="mif-progress-label">Scanning Progress:</strong>
        </div>
        <div class="mif-progress-bar-container">
            <div id="progress-bar" class="mif-progress-bar" style="width: 0%;">
                <div style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent); animation: shimmer 2s infinite;"></div>
            </div>
        </div>
        <p id="progress-text" class="mif-progress-status">0 / 0 processed</p>
    </div>

    <div id="summary-stats" style="margin-top: 20px; display: none;">
        <h3 class="mif-section-heading">Summary</h3>
        <div id="summary-content" class="mif-control-group"></div>
    </div>
</div>
