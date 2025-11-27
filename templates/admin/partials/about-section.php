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
        <span style="color: #FAF9F6 !important;">about media inventory forge</span>
        <span class="mif-toggle-icon" style="color: #FAF9F6 !important;">▼</span>
    </button>
    <div class="mif-info-content expanded" id="about-content">
        <div style="color: var(--clr-txt); font-size: 14px; line-height: 1.6;">
            <p style="margin: 0 0 16px 0; color: var(--clr-txt);">
                Media Inventory Forge is a <strong>read-only analysis tool</strong> that scans your WordPress uploads directory and generates detailed reports about your media files. It catalogs file types, sizes, categories, dimensions, and WordPress-generated variations. <strong>Media Inventory Forge does not modify, optimize, or delete any files</strong> - it only reads and reports what exists. Use this information to understand your media library before making optimization decisions with other tools.
            </p>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px; margin-bottom: 20px;">
                <div>
                    <h4 style="color: var(--clr-secondary); font-size: 15px; font-weight: 600; margin: 0 0 8px 0;">📊 What MIF Does</h4>
                    <p style="margin: 0; font-size: 13px; line-height: 1.5;">Scans and catalogs all media types - images, videos, audio, fonts, documents, and SVGs. Reports file counts, sizes, dimensions, and WordPress-generated variations.</p>
                </div>
                <div>
                    <h4 style="color: var(--clr-secondary); font-size: 15px; font-weight: 600; margin: 0 0 8px 0;">🔍 Analysis & Reporting</h4>
                    <p style="margin: 0; font-size: 13px; line-height: 1.5;">Provides storage breakdowns by category, identifies WordPress image size patterns, and exports detailed CSV reports for external analysis.</p>
                </div>
                <div>
                    <h4 style="color: var(--clr-secondary); font-size: 15px; font-weight: 600; margin: 0 0 8px 0;">⚡ Batch Processing</h4>
                    <p style="margin: 0; font-size: 13px; line-height: 1.5;">Handles large media libraries efficiently with progressive scanning and real-time progress tracking without timeout issues.</p>
                </div>
                <div>
                    <h4 style="color: var(--clr-secondary); font-size: 15px; font-weight: 600; margin: 0 0 8px 0;">⚠️ What MIF Does NOT Do</h4>
                    <p style="margin: 0; font-size: 13px; line-height: 1.5;">MIF does not compress, resize, convert, optimize, or delete files. It only reads and reports. Always backup before using other tools to act on MIF data.</p>
                </div>
            </div>
            <div style="background: rgba(60, 32, 23, 0.1); padding: 12px 16px; border-radius: 6px; border-left: 4px solid var(--clr-accent); margin-top: 20px;">
                <p style="margin: 0; font-size: 13px; opacity: 0.95; line-height: 1.5; color: var(--clr-txt);">
                    Part of the Jim R Forge professional WordPress development toolkit
                </p>
            </div>
        </div>
    </div>
</div>