<?php

/**
 * Factory for creating appropriate file processors
 * 
 * @package MediaInventoryForge
 * @subpackage Core
 * @since 2.0.0
 */

defined('ABSPATH') || exit;

class MIF_Processor_Factory
{
    /**
     * Create appropriate processor for the given MIME type
     * Currently returns existing processor until specialized ones are implemented
     */
    public static function create_processor($mime_type = null)
    {
        if ($mime_type) {
            $category = MIF_File_Utils::get_category($mime_type);

            switch ($category) {
                case 'Images':
                    return new MIF_Image_Processor();
                case 'Fonts':
                    return new MIF_Font_Processor();
                default:
                    return new MIF_File_Processor();
            }
        }

        return new MIF_File_Processor();
    }
    /**
     * Get processor type for given MIME type
     */
    public static function get_available_processors()
    {
        return [
            'default' => 'MIF_File_Processor'
        ];
    }

    public static function can_handle($mime_type)
    {
        return true; // Default processor handles everything for now
    }
}
