# Virtual Media Folders Rules Engine

Rule-based automatic folder assignment for media uploads. Add-on plugin for [Virtual Media Folders](https://github.com/soderlind/virtual-media-folders).

<img width="1280" height="651" alt="vmfa-rules-engine-some" src="https://github.com/user-attachments/assets/8084a15b-0b99-4f46-b624-264b7fb4f078" />

## Description

Turn "Default folder for uploads" into a powerful rule system. Automatically assign media to folders based on:

- **Filename patterns** — Match filenames using regular expressions (e.g., `^IMG_`, `^DSC`, `screenshot.*`)
- **MIME type** — Sort by file type (images, videos, PDFs, etc.)
- **Image dimensions** — Organize by resolution (HD, 4K, thumbnails)
- **File size** — Separate large files from small ones
- **EXIF camera model** — Group photos by device (iPhone, Canon, etc.)
- **EXIF date taken** — Organize by capture date
- **Upload author** — Assign based on who uploaded the file
- **IPTC keywords** — Match embedded metadata keywords

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
- [Virtual Media Folders](https://github.com/soderlind/virtual-media-folders) plugin (parent plugin)

## Installation


1. Download [`vmfa-rules-engine.zip`](https://github.com/soderlind/vmfa-rules-engine/releases/latest/download/vmfa-rules-engine.zip)
2. Upload via  `Plugins → Add New → Upload Plugin`
3. Activate via `WordPress Admin → Plugins`

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

## Development

See [docs/DEVELOPMENT.md](docs/DEVELOPMENT.md).

## License

GPL-2.0-or-later. See [LICENSE](LICENSE).

Copyright 2026 Per Soderlind
