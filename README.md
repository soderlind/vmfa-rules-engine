# Virtual Media Folders Rules Engine

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

```bash
# Install dependencies
composer install
npm install

# Start development build with watch
npm run start

# Production build
npm run build

# Lint JavaScript
npm run lint:js

# Lint CSS
npm run lint:css

# Lint PHP (WordPress Coding Standards)
composer lint:php
composer fix:php
```

### Running Tests

```bash
# PHP tests (PHPUnit with Brain Monkey)
composer test
composer test:coverage  # With HTML coverage report

# JavaScript tests (Vitest)
npm test
npm run test:watch      # Watch mode
npm run test:coverage   # With coverage report
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
| POST | `/vmfa-rules/v1/preview` | Scan existing media (preview) |
| POST | `/vmfa-rules/v1/apply-rules` | Apply rules to media |
| GET | `/vmfa-rules/v1/stats` | Get media library statistics |

## License

GPL-2.0-or-later
