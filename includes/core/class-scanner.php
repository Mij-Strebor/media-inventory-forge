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

        foreach ($attachments as $attachment_id) {
            try {
                $item_data = $this->process_attachment($attachment_id);
                if ($item_data) {
                    $inventory_data[] = $item_data;
                    $this->processed_count++;
                }
            } catch (Exception $e) {
                $this->log_error($attachment_id, $e->getMessage());
            }
        }

        $total_attachments = $this->get_total_attachments();
        $processed_total = min($offset + $this->batch_size, $total_attachments);

        return [
            'data' => $inventory_data,
            'offset' => $offset + $this->batch_size,
            'total' => $total_attachments,
            'complete' => $processed_total >= $total_attachments,
            'processed' => $processed_total,
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
       END OF MIF_SCANNER CLASS
       ========================================================================== */
}
