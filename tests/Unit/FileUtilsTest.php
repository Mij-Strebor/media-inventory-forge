<?php

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Tests for MINVF_File_Utils
 *
 * All methods under test are pure static functions — no WordPress mocking required.
 */
class FileUtilsTest extends TestCase
{
    // =========================================================================
    // format_bytes
    // =========================================================================

    #[DataProvider('formatBytesProvider')]
    public function test_format_bytes(int $input, string $expected): void
    {
        $this->assertSame($expected, MINVF_File_Utils::format_bytes($input));
    }

    public static function formatBytesProvider(): array
    {
        return [
            'zero'                => [0,          '0 B'],
            'one byte'            => [1,          '1 B'],
            '1023 bytes'          => [1023,       '1023 B'],
            '1 KB exactly'        => [1024,       '1 KB'],
            '1.5 KB'              => [1536,       '1.5 KB'],
            '1.46 KB (rounded)'   => [1500,       '1.46 KB'],
            '1 MB exactly'        => [1048576,    '1 MB'],
            '1 GB exactly'        => [1073741824, '1 GB'],
            'negative clips to 0' => [-1,         '0 B'],
        ];
    }

    public function test_format_bytes_precision_zero_drops_decimal(): void
    {
        $this->assertSame('2 KB', MINVF_File_Utils::format_bytes(2048, 0));
    }

    public function test_format_bytes_precision_one(): void
    {
        $this->assertSame('1.5 KB', MINVF_File_Utils::format_bytes(1536, 1));
    }

    // =========================================================================
    // get_category
    // =========================================================================

    #[DataProvider('categoryProvider')]
    public function test_get_category(string $mime_type, string $expected): void
    {
        $this->assertSame($expected, MINVF_File_Utils::get_category($mime_type));
    }

    public static function categoryProvider(): array
    {
        return [
            // Images
            'jpeg'      => ['image/jpeg',       'Images'],
            'png'       => ['image/png',        'Images'],
            'gif'       => ['image/gif',        'Images'],
            'webp'      => ['image/webp',       'Images'],
            // SVG is its own category
            'svg'       => ['image/svg+xml',    'SVG'],
            // Videos
            'mp4'       => ['video/mp4',        'Videos'],
            'webm'      => ['video/webm',       'Videos'],
            // Audio
            'mp3'       => ['audio/mpeg',       'Audio'],
            'wav'       => ['audio/wav',        'Audio'],
            // PDF
            'pdf'       => ['application/pdf', 'PDFs'],
            // Fonts — modern MIME types
            'woff2'     => ['font/woff2',       'Fonts'],
            'woff'      => ['font/woff',        'Fonts'],
            'ttf'       => ['font/ttf',         'Fonts'],
            'otf'       => ['font/otf',         'Fonts'],
            // Fonts — legacy application/ MIME types
            'woff-app'  => ['application/font-woff',            'Fonts'],
            'woff2-app' => ['application/font-woff2',           'Fonts'],
            'x-font-ttf'=> ['application/x-font-ttf',          'Fonts'],
            'eot'       => ['application/vnd.ms-fontobject',    'Fonts'],
            // Archives (must resolve before generic application/)
            'zip'       => ['application/zip',              'Archives'],
            'zip-x'     => ['application/x-zip-compressed', 'Archives'],
            'rar'       => ['application/x-rar-compressed', 'Archives'],
            '7z'        => ['application/x-7z-compressed',  'Archives'],
            'tar'       => ['application/x-tar',            'Archives'],
            // Office documents
            'doc'       => ['application/msword',                                                           'Documents'],
            'docx'      => ['application/vnd.openxmlformats-officedocument.wordprocessingml.document',     'Documents'],
            'xls'       => ['application/vnd.ms-excel',                                                    'Documents'],
            'xlsx'      => ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',           'Documents'],
            'ppt'       => ['application/vnd.ms-powerpoint',                                               'Documents'],
            'pptx'      => ['application/vnd.openxmlformats-officedocument.presentationml.presentation',   'Documents'],
            // Catch-all application/ types
            'binary'    => ['application/octet-stream', 'Other Documents'],
            'json'      => ['application/json',         'Other Documents'],
            // text/* and unknown types are not WordPress media — return Other
            'text'      => ['text/plain',  'Other'],
            'csv'       => ['text/csv',    'Other'],
            'unknown'   => ['unknown/xyz', 'Other'],
        ];
    }

    // =========================================================================
    // get_font_family
    // =========================================================================

    #[DataProvider('fontFamilyProvider')]
    public function test_get_font_family(string $title, string $filename, string $expected): void
    {
        $this->assertSame($expected, MINVF_File_Utils::get_font_family($title, $filename));
    }

    public static function fontFamilyProvider(): array
    {
        return [
            'strips bold weight from filename'      => ['', 'Inter-Bold.woff2',             'Inter'],
            'strips regular and hyphen separators'  => ['', 'open-sans-regular.ttf',        'Open Sans'],
            'strips semibold'                       => ['', 'lato-semibold.woff2',          'Lato'],
            'strips italic'                         => ['', 'merriweather-italic.woff',     'Merriweather'],
            'underscore becomes space'              => ['', 'source_code_pro.otf',          'Source Code Pro'],
            'title takes priority over filename'    => ['Roboto Light', 'roboto-light.woff2', 'Roboto'],
            'multi-word title strips weight'        => ['Playfair Display Bold', '',        'Playfair Display'],
            'both empty returns unknown'            => ['', '',                             'Unknown Font'],
        ];
    }

    // =========================================================================
    // sanitize_file_path
    // =========================================================================

    #[DataProvider('sanitizePathProvider')]
    public function test_sanitize_file_path(string $path, string $basedir, string $expected): void
    {
        $this->assertSame($expected, MINVF_File_Utils::sanitize_file_path($path, $basedir));
    }

    public static function sanitizePathProvider(): array
    {
        return [
            'strips upload basedir prefix' => [
                '/var/www/wp-content/uploads/2024/image.jpg',
                '/var/www/wp-content/uploads',
                '/2024/image.jpg',
            ],
            'nested subdirectory' => [
                '/path/to/uploads/fonts/Inter.woff2',
                '/path/to/uploads',
                '/fonts/Inter.woff2',
            ],
            'basedir not in path returns original' => [
                '/some/other/path/file.jpg',
                '/var/www/uploads',
                '/some/other/path/file.jpg',
            ],
            'empty basedir returns path unchanged' => [
                '/path/to/file.jpg',
                '',
                '/path/to/file.jpg',
            ],
        ];
    }
}
