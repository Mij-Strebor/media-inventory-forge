<?php

/**
 * Image file processor for Media Inventory Forge
 *
 * Shared file handling lives in MINVF_Abstract_File_Processor.
 *
 * @package MediaInventoryForge
 * @subpackage Core
 * @since 2.0.0
 */

defined('ABSPATH') || exit;

class MINVF_Image_Processor extends MINVF_Abstract_File_Processor
{
    protected function matches_mime_type($mime_type)
    {
        return strpos($mime_type, 'image/') === 0;
    }

    public function process_file($attachment_id, $file_path, $mime_type, $title)
    {
        $extension = strtolower(pathinfo($file_path, PATHINFO_EXTENSION));

        $item_data = [
            'id' => $attachment_id,
            'title' => sanitize_text_field($title),
            'mime_type' => $mime_type,
            'category' => 'Images',
            'extension' => $extension,
            'files' => [],
            'file_count' => 0,
            'total_size' => 0,
            'dimensions' => '',
            'font_family' => ''
        ];

        $this->process_main_file($item_data, $file_path, $mime_type);
        $this->process_image_variations($item_data, $attachment_id, $file_path);

        return $item_data;
    }
}
