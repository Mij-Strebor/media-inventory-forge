<?php

/**
 * Font file processor for Media Inventory Forge
 *
 * Shared file handling lives in MINVF_Abstract_File_Processor.
 *
 * @package MediaInventoryForge
 * @subpackage Core
 * @since 2.0.0
 */

defined('ABSPATH') || exit;

class MINVF_Font_Processor extends MINVF_Abstract_File_Processor
{
    protected function matches_mime_type($mime_type)
    {
        return strpos($mime_type, 'font/') === 0 || strpos($mime_type, 'application/font') === 0;
    }

    public function process_file($attachment_id, $file_path, $mime_type, $title)
    {
        $extension = strtolower(pathinfo($file_path, PATHINFO_EXTENSION));

        $item_data = [
            'id' => $attachment_id,
            'title' => sanitize_text_field($title),
            'mime_type' => $mime_type,
            'category' => 'Fonts',
            'extension' => $extension,
            'files' => [],
            'file_count' => 0,
            'total_size' => 0,
            'dimensions' => '',
            'font_family' => MINVF_File_Utils::get_font_family($title, $file_path)
        ];

        // Fonts never take the image-dimensions branch in process_main_file()
        // since $mime_type never starts with 'image/'.
        $this->process_main_file($item_data, $file_path, $mime_type);

        return $item_data;
    }
}
