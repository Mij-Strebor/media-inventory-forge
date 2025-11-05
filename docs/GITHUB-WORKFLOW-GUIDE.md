# Media Inventory Forge - Complete GitHub Workflow Guide

**For:** Jim R. (New to GitHub Release Management)
**Repository:** https://github.com/Mij-Strebor/media-inventory-forge
**Working Folder:** E:\onedrive\projects\plugins\mif

---

## 🎯 The Three-Tier System Explained

Understanding where your code lives and what each location is for:

```
┌─────────────────────────────────────────────────────────┐
│  TIER 1: Your Local Working Files                      │
│  Location: E:\onedrive\projects\plugins\mif            │
│  Purpose: Where YOU edit, test, and develop            │
│  Backed up by: OneDrive (with .git excluded)           │
└─────────────────────────────────────────────────────────┘
                           ↓
                    git push origin
                           ↓
┌─────────────────────────────────────────────────────────┐
│  TIER 2: GitHub Code Repository                        │
│  Location: github.com/Mij-Strebor/media-inventory-forge│
│  Purpose: Version control, collaboration, backup       │
│  Shows: Source code, docs, history                     │
└─────────────────────────────────────────────────────────┘
                           ↓
                   Create GitHub Release
                           ↓
┌─────────────────────────────────────────────────────────┐
│  TIER 3: GitHub Releases                               │
│  Location: github.com/.../releases                     │
│  Purpose: Downloadable versions for end users          │
│  Shows: Version tags, release notes, ZIP downloads     │
└─────────────────────────────────────────────────────────┘
```

### What Each Tier Is For

**TIER 1 - Local Working Files:**
- Where you edit code in your editor
- Where you test in Local WordPress
- Has `.git` folder (Git history)
- Can have messy work-in-progress files
- OneDrive backs up your code (but not .git folder)

**TIER 2 - GitHub Code:**
- Shows the current state of your project
- Visitors see README.md, browse source code
- Has branches (master, development)
- Has commit history
- Other developers can see your work
- **This is NOT what end users download**

**TIER 3 - GitHub Releases:**
- What end users actually download
- Specific versions only (v3.0.0, v3.1.0, etc.)
- Has release notes and changelog
- Auto-generates clean ZIP files
- Shows as "Latest Release" badge
- **This is the official download**

---

## 📁 Current MIF Setup Status

✅ **Working Folder:** E:\onedrive\projects\plugins\mif
✅ **Git Repository:** Initialized and active
✅ **GitHub Remote:** Connected to github.com/Mij-Strebor/media-inventory-forge
✅ **Branches:**
   - `master` - Stable, production-ready code
   - `development` - Active development work
✅ **Both branches pushed to GitHub**
✅ **Symbolic Link:** Connected to Local WordPress for testing

**You're ready to go!**

---

## 🔄 Daily Development Workflow

### Morning: Start Your Day

**1. Pause OneDrive (IMPORTANT!):**
```
Right-click OneDrive icon → Pause syncing → For 2 hours (or "Until tomorrow")
```

**2. Start Local WordPress:**
```
Open Local app → Start "site"
```

**3. Open your working folder:**
```
cd E:\onedrive\projects\plugins\mif
```

**4. Check what branch you're on:**
```bash
git branch
# Should show: * development (or * master)
```

**5. Switch to development branch (if not already):**
```bash
git checkout development
```

**6. Pull latest changes (if working from multiple machines):**
```bash
git pull origin development
```

### During Development: Making Changes

**1. Edit files in your editor:**
```
E:\onedrive\projects\plugins\mif\[any file]
```

**2. Test in Local WordPress:**
```
http://site.local/wp-admin
Tools → Media Inventory
```

**3. Check what you changed:**
```bash
git status
# Shows modified files

git diff
# Shows exact changes
```

**4. When you reach a good stopping point, commit:**
```bash
# Add all changes
git add -A

# Commit with descriptive message
git commit -m "Add feature: [brief description]"

# Example:
git commit -m "Add CSV filtering by file type"
```

**5. Push to GitHub:**
```bash
git push origin development
```

**Result:** Your changes are now on GitHub in the `development` branch.

### End of Day: Wrap Up

**1. Make sure everything is committed:**
```bash
git status
# Should show: "nothing to commit, working tree clean"
```

**2. Push any remaining commits:**
```bash
git push origin development
```

**3. Resume OneDrive:**
```
Right-click OneDrive icon → Resume syncing
```

---

## 🚀 Release Workflow (When Ready to Release)

### Pre-Release Checklist

**BEFORE you start, verify ALL of these:**

```bash
cd E:\onedrive\projects\plugins\mif

# 1. Pause OneDrive
# (Do manually: Right-click OneDrive → Pause syncing → Until tomorrow)

# 2. Switch to master branch
git checkout master

# 3. Merge development into master
git merge development

# 4. Check for version synchronization (all 6 locations)
grep -n "Version: " media-inventory-forge.php
grep -n "MIF_VERSION" media-inventory-forge.php
grep -n "Stable tag:" readme.txt
grep -n "version-" README.md

# All should show the SAME version!
```

**Update version numbers if needed:**
1. `media-inventory-forge.php` (line 7): `Version: 3.0.1`
2. `media-inventory-forge.php` (line 108): `MIF_VERSION = '3.0.1'`
3. `readme.txt`: `Stable tag: 3.0.1`
4. `readme.txt` changelog: Add `= 3.0.1 =` entry
5. `CHANGELOG.md`: Add `## [3.0.1] - 2025-11-05` entry
6. `README.md` (line 9): `version-3.0.1`

**Remove any debug text:**
```bash
grep -rn "TEST\|DEBUG\|CACHE.*TEST" templates/
# Should return nothing!
```

### Release Steps

**1. Commit version changes:**
```bash
git add -A
git commit -m "Release v3.0.1: [Brief description]

[Detailed changelog]

🤖 Generated with Claude Code (https://claude.com/claude-code)

Co-Authored-By: Claude <noreply@anthropic.com>"
```

**2. Push master to GitHub:**
```bash
git push origin master
```

**3. Create and push tag:**
```bash
# Create tag
git tag -a v3.0.1 -m "Media Inventory Forge v3.0.1"

# Push tag
git push origin v3.0.1
```

**4. Merge changes back to development:**
```bash
git checkout development
git merge master
git push origin development
```

**5. Go back to master:**
```bash
git checkout master
```

### Your Tasks on GitHub Website

Now you need to create the **GitHub Release** (this is the important part!):

**Step 1: Navigate to Releases Page**
```
1. Open browser
2. Go to: https://github.com/Mij-Strebor/media-inventory-forge/releases
3. Click "Draft a new release" button (top right)
```

**Step 2: Fill Out Release Form**

**Choose Tag:**
```
Select: v3.0.1 (the tag you just pushed)
```

**Release Title:**
```
Media Inventory Forge v3.0.1 - [Brief Description]

Example:
Media Inventory Forge v3.0.1 - CSV Filtering Enhancement
```

**Description:**
```
Copy from CHANGELOG.md, format like this:

## What's New

- Feature: Added CSV filtering by file type
- Enhancement: Improved scan performance
- Fix: Resolved progress bar accuracy issue

## Installation

Download the Source code (zip) below and install via WordPress admin.

## Requirements

- WordPress 5.0+
- PHP 7.4+

See full changelog: [CHANGELOG.md](CHANGELOG.md)
```

**Pre-release Checkbox:**
```
☐ Set as a pre-release

LEAVE UNCHECKED (unless it's alpha/beta)
For Release Candidates (v3.0.1-rc), also LEAVE UNCHECKED
The tag name indicates RC status.
```

**Step 3: Publish**
```
Click "Publish release" button (green, bottom of form)
```

**Step 4: VERIFY (Very Important!)**

After publishing, check:

```
✅ Release shows as "Latest"
✅ Has 2 auto-generated assets:
   - Source code (zip)
   - Source code (tar.gz)
✅ NO manually uploaded ZIP files
✅ Release date is today
✅ Tag matches (v3.0.1)
```

**If you see manually uploaded ZIPs:**
1. Click "Edit" on the release
2. Find uploaded ZIPs in Assets section
3. Click X next to each to delete
4. Click "Update release"

---

## 🌿 Branch Management

### When to Use Each Branch

**`master` branch:**
- Always stable and deployable
- Matches latest GitHub release
- Only merge into master when ready to release
- Never commit directly to master (except hotfixes)

**`development` branch:**
- Your daily work goes here
- Can have work-in-progress commits
- Test features before merging to master
- This is your default working branch

### Switching Branches

**To work on a feature (development):**
```bash
git checkout development
# Edit files, test, commit
git add -A
git commit -m "Add feature"
git push origin development
```

**To prepare a release (master):**
```bash
git checkout master
git merge development
# Update version numbers
git add -A
git commit -m "Release v3.0.1"
git push origin master
git tag -a v3.0.1 -m "Version 3.0.1"
git push origin v3.0.1
```

**To go back to development:**
```bash
git checkout development
```

### Check Which Branch You're On

```bash
git branch
# * indicates current branch

# Or in your prompt (if configured):
# (development) E:\onedrive\projects\plugins\mif>
```

---

## 🆘 Common Problems and Solutions

### Problem: "Changes not showing on GitHub"

**Solution:**
```bash
# Check if you pushed
git status

# If it says "Your branch is ahead of 'origin/...'", push:
git push origin [branch-name]
```

### Problem: "Modified files showing but I didn't change anything"

**Cause:** OneDrive is syncing while Git is working

**Solution:**
```bash
# Pause OneDrive (right-click icon → Pause syncing)
# Then:
git status
# If files show as modified but aren't:
git reset --hard HEAD
```

### Problem: "Can't switch branches - uncommitted changes"

**Solution Option 1 - Commit your changes:**
```bash
git add -A
git commit -m "Work in progress"
git checkout [other-branch]
```

**Solution Option 2 - Stash your changes:**
```bash
git stash
git checkout [other-branch]
# Later, get changes back:
git stash pop
```

### Problem: "GitHub not showing my README banner"

**Check:**
```bash
# Make sure banner exists:
ls docs/screenshots/banner.png

# Check README.md uses correct path:
grep "banner" README.md
# Should show: ![...](docs/screenshots/banner.png)

# Commit and push if needed:
git add docs/screenshots/banner.png
git commit -m "Add banner"
git push origin master
```

### Problem: "I committed to wrong branch"

**Solution:**
```bash
# If you committed to master but meant development:
git checkout development
git merge master
git checkout master
git reset --hard HEAD~1  # Undo last commit on master
```

### Problem: "I forgot to update version numbers before releasing"

**Solution:**
```bash
# Make the version changes
# Then:
git add -A
git commit --amend -m "Release v3.0.1: [updated message]"
git push --force origin master
git tag -d v3.0.1  # Delete local tag
git push origin :refs/tags/v3.0.1  # Delete remote tag
git tag -a v3.0.1 -m "Version 3.0.1"  # Recreate tag
git push origin v3.0.1
```

---

## 📝 Quick Command Reference

### Daily Work Commands

```bash
# Start work
git checkout development
git pull origin development

# Check status
git status
git diff

# Save work
git add -A
git commit -m "Description"
git push origin development

# Switch branches
git checkout [branch-name]
```

### Release Commands

```bash
# Prepare release
git checkout master
git merge development
# [Update version numbers in files]
git add -A
git commit -m "Release vX.X.X"
git push origin master

# Tag release
git tag -a vX.X.X -m "Version X.X.X"
git push origin vX.X.X

# Return to development
git checkout development
git merge master
git push origin development
```

### Checking Commands

```bash
# What branch am I on?
git branch

# What changed?
git status

# What's the difference?
git diff

# Recent commits
git log --oneline -5

# All branches
git branch -a

# What's on GitHub?
git remote show origin
```

---

## 🎓 Understanding Git Terminology

**Repository (Repo):** Your project folder with `.git` folder
- Local repo: E:\onedrive\projects\plugins\mif
- Remote repo: github.com/Mij-Strebor/media-inventory-forge

**Branch:** A version of your code
- `master` = stable version
- `development` = work-in-progress version

**Commit:** A saved snapshot of your changes
- Like saving a game checkpoint
- Has a message describing what changed

**Push:** Send commits from your computer to GitHub
- `git push origin master` = upload master branch

**Pull:** Get commits from GitHub to your computer
- `git pull origin development` = download development branch

**Merge:** Combine one branch into another
- `git merge development` = bring development changes into current branch

**Tag:** A labeled version for release
- `v3.0.1` = marks the 3.0.1 release
- Used to create GitHub releases

**Remote:** GitHub connection
- `origin` = your GitHub repository URL

---

## 🔒 Safety Rules

### DO:
✅ Always pause OneDrive before Git operations
✅ Commit often with clear messages
✅ Push to GitHub daily
✅ Work on development branch for features
✅ Test in Local WordPress before pushing
✅ Update all 6 version numbers before release
✅ Remove debug text before release

### DON'T:
❌ Don't work with OneDrive syncing during Git operations
❌ Don't commit without a message
❌ Don't push to master without testing
❌ Don't create release without updating versions
❌ Don't manually upload ZIP files to GitHub releases
❌ Don't commit .backup, .bak, or .tmp files
❌ Don't commit personal test logs

---

## 📞 When You Need Help

### Check These First:

1. **Is OneDrive paused?** (During Git work)
2. **What branch am I on?** (`git branch`)
3. **Is everything committed?** (`git status`)
4. **Did I push to GitHub?** (`git log` vs GitHub website)

### If Stuck:

**Save your current state:**
```bash
# Take a snapshot of what you have
git add -A
git commit -m "WIP - before getting help"
```

Then describe:
1. What you were trying to do
2. What command you ran
3. What error message appeared
4. What `git status` shows

---

## 📚 Additional Resources

**Your Documentation:**
- E:\onedrive\projects\plugins\mif\WORDPRESS-BEST-PRACTICES.md
- E:\onedrive\projects\plugins\mif\docs\TEST-PLAN.md
- E:\onedrive\projects\plugins\mif\docs\LOCAL-TESTING-GUIDE.md
- E:\onedrive\projects\GITHUB-WORDPRESS-RELEASE-GUIDE.md

**GitHub URLs:**
- Code: https://github.com/Mij-Strebor/media-inventory-forge
- Releases: https://github.com/Mij-Strebor/media-inventory-forge/releases
- Issues: https://github.com/Mij-Strebor/media-inventory-forge/issues

---

## ✅ Final Checklist for First Release

Before your first release using this new workflow:

- [ ] OneDrive paused
- [ ] All version numbers updated (6 locations)
- [ ] No debug text in templates
- [ ] CHANGELOG.md updated with release notes
- [ ] readme.txt updated with changelog entry
- [ ] Working tree clean (`git status`)
- [ ] Committed to master
- [ ] Pushed to GitHub (`git push origin master`)
- [ ] Tagged (`git tag -a vX.X.X -m "..."`)
- [ ] Tag pushed (`git push origin vX.X.X`)
- [ ] Development branch merged with master
- [ ] Created GitHub Release on website
- [ ] Verified 2 auto-generated assets only
- [ ] Release shows as "Latest"
- [ ] Tested download works
- [ ] OneDrive resumed

---

**Created:** 2025-11-04
**For:** MIF v3.0.0+
**Workflow:** Direct Git integration (no separate release folder)
