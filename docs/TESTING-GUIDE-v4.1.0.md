# Media Inventory Forge v4.1.0 - Testing Guide

**Version:** 4.1.0
**Test Date:** 2025-01-26
**Tester:** Jim (Developer)

---

## Pre-Test Setup

### 1. Clear Browser Cache
**CRITICAL:** Hard refresh to load new CSS
- **Windows:** Ctrl + Shift + R
- **Mac:** Cmd + Shift + R

### 2. Check WordPress Admin
- Navigate to: **Tools → Media Inventory Forge**
- Plugin should load without errors

---

## Visual Tests (5 minutes)

### Test 1: Button Styling ✅
**What to check:**
- [ ] "start scan" button = GOLD background with BROWN text
- [ ] "stop scan" button = RED background with WHITE text
- [ ] "export csv" button = GOLD background with BROWN text
- [ ] All button text is lowercase
- [ ] NO emoji icons (🔍, ⏹️, 📊)

**Expected:**
![Gold button with brown text, lowercase](expected-button-look.jpg)

**Hover Test:**
- [ ] Hover over "start scan" → button moves UP and LEFT
- [ ] Background changes to darker gold (#dda824)
- [ ] Shadow increases (dramatic effect)

### Test 2: Layout & Spacing ✅
**What to check:**
- [ ] Page centered on screen
- [ ] Maximum width = 1280px
- [ ] About section displays correctly
- [ ] Two-column layout for Scan Controls + File Distribution
- [ ] No visual regressions (looks same as v4.0.2)

### Test 3: Branding ✅
**What to check:**
- [ ] Plugin header shows "Media Inventory Forge"
- [ ] Version shows "4.1.0"
- [ ] NO references to "JimRWeb" visible
- [ ] About section toggle text is lowercase: "about media inventory forge"

---

## Functional Tests (10 minutes)

### Test 4: Scan Functionality ✅
**Steps:**
1. Check "Media Library" source filter
2. Click "start scan" button
3. Watch progress bar
4. Wait for scan to complete

**Expected:**
- [ ] Progress bar appears and updates
- [ ] "stop scan" button becomes visible (red)
- [ ] Scan completes successfully
- [ ] Results display in card view
- [ ] Summary stats show file counts

### Test 5: Display Mode Toggle ✅
**Steps:**
1. Complete a scan (Test 4)
2. Click "Table View" radio button
3. Click "Card View" radio button

**Expected:**
- [ ] Table view displays expandable category rows
- [ ] Card view displays file cards with thumbnails
- [ ] Switching between views works smoothly
- [ ] Data persists when switching

### Test 6: Export CSV ✅
**Steps:**
1. Complete a scan (Test 4)
2. Click "export csv" button

**Expected:**
- [ ] CSV file downloads
- [ ] Filename: `media-inventory-YYYY-MM-DD-HH-MM-SS.csv`
- [ ] CSV contains all scanned data
- [ ] Opens correctly in Excel/Google Sheets

### Test 7: About Section Toggle ✅
**Steps:**
1. Click "about media inventory forge" header (brown bar)
2. Click it again to collapse

**Expected:**
- [ ] Section expands smoothly (shows content)
- [ ] Chevron icon rotates (▼ → ▲)
- [ ] Section collapses smoothly (hides content)
- [ ] Chevron rotates back (▲ → ▼)

---

## Browser Compatibility Tests (15 minutes)

### Test 8: Chrome ✅
- [ ] All visual tests pass
- [ ] All functional tests pass
- [ ] No console errors (F12 → Console tab)

### Test 9: Firefox ✅
- [ ] All visual tests pass
- [ ] All functional tests pass
- [ ] No console errors

### Test 10: Edge ✅
- [ ] All visual tests pass
- [ ] All functional tests pass
- [ ] No console errors

### Test 11: Safari (Mac Only) ⚠️
- [ ] All visual tests pass
- [ ] All functional tests pass
- [ ] No console errors

---

## Error Checks (5 minutes)

### Test 12: PHP Errors ✅
**Steps:**
1. Enable WordPress debug mode:
   - Edit `wp-config.php`
   - Set `define('WP_DEBUG', true);`
   - Set `define('WP_DEBUG_LOG', true);`
2. Use plugin (run a scan)
3. Check `wp-content/debug.log`

**Expected:**
- [ ] NO PHP errors logged
- [ ] NO warnings logged
- [ ] File is empty or has unrelated entries

### Test 13: JavaScript Errors ✅
**Steps:**
1. Open DevTools (F12)
2. Go to Console tab
3. Clear console
4. Run a complete scan

**Expected:**
- [ ] NO red errors in console
- [ ] NO yellow warnings (or only WordPress core warnings)
- [ ] AJAX requests succeed (check Network tab)

---

## Regression Tests (5 minutes)

### Test 14: Source Filters ✅
**Steps:**
1. Check "Theme" filter
2. Check "Plugins" filter
3. Uncheck "Media Library"
4. Click "start scan"

**Expected:**
- [ ] Scan runs with selected sources only
- [ ] Results show files from selected sources
- [ ] Filter state saves after scan

### Test 15: "Toggle All Sources" ✅
**Steps:**
1. Click "Scan Sources:" checkbox (next to label)
2. Click it again

**Expected:**
- [ ] First click: ALL sources checked
- [ ] Second click: ALL sources unchecked
- [ ] Checkbox state updates correctly

---

## Accessibility Quick Check (Optional)

### Test 16: Keyboard Navigation ⚠️
**Steps:**
1. Click in browser address bar
2. Press Tab repeatedly
3. Try to navigate entire interface

**Expected:**
- [ ] Tab moves through all interactive elements
- [ ] Focus states visible (outline around focused element)
- [ ] Enter activates buttons
- [ ] Space activates checkboxes/radios

### Test 17: Color Contrast ⚠️
**Use Chrome DevTools:**
1. Inspect "start scan" button
2. Check Computed styles
3. Verify contrast ratio

**Expected:**
- [ ] Button text contrast > 4.5:1 (WCAG AA)
- [ ] Gold (#f4c542) on Brown (#3d2f1f) passes

---

## Issue Reporting Template

If you find any issues, document them like this:

```
## Issue: [Short Description]

**Severity:** Critical / High / Medium / Low
**Browser:** Chrome 120 / Firefox 121 / etc.
**WordPress:** 6.8
**PHP:** 7.4

**Steps to Reproduce:**
1. Step one
2. Step two
3. Step three

**Expected Behavior:**
What should happen

**Actual Behavior:**
What actually happened

**Screenshot:**
[Attach screenshot if visual issue]

**Console Errors:**
[Paste any JavaScript errors]

**PHP Errors:**
[Paste any debug.log errors]
```

---

## Test Results Summary

### Overall Result: ✅ PASS / ⚠️ PARTIAL / ❌ FAIL

**Tests Passed:** __ / 17
**Critical Issues:** __
**Medium Issues:** __
**Low Issues:** __

**Ready for Production:** YES / NO

---

## Sign-Off

**Tester:** Jim
**Date:** __________
**Signature:** __________

**Notes:**
[Any additional observations or comments]

---

## Next Steps

### If All Tests Pass ✅
1. Create git commit:
   ```bash
   cd e:\onedrive\projects\plugins\mif
   git add .
   git commit -m "Release: v4.1.0 JIMRFORGE standards compliance Phase 1"
   git tag -a v4.1.0 -m "Phase 1 JIMRFORGE UI standards implementation"
   ```

2. Create backup:
   - File: `mif-v4.1.0-jimrforge-phase1 250126 HHMM.zip`
   - Location: `E:\onedrive\WordPress Project Data\Backups\`

3. Deploy to production (when ready)

### If Issues Found ❌
1. Document all issues (use template above)
2. Report to Claude Code for fixes
3. Re-test after fixes applied
4. Repeat until all tests pass

---

*Testing Guide for MIF v4.1.0 JIMRFORGE Standards Phase 1*
*Generated: 2025-01-26*
