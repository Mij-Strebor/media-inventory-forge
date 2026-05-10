# Media Inventory Forge — Roadmap

Items are listed roughly in priority order. Nothing here is a commitment — this is a working plan, not a release schedule.

---

## Planned Features

### 1. Media Categories

**Status:** Planned  
**Complexity:** High

Today the plugin scans and inventories all WordPress media in a single flat list. Every attachment — images, PDFs, videos, documents — appears together regardless of its role in the site's design or content structure.

The goal is to let the user organize media into named **categories** that reflect how the media is actually used on the site. Each category can carry its own filtering rules, display settings, and export output. Names must be unique across the entire project.

#### Default categories

| Category | Purpose | Example content |
|----------|---------|-----------------|
| **Images** | General photography, graphics, and illustrations used across the site | Post thumbnails, in-content images, gallery photos |
| **Headers** | Banner and hero images used at the top of pages and posts | Page hero banners, post featured images, slider images |
| **Page Data** | Icons, logos, UI graphics, and decorative assets embedded in page layouts | Logos, icons, background textures, section dividers |
| **Event Panels** | Media attached to events — promotional images, speaker photos, venue imagery | Event banners, speaker headshots, venue photos |
| **Cards** | Thumbnail-format images designed for card layouts and grid displays | Card thumbnails, portfolio previews, product images |

The user can rename, reorder, or delete any default category, and add as many custom categories as needed (e.g., Downloads, Videos, Team Photos).

#### UX concept

- A **category selector** (tabs or a filter panel) sits above the media inventory table.
- Selecting a category filters the inventory to show only media assigned to that category.
- Media can be assigned to a category manually or by rule (e.g., all images attached to pages tagged "event" automatically appear in Event Panels).
- Each category can carry its own column set — Events might show "Event Date" while Cards might show "Dimensions" and "File Size."
- The **export panel** lets the user export the inventory for one category, all categories combined, or a custom selection.

#### Assignment rules

Categories can be populated by:
1. **Manual tagging** — user assigns media directly from the inventory table
2. **Automatic rules** — based on attachment metadata: post type, taxonomy, image dimensions, filename pattern, or MIME type
3. **Uncategorized** — media not matching any rule appears in a catch-all Uncategorized view

#### Data model change

Categories introduce an organizational layer on top of the current flat attachment list:

```
inventory
  └── categories[]
        ├── id
        ├── name          ("Images", "Headers", "Event Panels", …)
        ├── rules[]       (auto-assignment rules: post_type, taxonomy, size range, etc.)
        └── attachments[] (WordPress attachment IDs matching this category)
```

Backward compatibility: existing inventories with no category data load as a single Uncategorized view preserving all current entries.

---

## Completed

| Version | Feature |
|---------|---------|
| 5.1.0 | Stability and compatibility improvements |
| 4.0.1 | Bug fixes |
| 4.0.0 | Major rebuild |
| 2.1.0 | Enhanced scanning and filtering |
| 2.0.0 | Improved media analysis |
| 1.0.0 | Initial public release — WordPress media library scanner and analyzer |
