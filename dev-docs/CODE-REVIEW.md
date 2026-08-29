# MIF Code Review
**Date:** 2026-04-29 | **Reviewer:** Claude (automated) | **Version:** 5.0.2

## Summary

Architecturally sound: processor pattern, interface contract, scanner/controller separation, and transient-based data flow all show deliberate design. Security fundamentals (nonce checks, capability gates, output escaping in PHP) are generally solid.

Three main problem clusters: (1) The factory pattern was built but never wired in — the scanner still creates `MINVF_File_Processor` directly, making `MINVF_Image_Processor` and `MINVF_Font_Processor` dead code with hundreds of lines of duplicated logic. (2) Four source-filter UI options (`parent-theme`, `plugins`, `wordpress-core`, `uploads`) have no backend handler — the UI promises functionality that silently does nothing. (3) Comment volume in the bootstrap file and `admin.js` is extreme — PHPDoc blocks for `if (!defined('ABSPATH')) { exit; }` are longer than the code itself.

---

## Issues by Severity

### High — Fix before WordPress.org submission

**H1 — Unescaped `data-sort-value` attributes (XSS vector)**
`class-table-builder.php` lines 232–233, 312–313, 382–383. Integer values from `$total_files`, `$total_size`, `$item['file_count']`, `$item['total_size']` are interpolated directly into HTML attributes without `esc_attr()`. Wrap each with `intval()` or `esc_attr(intval(...))`.

**H2 — `sanitize_key()` on JSON array keys corrupts URL and path data**
`class-admin-controller.php` line 434. `sanitize_key()` strips everything except `[a-z0-9_-]`. Applied recursively to scan data, it corrupts any key with uppercase characters. Worse, `sanitize_text_field()` on all string values strips URL-encoded characters and slashes from `thumbnail_url` values and file paths. Use `esc_url_raw()` for URL-valued keys.

**H3 — Unescaped checkbox value in `column_cb()`**
`class-media-list-table.php` lines 198–205. `sprintf(..., $item['id'])` outputs the ID without escaping — should be `esc_attr(intval($item['id']))`.

**H4 — Four source filter UI options have no backend implementation**
`scan-controls.php` lines 53–71. `parent-theme`, `plugins`, `wordpress-core`, `uploads` are offered in the UI but the scanner implements only `media-library` and `theme`. When selected, they silently produce no results with no error or notice to the user.

**H5 — `ajax_export()` uses `wp_die()` instead of `wp_send_json_error()` for permission denial**
`class-admin-controller.php` lines 136–138. All other handlers return JSON via `wp_send_json_error()`; this one returns an HTML error page to the AJAX caller, breaking the JS response handler.

**H6 — `extract()` used in `MINVF_Media_Type_Info::render_explanation()`**
`class-media-type-info.php` line 273. `extract()` is prohibited by WordPress Coding Standards and will cause WP.org plugin review rejection. Replace with explicit variable assignments.

**H7 — CSS typo `color: color:` in `createSubPanel()`**
`admin.js` line 1588. The string `color: color: var(--clr-light-txt)` contains a duplicate `color:` property name. The malformed rule is silently ignored by browsers — sub-panel header text renders with inherited (likely dark) colour rather than the intended light token.

---

### Medium — Fix before v6.0 release

**M1 — `MINVF_Image_Processor` and `MINVF_Font_Processor` are dead code**
`class-processor-factory.php` instantiates them, but the factory itself is never called. `MINVF_Scanner` constructs `MINVF_File_Processor` directly (`class-scanner.php` line 144). Either wire the factory into the scanner or remove the unused processor classes.

**M2 — Image/font processing logic duplicated across three classes**
`process_main_file()`, `process_image_variations()`, `process_wordpress_image_sizes()`, `get_image_dimensions()`, and `validate_attachment_data()` are copied nearly verbatim between `MINVF_File_Processor` and `MINVF_Image_Processor`. Any bug fix must be applied in multiple places.

**M3 — Category display order duplicated in PHP and JS**
`class-table-builder.php` line 108 and `admin.js` lines 737–749. Two independent hardcoded arrays with the same information. Also slightly inconsistent: PHP omits `'Text Files'`, which JS includes.

**M4 — `displaySizeSummary()` three-column rendering is copy-pasted**
`admin.js` lines 1380–1464. Left/middle/right column blocks are structurally identical — same wrapper HTML, same iteration, same template. ~80 lines that should be a single helper called in a loop over the three columns.

**M5 — Extension array in `scan_theme_directory()` duplicates `get_mime_type_from_extension()`**
`class-scanner.php` lines 421–434 and 549–594. Adding a new file type (e.g., `.avif`) requires editing both arrays independently. The extension list should be derived from the keys of `get_mime_type_from_extension()`.

**M6 — Three dead AJAX handlers are still registered and executable**
`class-admin-controller.php` lines 42–45. `ajax_scan_usage`, `ajax_get_usage`, `ajax_create_table` execute real database code and are reachable by any `manage_options` user who knows the action names. Remove the `add_action` registrations until the v4.1.0 UI is implemented.

**M7 — `ajaxurl` vs `minvfData.ajaxUrl` inconsistency in table-view.js**
`table-view.js` lines 241 and 416 use the bare WordPress `ajaxurl` global. All AJAX calls should use `minvfData.ajaxUrl` for consistency with `admin.js`.

**M8 — `MINVF_Media_List_Table` is not loaded in the bootstrap**
`class-media-list-table.php` has no corresponding `require_once` in `media-inventory-forge.php`. If any template attempts to use the class, PHP throws a fatal `Class not found` error.

**M9 — `MINVF_Media_List_Table::prepare_items()` re-scans on every render**
`class-media-list-table.php` line 102 calls `scan_batch(0)` on every `WP_List_Table` render. Should read from the user transient as `MINVF_Table_Builder::build_tables()` does.

**M10 — Private method `minvf_sanitize_scan_data()` uses unnecessary class prefix**
`class-admin-controller.php` line 428. Private class methods don't need the plugin prefix. Rename to `sanitize_scan_data()`.

**M11 — Pie chart colours are hardcoded hex values**
`admin.js` lines 834–843, 863. The chart slice colours and the `#FAF6F0` stroke colour are not CSS-token-based. The stroke colour is labelled "page background" and will become a visible line if the admin background ever changes.

**M12 — `get_usage_stats()` accesses `->inherit` without null-checking**
`class-usage-database.php` line 328. `wp_count_posts('attachment')->inherit` — if no attachments exist with `inherit` status, PHP throws a notice. Use `isset()` or the null coalescing operator.

**M13 — `file_get_contents()` in `class-usage-scanner.php` will fail WP.org sniff**
WordPress Coding Standards require `WP_Filesystem` for all file reads. `file_get_contents()` is flagged by the plugin review team's automated tools.

---

### Low — Cleanup / nice-to-have

**L1** — `admin.js` line 8: `@author Jim R (JimRWeb)` should be `@author Jim R Forge`.
**L2** — `admin.js` line 9: `@version 2.0.0` is stale (plugin is v5.0.2).
**L3** — `admin.js` line 60: Section heading `1. INITIALIZATION` duplicates the `1. INITIALIZATION & GLOBAL STATE` heading already at line 35.
**L4** — `media-inventory-forge.php`: ~350 lines for ~70 lines of functional code. Multi-line PHPDoc blocks for `require_once` calls restate what the filename already says. Comments must explain WHY, not WHAT.
**L5** — `media-inventory-forge.php` lines 105–146: `if (!defined(...))` guards on constants that are defined once in a single file are unnecessary boilerplate.
**L6** — `main-page.php` line 20: Classes `text-2xl font-bold mb-4` are Tailwind utility classes. Tailwind is not loaded; these have no visual effect.
**L7** — `class-table-builder.php`: Inline `style=` attributes throughout PHP string output (margin, padding, display values). These should be CSS classes for maintainability.
**L8** — `class-processor-factory.php` lines 39–43: `get_available_processors()` returns only `['default' => 'MINVF_File_Processor']`, omitting image and font processors — misleading since those classes exist.
**L9** — `class-usage-scanner.php` and `class-usage-database.php` use K&R brace style. All other PHP files use Allman style (WordPress standard).
**L10** — `community-panel.php` line 53: `buymeacoffee.com/jimrweb` uses the old brand handle `jimrweb` — should be updated to the current brand.
**L11** — `scan-controls.php`: Inline `style="display: none;"` etc. belong in CSS or a JS init function.
**L12** — `class-scanner.php` lines 388–403: When "Active Theme" source is selected, `glob($themes_dir . '/*')` scans ALL installed themes, not just the active one. Use `get_stylesheet_directory()` to limit scope.
**L13** — `class-media-type-info.php` lines 265–281: `render_explanation()` calls `include` from inside a data utility class, mixing view responsibilities into the data layer.
**L14** — `class-scanner.php` `process_theme_file()`: `$extension` is assigned twice (lines 479 and 493) — the first assignment is immediately overwritten.

---

## File-by-File Notes

| File | Key issues |
|---|---|
| `media-inventory-forge.php` | L4, L5 — extreme over-commenting |
| `class-admin.php` | Clean. Dead `j-forge` hook check on line 39 (benign). |
| `class-admin-controller.php` | H2, H5, M6, M10 |
| `class-table-builder.php` | H1, L7. Dead `if/else` block in `build_tables()` has an empty success branch (no-op comment). |
| `class-media-list-table.php` | H3, M8, M9 |
| `class-scanner.php` | M5, L12, L14. `$extension` assigned twice in `process_theme_file()`. |
| `class-file-processor.php` | Good. Would become the base class in a factory refactor. |
| `class-image-processor.php` | M1, M2 — entirely dead code. |
| `class-font-processor.php` | M1, M2 — dead code. |
| `class-processor-factory.php` | M1, L8. `can_handle()` always returns `true` and serves no purpose. |
| `class-usage-database.php` | M12. Otherwise good SQL discipline with correct `$wpdb->prepare()` usage. |
| `class-usage-scanner.php` | M6 (reachable only via dead handlers). M13 (`file_get_contents()`). L9 (brace style). |
| `class-file-utils.php` | Good. Dead check at line 59 for `text/xml`/`text/plain` in the SVG block. |
| `class-media-type-info.php` | H6, L13. Font MIME list differs slightly from `class-file-utils.php::get_category()`. |
| `admin.js` | H7 (CSS typo), M3, M4, M11, L1–L3 |
| `table-view.js` | M7 (`ajaxurl`). `saveCollapseState`/`getCollapseState` near-duplicate of `admin.js` functions — the key prefix difference (`mif_table_` vs `mif_`) is intentional and acceptable. |
| `main-page.php` | L6 (Tailwind classes with no effect) |
| `scan-controls.php` | H4, L11 |
| `uninstall.php` | Correct and complete. |

---

## Positive Observations

- **Nonce verification on every AJAX handler** without exception; consistent `current_user_can('manage_options')` checks throughout.
- **Output escaping in PHP is thorough** — `esc_html()`, `esc_url()`, `esc_attr()` applied correctly throughout PHP-generated HTML (the H1 XSS issue is the sole exception).
- **`MINVF_File_Utils`** is clean and well-scoped: each method does exactly one thing, all static, no hidden state.
- **Batch scanning architecture** is well-considered: offset pagination, `wp_raise_memory_limit()`, inter-batch delay, `is_admin()` guard, `no_found_rows` query optimisation.
- **Transient-based data handoff** between scan-save and table-view is a sound design that avoids re-scanning on view switch.
- **`MINVF_Usage_Database` SQL** uses `$wpdb->prepare()` correctly; phpcs suppression comments are narrowly scoped and accurate.
- **`uninstall.php`** is comprehensive: removes the custom table, all options, per-user transients, user meta, and flushes the object cache.
- **`class-usage-scanner.php`** shows strong domain knowledge — Elementor JSON structure, widget option names, customizer keys, and Gutenberg block attribute paths are all handled correctly.
- **The processor interface** establishes a real contract (`MINVF_File_Processor_Interface`). When the factory is wired into the scanner, the pattern will work correctly with minimal changes.
