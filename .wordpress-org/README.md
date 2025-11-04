# WordPress.org Plugin Assets

This folder contains assets specifically for WordPress.org Plugin Directory display.

## Required Files

### Banners (Plugin Directory Header)
- **banner-772x250.png** - Low-res banner (for retina displays x2 = 1544x500)
- **banner-1544x500.png** - High-res banner (2x retina version)

### Icons (Plugin Directory Listing)
- **icon-128x128.png** - Small icon (for retina displays x2 = 256x256)
- **icon-256x256.png** - Large icon (2x retina version)
- **icon.svg** - Vector icon (optional, preferred if available)

### Screenshots (Plugin Directory Screenshots Tab)
- **screenshot-1.png** - First screenshot (1280x720 or similar)
- **screenshot-2.png** - Second screenshot
- **screenshot-3.png** - Third screenshot
- etc.

## File Requirements

**Banners:**
- Format: PNG or JPG
- Size: Exactly 1544x500px (high-res), 772x250px (low-res)
- Max file size: 1MB recommended

**Icons:**
- Format: PNG or SVG
- Size: Exactly 256x256px (high-res), 128x128px (low-res)
- Max file size: 500KB recommended
- Transparent background recommended

**Screenshots:**
- Format: PNG or JPG
- Size: 1280x720px recommended (or similar 16:9 ratio)
- Max file size: 1MB per screenshot
- These correspond to descriptions in readme.txt

## Usage

These files are deployed to WordPress.org SVN:
```
plugins/media-inventory-forge-svn/assets/
```

They are NOT included in:
- GitHub releases
- WordPress.org plugin ZIP downloads
- Plugin runtime

They are ONLY for WordPress.org visual display.

## GitHub vs WordPress.org

For GitHub README.md banner:
- Copy `banner-1544x500.png` → `../docs/screenshots/banner.png`
- This keeps GitHub and WordPress.org in sync visually

## Screenshot Captions

In readme.txt, add captions:
```
== Screenshots ==

1. Main media inventory dashboard
2. Scan results and file management
3. Bulk operations interface
4. Media library statistics
```

The numbers match the screenshot filenames (screenshot-1.png, etc.)
