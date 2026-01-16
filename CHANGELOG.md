# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

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
- Batch processing for existing media library:
  - Dry-run preview mode showing which files would be affected
  - Apply rules to all unassigned media
- REST API endpoints for rule management (`vmfa-rules/v1`)
- React-based admin UI matching vmfa-ai-organizer style
- PHPUnit test suite with Brain Monkey for WordPress mocking (74 tests)

### Requirements

- WordPress 6.8 or higher
- PHP 8.3 or higher
- [Virtual Media Folders](https://developer.developer.developer.developer.developer.developer.developer.developer.developer.developer.developer/) parent plugin

[Unreleased]: https://github.com/soderlind/vmfa-rules-engine/compare/v0.1.0...HEAD
[0.1.0]: https://github.com/soderlind/vmfa-rules-engine/releases/tag/v0.1.0
