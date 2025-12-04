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
 * Class MIF_Table_Builder
 *
 * Generates HTML tables organized by category with expandable row details
 */
class MIF_Table_Builder
{
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
        $saved_results = get_transient('mif_scan_results_' . $user_id);

        if (!empty($saved_results)) {
            // Use saved results instead of re-scanning
            $all_items = json_decode($saved_results, true);

            if (is_array($all_items) && !empty($all_items)) {
                // Successfully loaded saved results
            } else {
                // Invalid saved data
                return '<div style="text-align: center; padding: 40px; color: var(--clr-txt); font-style: italic;">No scan results available. Click "start scan" to begin inventory scanning.</div>';
            }
        } else {
            // No saved results - user needs to scan first
            return '<div style="text-align: center; padding: 40px; color: var(--clr-txt); font-style: italic;">No scan results available. Click "start scan" to begin inventory scanning.</div>';
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
        $order = ['Images', 'Fonts', 'SVG', 'Videos', 'Audio', 'PDFs', 'Documents', 'Text Files', 'Archives', 'Other Documents', 'Other'];
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
        $formatted_size = MIF_File_Utils::format_bytes($total_size);

        $section_id = 'mif-category-' . sanitize_title($category_name);

        $html = '<div class="mif-category-table-section" style="margin-bottom: 20px;">';

        // Collapsible header
        $html .= '<h3 class="mif-category-header" data-target="' . $section_id . '">';
        $html .= '<span>' . esc_html($category_name) . ' (' . $item_count . ' items, ' . $formatted_size . ')</span>';
        $html .= '<span class="dashicons dashicons-arrow-down-alt2 mif-category-toggle-icon"></span>';
        $html .= '</h3>';

        // Collapsible content
        $html .= '<div id="' . $section_id . '" class="mif-category-content" style="display: block;">';

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
        $html .= '<th style="width: 40px;"></th>';
        $html .= '<th class="mif-sortable" data-column="title"><span class="mif-sort-label">Font Family</span><span class="mif-sort-indicator"></span></th>';
        $html .= '<th>Source</th>';
        $html .= '<th style="width: 100px;">Variants</th>';
        $html .= '<th class="mif-sortable" data-column="files" style="width: 100px;"><span class="mif-sort-label">Files</span><span class="mif-sort-indicator"></span></th>';
        $html .= '<th class="mif-sortable" data-column="size" style="width: 120px;"><span class="mif-sort-label">Total Size</span><span class="mif-sort-indicator"></span></th>';
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
            $html .= '<td data-sort-value="' . $total_files . '">' . $total_files . '</td>';
            $html .= '<td data-sort-value="' . $total_size . '">' . MIF_File_Utils::format_bytes($total_size) . '</td>';
            $html .= '</tr>';

            // Expanded details row
            $html .= '<tr class="mif-expanded-details" id="' . $row_id . '" style="display: none;">';
            $html .= '<td colspan="6">';
            $html .= '<div style="padding: 12px; background: #f9f9f9;">';
            $html .= '<table class="mif-details-table" style="width: 100%; border-collapse: collapse;">';
            $html .= '<tr style="background: #e0e0e0; font-weight: 600;"><td>File</td><td>Type</td><td>Size</td></tr>';

            foreach ($family_items as $font_item) {
                foreach ($font_item['files'] as $file) {
                    $html .= '<tr>';
                    $html .= '<td>' . esc_html($font_item['title']) . '</td>';
                    $html .= '<td>' . esc_html($file['type']) . '</td>';
                    $html .= '<td>' . MIF_File_Utils::format_bytes($file['size']) . '</td>';
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
        $html .= '<th style="width: 40px;"></th>';
        $html .= '<th style="width: 80px;">Thumbnail</th>';
        $html .= '<th class="mif-sortable" data-column="title"><span class="mif-sort-label">Title</span><span class="mif-sort-indicator"></span></th>';
        $html .= '<th>Source</th>';
        $html .= '<th class="mif-sortable" data-column="files" style="width: 100px;"><span class="mif-sort-label">Files</span><span class="mif-sort-indicator"></span></th>';
        $html .= '<th class="mif-sortable" data-column="size" style="width: 120px;"><span class="mif-sort-label">Total Size</span><span class="mif-sort-indicator"></span></th>';
        $html .= '<th style="width: 140px;">Dimensions</th>';
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
                $html .= '<img src="' . esc_url($item['thumbnail_url']) . '" alt="' . esc_attr($item['title']) . '" style="width: 60px; height: 60px; object-fit: cover; border-radius: 4px;" />';
            } else {
                $html .= '<div style="width: 60px; height: 60px; background: #f0f0f0; display: flex; align-items: center; justify-content: center; border-radius: 4px;">📷</div>';
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

            $html .= '<td data-sort-value="' . $item['file_count'] . '">' . $item['file_count'] . '</td>';
            $html .= '<td data-sort-value="' . $item['total_size'] . '">' . MIF_File_Utils::format_bytes($item['total_size']) . '</td>';
            $html .= '<td>' . esc_html($item['dimensions'] ?? 'N/A') . '</td>';
            $html .= '</tr>';

            // Expanded details row
            $html .= '<tr class="mif-expanded-details" id="' . $row_id . '" style="display: none;">';
            $html .= '<td colspan="7">';
            $html .= '<div style="padding: 12px; background: #f9f9f9;">';
            $html .= '<table class="mif-details-table" style="width: 100%; border-collapse: collapse;">';
            $html .= '<tr style="background: #e0e0e0; font-weight: 600;"><td>File</td><td>Type</td><td>Dimensions</td><td>Size</td></tr>';

            foreach ($item['files'] as $file) {
                $html .= '<tr>';
                $html .= '<td>' . esc_html($file['filename'] ?? 'Unknown') . '</td>';
                $html .= '<td>' . esc_html($file['type']) . '</td>';
                $html .= '<td>' . esc_html($file['dimensions'] ?? 'N/A') . '</td>';
                $html .= '<td>' . MIF_File_Utils::format_bytes($file['size']) . '</td>';
                $html .= '</tr>';
            }

            $html .= '</table></div></td></tr>';
        }

        $html .= '</tbody></table>';

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
        $html .= '<th style="width: 40px;"></th>';
        $html .= '<th class="mif-sortable" data-column="title"><span class="mif-sort-label">Title</span><span class="mif-sort-indicator"></span></th>';
        $html .= '<th>Source</th>';
        $html .= '<th style="width: 120px;">Type</th>';
        $html .= '<th class="mif-sortable" data-column="files" style="width: 100px;"><span class="mif-sort-label">Files</span><span class="mif-sort-indicator"></span></th>';
        $html .= '<th class="mif-sortable" data-column="size" style="width: 120px;"><span class="mif-sort-label">Size</span><span class="mif-sort-indicator"></span></th>';
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
            $html .= '<td data-sort-value="' . $item['file_count'] . '">' . $item['file_count'] . '</td>';
            $html .= '<td data-sort-value="' . $item['total_size'] . '">' . MIF_File_Utils::format_bytes($item['total_size']) . '</td>';
            $html .= '</tr>';

            // Expanded details row
            $html .= '<tr class="mif-expanded-details" id="' . $row_id . '" style="display: none;">';
            $html .= '<td colspan="6">';
            $html .= '<div style="padding: 12px; background: #f9f9f9;">';
            $html .= '<table class="mif-details-table" style="width: 100%; border-collapse: collapse;">';
            $html .= '<tr style="background: #e0e0e0; font-weight: 600;"><td>File</td><td>Type</td><td>Size</td></tr>';

            foreach ($item['files'] as $file) {
                $html .= '<tr>';
                $html .= '<td>' . esc_html($file['filename'] ?? $item['title']) . '</td>';
                $html .= '<td>' . esc_html($file['type']) . '</td>';
                $html .= '<td>' . MIF_File_Utils::format_bytes($file['size']) . '</td>';
                $html .= '</tr>';
            }

            $html .= '</table></div></td></tr>';
        }

        $html .= '</tbody></table>';

        return $html;
    }
}
