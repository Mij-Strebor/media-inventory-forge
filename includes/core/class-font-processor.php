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
    /**
     * Delegates to MINVF_File_Utils::get_category() - the same check
     * MINVF_Processor_Factory uses to decide this class gets instantiated
     * at all - rather than keeping a second, independent list of font MIME
     * patterns. Duplicating it once already caused a real bug: this
     * previously only recognized 'font/' and 'application/font', missing
     * 'application/x-font' (application/x-font-ttf, used by real uploaded
     * .ttf font files). The factory routed those to this class correctly,
     * but this method then rejected them, so validate_attachment_data()
     * failed and the scanner silently dropped every .ttf font from the
     * inventory - confirmed live: Space Grotesk and Inter (8 .ttf files)
     * were invisible in the Fonts category while JetBrains Mono (.woff2,
     * matched by 'font/') was not.
     */
    protected function matches_mime_type($mime_type)
    {
        return 'Fonts' === MINVF_File_Utils::get_category($mime_type);
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
