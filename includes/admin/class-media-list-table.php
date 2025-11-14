<?php

/**
 * Media List Table - WordPress Table View
 *
 * Extends WP_List_Table to provide a sortable, paginated table view
 * for media inventory results as an alternative to card view.
 *
 * @package MediaInventoryForge
 * @subpackage Admin
 * @since 4.0.0
 */

// Prevent direct access
defined('ABSPATH') || exit;

// Load WP_List_Table if not already loaded
if (!class_exists('WP_List_Table')) {
    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

class MIF_Media_List_Table extends WP_List_Table
{

    /**
     * Scanner instance for data retrieval
     *
     * @var MIF_Scanner
     */
    private $scanner;

    /**
     * Raw scan data before processing
     *
     * @var array
     */
    private $scan_data;

    /**
     * Constructor
     *
     * @since 4.0.0
     */
    public function __construct()
    {
        parent::__construct([
            'singular' => 'media_item',
            'plural'   => 'media_items',
            'ajax'     => true
        ]);

        $this->scanner = new MIF_Scanner();
    }

    /**
     * Define table columns
     *
     * @return array Column definitions
     * @since 4.0.0
     */
    public function get_columns()
    {
        return [
            'cb'          => '<input type="checkbox" />',
            'thumbnail'   => 'Preview',
            'title'       => 'Title',
            'type'        => 'Type',
            'source'      => 'Source',
            'files'       => 'Files',
            'size'        => 'Size',
            'dimensions'  => 'Dimensions',
        ];
    }

    /**
     * Define sortable columns
     *
     * @return array Sortable column definitions
     * @since 4.0.0
     */
    public function get_sortable_columns()
    {
        return [
            'title'      => ['title', false],
            'type'       => ['type', false],
            'source'     => ['source', false],
            'files'      => ['files', false],
            'size'       => ['size', false],
        ];
    }

    /**
     * Prepare table items for display
     *
     * Fetches data, applies sorting and pagination
     *
     * @since 4.0.0
     */
    public function prepare_items()
    {
        // Get scan data
        $scan_results = $this->scanner->scan_batch(0);
        $all_items = $scan_results['data'] ?? [];

        // Apply sorting with sanitization
        $valid_orderby = ['title', 'type', 'source', 'files', 'size'];
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Table sorting via WP_List_Table, read-only operation
        $orderby = isset($_GET['orderby']) ? sanitize_key(wp_unslash($_GET['orderby'])) : 'title';
        $orderby = in_array($orderby, $valid_orderby, true) ? $orderby : 'title';

        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Table sorting via WP_List_Table, read-only operation
        $order = isset($_GET['order']) ? sanitize_key(wp_unslash($_GET['order'])) : 'asc';
        $order = in_array($order, ['asc', 'desc'], true) ? $order : 'asc';

        $all_items = $this->sort_items($all_items, $orderby, $order);

        // Setup pagination
        $per_page = $this->get_items_per_page('mif_items_per_page', 50);
        $current_page = $this->get_pagenum();
        $total_items = count($all_items);

        $this->set_pagination_args([
            'total_items' => $total_items,
            'per_page'    => $per_page,
            'total_pages' => ceil($total_items / $per_page)
        ]);

        // Slice items for current page
        $this->items = array_slice($all_items, ($current_page - 1) * $per_page, $per_page);
    }

    /**
     * Sort items by specified column
     *
     * @param array  $items   Items to sort
     * @param string $orderby Column to sort by
     * @param string $order   Sort direction (asc/desc)
     * @return array Sorted items
     * @since 4.0.0
     */
    private function sort_items($items, $orderby, $order)
    {
        usort($items, function ($a, $b) use ($orderby, $order) {
            $value_a = $this->get_sort_value($a, $orderby);
            $value_b = $this->get_sort_value($b, $orderby);

            if ($value_a == $value_b) {
                return 0;
            }

            $result = ($value_a < $value_b) ? -1 : 1;

            return ($order === 'asc') ? $result : -$result;
        });

        return $items;
    }

    /**
     * Get sort value for item based on column
     *
     * @param array  $item    Item to get value from
     * @param string $orderby Column name
     * @return mixed Sort value
     * @since 4.0.0
     */
    private function get_sort_value($item, $orderby)
    {
        switch ($orderby) {
            case 'title':
                return strtolower($item['title'] ?? '');

            case 'type':
                return strtolower($item['category'] ?? '');

            case 'source':
                return strtolower($item['source'] ?? 'Media Library');

            case 'files':
                return intval($item['file_count'] ?? 0);

            case 'size':
                return intval($item['total_size'] ?? 0);

            default:
                return '';
        }
    }

    /**
     * Render checkbox column
     *
     * @param array $item Item data
     * @return string Checkbox HTML
     * @since 4.0.0
     */
    public function column_cb($item)
    {
        if (isset($item['id']) && $item['id'] > 0) {
            return sprintf(
                '<input type="checkbox" name="media[]" value="%s" />',
                $item['id']
            );
        }
        return '';
    }

    /**
     * Render thumbnail column
     *
     * @param array $item Item data
     * @return string Thumbnail HTML
     * @since 4.0.0
     */
    public function column_thumbnail($item)
    {
        if (!empty($item['thumbnail_url'])) {
            return sprintf(
                '<img src="%s" alt="%s" style="width: 60px; height: 60px; object-fit: cover; border-radius: 4px;">',
                esc_url($item['thumbnail_url']),
                esc_attr($item['title'])
            );
        }

        return '<div style="width: 60px; height: 60px; background: #f0f0f0; display: flex; align-items: center; justify-content: center; border-radius: 4px; font-size: 20px;">📷</div>';
    }

    /**
     * Render title column
     *
     * @param array $item Item data
     * @return string Title HTML
     * @since 4.0.0
     */
    public function column_title($item)
    {
        return '<strong>' . esc_html($item['title']) . '</strong>';
    }

    /**
     * Render type column
     *
     * @param array $item Item data
     * @return string Type HTML
     * @since 4.0.0
     */
    public function column_type($item)
    {
        return esc_html($item['category'] ?? 'Unknown');
    }

    /**
     * Render source column
     *
     * @param array $item Item data
     * @return string Source HTML with badge
     * @since 4.0.0
     */
    public function column_source($item)
    {
        $source = $item['source'] ?? 'Media Library';
        $badge_class = ($source === 'Media Library') ? 'source-media-library' : 'source-theme';

        return sprintf(
            '<span class="source-badge %s">%s</span>',
            $badge_class,
            esc_html($source)
        );
    }

    /**
     * Render files column
     *
     * @param array $item Item data
     * @return string Files count
     * @since 4.0.0
     */
    public function column_files($item)
    {
        return intval($item['file_count'] ?? 0);
    }

    /**
     * Render size column
     *
     * @param array $item Item data
     * @return string Formatted size
     * @since 4.0.0
     */
    public function column_size($item)
    {
        $bytes = intval($item['total_size'] ?? 0);
        return MIF_File_Utils::format_bytes($bytes);
    }

    /**
     * Render dimensions column
     *
     * @param array $item Item data
     * @return string Dimensions or dash
     * @since 4.0.0
     */
    public function column_dimensions($item)
    {
        if (!empty($item['dimensions'])) {
            return esc_html($item['dimensions']);
        }

        if (!empty($item['font_family'])) {
            return '<em>' . esc_html($item['font_family']) . '</em>';
        }

        return '—';
    }

    /**
     * Default column rendering
     *
     * @param array  $item        Item data
     * @param string $column_name Column name
     * @return string Column content
     * @since 4.0.0
     */
    public function column_default($item, $column_name)
    {
        return isset($item[$column_name]) ? esc_html($item[$column_name]) : '—';
    }

    /**
     * Message to display when no items found
     *
     * @since 4.0.0
     */
    public function no_items()
    {
        esc_html_e('No media items found.', 'media-inventory-forge');
    }
}
