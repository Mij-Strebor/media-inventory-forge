<?php

/**
 * Abstract base for file processors
 *
 * Holds the file-handling logic shared by every category-specific processor:
 * main-file recording, image-variation/WordPress-size processing, dimension
 * extraction, and the common attachment-validation checks. Concrete
 * processors (MINVF_File_Processor, MINVF_Image_Processor,
 * MINVF_Font_Processor) implement only what actually differs between
 * categories: process_file()'s item_data shape and matches_mime_type().
 *
 * @package MediaInventoryForge
 * @subpackage Core
 * @since 5.2.0
 */

defined('ABSPATH') || exit;

abstract class MINVF_Abstract_File_Processor implements MINVF_File_Processor_Interface
{
    /**
     * WordPress uploads directory base path
     *
     * @var string
     */
    protected $upload_basedir;

    public function __construct()
    {
        $upload_dir = wp_upload_dir();
        $this->upload_basedir = $upload_dir['basedir'];
    }

    /**
     * Whether this processor's category accepts the given MIME type.
     * Used as the final check in validate_attachment_data(). Default
     * processor accepts anything that passes the common checks.
     *
     * @param string $mime_type MIME type of file
     * @return bool
     */
    protected function matches_mime_type($mime_type)
    {
        return true;
    }

    /**
     * Performs comprehensive validation of attachment data before processing.
     * Checks for required parameters, validates attachment ID format, verifies
     * file is within uploads directory, confirms file accessibility, and
     * defers the category-specific MIME check to matches_mime_type().
     *
     * @param int    $attachment_id WordPress attachment post ID
     * @param string $file_path     Full filesystem path to file
     * @param string $mime_type     MIME type of file
     * @return bool True if all validation passes, false otherwise
     */
    public function validate_attachment_data($attachment_id, $file_path, $mime_type)
    {
        if (!$attachment_id || !$file_path || !$mime_type) {
            return false;
        }

        if (!is_numeric($attachment_id) || $attachment_id <= 0) {
            return false;
        }

        if (!MINVF_File_Utils::is_valid_upload_path($file_path)) {
            return false;
        }

        if (!MINVF_File_Utils::is_file_accessible($file_path)) {
            return false;
        }

        return $this->matches_mime_type($mime_type);
    }

    /**
     * Processes the primary attachment file by extracting file information,
     * calculating size, and determining dimensions for images. Updates
     * item_data by reference. $mime_type may be omitted by processors whose
     * category is never an image (e.g. fonts) - the dimensions branch simply
     * never fires.
     *
     * @param array  &$item_data Data array to update (passed by reference)
     * @param string $file_path  Full filesystem path to file
     * @param string $mime_type  MIME type of file
     * @return void
     */
    protected function process_main_file(&$item_data, $file_path, $mime_type = '')
    {
        if (!MINVF_File_Utils::is_file_accessible($file_path)) {
            return;
        }

        $file_size = MINVF_File_Utils::get_safe_file_size($file_path);
        $file_info = [
            'path' => MINVF_File_Utils::sanitize_file_path($file_path, $this->upload_basedir),
            'filename' => basename($file_path),
            'size' => $file_size,
            'type' => 'original',
            'dimensions' => ''
        ];

        if (strpos($mime_type, 'image/') === 0) {
            $dimensions = $this->get_image_dimensions($file_path);
            if ($dimensions) {
                $file_info['dimensions'] = $dimensions;
                $item_data['dimensions'] = $dimensions; // Store primary dimensions
            }
        }

        $item_data['files'][] = $file_info;
        $item_data['file_count']++;
        $item_data['total_size'] += $file_size;
    }

    /**
     * Handles image-specific processing: thumbnail URL retrieval for
     * display, and delegation to WordPress size processing.
     *
     * @param array  &$item_data    Data array to update (passed by reference)
     * @param int    $attachment_id WordPress attachment post ID
     * @param string $file_path     Full filesystem path to original file
     * @return void
     */
    protected function process_image_variations(&$item_data, $attachment_id, $file_path)
    {
        $thumbnail_url = wp_get_attachment_image_src($attachment_id, 'thumbnail');
        if ($thumbnail_url) {
            $item_data['thumbnail_url'] = $thumbnail_url[0];
            $item_data['thumbnail_width'] = $thumbnail_url[1];
            $item_data['thumbnail_height'] = $thumbnail_url[2];
        } else {
            // Fallback to the original image if no thumbnail
            $item_data['thumbnail_url'] = wp_get_attachment_url($attachment_id);
        }

        $this->process_wordpress_image_sizes($item_data, $attachment_id, $file_path);
    }

    /**
     * Processes all WordPress-generated intermediate image sizes (thumbnail,
     * medium, large, etc.) by reading attachment metadata, locating size
     * files, and extracting their information. Tracks processed files to
     * avoid duplicate entries.
     *
     * @param array  &$item_data    Data array to update (passed by reference)
     * @param int    $attachment_id WordPress attachment post ID
     * @param string $file_path     Full filesystem path to original file
     * @return void
     */
    protected function process_wordpress_image_sizes(&$item_data, $attachment_id, $file_path)
    {
        $metadata = wp_get_attachment_metadata($attachment_id);

        if (!$metadata || !isset($metadata['sizes'])) {
            return;
        }

        $dirname = dirname($file_path);
        $processed_files = []; // Track processed files to avoid duplicates

        foreach ($metadata['sizes'] as $size_name => $size_data) {
            $size_file = $dirname . '/' . $size_data['file'];
            $size_file_key = basename($size_file); // Use basename as key to avoid duplicates

            if (MINVF_File_Utils::is_file_accessible($size_file) && !isset($processed_files[$size_file_key])) {
                $file_size = MINVF_File_Utils::get_safe_file_size($size_file);
                $file_info = [
                    'path' => MINVF_File_Utils::sanitize_file_path($size_file, $this->upload_basedir),
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

    /**
     * Extracts image dimensions using PHP's getimagesize() function with
     * error suppression. Validates file accessibility before attempting
     * to read dimensions and handles errors gracefully.
     *
     * @param string $file_path Full filesystem path to image file
     * @return string|null Formatted dimensions string (e.g., "1920 x 1080px") or null on failure
     */
    protected function get_image_dimensions($file_path)
    {
        if (!MINVF_File_Utils::is_file_accessible($file_path)) {
            return null;
        }

        $image_info = @getimagesize($file_path);
        if (!$image_info || !isset($image_info[0], $image_info[1])) {
            return null;
        }

        return $image_info[0] . ' × ' . $image_info[1] . 'px';
    }
}
