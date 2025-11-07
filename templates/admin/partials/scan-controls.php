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
<div class="fcc-panel">
    <h2 style="margin-bottom: 16px;">Scan Controls</h2>

    <!-- Display Mode Selection -->
    <div style="margin-bottom: 16px; padding: 12px; background: var(--clr-light); border-radius: var(--jimr-border-radius); border: 1px solid var(--clr-secondary);">
        <label style="display: block; margin-bottom: 8px; font-weight: 600; color: var(--clr-primary);">Display Mode:</label>
        <div style="display: flex; gap: 16px; align-items: center;">
            <label style="display: flex; align-items: center; gap: 6px; cursor: pointer; color: var(--clr-txt);">
                <input type="radio" name="mif-display-mode" id="mif-display-card" value="card" checked />
                <span class="dashicons dashicons-grid-view" style="font-size: 16px;"></span>
                Card View
            </label>
            <label style="display: flex; align-items: center; gap: 6px; cursor: pointer; color: var(--clr-txt);">
                <input type="radio" name="mif-display-mode" id="mif-display-table" value="table" />
                <span class="dashicons dashicons-list-view" style="font-size: 16px;"></span>
                Table View
            </label>
        </div>
    </div>

    <div style="display: flex; gap: 12px; align-items: center; margin-bottom: 16px; flex-wrap: wrap;">
        <button id="start-scan" class="fcc-btn">🔍 start scan</button>
        <button id="stop-scan" class="fcc-btn fcc-btn-danger" style="display: none;">⏹️ stop scan</button>
        <button id="scan-usage" class="fcc-btn" style="background: var(--clr-accent);">🔎 scan for usage</button>
        <button id="create-table" class="fcc-btn" style="background: var(--clr-danger);">🔧 create table</button>
        <button id="view-usage" class="fcc-btn" style="background: var(--clr-secondary);">📋 view usage data</button>
        <button id="export-csv" class="fcc-btn" style="display: none;">📊 export csv</button>
        <button id="clear-results" class="fcc-btn fcc-btn-ghost" style="display: none;">🗑️ clear results</button>
    </div>

    <div id="usage-scan-progress" style="display: none; margin-top: 16px; padding: 16px; background: var(--clr-light); border-radius: var(--jimr-border-radius); border: 2px solid var(--clr-accent);">
        <div style="margin-bottom: 8px;">
            <strong style="color: var(--clr-accent);">Usage Scanning:</strong>
            <span id="usage-scan-status" style="margin-left: 8px;">Initializing...</span>
        </div>
        <div style="background: var(--clr-card-bg); height: 20px; border-radius: 10px; overflow: hidden; border: 1px solid var(--clr-secondary);">
            <div id="usage-progress-bar" style="background: var(--clr-accent); height: 100%; width: 0%; transition: width 0.3s ease;"></div>
        </div>
    </div>

    <div id="scan-progress" style="display: none;">
        <div style="margin-bottom: 12px;">
            <strong style="color: var(--clr-primary);">Scanning Progress:</strong>
        </div>
        <div style="background: var(--clr-light); height: 24px; border-radius: 12px; overflow: hidden; border: 2px solid var(--clr-secondary); margin-bottom: 8px;">
            <div id="progress-bar" style="background: linear-gradient(90deg, var(--clr-accent), var(--clr-btn-hover)); height: 100%; width: 0%; transition: width 0.3s ease; position: relative; overflow: hidden;">
                <div style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent); animation: shimmer 2s infinite;"></div>
            </div>
        </div>
        <p id="progress-text" style="margin: 0; color: var(--clr-txt); font-weight: 500;">0 / 0 processed</p>
    </div>

    <div id="summary-stats" style="margin-top: 20px; display: none;">
        <h3 style="color: var(--clr-primary); margin: 0 0 12px 0;">Summary</h3>
        <div id="summary-content" style="background: var(--clr-light); padding: 16px; border-radius: var(--jimr-border-radius); border: 1px solid var(--clr-secondary);"></div>
    </div>

    <div id="usage-data-display" style="margin-top: 20px; display: none;">
        <h3 style="color: var(--clr-primary); margin: 0 0 12px 0;">Usage Data (Debug)</h3>
        <div id="usage-data-content" style="background: var(--clr-light); padding: 16px; border-radius: var(--jimr-border-radius); border: 1px solid var(--clr-secondary); max-height: 600px; overflow-y: auto;"></div>
    </div>
</div>
