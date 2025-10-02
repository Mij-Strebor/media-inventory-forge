<?php

/**
 * Image file processor for Media Inventory Forge
 * 
 * @package MediaInventoryForge
 * @subpackage Core
 * @since 2.0.0
 */

defined('ABSPATH') || exit;

class MIF_Image_Processor implements MIF_File_Processor_Interface
{
    private $upload_basedir;
    private $upload_baseurl;

    public function __construct()
    {
        $upload_dir = wp_upload_dir();
        $this->upload_basedir = $upload_dir['basedir'];
        $this->upload_baseurl = $upload_dir['baseurl'];
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

        return strpos($mime_type, 'image/') === 0;
    }

    private function process_main_file(&$item_data, $file_path, $mime_type)
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

        $dimensions = $this->get_image_dimensions($file_path);
        if ($dimensions) {
            $file_info['dimensions'] = $dimensions;
            $item_data['dimensions'] = $dimensions;
        }

        $item_data['files'][] = $file_info;
        $item_data['file_count']++;
        $item_data['total_size'] += $file_size;
    }

    private function process_image_variations(&$item_data, $attachment_id, $file_path)
    {
        $thumbnail_url = wp_get_attachment_image_src($attachment_id, 'thumbnail');
        if ($thumbnail_url) {
            $item_data['thumbnail_url'] = $thumbnail_url[0];
            $item_data['thumbnail_width'] = $thumbnail_url[1];
            $item_data['thumbnail_height'] = $thumbnail_url[2];
        } else {
            $item_data['thumbnail_url'] = wp_get_attachment_url($attachment_id);
        }

        $this->process_wordpress_image_sizes($item_data, $attachment_id, $file_path);
    }

    private function process_wordpress_image_sizes(&$item_data, $attachment_id, $file_path)
    {
        $metadata = wp_get_attachment_metadata($attachment_id);

        if (!$metadata || !isset($metadata['sizes'])) {
            return;
        }

        $dirname = dirname($file_path);
        $processed_files = [];

        foreach ($metadata['sizes'] as $size_name => $size_data) {
            $size_file = $dirname . '/' . $size_data['file'];
            $size_file_key = basename($size_file);

            if (MIF_File_Utils::is_file_accessible($size_file) && !isset($processed_files[$size_file_key])) {
                $file_size = MIF_File_Utils::get_safe_file_size($size_file);
                $file_info = [
                    'path' => MIF_File_Utils::sanitize_file_path($size_file, $this->upload_basedir),
                    'filename' => basename($size_file),
                    'size' => $file_size,
                    'type' => 'size: ' . $size_name,
                    'dimensions' => ''
                ];

                $dimensions = $this->get_image_dimensions($size_file);
                if ($dimensions) {
                    $file_info['dimensions'] = $dimensions;
                }

                $item_data['files'][] = $file_info;
                $item_data['file_count']++;
                $item_data['total_size'] += $file_size;
                $processed_files[$size_file_key] = true;
            }
        }
    }

    private function get_image_dimensions($file_path)
    {
        if (!MIF_File_Utils::is_file_accessible($file_path)) {
            return null;
        }

        $image_info = @getimagesize($file_path);
        if (!$image_info || !isset($image_info[0], $image_info[1])) {
            return null;
        }

        return $image_info[0] . ' × ' . $image_info[1] . 'px';
    }
}
