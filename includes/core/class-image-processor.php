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
    /**
     * Delegates to MINVF_File_Utils::get_category() - the same check
     * MINVF_Processor_Factory uses to decide this class gets instantiated -
     * rather than keeping a second, independent MIME check that could drift
     * out of sync with it. See MINVF_Font_Processor::matches_mime_type()
     * for the real bug this pattern already caused once.
     */
    protected function matches_mime_type($mime_type)
    {
        return 'Images' === MINVF_File_Utils::get_category($mime_type);
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
