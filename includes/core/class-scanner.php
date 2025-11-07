<?php

/**
 * Media Scanner Core Functionality for Media Inventory Forge
 * 
 * Handles batch scanning and processing of WordPress media attachments.
 * Provides progressive scanning capabilities for large media libraries with
 * comprehensive error handling, memory management, and performance optimization.
 * 
 * This class orchestrates the scanning process by:
 * - Managing batch iterations through the media library
 * - Coordinating with MIF_File_Processor for individual file analysis
 * - Tracking progress and collecting errors
 * - Optimizing WordPress queries for performance
 * - Managing memory limits for large-scale operations
 * 
 * @package MediaInventoryForge
 * @subpackage Core
 * @since 2.0.0
 * 
 * Architecture:
 * 1. Public API - Main scanning interface
 * 2. Environment Management - Resource allocation and cleanup
 * 3. Data Retrieval - WordPress attachment queries
 * 4. Processing Coordination - Individual attachment handling
 * 5. Statistics & Reporting - Progress tracking and error logging
 */

// Prevent direct access
defined('ABSPATH') || exit;

/**
 * MIF_Scanner Class
 * 
 * Core scanning engine for Media Inventory Forge. Handles batch processing
 * of WordPress media attachments with progressive scanning, error tracking,
 * and memory-conscious operations.
 */
class MIF_Scanner
{
    /* ==========================================================================
       PROPERTIES
       ========================================================================== */

    /**
     * Number of attachments to process per batch
     * 
     * @var int
     */
    private $batch_size;

    /**
     * WordPress upload directory information
     * 
     * @var array
     */
    private $upload_dir;

    /**
     * Count of successfully processed attachments
     * 
     * @var int
     */
    private $processed_count = 0;

    /**
     * Collection of error messages from current batch
     * 
     * @var array
     */
    private $errors = [];

    /**
     * File processor instance for individual attachment handling
     *
     * @var MIF_File_Processor
     */
    private $file_processor;

    /**
     * Whether theme files have been scanned yet
     *
     * @var bool
     */
    private $theme_files_scanned = false;

    /**
     * Collection of theme files found during scanning
     *
     * @var array
     */
    private $theme_files = [];

    /* ==========================================================================
       1. PUBLIC API
       ========================================================================== */

    /**
     * Constructor
     * 
     * Initializes scanner with batch size configuration and sets up
     * file processor. Validates and constrains batch size to safe limits.
     * 
     * @param int $batch_size Number of attachments per batch (default: 10, max: 50)
     * 
     * @since 2.0.0
     */
    public function __construct($batch_size = 10)
    {
        $this->batch_size = max(1, min(50, intval($batch_size)));
        $this->upload_dir = wp_upload_dir();
        $this->file_processor = new MIF_File_Processor();
    }

    /**
     * Scan Batch of Attachments
     * 
     * Processes one batch of media attachments starting from specified offset.
     * Prepares environment, retrieves attachments, processes each file, and
     * returns comprehensive results including progress, data, and errors.
     * 
     * @param int $offset Starting position in media library for this batch
     * @return array {
     *     Batch scan results with progress and data
     *     
     *     @type array  $data                 Processed attachment data
     *     @type int    $offset               Offset for next batch
     *     @type int    $total                Total attachments in library
     *     @type bool   $complete             Whether scanning is complete
     *     @type int    $processed            Total attachments processed so far
     *     @type array  $errors               Error messages from this batch
     *     @type int    $batch_size           Configured batch size
     *     @type int    $current_batch_count  Items processed in current batch
     * }
     * 
     * @since 2.0.0
     */
    public function scan_batch($offset)
    {
        // Set time and memory limits for batch processing
        $this->prepare_environment();

        $attachments = $this->get_attachments($offset);
        $inventory_data = [];

        // Scan theme files on first batch
        if ($offset === 0 && !$this->theme_files_scanned) {
            $this->scan_theme_files();
            $this->theme_files_scanned = true;
        }

        foreach ($attachments as $attachment_id) {
            try {
                $item_data = $this->process_attachment($attachment_id);
                if ($item_data) {
                    // Mark as Media Library source
                    $item_data['source'] = 'Media Library';
                    $inventory_data[] = $item_data;
                    $this->processed_count++;
                }
            } catch (Exception $e) {
                $this->log_error($attachment_id, $e->getMessage());
            }
        }

        // Add theme files to the first batch results
        if ($offset === 0 && !empty($this->theme_files)) {
            $inventory_data = array_merge($inventory_data, $this->theme_files);
        }

        $total_attachments = $this->get_total_attachments();
        $total_with_theme = $total_attachments + count($this->theme_files);
        $processed_total = min($offset + $this->batch_size, $total_attachments);

        return [
            'data' => $inventory_data,
            'offset' => $offset + $this->batch_size,
            'total' => $total_with_theme,
            'complete' => $processed_total >= $total_attachments,
            'processed' => $processed_total + count($this->theme_files),
            'errors' => $this->errors,
            'batch_size' => $this->batch_size,
            'current_batch_count' => count($inventory_data)
        ];
    }

    /**
     * Get Current Error List
     * 
     * Returns all error messages collected during the current batch.
     * Errors are cleared at the start of each batch in prepare_environment().
     * 
     * @return array Array of error message strings
     * 
     * @since 2.0.0
     */
    public function get_errors()
    {
        return $this->errors;
    }

    /* ==========================================================================
       2. ENVIRONMENT MANAGEMENT
       ========================================================================== */

    /**
     * Prepare Environment for Batch Processing
     * 
     * Configures PHP environment for optimal batch processing performance.
     * Raises memory limits using WordPress helper and clears error collection
     * to ensure clean slate for new batch.
     * 
     * @return void
     * 
     * @since 2.0.0
     */
    private function prepare_environment()
    {
        // Raise memory limit if possible
        wp_raise_memory_limit('admin');

        // Clear any existing errors for this batch
        $this->errors = [];
    }

    /* ==========================================================================
       3. DATA RETRIEVAL
       ========================================================================== */

    /**
     * Get Attachments for Current Batch
     * 
     * Retrieves attachment IDs for processing using optimized WordPress query.
     * Disables unnecessary meta and term caching for performance, and returns
     * only IDs rather than full post objects.
     * 
     * @param int $offset Starting position in media library
     * @return array Array of attachment IDs
     * 
     * @since 2.0.0
     */
    private function get_attachments($offset)
    {
        $args = [
            'post_type' => 'attachment',
            'posts_per_page' => $this->batch_size,
            'offset' => $offset,
            'fields' => 'ids',
            'post_status' => 'inherit',
            'no_found_rows' => true, // Performance optimization
            'update_post_meta_cache' => false, // Performance optimization
            'update_post_term_cache' => false  // Performance optimization
        ];

        return get_posts($args);
    }

    /**
     * Get Total Number of Attachments
     * 
     * Queries WordPress for total count of attachment posts with 'inherit'
     * status. Used for progress calculation and completion detection.
     * 
     * @return int Total number of attachments in media library
     * 
     * @since 2.0.0
     */
    private function get_total_attachments()
    {
        $counts = wp_count_posts('attachment');
        return isset($counts->inherit) ? $counts->inherit : 0;
    }

    /* ==========================================================================
       4. PROCESSING COORDINATION
       ========================================================================== */

    /**
     * Process Individual Attachment
     * 
     * Coordinates processing of single attachment by retrieving WordPress
     * metadata, validating data integrity, and delegating to file processor.
     * Handles errors gracefully and logs issues for reporting.
     * 
     * @param int $attachment_id WordPress attachment post ID
     * @return array|null Processed attachment data or null on failure
     * 
     * @since 2.0.0
     */
    private function process_attachment($attachment_id)
    {
        $file_path = get_attached_file($attachment_id);
        $mime_type = get_post_mime_type($attachment_id);
        $title = get_the_title($attachment_id);

        // Validate attachment data
        if (!$this->file_processor->validate_attachment_data($attachment_id, $file_path, $mime_type)) {
            $this->log_error($attachment_id, "Invalid attachment data: missing file path or MIME type");
            return null;
        }

        // Process the file
        try {
            return $this->file_processor->process_file($attachment_id, $file_path, $mime_type, $title);
        } catch (Exception $e) {
            $this->log_error($attachment_id, "File processing failed: " . $e->getMessage());
            return null;
        }
    }

    /* ==========================================================================
       5. STATISTICS & REPORTING
       ========================================================================== */

    /**
     * Log Error for Specific Attachment
     *
     * Records error message with attachment ID context for debugging and
     * user feedback. Errors are collected per batch and returned with
     * scan results.
     *
     * @param int    $attachment_id WordPress attachment post ID
     * @param string $message       Error description
     * @return void
     *
     * @since 2.0.0
     */
    private function log_error($attachment_id, $message)
    {
        $error_msg = "Attachment ID {$attachment_id}: {$message}";
        $this->errors[] = $error_msg;
    }

    /* ==========================================================================
       6. THEME FILE SCANNING
       ========================================================================== */

    /**
     * Scan Theme Directories for Media Files
     *
     * Scans all installed themes for media files (images, fonts, documents, etc.)
     * and adds them to the inventory with theme source notation.
     *
     * @return void
     *
     * @since 4.0.0
     */
    private function scan_theme_files()
    {
        $themes_dir = get_theme_root();
        if (!is_dir($themes_dir)) {
            return;
        }

        // Get all theme directories
        $theme_dirs = glob($themes_dir . '/*', GLOB_ONLYDIR);
        if (empty($theme_dirs)) {
            return;
        }

        foreach ($theme_dirs as $theme_dir) {
            $theme_name = basename($theme_dir);
            $this->scan_theme_directory($theme_dir, $theme_name);
        }
    }

    /**
     * Scan Single Theme Directory Recursively
     *
     * Recursively scans a theme directory for media files, processes each file,
     * and adds it to the theme_files collection.
     *
     * @param string $directory  Path to theme directory
     * @param string $theme_name Name of the theme
     * @return void
     *
     * @since 4.0.0
     */
    private function scan_theme_directory($directory, $theme_name)
    {
        // Media file extensions to look for (WordPress-registered media types only)
        $media_extensions = [
            // Images
            'jpg', 'jpeg', 'png', 'gif', 'svg', 'webp', 'ico', 'bmp',
            // Fonts
            'ttf', 'otf', 'woff', 'woff2', 'eot',
            // Documents
            'pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx',
            // Archives
            'zip', 'rar', 'tar', 'gz', '7z', 'bz2',
            // Audio
            'mp3', 'wav', 'ogg', 'm4a', 'flac',
            // Video
            'mp4', 'mov', 'avi', 'wmv', 'mkv', 'webm'
        ];

        try {
            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($directory, RecursiveDirectoryIterator::SKIP_DOTS),
                RecursiveIteratorIterator::SELF_FIRST
            );

            foreach ($iterator as $file) {
                if (!$file->isFile()) {
                    continue;
                }

                $extension = strtolower($file->getExtension());
                if (!in_array($extension, $media_extensions)) {
                    continue;
                }

                $file_path = $file->getPathname();
                $this->process_theme_file($file_path, $theme_name);
            }
        } catch (Exception $e) {
            $this->errors[] = "Theme scan error ({$theme_name}): " . $e->getMessage();
        }
    }

    /**
     * Process Single Theme File
     *
     * Processes a theme file and adds it to the theme_files collection.
     * Uses same data structure as Media Library files but with theme source.
     *
     * @param string $file_path  Full path to theme file
     * @param string $theme_name Name of the theme
     * @return void
     *
     * @since 4.0.0
     */
    private function process_theme_file($file_path, $theme_name)
    {
        if (!file_exists($file_path) || !is_readable($file_path)) {
            return;
        }

        // Get MIME type
        $extension = strtolower(pathinfo($file_path, PATHINFO_EXTENSION));
        $mime_type = mime_content_type($file_path);

        // SVG files are often misdetected as text/xml or text/plain
        // Force correct MIME type based on extension
        if ($extension === 'svg') {
            $mime_type = 'image/svg+xml';
        } elseif (!$mime_type) {
            // Fallback to extension-based MIME type
            $mime_type = $this->get_mime_type_from_extension($extension);
        }

        // Get category using existing utility
        $category = MIF_File_Utils::get_category($mime_type);
        $extension = strtolower(pathinfo($file_path, PATHINFO_EXTENSION));
        $filename = basename($file_path);
        $file_size = filesize($file_path);

        // Build item data structure matching Media Library format
        $item_data = [
            'id' => 0, // No attachment ID for theme files
            'title' => pathinfo($filename, PATHINFO_FILENAME),
            'mime_type' => $mime_type,
            'category' => $category,
            'extension' => $extension,
            'source' => 'Theme: ' . $theme_name,
            'files' => [
                [
                    'path' => str_replace(WP_CONTENT_DIR, '', $file_path),
                    'filename' => $filename,
                    'size' => $file_size,
                    'type' => 'original',
                    'dimensions' => ''
                ]
            ],
            'file_count' => 1,
            'total_size' => $file_size,
            'dimensions' => '',
            'font_family' => ''
        ];

        // Get dimensions for images
        if (strpos($mime_type, 'image/') === 0) {
            $image_info = @getimagesize($file_path);
            if ($image_info && isset($image_info[0], $image_info[1])) {
                $dimensions = $image_info[0] . ' × ' . $image_info[1] . 'px';
                $item_data['dimensions'] = $dimensions;
                $item_data['files'][0]['dimensions'] = $dimensions;
            }
        }

        // Get font family for fonts
        if ($category === 'Fonts') {
            $item_data['font_family'] = MIF_File_Utils::get_font_family($filename, $file_path);
        }

        $this->theme_files[] = $item_data;
    }

    /**
     * Get MIME Type from File Extension
     *
     * Fallback method to determine MIME type when mime_content_type() fails.
     *
     * @param string $extension File extension
     * @return string MIME type
     *
     * @since 4.0.0
     */
    private function get_mime_type_from_extension($extension)
    {
        $mime_types = [
            // Images
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'svg' => 'image/svg+xml',
            'webp' => 'image/webp',
            'ico' => 'image/x-icon',
            'bmp' => 'image/bmp',
            // Fonts
            'ttf' => 'font/ttf',
            'otf' => 'font/otf',
            'woff' => 'font/woff',
            'woff2' => 'font/woff2',
            'eot' => 'application/vnd.ms-fontobject',
            // Documents
            'pdf' => 'application/pdf',
            'doc' => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'xls' => 'application/vnd.ms-excel',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'ppt' => 'application/vnd.ms-powerpoint',
            'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            // Archives
            'zip' => 'application/zip',
            'rar' => 'application/x-rar-compressed',
            'tar' => 'application/x-tar',
            'gz' => 'application/gzip',
            '7z' => 'application/x-7z-compressed',
            'bz2' => 'application/x-bzip2',
            // Audio
            'mp3' => 'audio/mpeg',
            'wav' => 'audio/wav',
            'ogg' => 'audio/ogg',
            'm4a' => 'audio/mp4',
            'flac' => 'audio/flac',
            // Video
            'mp4' => 'video/mp4',
            'mov' => 'video/quicktime',
            'avi' => 'video/x-msvideo',
            'wmv' => 'video/x-ms-wmv',
            'mkv' => 'video/x-matroska',
            'webm' => 'video/webm'
        ];

        return isset($mime_types[$extension]) ? $mime_types[$extension] : 'application/octet-stream';
    }

    /* ==========================================================================
       END OF MIF_SCANNER CLASS
       ========================================================================== */
}
