# Development

>[Add-on Development](https://github.com/soderlind/virtual-media-folders/blob/main/docs/addon-development.md) – Guide to building add-on plugins for Virtual Media Folders.

## Setup

```bash
# Add to composer.json
composer require soderlind/virtual-media-folders
composer require soderlind/vmfa-rules-engine


# Install dependencies
composer install
npm install
```

## Build

```bash
# Start development build with watch
npm run start

# Production build
npm run build
```

## Lint

```bash
# Lint JavaScript
npm run lint:js

# Lint CSS
npm run lint:css

# Lint PHP (WordPress Coding Standards)
composer lint:php
composer fix:php
```

## Tests

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
