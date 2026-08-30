<?php

/**
 * Table Builder for Media Inventory Forge
 *
 * Builds category-based HTML tables with expandable rows
 *
 * @package MediaInventoryForge
 * @subpackage Admin
 * @since 4.0.0
 */

defined('ABSPATH') || exit;

/**
 * Class MINVF_Table_Builder
 *
 * Generates HTML tables organized by category with expandable row details
 */
class MINVF_Table_Builder
{
    /** Canonical display order for media categories — single source of truth for PHP and JS. */
    public static $category_order = ['Images', 'Fonts', 'SVG', 'Videos', 'Audio', 'PDFs', 'Documents', 'Text Files', 'Archives', 'Other Documents', 'Other'];
    /**
     * Build complete table view HTML
     *
     * Retrieves all media items from scanner and generates category-organized
     * HTML tables with expandable row details.
     *
     * @since 4.0.0
     *
     * @return string HTML for all category tables, or message if no media found.
     */
    public function build_tables()
    {
        // Try to get saved scan results from transient
        $user_id = get_current_user_id();
        $saved_results = get_transient('minvf_scan_results_' . $user_id);

        if (!empty($saved_results)) {
            // Use saved results instead of re-scanning
            $all_items = json_decode($saved_results, true);

            if (is_array($all_items) && !empty($all_items)) {
                // Successfully loaded saved results
            } else {
                // Invalid saved data
                return '<div class="mif-empty-state">No scan results available. Click "start scan" to begin inventory scanning.</div>';
            }
        } else {
            // No saved results - user needs to scan first
            return '<div class="mif-empty-state">No scan results available. Click "start scan" to begin inventory scanning.</div>';
        }

        // Group by category
        $categories = $this->group_by_category($all_items);

        // Build HTML for each category
        $html = '';
        $ordered_categories = $this->get_ordered_categories(array_keys($categories));

        foreach ($ordered_categories as $category_name) {
            $category_data = $categories[$category_name];
            $html .= $this->build_category_section($category_name, $category_data);
        }

        return $html;
    }

    /**
     * Group items by category
     *
     * Organizes media items into associative array grouped by category name.
     *
     * @since 4.0.0
     *
     * @param array $items Media items to group.
     * @return array Associative array of items grouped by category.
     */
    private function group_by_category($items)
    {
        $categories = [];

        foreach ($items as $item) {
            $category = $item['category'] ?? 'Other';

            if (!isset($categories[$category])) {
                $categories[$category] = [];
            }

            $categories[$category][] = $item;
        }

        return $categories;
    }

    /**
     * Get ordered category names
     *
     * Returns categories in predefined display order (Images first, then Fonts, etc.).
     * Any categories not in the predefined order are appended at the end.
     *
     * @since 4.0.0
     *
     * @param array $category_names Available category names to order.
     * @return array Ordered category names.
     */
    private function get_ordered_categories($category_names)
    {
        $order = self::$category_order;
        $ordered = [];

        foreach ($order as $cat) {
            if (in_array($cat, $category_names)) {
                $ordered[] = $cat;
            }
        }

        // Add any remaining categories
        foreach ($category_names as $cat) {
            if (!in_array($cat, $ordered)) {
                $ordered[] = $cat;
            }
        }

        return $ordered;
    }

    /**
     * Build HTML for a category section
     *
     * Creates collapsible category section with header showing item count and size,
     * and appropriate table type based on category.
     *
     * @since 4.0.0
     *
     * @param string $category_name Category name (e.g., 'Fonts', 'Images').
     * @param array  $items         Items in this category.
     * @return string HTML for category table section.
     */
    private function build_category_section($category_name, $items)
    {
        $item_count = count($items);
        $total_size = array_sum(array_column($items, 'total_size'));
        $formatted_size = MINVF_File_Utils::format_bytes($total_size);

        $section_id = 'mif-category-' . sanitize_title($category_name);

        $html = '<div class="mif-category-table-section">';

        // Collapsible header
        $html .= '<h3 class="mif-category-header" data-target="' . $section_id . '">';
        $html .= '<span>' . esc_html($category_name) . ' (' . $item_count . ' items, ' . $formatted_size . ')</span>';
        $html .= '<span class="dashicons dashicons-arrow-down-alt2 mif-category-toggle-icon"></span>';
        $html .= '</h3>';

        // Collapsible content
        $html .= '<div id="' . $section_id . '" class="mif-category-content">';

        // Build category-specific table
        if ($category_name === 'Fonts') {
            $html .= $this->build_fonts_table($items);
        } elseif ($category_name === 'Images') {
            $html .= $this->build_images_table($items);
        } else {
            $html .= $this->build_default_table($items);
        }

        $html .= '</div>'; // .mif-category-content
        $html .= '</div>'; // .mif-category-table-section

        return $html;
    }

    /**
     * Build fonts table with expandable font families
     *
     * Groups font items by family and creates expandable rows showing
     * font variants (WOFF, TTF, etc.) within each family.
     *
     * @since 4.0.0
     *
     * @param array $items Font items to display.
     * @return string HTML table with expandable font family rows.
     */
    private function build_fonts_table($items)
    {
        // Group by font family
        $families = [];
        foreach ($items as $item) {
            $family = $item['font_family'] ?? 'Unknown Font';
            if (!isset($families[$family])) {
                $families[$family] = [];
            }
            $families[$family][] = $item;
        }

        ksort($families);

        $html = '<table class="mif-expandable-table widefat mif-sortable-table">';
        $html .= '<thead><tr>';
        $html .= '<th class="mif-col-icon"></th>';
        $html .= '<th class="mif-sortable" data-column="title"><span class="mif-sort-label">Font Family</span><span class="mif-sort-indicator"></span></th>';
        $html .= '<th>Source</th>';
        $html .= '<th class="mif-col-variants">Variants</th>';
        $html .= '<th class="mif-sortable mif-col-files" data-column="files"><span class="mif-sort-label">Files</span><span class="mif-sort-indicator"></span></th>';
        $html .= '<th class="mif-sortable mif-col-size" data-column="size"><span class="mif-sort-label">Total Size</span><span class="mif-sort-indicator"></span></th>';
        $html .= '</tr></thead>';
        $html .= '<tbody>';

        foreach ($families as $family_name => $family_items) {
            $total_files = array_sum(array_column($family_items, 'file_count'));
            $total_size = array_sum(array_column($family_items, 'total_size'));
            $variants = array_unique(array_map(function($item) {
                return strtoupper($item['extension']);
            }, $family_items));

            // Get unique sources
            $sources = array_unique(array_filter(array_column($family_items, 'source')));

            $row_id = 'font-' . sanitize_title($family_name);

            // Main row
            $html .= '<tr class="mif-expandable-row" data-target="' . $row_id . '">';
            $html .= '<td><span class="dashicons dashicons-plus-alt2 mif-expand-icon"></span></td>';
            $html .= '<td data-sort-value="' . esc_attr(strtolower($family_name)) . '"><strong>' . esc_html($family_name) . '</strong></td>';
            $html .= '<td>';
            foreach ($sources as $source) {
                $badge_class = ($source === 'Media Library') ? 'source-media-library' : 'source-theme';
                $html .= '<span class="source-badge ' . $badge_class . '">' . esc_html($source) . '</span> ';
            }
            $html .= '</td>';
            $html .= '<td>' . implode(', ', $variants) . '</td>';
            $html .= '<td data-sort-value="' . intval($total_files) . '">' . intval($total_files) . '</td>';
            $html .= '<td data-sort-value="' . intval($total_size) . '">' . MINVF_File_Utils::format_bytes($total_size) . '</td>';
            $html .= '</tr>';

            // Expanded details row
            $html .= '<tr class="mif-expanded-details" id="' . $row_id . '">';
            $html .= '<td colspan="6">';
            $html .= '<div class="mif-details-container">';
            $html .= '<table class="mif-details-table">';
            $html .= '<tr class="mif-details-header-row"><td>File</td><td>Type</td><td>Size</td></tr>';

            foreach ($family_items as $font_item) {
                foreach ($font_item['files'] as $file) {
                    $html .= '<tr>';
                    $html .= '<td>' . esc_html($font_item['title']) . '</td>';
                    $html .= '<td>' . esc_html($file['type']) . '</td>';
                    $html .= '<td>' . MINVF_File_Utils::format_bytes($file['size']) . '</td>';
                    $html .= '</tr>';
                }
            }

            $html .= '</table></div></td></tr>';
        }

        $html .= '</tbody></table>';

        return $html;
    }

    /**
     * Build images table with expandable image details
     *
     * Creates table with thumbnails and expandable rows showing image variants
     * (thumbnails, different sizes, etc.).
     *
     * @since 4.0.0
     *
     * @param array $items Image items to display.
     * @return string HTML table with expandable image rows.
     */
    private function build_images_table($items)
    {
        $html = '<table class="mif-expandable-table widefat mif-sortable-table">';
        $html .= '<thead><tr>';
        $html .= '<th class="mif-col-icon"></th>';
        $html .= '<th class="mif-col-thumb">Thumbnail</th>';
        $html .= '<th class="mif-sortable" data-column="title"><span class="mif-sort-label">Title</span><span class="mif-sort-indicator"></span></th>';
        $html .= '<th>Source</th>';
        $html .= '<th class="mif-sortable mif-col-files" data-column="files"><span class="mif-sort-label">Files</span><span class="mif-sort-indicator"></span></th>';
        $html .= '<th class="mif-sortable mif-col-size" data-column="size"><span class="mif-sort-label">Total Size</span><span class="mif-sort-indicator"></span></th>';
        $html .= '<th class="mif-col-dims">Dimensions</th>';
        $html .= '<th class="mif-sortable mif-col-usage" data-column="usage_count"><span class="mif-sort-label">Uses</span><span class="mif-sort-indicator"></span></th>';
        $html .= '</tr></thead>';
        $html .= '<tbody>';

        foreach ($items as $item) {
            $row_id = 'image-' . sanitize_title($item['id']);

            // Main row
            $html .= '<tr class="mif-expandable-row" data-target="' . $row_id . '">';
            $html .= '<td><span class="dashicons dashicons-plus-alt2 mif-expand-icon"></span></td>';

            // Thumbnail
            $html .= '<td>';
            if (!empty($item['thumbnail_url'])) {
                $html .= '<img src="' . esc_url($item['thumbnail_url']) . '" alt="' . esc_attr($item['title']) . '" class="mif-thumb-img" />';
            } else {
                $html .= '<div class="mif-thumb-placeholder">📷</div>';
            }
            $html .= '</td>';

            $html .= '<td data-sort-value="' . esc_attr(strtolower($item['title'])) . '"><strong>' . esc_html($item['title']) . '</strong></td>';

            // Source
            $html .= '<td>';
            if (!empty($item['source'])) {
                $badge_class = ($item['source'] === 'Media Library') ? 'source-media-library' : 'source-theme';
                $html .= '<span class="source-badge ' . $badge_class . '">' . esc_html($item['source']) . '</span>';
            }
            $html .= '</td>';

            $html .= '<td data-sort-value="' . intval($item['file_count']) . '">' . intval($item['file_count']) . '</td>';
            $html .= '<td data-sort-value="' . intval($item['total_size']) . '">' . MINVF_File_Utils::format_bytes($item['total_size']) . '</td>';
            $html .= '<td>' . esc_html($item['dimensions'] ?? 'N/A') . '</td>';
            $html .= $this->build_usage_count_cell($item);
            $html .= '</tr>';

            // Expanded details row
            $html .= '<tr class="mif-expanded-details" id="' . $row_id . '">';
            $html .= '<td colspan="8">';
            $html .= '<div class="mif-details-container">';
            $html .= '<table class="mif-details-table">';
            $html .= '<tr class="mif-details-header-row"><td>File</td><td>Type</td><td>Dimensions</td><td>Size</td></tr>';

            foreach ($item['files'] as $file) {
                $html .= '<tr>';
                $html .= '<td>' . esc_html($file['filename'] ?? 'Unknown') . '</td>';
                $html .= '<td>' . esc_html($file['type']) . '</td>';
                $html .= '<td>' . esc_html($file['dimensions'] ?? 'N/A') . '</td>';
                $html .= '<td>' . MINVF_File_Utils::format_bytes($file['size']) . '</td>';
                $html .= '</tr>';
            }

            $html .= '</table>';
            $html .= $this->build_where_used_html($item);
            $html .= '</div></td></tr>';
        }

        $html .= '</tbody></table>';
        $html .= $this->build_unused_images_section($items);

        return $html;
    }

    /**
     * Build the Uses column cell for a single item.
     *
     * A plain number, not a pill badge - the column is narrow, and a
     * full-cell highlight on zero (via the "unused" class, already styled
     * in admin.css alongside the identical card-view badge) is easier to
     * spot at this width than a small inline badge would be. Renders an
     * empty cell if the item carries no usage_count at all - the usage
     * scan hasn't completed/run yet, so showing "0" would be misleading.
     *
     * @since 5.2.0
     * @param array $item Media item, optionally carrying usage_count
     * @return string HTML <td>
     */
    private function build_usage_count_cell($item)
    {
        if (!array_key_exists('usage_count', $item)) {
            return '<td class="usage-count-cell"></td>';
        }

        $count = intval($item['usage_count']);
        $class = 'usage-count-cell' . (0 === $count ? ' unused' : '');

        return '<td class="' . esc_attr($class) . '" data-sort-value="' . $count . '">' . $count . '</td>';
    }

    /**
     * Build the "Where Used" block for an item's expanded-details row.
     *
     * Mirrors admin.js's buildUsageLocationsListHtml()/card-view markup
     * (.usage-location-item, <strong>Where Used</strong>, a <ul> of linked
     * locations or the same "candidate for removal" message) so the list/
     * table view's expansion shows the same information the card view
     * shows directly on each card. Returns an empty string if the item
     * carries no usage_count - the usage scan hasn't run yet.
     *
     * @since 5.2.0
     * @param array $item Media item, optionally carrying usage_count and
     *   usage_locations: [{title, url}, ...]
     * @return string HTML, or '' if there's no usage data to show yet
     */
    private function build_where_used_html($item)
    {
        if (!array_key_exists('usage_count', $item)) {
            return '';
        }

        $html = '<div class="usage-location-item" style="margin-top: 12px;">';
        $html .= '<strong>Where Used</strong>';

        $locations = (isset($item['usage_locations']) && is_array($item['usage_locations'])) ? $item['usage_locations'] : [];

        if (!empty($locations)) {
            $html .= '<ul class="usage-location-list">';
            foreach ($locations as $loc) {
                $title = !empty($loc['title']) ? $loc['title'] : '';
                $url = !empty($loc['url']) ? $loc['url'] : '';
                if ($url) {
                    $html .= '<li><a href="' . esc_url($url) . '" target="_blank" rel="noopener">' . esc_html($title ?: $url) . '</a></li>';
                } else {
                    $html .= '<li>' . esc_html($title ?: 'Unknown location') . '</li>';
                }
            }
            $html .= '</ul>';
        } else {
            $html .= '<p class="usage-location-empty">Not found anywhere &mdash; candidate for removal.</p>';
        }

        $html .= '</div>';

        return $html;
    }

    /**
     * Build the collapsible "Unused Images" section - a full-width list of
     * every image whose usage_count is 0, gathered in one place at the
     * bottom of the Images table instead of requiring a scan through every
     * row's Uses column. Uses the same .mif-category-header/-content
     * collapse markup as the category sections above it, so the existing
     * delegated click handlers in table-view.js pick it up for free.
     * Returns '' entirely if the usage scan hasn't completed (no item
     * carries usage_count).
     *
     * @since 5.2.0
     * @param array $items Image items
     * @return string HTML, or '' if there's no usage data yet
     */
    private function build_unused_images_section($items)
    {
        $scanned = array_filter($items, function ($item) {
            return array_key_exists('usage_count', $item);
        });

        if (empty($scanned)) {
            return '';
        }

        $unused = array_filter($scanned, function ($item) {
            return 0 === intval($item['usage_count']);
        });

        $section_id = 'mif-unused-images';

        $html = '<div class="mif-category-table-section">';
        $html .= '<h3 class="mif-category-header" data-target="' . $section_id . '">';
        $html .= '<span>Unused Images (' . count($unused) . ')</span>';
        $html .= '<span class="dashicons dashicons-arrow-down-alt2 mif-category-toggle-icon"></span>';
        $html .= '</h3>';
        $html .= '<div id="' . $section_id . '" class="mif-category-content">';

        if (empty($unused)) {
            $html .= '<p style="padding: 4px 0; color: var(--clr-txt);">None - every image is used somewhere.</p>';
        } else {
            $html .= '<ul style="margin: 0; padding: 4px 0 4px 20px; list-style: disc;">';
            foreach ($unused as $item) {
                $html .= '<li style="padding: 4px 0; display: flex; align-items: center; gap: 8px;">';
                if (!empty($item['thumbnail_url'])) {
                    $html .= '<img src="' . esc_url($item['thumbnail_url']) . '" alt="" loading="lazy" style="width: 32px; height: 32px; object-fit: cover; border-radius: 3px; flex-shrink: 0;" />';
                }
                $html .= '<span>' . esc_html($item['title']) . '</span>';
                $html .= '</li>';
            }
            $html .= '</ul>';
        }

        $html .= '</div></div>';

        return $html;
    }

    /**
     * Build default table for other categories
     *
     * Creates generic expandable table for categories that don't have
     * specialized table layouts (Videos, PDFs, Documents, etc.).
     *
     * @since 4.0.0
     *
     * @param array $items Media items to display.
     * @return string HTML table with expandable rows.
     */
    private function build_default_table($items)
    {
        $html = '<table class="mif-expandable-table widefat mif-sortable-table">';
        $html .= '<thead><tr>';
        $html .= '<th class="mif-col-icon"></th>';
        $html .= '<th class="mif-sortable" data-column="title"><span class="mif-sort-label">Title</span><span class="mif-sort-indicator"></span></th>';
        $html .= '<th>Source</th>';
        $html .= '<th class="mif-col-type">Type</th>';
        $html .= '<th class="mif-sortable mif-col-files" data-column="files"><span class="mif-sort-label">Files</span><span class="mif-sort-indicator"></span></th>';
        $html .= '<th class="mif-sortable mif-col-size" data-column="size"><span class="mif-sort-label">Size</span><span class="mif-sort-indicator"></span></th>';
        $html .= '</tr></thead>';
        $html .= '<tbody>';

        foreach ($items as $item) {
            $row_id = 'item-' . sanitize_title($item['id']);

            // Main row
            $html .= '<tr class="mif-expandable-row" data-target="' . $row_id . '">';
            $html .= '<td><span class="dashicons dashicons-plus-alt2 mif-expand-icon"></span></td>';
            $html .= '<td data-sort-value="' . esc_attr(strtolower($item['title'])) . '"><strong>' . esc_html($item['title']) . '</strong></td>';

            // Source
            $html .= '<td>';
            if (!empty($item['source'])) {
                $badge_class = ($item['source'] === 'Media Library') ? 'source-media-library' : 'source-theme';
                $html .= '<span class="source-badge ' . $badge_class . '">' . esc_html($item['source']) . '</span>';
            }
            $html .= '</td>';

            $html .= '<td>' . strtoupper(esc_html($item['extension'])) . '</td>';
            $html .= '<td data-sort-value="' . intval($item['file_count']) . '">' . intval($item['file_count']) . '</td>';
            $html .= '<td data-sort-value="' . intval($item['total_size']) . '">' . MINVF_File_Utils::format_bytes($item['total_size']) . '</td>';
            $html .= '</tr>';

            // Expanded details row
            $html .= '<tr class="mif-expanded-details" id="' . $row_id . '">';
            $html .= '<td colspan="6">';
            $html .= '<div class="mif-details-container">';
            $html .= '<table class="mif-details-table">';
            $html .= '<tr class="mif-details-header-row"><td>File</td><td>Type</td><td>Size</td></tr>';

            foreach ($item['files'] as $file) {
                $html .= '<tr>';
                $html .= '<td>' . esc_html($file['filename'] ?? $item['title']) . '</td>';
                $html .= '<td>' . esc_html($file['type']) . '</td>';
                $html .= '<td>' . MINVF_File_Utils::format_bytes($file['size']) . '</td>';
                $html .= '</tr>';
            }

            $html .= '</table></div></td></tr>';
        }

        $html .= '</tbody></table>';

        return $html;
    }
}
