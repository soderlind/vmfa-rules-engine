# VMFA Rules Engine

Rule-based automatic folder assignment for media uploads. Add-on plugin for [Virtual Media Folders](https://github.com/soderlind/virtual-media-folders).

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
- **Apply to existing library** — Run rules against your existing media with dry-run preview
- **Modern React UI** — Consistent with WordPress admin design

## Requirements

- WordPress 6.8+
- PHP 8.3+
- [Virtual Media Folders](https://github.com/soderlind/virtual-media-folders) plugin (parent plugin)

## Installation

1. Install and activate the [Virtual Media Folders](https://github.com/soderlind/virtual-media-folders) plugin
2. Download and extract this plugin to `wp-content/plugins/vmfa-rules-engine/`
3. Run `composer install` to install PHP dependencies
4. Run `npm install && npm run build` to compile assets
5. Activate the plugin through the WordPress admin

## Usage

1. Go to **Media → Rules Engine** in the WordPress admin
2. Click **Add Rule** to create your first rule
3. Configure conditions and select a target folder
4. Enable the rule and save
5. New uploads will automatically be assigned to folders based on your rules

### Applying Rules to Existing Media

1. Click **Dry Run (Preview)** to see what changes would be made
2. Review the preview and select items to process
3. Click **Apply Changes** to assign folders

## Development

```bash
# Install dependencies
npm install

# Start development build with watch
npm run start

# Production build
npm run build

# Lint JavaScript
npm run lint:js

# Lint CSS
npm run lint:css

# Run tests
npm run test
```

## Hooks & Filters

### Actions

- `vmfa_rules_engine_folder_assigned` — Fired after a rule assigns a folder
  - Parameters: `$attachment_id`, `$folder_id`, `$rule`

### Filters

- `vmfa_rules_engine_matchers` — Add custom condition matchers
- `vmfa_rules_engine_skip_if_assigned` — Skip rule evaluation if folder already assigned

### Example: Custom Condition Matcher

```php
add_filter( 'vmfa_rules_engine_matchers', function( $matchers ) {
    $matchers['custom_type'] = new My_Custom_Matcher();
    return $matchers;
} );

class My_Custom_Matcher implements \VmfaRulesEngine\Conditions\MatcherInterface {
    public function matches( $attachment_id, $metadata, $params ) {
        // Your custom logic
        return true;
    }
    
    public function get_type() {
        return 'custom_type';
    }
}
```

## REST API Endpoints

All endpoints require `manage_options` capability.

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/vmfa-rules/v1/rules` | List all rules |
| POST | `/vmfa-rules/v1/rules` | Create a rule |
| GET | `/vmfa-rules/v1/rules/{id}` | Get a single rule |
| PUT | `/vmfa-rules/v1/rules/{id}` | Update a rule |
| DELETE | `/vmfa-rules/v1/rules/{id}` | Delete a rule |
| POST | `/vmfa-rules/v1/rules/reorder` | Reorder rules |
| POST | `/vmfa-rules/v1/preview` | Preview rule application (dry run) |
| POST | `/vmfa-rules/v1/apply-rules` | Apply rules to media |
| GET | `/vmfa-rules/v1/stats` | Get media library statistics |

## Changelog

### 0.1.0
- Initial release
- 8 condition types: filename regex, MIME type, dimensions, file size, EXIF camera, EXIF date, author, IPTC keywords
- Drag-and-drop rule reordering
- Dry-run preview for batch operations
- React-based admin UI

## License

GPL-2.0-or-later
