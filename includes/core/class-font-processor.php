<?php

/**
 * Font file processor for Media Inventory Forge
 * 
 * @package MediaInventoryForge
 * @subpackage Core
 * @since 2.0.0
 */

defined('ABSPATH') || exit;

class MIF_Font_Processor implements MIF_File_Processor_Interface
{
    private $upload_basedir;

    public function __construct()
    {
        $upload_dir = wp_upload_dir();
        $this->upload_basedir = $upload_dir['basedir'];
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
            'font_family' => MIF_File_Utils::get_font_family($title, $file_path)
        ];

        $this->process_main_file($item_data, $file_path);

        return $item_data;
    }

    public function validate_attachment_data($attachment_id, $file_path, $mime_type)
    {
        if (!$attachment_id || !$file_path || !$mime_type) {
            return false;
        }

        if (!is_numeric($attachment_id) || $attachment_id <= 0) {
            return false;
        }

        if (!MIF_File_Utils::is_valid_upload_path($file_path)) {
            return false;
        }

        if (!MIF_File_Utils::is_file_accessible($file_path)) {
            return false;
        }

        return strpos($mime_type, 'font/') === 0 || strpos($mime_type, 'application/font') === 0;
    }

    private function process_main_file(&$item_data, $file_path)
    {
        if (!MIF_File_Utils::is_file_accessible($file_path)) {
            return;
        }

        $file_size = MIF_File_Utils::get_safe_file_size($file_path);
        $file_info = [
            'path' => MIF_File_Utils::sanitize_file_path($file_path, $this->upload_basedir),
            'filename' => basename($file_path),
            'size' => $file_size,
            'type' => 'original',
            'dimensions' => ''
        ];

        $item_data['files'][] = $file_info;
        $item_data['file_count']++;
        $item_data['total_size'] += $file_size;
    }
}
