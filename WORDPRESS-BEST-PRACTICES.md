# WordPress Plugin Best Practices - Quick Reference

## 📋 Pre-Release Checklist

### Version Numbers (CRITICAL)
- [ ] `media-inventory-forge.php` header: `Version: X.X.X` (line 7)
- [ ] `media-inventory-forge.php` constant: `MIF_VERSION = 'X.X.X'` (line 108)
- [ ] `readme.txt` Stable tag: `Stable tag: X.X.X`
- [ ] `readme.txt` changelog: New `= X.X.X =` entry
- [ ] `CHANGELOG.md`: New `## [X.X.X]` entry
- [ ] `README.md` badge: `version-X.X.X`

### File Cleanup
- [ ] No debug text in templates (search for "TEST", "DEBUG", "CACHE")
- [ ] No `.backup`, `.bak`, `.tmp` files
- [ ] No personal notes or test logs
- [ ] No `node_modules/` if accidentally added

### Testing
- [ ] Plugin loads without PHP errors
- [ ] No JavaScript console errors
- [ ] All features work as expected
- [ ] Screenshots are current (if UI changed)
- [ ] Links in readme.txt work

## 📸 Screenshot Best Practices

### Naming Convention
WordPress.org REQUIRES this exact naming:
```
screenshot-1.png
screenshot-2.png
screenshot-3.png
```

NOT: `screenshot_1.png` or `1-screenshot.png` or `screenshot1.png`

### Recommended Sizes
- **1280x720px** (16:9 ratio) - Most common
- **1920x1080px** (if you need more detail)
- **Min:** 772x250px
- **Max:** 3840x2160px

### File Format
- **PNG** for UI screenshots (better text clarity)
- **JPG** for photos/illustrations (smaller file size)
- Keep under 1MB each

## 🎨 Icon & Banner Requirements

### Banner Dimensions (EXACT sizes required)
```
banner-772x250.png    (1x - standard displays)
banner-1544x500.png   (2x - retina displays)
```

### Icon Dimensions (EXACT sizes required)
```
icon-128x128.png      (1x - standard displays)
icon-256x256.png      (2x - retina displays)
icon.svg             (vector - best quality, optional)
```

## 📝 readme.txt Requirements

### Header Format
```
=== Plugin Name ===
Contributors: wordpressusername
Tags: tag1, tag2, tag3
Requires at least: 5.0
Tested up to: 6.7
Requires PHP: 7.4
Stable tag: 3.0.0
License: GPLv2 or later
```

### Screenshot Captions
```
== Screenshots ==

1. Main media inventory dashboard
2. Scan results and file management
3. Bulk operations interface
4. Media library statistics
```

Numbers must match filenames (screenshot-1.png = caption #1)

## 🚀 WordPress.org SVN Structure

```
media-inventory-forge-svn/
├── trunk/              # Development version
├── tags/
│   └── 3.0.0/         # Released versions
└── assets/            # Banners, icons, screenshots
```

Assets folder is SEPARATE from code - only for WordPress.org display.

## 🚨 Common Mistakes to Avoid

1. **Version Mismatch** - Different versions in different files
2. **Wrong Screenshot Names** - Using underscores instead of hyphens
3. **Missing Stable Tag** - readme.txt without Stable tag
4. **Including Development Files** - .git, docs in release ZIP
5. **Wrong Banner Sizes** - WordPress.org rejects incorrect dimensions
6. **Forgetting Assets Folder** - Icons/banners in trunk instead of assets/

## 🔗 Useful Resources

- **Readme Validator:** https://wordpress.org/plugins/developers/readme-validator/
- **Plugin Handbook:** https://developer.wordpress.org/plugins/
- **Asset Guidelines:** https://developer.wordpress.org/plugins/wordpress-org/plugin-assets/
