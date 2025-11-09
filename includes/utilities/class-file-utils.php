<?php

/**
 * File utilities for Media Inventory Forge
 * 
 * This class provides methods for handling file operations such as formatting sizes,
 * determining file categories, extracting font family names, and validating file paths.
 * It is designed to work with WordPress uploads and ensure files are accessible and properly categorized.
 * 
 * @package MediaInventoryForge
 * @subpackage Utilities
 * @since 2.0.0
 */

// Prevent direct access
defined('ABSPATH') || exit;

class MIF_File_Utils
{

    /**
     * Format file sizes in human-readable format
     *
     * Converts byte values to human-readable format (B, KB, MB, GB, TB)
     * with specified precision.
     *
     * @since 2.0.0
     *
     * @param int $bytes     The number of bytes to format.
     * @param int $precision Decimal precision (default 2).
     * @return string Formatted file size with unit (e.g., "2.50 MB").
     */
    public static function format_bytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = ($bytes > 0) ? floor(log($bytes) / log(1024)) : 0;
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);
        return round($bytes, $precision) . ' ' . $units[$pow];
    }

    /**
     * Get file category based on MIME type
     *
     * Categorizes media files into standard categories (Images, Fonts, Videos, etc.)
     * based on their MIME type. Includes special handling for SVG, fonts, archives,
     * and Office documents.
     *
     * @since 2.0.0
     * @since 4.0.0 Added improved font detection for EOT and TTF files.
     *
     * @param string $mime_type The MIME type to categorize.
     * @return string The category name (e.g., 'Images', 'Fonts', 'Videos').
     */
    public static function get_category($mime_type)
    {
        // SVG detection - check for both standard MIME type and common variants
        if ($mime_type === 'image/svg+xml' || $mime_type === 'text/xml' || $mime_type === 'text/plain') {
            // For text/* types, only return SVG if we're sure - caller should check extension
            // This will be handled in the scanner with extension-based fallback
            if ($mime_type === 'image/svg+xml') return 'SVG';
        }

        if (strpos($mime_type, 'image/') === 0) return 'Images';
        if (strpos($mime_type, 'video/') === 0) return 'Videos';
        if (strpos($mime_type, 'audio/') === 0) return 'Audio';
        if (strpos($mime_type, 'application/pdf') === 0) return 'PDFs';

        // Font detection - includes EOT, TTF, WOFF variants
        if (strpos($mime_type, 'font/') === 0 ||
            strpos($mime_type, 'application/font') === 0 ||
            strpos($mime_type, 'application/x-font') === 0 ||
            $mime_type === 'application/vnd.ms-fontobject') {
            return 'Fonts';
        }

        // Archives - MUST come before generic application/ check
        if (in_array($mime_type, [
            'application/zip',
            'application/x-zip-compressed',
            'application/x-rar-compressed',
            'application/x-rar',
            'application/x-tar',
            'application/x-gzip',
            'application/gzip',
            'application/x-7z-compressed',
            'application/x-bzip',
            'application/x-bzip2'
        ])) {
            return 'Archives';
        }

        // Office documents
        if (in_array($mime_type, [
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'application/vnd.ms-powerpoint',
            'application/vnd.openxmlformats-officedocument.presentationml.presentation'
        ])) {
            return 'Documents';
        }

        // DO NOT categorize text/* files - they are not WordPress-registered media
        // Text files are skipped during scanning

        if (strpos($mime_type, 'application/') === 0) return 'Other Documents';
        return 'Other';
    }

    /**
     * Extract font family name from filename or title
     *
     * Removes font weight/style indicators and file extensions to extract
     * the core font family name. Normalizes formatting to Title Case.
     *
     * @since 2.0.0
     *
     * @param string $title    The font title from media library.
     * @param string $filename The font filename.
     * @return string The extracted font family name (e.g., "Open Sans").
     */
    public static function get_font_family($title, $filename)
    {
        $name = !empty($title) ? $title : pathinfo($filename, PATHINFO_FILENAME);
        $name = preg_replace('/[-_\s]?(regular|bold|italic|light|medium|heavy|black|thin|extralight|semibold|extrabold)[-_\s]?/i', '', $name);
        $name = preg_replace('/\.(woff2?|ttf|otf|eot)$/i', '', $name);
        $name = preg_replace('/[-_\s]*\d+[-_\s]*/', '', $name);
        $name = trim($name, '-_ ');
        $name = ucwords(str_replace(['-', '_'], ' ', $name));
        return !empty($name) ? $name : 'Unknown Font';
    }

    /**
     * Validate file path is within uploads directory
     *
     * Security check to ensure file paths are within the WordPress uploads
     * directory to prevent directory traversal attacks.
     *
     * @since 2.0.0
     *
     * @param string $file_path The file path to validate.
     * @return bool True if path is valid and within uploads directory, false otherwise.
     */
    public static function is_valid_upload_path($file_path)
    {
        $upload_dir = wp_upload_dir();
        $upload_basedir = $upload_dir['basedir'];
        $real_file_path = realpath($file_path);
        $real_upload_path = realpath($upload_basedir);

        if (!$real_file_path || !$real_upload_path) {
            return false;
        }

        return strpos($real_file_path, $real_upload_path) === 0;
    }

    /**
     * Sanitize file path for display
     *
     * Removes the uploads base directory from file paths for cleaner display.
     *
     * @since 2.0.0
     *
     * @param string $file_path       The full file path.
     * @param string $upload_basedir  The uploads base directory path.
     * @return string The sanitized relative path.
     */
    public static function sanitize_file_path($file_path, $upload_basedir)
    {
        return str_replace($upload_basedir, '', $file_path);
    }

    /**
     * Check if file exists and is readable
     *
     * @since 2.0.0
     *
     * @param string $file_path The file path to check.
     * @return bool True if file exists and is readable, false otherwise.
     */
    public static function is_file_accessible($file_path)
    {
        return file_exists($file_path) && is_readable($file_path);
    }

    /**
     * Get safe file size (handles errors gracefully)
     *
     * @since 2.0.0
     *
     * @param string $file_path The file path.
     * @return int File size in bytes, or 0 if unable to read.
     */
    public static function get_safe_file_size($file_path)
    {
        if (!self::is_file_accessible($file_path)) {
            return 0;
        }

        $size = filesize($file_path);
        return $size !== false ? $size : 0;
    }
}
