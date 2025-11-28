<?php

/**
 * Jim R Forge Community Panel Template
 *
 * Displays community links, related plugins, and support options.
 *
 * @package MediaInventoryForge
 * @since 4.0.1
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="mif-info-toggle-section mif-community-section">
    <div class="mif-community-header">
        <span class="mif-community-title">Community & Tools</span>
    </div>
    <div class="mif-community-content">
        <p class="mif-community-intro">
            Media Inventory Forge is part of the Jim R Forge ecosystem - a growing collection of professional WordPress tools for designers and developers.
        </p>

        <h3 class="mif-community-heading">Related Tools & Plugins</h3>
        <ul class="mif-community-list">
            <li>
                <strong><a href="https://wordpress.org/plugins/fluid-font-forge/" target="_blank" rel="noopener" style="color: var(--clr-link); text-decoration: underline;">Fluid Font Forge</a></strong> - Craft responsive fonts for WordPress (a WordPress plugin)
            </li>
            <li>
                <strong><a href="https://wordpress.org/plugins/fluid-space-forge/" target="_blank" rel="noopener" style="color: var(--clr-link); text-decoration: underline;">Fluid Space Forge</a></strong> - Responsive spacing with CSS clamp() functions (In review at WordPress.org)
            </li>
            <li>
                <strong>Fluid Button Forge</strong> - Advanced button customization with fluid sizing (In Development)
            </li>
            <li>
                <strong>Elementor Color Inventory</strong> - Color palette management for Elementor (In Development)
            </li>
        </ul>

        <h3 class="mif-community-heading">Project Hub</h3>
        <p class="mif-community-text">
            Soon you can visit <a href="https://jimrforge.com" target="_blank" rel="noopener" style="color: var(--clr-link); text-decoration: underline; font-weight: 600;">jimrforge.com</a> for complete documentation and information. Coming soon.
        </p>

        <h3 class="mif-community-heading">Support Development</h3>
        <p class="mif-community-text">
            All Jim R Forge tools are free and open source. If you find them useful, please consider supporting development:
        </p>
        <div class="mif-community-buttons">
            <a href="https://www.buymeacoffee.com/jimrweb" target="_blank" rel="noopener" class="button button-secondary mif-community-link">
                ☕ Buy Me a Coffee
            </a>
            <a href="https://github.com/Mij-Strebor/media-inventory-forge/stargazers" target="_blank" rel="noopener" class="button button-secondary mif-community-link">
                ⭐ Star on GitHub
            </a>
        </div>
    </div>
</div>
