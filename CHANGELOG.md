# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [0.4.0] - 2026-01-16

### Added
- **Parent Plugin Tab Integration**: Settings now appear as a tab within Virtual Media Folders "Folder Settings" page
  - Registers via `vmfo_settings_tabs` filter when parent plugin supports tabs
  - URL pattern: `?page=vmfo-settings&tab=rules-engine`

### Changed
- Settings menu no longer appears as separate item under Media when parent supports tabs
- Backwards compatible: falls back to standalone menu if parent plugin is outdated

## [0.3.2] - 2026-01-16

### Fixed

- Fixed WordPress 6.8+ deprecation warnings for TextControl, SelectControl, and ToggleControl components
- Added `__next40pxDefaultSize` and `__nextHasNoMarginBottom` props to all form controls

## [0.3.1] - 2026-01-16

### Fixed

- Fixed namespace reference for GitHubPluginUpdater in main plugin file

## [0.3.0] - 2026-01-16

### Added

- GitHub auto-updater for plugin updates via GitHub releases
- Unit tests for GitHubPluginUpdater class (15 new tests)

## [0.2.1] - 2026-01-16

### Fixed

- Lazy loading pagination offset calculation - now correctly loads all items beyond 300
- Added missing `__nextHasNoMarginBottom` prop to scan options checkbox (WP 6.7 deprecation warning)
- Increased preview table height from 300px to 400px for better scrolling experience

## [0.2.0] - 2026-01-16

### Added

- Lazy loading (infinite scroll) for preview results - loads items automatically as you scroll
- "Scan Options" modal before preview to choose unassigned-only or all media

### Changed

- Renamed "Dry Run (Preview)" button to "Scan Existing Media" for clarity
- Page title changed to "Virtual Media Folders Rules Engine"
- Preview now shows "X of Y" total count with scroll-to-load-more hint
- Default batch size reduced to 50 items per request for better performance

### Fixed

- Button alignment in Rules card header

## [0.1.0] - 2026-01-16

### Added

- Initial release of VMFA Rules Engine add-on for Virtual Media Folders
- Rule-based automatic folder assignment for media uploads
- 8 condition matchers:
  - **Filename Regex**: Match filenames using regular expressions
  - **MIME Type**: Match by file type with wildcard support (e.g., `image/*`)
  - **Dimensions**: Match image width/height with comparison operators
  - **File Size**: Match by file size in KB with range support
  - **EXIF Camera**: Match by camera make/model from EXIF data
  - **EXIF Date**: Match by photo capture date ranges
  - **Author**: Match by WordPress user who uploaded the file
  - **IPTC Keywords**: Match by embedded IPTC keyword metadata
- AND logic for combining multiple conditions within a rule
- Priority-based rule ordering with drag-and-drop reordering
- Stop processing option (first matching rule wins)
- Enable/disable individual rules without deleting them
- Automatic folder assignment on new uploads
- Scan existing media with preview before applying
- REST API endpoints for rule management (`vmfa-rules/v1`)
- React-based admin UI matching vmfa-ai-organizer style
- PHPUnit test suite with Brain Monkey for WordPress mocking (74 tests)

### Requirements

- WordPress 6.8 or higher
- PHP 8.3 or higher
- [Virtual Media Folders](https://developer.developer.developer.developer.developer.developer.developer.developer.developer.developer.developer/) parent plugin

[Unreleased]: https://github.com/soderlind/vmfa-rules-engine/compare/v0.3.2...HEAD
[0.3.2]: https://github.com/soderlind/vmfa-rules-engine/compare/v0.3.1...v0.3.2
[0.3.1]: https://github.com/soderlind/vmfa-rules-engine/compare/v0.3.0...v0.3.1
[0.3.0]: https://github.com/soderlind/vmfa-rules-engine/compare/v0.2.1...v0.3.0
[0.2.1]: https://github.com/soderlind/vmfa-rules-engine/compare/v0.2.0...v0.2.1
[0.2.0]: https://github.com/soderlind/vmfa-rules-engine/compare/v0.1.0...v0.2.0
[0.1.0]: https://github.com/soderlind/vmfa-rules-engine/releases/tag/v0.1.0
