<?php

/**
 * File Processor for Media Inventory Forge
 *
 * Default/fallback processor for any category not handled by a dedicated
 * processor (MINVF_Image_Processor, MINVF_Font_Processor). Shared file
 * handling lives in MINVF_Abstract_File_Processor.
 *
 * @package MediaInventoryForge
 * @subpackage Core
 * @since 2.0.0
 */

// Prevent direct access
defined('ABSPATH') || exit;

class MINVF_File_Processor extends MINVF_Abstract_File_Processor
{
    /**
     * Process Single File
     *
     * Main entry point for file processing. Validates inputs, determines
     * category, and processes the main file. Only ever invoked by
     * MINVF_Processor_Factory for categories that are not Images or Fonts -
     * those are routed to their own dedicated processors before this class
     * is ever instantiated.
     *
     * @param int    $attachment_id WordPress attachment post ID
     * @param string $file_path     Full filesystem path to file
     * @param string $mime_type     MIME type of file
     * @param string $title         Attachment title from WordPress
     * @return array|null Processed file data or null on failure
     *
     * @since 2.0.0
     */
    public function process_file($attachment_id, $file_path, $mime_type, $title)
    {
        if (!$attachment_id || !$file_path || !$mime_type) {
            return null;
        }

        $category = MINVF_File_Utils::get_category($mime_type);
        $extension = strtolower(pathinfo($file_path, PATHINFO_EXTENSION));

        $item_data = [
            'id' => $attachment_id,
            'title' => sanitize_text_field($title),
            'mime_type' => $mime_type,
            'category' => $category,
            'extension' => $extension,
            'files' => [],
            'file_count' => 0,
            'total_size' => 0,
            'dimensions' => '',
            'font_family' => ''
        ];

        $this->process_main_file($item_data, $file_path, $mime_type);

        // SVGs are vector, so - unlike raster images - they have no
        // WordPress-generated size variants to pick a thumbnail from; the
        // original file itself is always the right preview to show.
        if ('SVG' === $category) {
            $item_data['thumbnail_url'] = wp_get_attachment_url($attachment_id);
        }

        return $item_data;
    }
}
