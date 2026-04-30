<?php

/**
 * Factory for creating appropriate file processors
 * 
 * @package MediaInventoryForge
 * @subpackage Core
 * @since 2.0.0
 */

defined('ABSPATH') || exit;

class MINVF_Processor_Factory
{
    /**
     * Create appropriate processor for the given MIME type
     * Currently returns existing processor until specialized ones are implemented
     */
    public static function create_processor($mime_type = null)
    {
        if ($mime_type) {
            $category = MINVF_File_Utils::get_category($mime_type);

            switch ($category) {
                case 'Images':
                    return new MINVF_Image_Processor();
                case 'Fonts':
                    return new MINVF_Font_Processor();
                default:
                    return new MINVF_File_Processor();
            }
        }

        return new MINVF_File_Processor();
    }
    /**
     * Get processor type for given MIME type
     */
    public static function get_available_processors()
    {
        return [
            'default' => 'MINVF_File_Processor',
            'image'   => 'MINVF_Image_Processor',
            'font'    => 'MINVF_Font_Processor',
        ];
    }

    public static function can_handle($mime_type)
    {
        return true; // Default processor handles everything for now
    }
}
