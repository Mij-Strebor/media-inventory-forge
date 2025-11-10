<?php
/**
 * Media Type Explanation Template
 *
 * Displays detection capabilities and limitations for each media type.
 * Used by both card view and table view to inform users about what
 * the usage scanner can and cannot detect.
 *
 * @package    MediaInventoryForge
 * @subpackage Templates
 * @since      4.0.0
 * @version    4.0.0
 *
 * Template Variables:
 * @var string $type           Media type (image, pdf, video, audio, svg, font, document)
 * @var string $title          Display title
 * @var string $icon           Icon HTML/emoji
 * @var int    $count          Total items of this type
 * @var array  $stats          Usage statistics (used, unused, unknown)
 * @var array  $can_detect     Array of detection capabilities
 * @var array  $might_miss     Array of potential misses
 * @var string $special_note   Special note for this media type
 */

// Prevent direct file access
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="mif-type-explanation mif-type-<?php echo esc_attr($type); ?>" data-media-type="<?php echo esc_attr($type); ?>">
    <div class="mif-type-header">
        <span class="mif-type-icon"><?php echo wp_kses_post($icon); ?></span>
        <h3 class="mif-type-title"><?php echo esc_html($title); ?></h3>
        <span class="mif-type-count"><?php echo esc_html($count); ?> items</span>
    </div>

    <div class="mif-detection-info">
        <?php if (!empty($can_detect)): ?>
        <div class="mif-can-detect">
            <strong>✓ What we detect:</strong>
            <ul>
                <?php foreach ($can_detect as $item): ?>
                <li><?php echo esc_html($item); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>

        <?php if (!empty($might_miss)): ?>
        <div class="mif-might-miss">
            <strong>⚠️ What we might miss:</strong>
            <ul>
                <?php foreach ($might_miss as $item): ?>
                <li><?php echo esc_html($item); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>

        <?php if (!empty($special_note)): ?>
        <div class="mif-special-note">
            <strong>💡 Note:</strong> <?php echo esc_html($special_note); ?>
        </div>
        <?php endif; ?>
    </div>

    <?php if (!empty($stats)): ?>
    <div class="mif-type-stats">
        <?php
        $used = isset($stats['used']) ? intval($stats['used']) : 0;
        $unused = isset($stats['unused']) ? intval($stats['unused']) : 0;
        $unknown = isset($stats['unknown']) ? intval($stats['unknown']) : 0;
        ?>
        <span class="mif-stat-used" title="Used in content">
            <span class="mif-stat-label">Used:</span>
            <span class="mif-stat-value"><?php echo esc_html($used); ?></span>
        </span>
        <span class="mif-stat-separator">•</span>
        <span class="mif-stat-unused" title="Not found in scanned content">
            <span class="mif-stat-label">Unused:</span>
            <span class="mif-stat-value"><?php echo esc_html($unused); ?></span>
        </span>
        <?php if ($unknown > 0): ?>
        <span class="mif-stat-separator">•</span>
        <span class="mif-stat-unknown" title="Unable to determine usage">
            <span class="mif-stat-label">Unknown:</span>
            <span class="mif-stat-value"><?php echo esc_html($unknown); ?></span>
        </span>
        <?php endif; ?>
    </div>
    <?php endif; ?>
</div>
