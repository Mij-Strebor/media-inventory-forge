<?php

/**
 * About section template
 * @package MediaInventoryForge
 * @subpackage Templates
 * @since 2.0.0
 *
 * Provides an expandable "About" section with plugin details and features.
 */

// Prevent direct access
defined('ABSPATH') || exit;
?>

<!-- About Section -->
<div class="mif-info-toggle-section">
    <button class="mif-info-toggle expanded" data-toggle-target="about-content">
        <span class="mif-toggle-text">about media inventory forge</span>
        <span class="mif-toggle-icon mif-toggle-text">▼</span>
    </button>
    <div class="mif-info-content expanded" id="about-content">
        <div class="mif-about-wrapper">
            <p class="mif-about-intro">
                Media Inventory Forge is a <strong>read-only analysis tool</strong> that scans your WordPress uploads directory and generates detailed reports about your media files. It catalogs file types, sizes, categories, dimensions, and WordPress-generated variations. <strong>Media Inventory Forge does not modify, optimize, or delete any files</strong> - it only reads and reports what exists. Use this information to understand your media library before making optimization decisions with other tools.
            </p>
            <div class="mif-features-grid">
                <div>
                    <h4 class="mif-feature-heading">📊 What MIF Does</h4>
                    <p class="mif-feature-text">Scans and catalogs all media types - images, videos, audio, fonts, documents, and SVGs. Reports file counts, sizes, dimensions, and WordPress-generated variations.</p>
                </div>
                <div>
                    <h4 class="mif-feature-heading">🔍 Analysis & Reporting</h4>
                    <p class="mif-feature-text">Provides storage breakdowns by category, identifies WordPress image size patterns, and exports detailed CSV reports for external analysis.</p>
                </div>
                <div>
                    <h4 class="mif-feature-heading">⚡ Batch Processing</h4>
                    <p class="mif-feature-text">Handles large media libraries efficiently with progressive scanning and real-time progress tracking without timeout issues.</p>
                </div>
                <div>
                    <h4 class="mif-feature-heading">⚠️ What MIF Does NOT Do</h4>
                    <p class="mif-feature-text">MIF does not compress, resize, convert, optimize, or delete files. It only reads and reports. Always backup before using other tools to act on MIF data.</p>
                </div>
            </div>
            <div class="mif-about-notice">
                <p>
                    Part of the Jim R Forge professional WordPress development toolkit
                </p>
            </div>
        </div>
    </div>
</div>
