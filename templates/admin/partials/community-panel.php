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

<div class="fcc-info-toggle-section" style="margin-top: 40px; max-width: 1280px; margin-left: auto; margin-right: auto;">
    <div style="width: 100%; padding: 16px 24px; display: flex; justify-content: space-between; align-items: center; background: var(--clr-secondary); cursor: default; font-weight: 600; font-size: 18px; border-radius: var(--jimr-border-radius-lg) var(--jimr-border-radius-lg) 0 0;">
        <span style="color: #FAF9F6; font-size: 24px; font-weight: 600;">Community & Tools</span>
    </div>
    <div style="background: #faf9f6; padding: 32px; border-radius: 0 0 var(--jimr-border-radius-lg) var(--jimr-border-radius-lg);">
        <p style="margin: 0 0 20px 0; font-size: 16px; line-height: 1.6; color: var(--clr-txt);">
            Media Inventory Forge is part of the Jim R Forge ecosystem - a growing collection of professional WordPress tools for designers and developers.
        </p>

        <h3 style="color: var(--clr-primary); font-size: 18px; font-weight: 600; margin: 0 0 12px 0;">Related Tools & Plugins</h3>
        <ul style="margin: 0 0 20px 0; padding-left: 20px; font-size: 16px; line-height: 1.8; color: var(--clr-txt);">
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

        <h3 style="color: var(--clr-primary); font-size: 18px; font-weight: 600; margin: 0 0 12px 0;">Project Hub</h3>
        <p style="margin: 0 0 16px 0; padding-left: 20px; font-size: 16px; line-height: 1.6; color: var(--clr-txt);">
            Soon you can visit <a href="https://jimrforge.com" target="_blank" rel="noopener" style="color: var(--clr-link); text-decoration: underline; font-weight: 600;">jimrforge.com</a> for complete documentation and information. Coming.
        </p>

        <h3 style="color: var(--clr-primary); font-size: 18px; font-weight: 600; margin: 0 0 12px 0;">Support Development</h3>
        <p style="margin: 0 0 16px 0; padding-left: 20px; font-size: 16px; line-height: 1.6; color: var(--clr-txt);">
            All Jim R Forge tools are free and open source. If you find them useful, please consider supporting development:
        </p>
        <div style="display: flex; gap: 12px; flex-wrap: wrap; padding-left: 20px;">
            <a href="https://www.buymeacoffee.com/jimrweb" target="_blank" rel="noopener" class="button button-secondary" style="background: #2271b1 !important; border-color: #2271b1 !important; color: #fff !important; text-transform: none !important; text-decoration: none !important;">
                ☕ Buy Me a Coffee
            </a>
            <a href="https://github.com/Mij-Strebor/media-inventory-forge/stargazers" target="_blank" rel="noopener" class="button button-secondary" style="background: #2271b1 !important; border-color: #2271b1 !important; color: #fff !important; text-transform: none !important; text-decoration: none !important;">
                ⭐ Star on GitHub
            </a>
        </div>
    </div>
</div>
