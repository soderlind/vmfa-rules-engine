# Virtual Media Folders Rules Engine

[![Try in WordPress Playground](https://img.shields.io/badge/▶_Try_in_WordPress_Playground-blue?style=for-the-badge)](https://playground.wordpress.net/?blueprint-url=https://raw.githubusercontent.com/soderlind/vmfa-rules-engine/main/.wordpress-org/blueprints/blueprint.json)

Rule-based automatic folder assignment for media uploads. Add-on plugin for [Virtual Media Folders](https://wordpress.org/plugins/virtual-media-folders/).


https://github.com/user-attachments/assets/8655014d-b963-493d-a93e-ca1fc63e8b1b


[Requirements](#requirements) | [Installation](#installation) | [Usage](#usage) | [Organizing a Photo Library](#organizing-a-photo-library) | [Development](#development) | [License](#license)

## Description

Turn "Default folder for uploads" into a powerful rule system. Automatically assign media to folders based on:

- **Filename patterns** — Match filenames using regular expressions (e.g., `^IMG_`, `^DSC`, `screenshot.*`)
- **MIME type** — Sort by file type (images, videos, PDFs, etc.)
- **Image dimensions** — Organize by resolution (HD, 4K, thumbnails)
- **File size** — Separate large files from small ones
- **EXIF camera model** — Group photos by device (iPhone, Canon, etc.)
- **EXIF date taken** — Organize by capture date
- **EXIF aperture, focal length, ISO, shutter speed, orientation** — Fine-grained camera settings
- **IPTC keywords, credit, caption, copyright, title** — Match any embedded metadata field
- **Upload author** — Assign based on who uploaded the file

### Features

- **Multiple rules with priority** — Create as many rules as needed, drag to reorder
- **Stop after first match** — Control whether to continue evaluating rules
- **AND logic** — All conditions in a rule must match
- **Automatic on upload** — New uploads are processed instantly
- **Scan existing media** — Apply rules to your existing library with preview
- **WP-CLI compatible** — Rules apply to imports via `wp media import`
- **Modern React UI** — Consistent with WordPress admin design

## Requirements

- WordPress 6.8+
- PHP 8.3+
- [Virtual Media Folders](https://wordpress.org/plugins/virtual-media-folders/) plugin (parent plugin)

## Installation


1. Download [`vmfa-rules-engine.zip`](https://github.com/soderlind/vmfa-rules-engine/releases/latest/download/vmfa-rules-engine.zip)
2. Upload via  `Plugins → Add New → Upload Plugin`
3. Activate via `WordPress Admin → Plugins`

Plugin [updates are handled automatically](https://github.com/soderlind/wordpress-plugin-github-updater#readme) via GitHub. No need to manually download and install updates.

## Usage

1. Go to **Media → Rules Engine** in the WordPress admin
2. Click **Add Rule** to create your first rule
3. Configure conditions and select a target folder
4. Enable the rule and save
5. New uploads will automatically be assigned to folders based on your rules

### Scanning Existing Media

1. Click **Scan Existing Media** to preview what changes would be made
2. Review the preview and select items to process
3. Click **Apply Changes** to assign folders

## Organizing a Photo Library

The condition type selector groups conditions into **General**, **EXIF**, and **IPTC / XMP**, making it straightforward to build rules around embedded photo metadata.

### EXIF conditions

| Condition | Field | Example use |
|---|---|---|
| Camera model | `exif_camera` | Separate iPhone shots from DSLR shots |
| Date taken | `exif_date` | Archive by year or shoot date |
| Aperture (f-number) | `exif_aperture` | Isolate wide-open portraits (f/1.4 – f/2.8) |
| Focal length | `exif_focal_length` | Group wide-angle vs telephoto shots |
| ISO sensitivity | `exif_iso` | Flag high-ISO / low-light images for review |
| Shutter speed | `exif_shutter_speed` | Find motion-frozen or long-exposure shots |
| Orientation | `exif_orientation` | Separate portrait vs landscape images |

Aperture, focal length, ISO, and shutter speed all support comparison operators (`>`, `>=`, `<`, `<=`, `=`, `Between`), so you can create ranges like "ISO between 1600 and 12800". Shutter speed accepts standard photographic fraction notation (`1/1000`, `1/60`) as well as decimal seconds (`0.5`).

### IPTC / XMP conditions

| Condition | Field | Example use |
|---|---|---|
| Keywords | `iptc_keywords` | Route images tagged `product` to a product folder |
| Credit | `iptc_credit` | Separate agency photos (Reuters, AFP, Getty) |
| Caption | `iptc_caption` | Match photos with specific caption text |
| Copyright | `iptc_copyright` | Group images by rights holder or year |
| Title | `iptc_title` | Route by object name or headline |

All IPTC conditions use case-insensitive partial matching, so `"Reuters"` matches `"Reuters Photography"`.

### Example: organize a news photo workflow

```
Rule: Agency Photos
  Conditions:
    IPTC Credit  contains  "Reuters"
  → Folder: Agency / Reuters

Rule: High-ISO Night Shots
  Conditions:
    EXIF ISO  >=  3200
  → Folder: Review / High-ISO

Rule: Product Images
  Conditions:
    IPTC Keywords  contains  "product"
    MIME type      is        image/jpeg
  → Folder: Products
```

## Development

See [docs/DEVELOPMENT.md](docs/DEVELOPMENT.md).

## License

GPL-2.0-or-later. See [LICENSE](LICENSE).

Copyright 2026 Per Soderlind
