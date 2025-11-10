# Media Inventory Forge - Project Setup & Migration

**Date:** 2025-11-04
**Status:** Migrating best practices from FFF v5.1.1
**Purpose:** Set up MIF with all lessons learned from FFF release process

---

## Current State Assessment

**Version:** 4.0.0
**Branch:** master (clean)
**Documentation:** Comprehensive (CHANGELOG.md, README.md, ROADMAP.md, TEST-PLAN.md)

**Existing Assets:**
- docs/screenshots/banner-1544x500.png ✓
- docs/screenshots/banner-772x250.png ✓
- Basic .gitignore (needs expansion)

**Missing Critical Infrastructure:**
- [ ] .wordpress-org/ folder structure
- [ ] WordPress.org assets properly organized
- [ ] Comprehensive .gitignore
- [ ] WORDPRESS-BEST-PRACTICES.md
- [ ] TEST-PLAN.md
- [ ] Development branch
- [ ] Local WordPress testing documentation

---

## Migration Tasks from FFF

### 1. WordPress.org Asset Structure
**Create:** `.wordpress-org/` folder
**Purpose:** Centralize all WordPress.org-specific assets
**Contents:**
- README.md (asset requirements guide)
- IMAGE-CHECKLIST.txt (upload checklist)
- banner-1544x500.png (move from docs/screenshots/)
- banner-772x250.png (move from docs/screenshots/)
- icon-256x256.png (to be created)
- icon-128x128.png (to be created)
- screenshot-*.png files (to be created)

### 2. Comprehensive .gitignore
**Update existing** `.gitignore` with:
- Backup files (*.backup, *.bak*, *.tmp)
- Editor files (.vscode/, .idea/, etc.)
- OS files (.DS_Store, Thumbs.db)
- Node modules
- WordPress testing environment
- Personal notes/test logs

### 3. WordPress Best Practices Documentation
**Create:** `WORDPRESS-BEST-PRACTICES.md`
**Adapted from:** FFF version
**Customizations for MIF:**
- MIF-specific version number locations
- MIF screenshot descriptions
- MIF plugin header format

### 4. Test Plan
**Create:** `docs/TEST-PLAN.md`
**Adapted from:** FFF comprehensive test plan
**Customizations for MIF:**
- Media scanning functionality tests
- File management tests
- Bulk operations tests
- Database query performance tests
- Large media library tests

### 5. Git Branch Structure
**Create:** `development` branch
**Purpose:** Follow proper git workflow
**Structure:**
- master: Stable, production-ready code
- development: Active development work
- feature/* branches as needed

### 6. Local WordPress Testing
**Document:** Local by Flywheel connection
**Create:** `docs/LOCAL-TESTING-GUIDE.md`
**Contents:**
- Local site setup
- Plugin installation for testing
- Database reset procedures
- Testing with large media libraries

---

## Files to Create/Update

### New Files:
1. `.wordpress-org/README.md`
2. `.wordpress-org/IMAGE-CHECKLIST.txt`
3. `WORDPRESS-BEST-PRACTICES.md`
4. `docs/TEST-PLAN.md`
5. `docs/LOCAL-TESTING-GUIDE.md`
6. `docs/PROJECT-STATUS.md`

### Files to Update:
1. `.gitignore` (expand significantly)
2. `docs/screenshots/` → `.wordpress-org/` (reorganize)

### Files to Copy from FFF (with adaptations):
- .gitignore structure
- WORDPRESS-BEST-PRACTICES.md template
- TEST-PLAN.md template

---

## Version Synchronization Points (MIF)

**When releasing, verify version matches in:**
1. media-inventory-forge.php (line ~7): `* Version: X.X.X`
2. media-inventory-forge.php (line ~97): `define('MEDIA_INVENTORY_FORGE_VERSION', 'X.X.X');`
3. readme.txt (line ~8): `Stable tag: X.X.X`
4. readme.txt (changelog): `= X.X.X =`
5. CHANGELOG.md: `## [X.X.X] - YYYY-MM-DD`
6. README.md (badge): `version-X.X.X`

---

## Local WordPress Testing

**Site Details:**
- Local site name: site
- URL: http://site.local
- Admin path: http://site.local/wp-admin
- Plugin installation: Symbolic link to E:\onedrive\projects\plugins\mif
- WordPress plugins path: C:\Users\Owner\Local Sites\site\app\public\wp-content\plugins\media-inventory-forge

**Testing Requirements:**
- Test with small media library (< 100 files)
- Test with medium library (100-1000 files)
- Test with large library (1000+ files)
- Test scanning performance
- Test bulk operations
- Test database queries

---

## Next Steps (In Order)

1. ✅ Create this documentation
2. ✅ Create .wordpress-org/ folder structure
3. ✅ Move assets from docs/screenshots/ to .wordpress-org/
4. ✅ Update .gitignore
5. ✅ Create WORDPRESS-BEST-PRACTICES.md
6. ✅ Create TEST-PLAN.md template
7. ✅ Create LOCAL-TESTING-GUIDE.md
8. ✅ Create development branch
9. ✅ Document Local WordPress connection and symlink setup
10. ✅ Commit all infrastructure changes

---

## References

- **Release Guide:** E:/onedrive/projects/GITHUB-WORDPRESS-RELEASE-GUIDE.md
- **UI Standards:** E:/onedrive/projects/JIMRFORGE-UI-STANDARDS.md
- **General Standards:** E:/onedrive/projects/CLAUDE.md
- **FFF Example:** E:/onedrive/projects/plugins/fff/ (reference implementation)

---

**Status:** Setup in progress
**Last Updated:** 2025-11-04
