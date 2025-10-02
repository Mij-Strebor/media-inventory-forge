<?php

/**
 * Interface for file processors
 * 
 * @package MediaInventoryForge
 * @subpackage Core
 * @since 2.0.0
 */

defined('ABSPATH') || exit;

interface MIF_File_Processor_Interface
{
    /**
     * Process a file and return metadata
     */
    public function process_file($attachment_id, $file_path, $mime_type, $title);
    public function validate_attachment_data($attachment_id, $file_path, $mime_type);
}
