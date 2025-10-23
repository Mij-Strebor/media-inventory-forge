=== Media Inventory Forge ===
Contributors: MijStrebor
Donate link: https://buymeacoffee.com/jimrweb
Tags: media, inventory, scanner, analysis, optimization
Requires at least: 5.0
Tested up to: 6.7
Requires PHP: 7.4
Stable tag: 2.1.1
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Professional media library scanner and analyzer for WordPress developers and administrators.

== Description ==

Media Inventory Forge is a comprehensive media library scanning and analysis tool designed for WordPress developers and administrators. The plugin provides detailed insights into media assets, file organization, and optimization opportunities.

**Key Features:**

* **Comprehensive Media Scanning** – Analyzes all media types including images, videos, audio, fonts, documents, and SVGs
* **Detailed File Information** – Extracts metadata, dimensions, file sizes, and WordPress-generated variations
* **Category Organization** – Groups media by type with detailed breakdowns and statistics
* **Storage Analysis** – Provides precise storage usage by category with optimization recommendations
* **Progressive Scanning** – Handles large media libraries efficiently with batch processing
* **CSV Export** – Generate detailed reports for analysis, auditing, or cleanup planning
* **Professional Interface** – Clean, intuitive admin interface with collapsible sections
* **Font Analysis** – Specialized handling for font files with family grouping
* **Image Variations** – Tracks WordPress-generated image sizes and thumbnails
* **Network Compatible** – Works with WordPress multisite installations

**Perfect For:**

* WordPress developers analyzing client sites
* Site administrators planning cleanup projects
* Hosting providers optimizing storage usage
* SEO professionals auditing media assets
* Theme developers understanding media requirements

**Technical Highlights:**

* Efficient batch processing for large libraries
* Memory-conscious scanning algorithms
* Extensible architecture with processor factory pattern
* WordPress coding standards compliance
* Secure file access with capability checks
* Professional error handling and logging

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/media-inventory-forge` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress.
3. Navigate to Tools > Media Inventory Forge to access the scanner.
4. Click "start scan" to begin analyzing your media library.
5. Use the export function to generate CSV reports for further analysis.

== Frequently Asked Questions ==

= Can this plugin handle large media libraries? =

Yes! Media Inventory Forge uses progressive batch processing to handle libraries with thousands of media files without timeout issues.

= Does this plugin modify or delete my media files? =

No. Media Inventory Forge is a read-only analysis tool. It scans and reports on your media but never modifies or deletes files.

= What file types are supported? =

The plugin analyzes all media types including images (JPEG, PNG, GIF, WebP, SVG), videos (MP4, AVI, MOV), audio files (MP3, WAV), documents (PDF, DOC, XLS), fonts (TTF, OTF, WOFF), and more.

= Can I export the scan results? =

Yes. The plugin includes a CSV export function that generates detailed reports with all scanned data for external analysis.

= Is this compatible with multisite installations? =

Yes. Media Inventory Forge works with both single-site and multisite WordPress installations.

= How accurate are the file size calculations? =

The plugin uses direct filesystem access to get precise file sizes, including all WordPress-generated variations and thumbnails.

== Screenshots ==

1. Main scanning interface with progress tracking and controls
2. Detailed results with categorized media breakdown
3. Expandable sections showing file details and metadata
4. CSV export functionality for external analysis
5. Professional admin interface with WordPress integration

== Changelog ==

= 2.1.0 =
* Enhanced scanning performance with improved batch processing
* Added specialized font file analysis with family grouping
* Improved error handling and progress reporting
* Updated admin interface with better responsive design
* Added comprehensive CSV export with detailed file metadata
* Implemented WordPress coding standards compliance
* Enhanced security with proper input validation and sanitization
* Added support for additional media file types
* Improved memory management for large library scanning
* Fixed various minor bugs and performance improvements

= 2.0.0 =
* Complete rewrite with improved architecture
* Added processor factory pattern for extensible file handling
* Implemented professional admin interface
* Added batch processing for large media libraries
* Enhanced error handling and logging
* Improved file categorization and metadata extraction
* Added CSV export functionality
* Network multisite compatibility
* WordPress 6.0+ compatibility

= 1.0.0 =
* Initial release
* Basic media scanning functionality
* Simple file categorization
* WordPress admin integration
== Changelog ==

= 2.1.0 =
* Enhanced scanning performance with improved batch processing
…
* Fixed various minor bugs and performance improvements
“`

**After the entire Changelog section, add**:
“`

== Upgrade Notice ==

= 2.1.1 =
Documentation and code quality improvements. Includes WordPress.org compliance enhancements. Safe to update from any previous version.

= 2.1.0 =
Major update with enhanced performance, improved security, and WordPress.org compliance. Recommended for all users.

= 2.0.0 =
Complete rewrite with professional features. Backup your settings before upgrading.

== Privacy Policy ==

Media Inventory Forge does not collect, store, or transmit any personal data or website information outside of your WordPress installation. All scanning and analysis is performed locally on your server.

== Support ==

For support, bug reports, or feature requests, please visit our GitHub repository or contact us through our website at https://jimrweb.com.