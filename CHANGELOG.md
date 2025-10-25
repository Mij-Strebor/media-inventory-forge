
![Media Inventory Forge Banner](assets/images/changelog-1544x500.png)
# Media Inventory Forge

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
