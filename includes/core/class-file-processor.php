<?php

/**
 * File Processor for Media Inventory Forge
 * 
 * Base file processor that handles individual attachment processing including
 * metadata extraction, file validation, dimension calculation, and category-specific
 * operations. Serves as the default processor for all media types while providing
 * specialized handling for images and fonts.
 * 
 * This class coordinates with MIF_File_Utils for file system operations and
 * WordPress APIs for attachment metadata. It processes both original files and
 * WordPress-generated variations (thumbnails, intermediate sizes).
 * 
 * @package MediaInventoryForge
 * @subpackage Core
 * @since 2.0.0
 * 
 * Architecture:
 * 1. Public API - Interface methods for processing and validation
 * 2. Main File Processing - Original file handling
 * 3. Image Processing - Image-specific operations and variations
 * 4. WordPress Integration - Generated sizes and thumbnails
 * 5. Dimension Extraction - Image metadata retrieval
 * 6. Validation - Data integrity checks
 */

// Prevent direct access
defined('ABSPATH') || exit;

/**
 * MIF_File_Processor Class
 * 
 * Default file processor implementing MIF_File_Processor_Interface.
 * Handles general file processing with specialized branches for images
 * and fonts. Coordinates file system access, metadata extraction, and
 * WordPress API integration.
 */
class MIF_File_Processor implements MIF_File_Processor_Interface
{
    /* ==========================================================================
       PROPERTIES
       ========================================================================== */

    /**
     * WordPress uploads directory base path
     * 
     * @var string
     */
    private $upload_basedir;

    /**
     * WordPress uploads directory base URL
     * 
     * @var string
     */
    private $upload_baseurl;

    /* ==========================================================================
       CONSTRUCTOR
       ========================================================================== */

    /**
     * Constructor
     * 
     * Initializes file processor with WordPress upload directory paths
     * for file operations and URL generation.
     * 
     * @since 2.0.0
     */
    public function __construct()
    {
        $upload_dir = wp_upload_dir();
        $this->upload_basedir = $upload_dir['basedir'];
        $this->upload_baseurl = $upload_dir['baseurl'];
    }

    /* ==========================================================================
       1. PUBLIC API
       ========================================================================== */

    /**
     * Process Single File
     * 
     * Main entry point for file processing. Validates inputs, determines category,
     * processes main file, and delegates to category-specific handlers for
     * specialized processing (fonts, images).
     * 
     * @param int    $attachment_id WordPress attachment post ID
     * @param string $file_path     Full filesystem path to file
     * @param string $mime_type     MIME type of file
     * @param string $title         Attachment title from WordPress
     * @return array|null {
     *     Processed file data or null on failure
     *     
     *     @type int    $id           Attachment ID
     *     @type string $title        Sanitized title
     *     @type string $mime_type    MIME type
     *     @type string $category     File category (Images, Fonts, etc.)
     *     @type string $extension    Lowercase file extension
     *     @type array  $files        Array of file information objects
     *     @type int    $file_count   Total number of files
     *     @type int    $total_size   Combined size in bytes
     *     @type string $dimensions   Primary image dimensions (if applicable)
     *     @type string $font_family  Font family name (if applicable)
     * }
     * 
     * @since 2.0.0
     */
    public function process_file($attachment_id, $file_path, $mime_type, $title)
    {
        // Validate inputs
        if (!$attachment_id || !$file_path || !$mime_type) {
            return null;
        }

        $category = MIF_File_Utils::get_category($mime_type);
        $extension = strtolower(pathinfo($file_path, PATHINFO_EXTENSION));

        $item_data = [
            'id' => $attachment_id,
            'title' => sanitize_text_field($title),
            'mime_type' => $mime_type,
            'category' => $category,
            'extension' => $extension,
            'files' => [],
            'file_count' => 0,
            'total_size' => 0,
            'dimensions' => '',
            'font_family' => ''
        ];

        // Process main file
        $this->process_main_file($item_data, $file_path, $mime_type);

        // Handle category-specific processing
        switch ($category) {
            case 'Fonts':
                $item_data['font_family'] = MIF_File_Utils::get_font_family($title, $file_path);
                break;
            case 'Images':
                $this->process_image_variations($item_data, $attachment_id, $file_path);
                break;
        }

        return $item_data;
    }

    /**
     * Validate Attachment Data
     * 
     * Performs comprehensive validation of attachment data before processing.
     * Checks for required parameters, validates attachment ID format, verifies
     * file is within uploads directory, and confirms file accessibility.
     * 
     * @param int    $attachment_id WordPress attachment post ID
     * @param string $file_path     Full filesystem path to file
     * @param string $mime_type     MIME type of file
     * @return bool True if all validation passes, false otherwise
     * 
     * @since 2.0.0
     */
    public function validate_attachment_data($attachment_id, $file_path, $mime_type)
    {
        // Check required parameters
        if (!$attachment_id || !$file_path || !$mime_type) {
            return false;
        }

        // Validate attachment ID is numeric
        if (!is_numeric($attachment_id) || $attachment_id <= 0) {
            return false;
        }

        // Validate file path is in uploads directory
        if (!MIF_File_Utils::is_valid_upload_path($file_path)) {
            return false;
        }

        // Check if file is accessible
        if (!MIF_File_Utils::is_file_accessible($file_path)) {
            return false;
        }

        return true;
    }

    /* ==========================================================================
       2. MAIN FILE PROCESSING
       ========================================================================== */

    /**
     * Process Main/Original File
     * 
     * Processes the primary attachment file by extracting file information,
     * calculating size, and determining dimensions for images. Updates item_data
     * array by reference with file information.
     * 
     * @param array  &$item_data Data array to update (passed by reference)
     * @param string $file_path  Full filesystem path to file
     * @param string $mime_type  MIME type of file
     * @return void
     * 
     * @since 2.0.0
     */
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

        // Get image dimensions for image files
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

    /* ==========================================================================
       3. IMAGE PROCESSING
       ========================================================================== */

    /**
     * Process Image Variations and Thumbnails
     * 
     * Handles image-specific processing including thumbnail URL retrieval
     * for display purposes and delegation to WordPress size processing.
     * Sets thumbnail data for admin interface display.
     * 
     * @param array  &$item_data    Data array to update (passed by reference)
     * @param int    $attachment_id WordPress attachment post ID
     * @param string $file_path     Full filesystem path to original file
     * @return void
     * 
     * @since 2.0.0
     */
    private function process_image_variations(&$item_data, $attachment_id, $file_path)
    {
        // Get thumbnail URL for display
        $thumbnail_url = wp_get_attachment_image_src($attachment_id, 'thumbnail');
        if ($thumbnail_url) {
            $item_data['thumbnail_url'] = $thumbnail_url[0];
            $item_data['thumbnail_width'] = $thumbnail_url[1];
            $item_data['thumbnail_height'] = $thumbnail_url[2];
        } else {
            // Fallback to the original image if no thumbnail
            $item_data['thumbnail_url'] = wp_get_attachment_url($attachment_id);
        }

        // Process WordPress generated image sizes
        $this->process_wordpress_image_sizes($item_data, $attachment_id, $file_path);
    }

    /* ==========================================================================
       4. WORDPRESS INTEGRATION
       ========================================================================== */

    /**
     * Process WordPress Generated Image Sizes
     * 
     * Processes all WordPress-generated intermediate image sizes (thumbnail,
     * medium, large, etc.) by reading attachment metadata, locating size files,
     * and extracting their information. Tracks processed files to avoid
     * duplicate entries.
     * 
     * @param array  &$item_data    Data array to update (passed by reference)
     * @param int    $attachment_id WordPress attachment post ID
     * @param string $file_path     Full filesystem path to original file
     * @return void
     * 
     * @since 2.0.0
     */
    private function process_wordpress_image_sizes(&$item_data, $attachment_id, $file_path)
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

            if (MIF_File_Utils::is_file_accessible($size_file) && !isset($processed_files[$size_file_key])) {
                $file_size = MIF_File_Utils::get_safe_file_size($size_file);
                $file_info = [
                    'path' => MIF_File_Utils::sanitize_file_path($size_file, $this->upload_basedir),
                    'filename' => basename($size_file),
                    'size' => $file_size,
                    'type' => 'size: ' . $size_name,
                    'dimensions' => ''
                ];

                // Get dimensions for this size file
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

    /* ==========================================================================
       5. DIMENSION EXTRACTION
       ========================================================================== */

    /**
     * Get Image Dimensions Safely
     * 
     * Extracts image dimensions using PHP's getimagesize() function with
     * error suppression. Validates file accessibility before attempting
     * to read dimensions and handles errors gracefully.
     * 
     * @param string $file_path Full filesystem path to image file
     * @return string|null Formatted dimensions string (e.g., "1920 × 1080px") or null on failure
     * 
     * @since 2.0.0
     */
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

    /* ==========================================================================
       END OF MIF_FILE_PROCESSOR CLASS
       ========================================================================== */
}
