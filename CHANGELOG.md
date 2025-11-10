
![Media Inventory Forge Banner](assets/images/changelog-1544x500.png)
# Media Inventory Forge

## [4.0.0] - 2025-11-05

### 🐛 Bug Fixes (2025-11-10)

**Table View Session Behavior**
- ✅ Fixed Table View requiring explicit scan in current session (no auto-loading from cache)
- ✅ Synchronized window.inventoryData across JavaScript files for proper session tracking
- ✅ Consistent "start scan" messages between Card and Table views
- ✅ Removed debug console.log/warn/error statements from production code

**Table View Sorting**
- ✅ Fixed browser lockup when clicking sortable column headers
- ✅ Rewrote sorting algorithm using HTML string approach instead of DOM manipulation
- ✅ All three columns (Title, Files, Total Size) now sort correctly ascending/descending
- ✅ Eliminated jQuery iteration conflicts during DOM updates

### 🚀 Major New Features

**Unused Media Detection & Usage Tracking**
- 🔍 Identify media items not used anywhere on your website
- 📍 See exact locations where each media item is used (posts, pages, widgets, etc.)
- 🔗 Direct links to edit screens for content containing media
- ⚠️ Filter to show only unused media for cleanup planning
- 📊 Usage count badges for quick identification
- 🎯 ~95% accuracy with comprehensive content scanning

**Table View Mode**
- 📊 Professional tabular display alternative to card view
- 🔄 Sortable columns (name, size, type, date, usage)
- 👆 Click column headers to toggle ascending/descending sort
- 📄 Pagination support (50/100/500 items per page)
- 💾 Remembers user's preferred view mode and sort settings
- 🎨 Clean, WordPress-native UI design

**Advanced Filtering System**
- 🎛️ Filter by file type (Images, PDFs, Videos, Audio, Documents, SVG)
- 📏 Filter by file size (Small, Medium, Large, Very Large)
- ✓ Filter by usage status (Used, Unused, Used 2+ times, Used 5+ times)
- 📅 Filter by upload date (Last 30/90 days, Last year, Older than 1 year, Custom range)
- 🔀 Combine multiple filters for precise searches
- ⚡ AJAX-powered filter updates for smooth performance

### 🎨 UI/UX Improvements
- Completely redesigned admin interface with modern workflow
- Better responsive design for mobile and tablet devices
- Enhanced accessibility with ARIA labels and keyboard navigation
- Improved visual feedback for user actions
- Streamlined information architecture

### ⚡ Performance Enhancements
- Optimized database queries for usage tracking
- Improved memory management for large libraries
- Faster initial page load with lazy-loaded components
- Enhanced caching strategies

### 🔧 Technical Improvements
- New database schema for tracking media usage
- Extensible architecture for future feature additions
- Improved error handling and logging
- Enhanced security with additional sanitization

### 📚 Documentation
- Comprehensive feature planning document (FEATURE-PLANNING-V4.md)
- Complete GitHub workflow guide for direct repository development
- Updated testing guide with new features
- Detailed local WordPress setup documentation

### 🛠️ Development Infrastructure
- Simplified GitHub workflow (no separate release folder)
- Direct git repository integration
- Enhanced .gitignore for cleaner repository
- Development and master branch workflow established

### Breaking Changes
None - all changes are backward compatible with v3.x

### Upgrade Notes
Safe to upgrade from any v3.x version. Usage tracking data will be built on first scan after upgrade.

---

## [3.0.0] - 2025-10-23
 ⚠️ IMPORTANT: Upgrade Instructions for v2.x Users

**If upgrading from v2.1.0 or earlier:**
1. Deactivate the old Media Inventory plugin (v2.x)
2. Delete the old plugin completely
3. Install Media Inventory Forge v3.0.0
4. Activate the new version

**Why this is necessary:** Due to plugin slug changes between v2.x and v3.0+, WordPress treats them as separate plugins. Installing v3.0 without removing v2.x will create duplicate menu entries and confusion. This is a one-time migration step.


### Major Visual Enhancement - Integrated Forge Header System
- ✨ Custom Photoshop composite forge banner (1920x600px) with seamless multi-layer fade system
- 🔥 Dramatic multi-directional gradient title (bright yellow → deep orange) mimicking forge flame heat distribution
- 📐 Perfect alignment system: header (1280px) matches body panels for professional consistency
- 🎨 Enhanced glow effects using filter: drop-shadow() for gradient text compatibility
- 🎭 SVG noise texture overlay (3%) for visual interest in solid background areas
- 📏 Responsive design: 50vh header height with vh-based positioning scales beautifully across devices

### Design System Standardization (JimRWeb Brand)
- 🎨 Inter font family (locally loaded WOFF2) - 16px base size (upgraded from 14px)
- 🌈 Enhanced color palette: deeper browns (#3D2F1F, #6D4C2F), brighter gold (#F4C542), burnt orange links (#C97B3C)
- 📦 1280px max-width containers throughout for consistent layout
- 🔘 Enhanced button styles: lowercase text, translate(-2px, -2px) hover, 15-20% darker hover state
- 📊 36px panel padding with double margins (72px) for notices
- ⚡ All design standards now documented in resources/15-design-system/

### New Features
- 📊 **File Distribution Pie Chart**: Visual breakdown of storage by file type with color-coded legend
- 🎨 Side-by-side panel layout: Scan Controls + File Distribution in responsive grid
- 📈 Interactive canvas-based pie chart updates automatically after scan completion
- 🎨 JimRWeb color palette integration across all visualizations

### UI/UX Improvements
- 🎯 Subtle version display (8px italic, 60% opacity) below main title - non-distracting
- 🔲 Fixed border-radius "ears" on collapsible headers (3px button radius fits 5px panel radius)
- 📏 Optimized spacing: reduced category panel gaps from 24px to 16px for tighter layout
- 🎭 About panel positioning perfected with negative margin pull into forge fade area
- 🎨 Capitalized category headers (Images, PDFs, Documents) override lowercase button default

### Performance & Architecture
- 🚀 Scoped all global CSS to `.toplevel_page_media-inventory` - no longer affects WP admin menu/dashboard
- 🎨 Modular CSS organization: admin.css + forge-header.css for maintainability
- 📦 Clean asset structure: fonts/ and images/ directories properly organized
- 🔧 Enhanced JavaScript with updatePieChart() function using HTML5 Canvas API

### Documentation
- 📚 Complete forge header implementation guide (Photoshop workflow, CSS breakdown, troubleshooting)
- 📝 Partnership guidelines documented in .claude/claude.md for future sessions
- 🗂️ Master design system repository in resources/ with typography, colors, components
- 🎯 Quick start guides for 5-minute forge header implementation

### Technical Highlights
- ⚙️ Professional commit workflow established with detailed messages
- 🎨 CSS custom properties (design tokens) for theme consistency
- 🔥 Multi-layer background system: noise texture + top fade + bottom fade + forge banner
- 📐 vh-based responsive units with px fallbacks for cross-device compatibility
- 🎭 background-clip: text for gradient effects with proper fallbacks

### Files Added
- assets/css/forge-header.css - Complete forge header styling system
- assets/fonts/ - Inter font family (4 weights, WOFF2 format)
- assets/images/forge-banner.png - Custom Photoshop composite banner
- assets/images/forge-background.png - Original forge photograph
- templates/admin/partials/file-distribution.php - Pie chart panel template

### Breaking Changes
None - all changes are additive and backward compatible

---

## [2.1.1] - 2025-10-15

### WordPress.org Compliance
- Added Update URI to plugin header for GitHub update support
- Enhanced CSV export security comment with detailed justification
- Created uninstall.php handler for WordPress.org requirements
- Added GPL-2.0 LICENSE file to repository root
- Fixed version consistency in readme.txt (stable tag 2.1.1)
- Updated "Tested up to" version to WordPress 6.7
- Added upgrade notice section to readme.txt
- Deferred full internationalization (i18n) to v2.2.0

### Documentation
- Enhanced inline code comments for WordPress.org review
- Updated compliance roadmap with Phase 1 completion

## [2.1.0] - 2025-10-4

### Added
- Comprehensive JSDoc documentation throughout admin.js with @param, @returns, @note annotations
- Clear structural organization in admin.js with 10 numbered sections matching CSS file style
- Section headers with visual separators for improved code navigation
- Toggle functionality for all category sections (previously only Images, PDFs, Documents)

### Enhanced
- About section now clearly communicates MIF is a read-only analysis tool
- Added explicit warnings that MIF does not modify, optimize, or delete files
- Fourth panel in About section renamed to "What MIF Does NOT Do" with backup warnings
- Removed misleading language about "cleanup" and "optimization" from About section
- Improved code organization: related functions grouped, logical flow from initialization to utilities

### Fixed
- Missing toggle buttons on "Other Documents" and other non-primary categories
- Inconsistent UI behavior across category sections

### Removed
- `collapsibleCategories` Set and associated conditional logic (all categories now use consistent rendering)
- `createStandardCategorySection()` function (dead code - never executed after making all categories collapsible)
- Approximately 30 lines of redundant code
