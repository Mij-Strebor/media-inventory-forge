=== Media Inventory Forge ===
Contributors: MijStrebor
Donate link: https://buymeacoffee.com/jimrweb
Tags: media, inventory, scanner, analysis, optimization
Requires at least: 5.0
Tested up to: 6.8
Requires PHP: 7.4
Stable tag: 5.0.1
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Professional media library scanner and analyzer for WordPress developers and administrators.

== Description ==

Media Inventory Forge is a comprehensive media library scanning and analysis tool designed for WordPress developers and administrators. The plugin provides detailed insights into media assets, file organization, usage tracking, and optimization opportunities through an intuitive dual-view interface.

**Key Features:**

* **Dual View Modes** – Switch between Card View for detailed browsing or Table View for sortable data analysis
* **Visual Distribution Chart** – Interactive graphic showing media breakdown by file type at a glance
* **Unused Media Detection** – Identify media files not used anywhere on your site for safe cleanup
* **Usage Location Tracking** – See exactly where each media item is used (posts, pages, widgets, theme files)
* **Advanced Filtering** – Filter results by file type, size, usage status, and upload date
* **Sortable Table Columns** – Click column headers to sort by name, size, type, or upload date in Table View
* **Comprehensive Media Scanning** – Analyzes all media types including images, videos, audio, fonts, documents, and SVGs
* **Detailed File Information** – Extracts metadata, dimensions, file sizes, and WordPress-generated variations
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
4. Click "Start Scan" to begin analyzing your media library.
5. View results in Card View or Table View using the toggle buttons.
6. Review the visual distribution chart to see media breakdown by type.
7. Use filters to narrow results by type, size, usage, or date.
8. Sort table columns by clicking headers in Table View.
9. Export to CSV for detailed external analysis.

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

= What's the difference between Card View and Table View? =

Card View displays media in an expandable card format, perfect for browsing individual files with detailed metadata. Table View shows all media in a sortable table with columns for quick comparison and analysis. Switch between views anytime using the toggle buttons.

= How does unused media detection work? =

The plugin scans your entire WordPress installation including posts, pages, widgets, theme files, and page builders to identify where each media file is used. Files with no detected usage are flagged as "unused" - though you should always verify before deleting as some uses (like hardcoded URLs in custom code) may not be detectable.

= Can I sort the results in Table View? =

Yes! Click any column header in Table View to sort by that column. Click again to reverse the sort order. You can sort by filename, file size, file type, upload date, and usage status.

== Screenshots ==
1. Full application view
2. Main scanning interface with progress tracking and visual media distribution chart
3. Card View mode showing expandable media cards with detailed metadata
4. Table View mode with sortable columns and advanced filtering options

== Changelog ==

= 4.0.1 =
* Fix: Resolved race condition causing "no media available" message on first scan in Table View
* Fix: Table View now properly requires explicit scan in current session (no auto-loading from cache)
* Feature: Added Community & Tools panel showcasing Jim R Forge ecosystem plugins
* Enhancement: Synchronized window.inventoryData across JavaScript files for better session tracking
* Enhancement: Community panel with links to related plugins and support options

= 4.0.0 =
* Major Feature: Unused media detection - identify media not used anywhere on your site
* Major Feature: Usage location tracking - see exactly where each media item is used
* Major Feature: Table view mode - alternative to card view with sortable columns
* Major Feature: Advanced filtering - filter by type, size, usage status, and upload date
* Enhancement: Completely redesigned admin interface for better workflow
* Enhancement: Improved performance for large media libraries
* Enhancement: Better responsive design for mobile devices
* Update: Comprehensive testing and bug fixes

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

= 4.0.1 =
Bug fix release improving Table View reliability on fresh installs. Adds Community & Tools panel. Safe update from 4.0.0.

= 4.0.0 =
Major feature release with unused media detection, usage tracking, dual view modes (Card/Table), visual distribution charts, advanced filtering, and sortable columns. Recommended upgrade for all users.

== Privacy Policy ==

Media Inventory Forge does not collect, store, or transmit any personal data or website information outside of your WordPress installation. All scanning and analysis is performed locally on your server.

== Support ==

For support, bug reports, or feature requests, please visit our GitHub repository or contact us through our website at https://jimrweb.com.