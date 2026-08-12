=== Virtual Media Folders Rules Engine ===
Contributors: flavflavor
Donate link: https://developer.developer.developer.developer.developer.developer.developer/
Tags: media library, virtual folders, automation, rules engine, media organization
Requires at least: 6.8
Tested up to: 7.1
Requires PHP: 8.3
Stable tag: 1.7.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Rule-based automatic folder assignment for media uploads. Organize your media library automatically based on customizable conditions.

== Description ==

VMFA Rules Engine automatically assigns uploaded media files to virtual folders based on customizable rules. Define conditions based on filename patterns, MIME types, image dimensions, EXIF data, and more—then let the plugin organize your media library automatically.

**Note:** This plugin requires [Virtual Media Folders](https://wordpress.org/plugins/virtual-media-folders/) to be installed and activated.

= Features =

* **17 Condition Types** across three groups

  *General*
  * Filename Regex – Match filenames using regular expressions
  * MIME Type – Match by file type with wildcard support (e.g., `image/*`)
  * Dimensions – Match image width/height with comparison operators
  * File Size – Match by file size with range support
  * Author – Match by WordPress user who uploaded the file

  *EXIF*
  * Camera Model – Match by camera make/model
  * Date Taken – Match by photo capture date ranges
  * Aperture (f-number) – Match by lens aperture (supports >, <, =, Between)
  * Focal Length – Match by focal length in millimetres
  * ISO Sensitivity – Match by ISO value
  * Shutter Speed – Match by exposure time; accepts fraction (`1/1000`) or decimal notation
  * Orientation – Match by EXIF orientation tag (portrait, landscape, rotated)

  *IPTC / XMP*
  * Keywords – Match by embedded IPTC keyword metadata
  * Credit – Match by photo credit line
  * Caption – Match by image description or caption
  * Copyright – Match by copyright notice
  * Title – Match by object name or headline

* **Powerful Rule Management**
  * Combine multiple conditions with AND logic
  * Set rule priority with drag-and-drop ordering
  * Stop processing option (first matching rule wins)
  * Enable/disable individual rules without deleting

* **Two Ways to Organize**
  * Automatic on upload – new files are processed instantly
  * Scan existing media – apply rules to your current library

* **Batch Processing**
  * Preview mode to see affected files before applying
  * Apply rules to existing unassigned media
  * Filter by MIME type for targeted processing
  * WP-CLI compatible – works with `wp media import`

* **Modern Admin Interface**
  * Clean, intuitive React-based interface
  * Real-time validation and feedback
  * Fully responsive design

= Example Use Cases =

* Automatically sort iPhone photos to an "iPhone Photos" folder based on EXIF camera data
* Move all PDFs to a "Documents" folder
* Organize high-resolution images (4K+) to a "High Resolution" folder
* Route uploads from specific authors to designated folders
* Sort images by capture date into year-based folders

= Organizing a Photo Library =

The condition picker groups options under **General**, **EXIF**, and **IPTC / XMP**, making it easy to build rules around embedded photo metadata.

*EXIF conditions* let you match on camera settings written into the image file by the camera itself:

* **Aperture** – Isolate wide-open portraits shot at f/1.4–f/2.8
* **Focal Length** – Separate wide-angle from telephoto shots
* **ISO** – Flag high-ISO / low-light images for review (e.g., ISO >= 3200)
* **Shutter Speed** – Find motion-frozen action shots (e.g., <= 1/1000 s) or long exposures
* **Orientation** – Route portrait-orientation images to a separate folder automatically

*IPTC / XMP conditions* match on editorial metadata embedded by photo-editing software or DAM systems:

* **Keywords** – Route images tagged "product" or "editorial" to the right folder
* **Credit** – Automatically separate agency images from Reuters, AFP, or Getty
* **Caption** – Match images whose description contains specific text
* **Copyright** – Group images by rights holder or copyright year
* **Title** – Route by object name or headline

All IPTC conditions use case-insensitive partial matching, so `"Reuters"` matches `"Reuters Photography"`.

Example rules for a news-photo workflow:

  Rule: Agency Photos
    IPTC Credit  contains  "Reuters"
  → Folder: Agency / Reuters

  Rule: High-ISO Night Shots
    EXIF ISO  >=  3200
  → Folder: Review / High-ISO

  Rule: Product Images
    IPTC Keywords  contains  "product"
    MIME Type      is        image/jpeg
  → Folder: Products

== Installation ==

1. Ensure the Virtual Media Folders plugin is installed and activated
2. Upload the `vmfa-rules-engine` folder to the `/wp-content/plugins/` directory
3. Activate the plugin through the 'Plugins' menu in WordPress
4. Navigate to **Media → Rules Engine** to start creating rules

== Frequently Asked Questions ==

= Does this plugin work without Virtual Media Folders? =

No, this plugin requires Virtual Media Folders to be installed and activated. It extends the parent plugin's functionality by adding automated rule-based folder assignment.

= What happens when multiple rules match? =

Rules are evaluated in priority order (lowest number first). When a rule matches and has "Stop processing" enabled, no further rules are checked. This "first match wins" approach gives you precise control over folder assignment.

= Can I combine multiple conditions in a single rule? =

Yes! Each rule can have multiple conditions, and all conditions must match for the rule to apply (AND logic). For example, you can create a rule that matches files where MIME type is `image/jpeg` AND width is greater than 1920px.

= Will this affect my existing media files? =

Newly uploaded files are processed automatically. For existing files, you have full control—use the Preview feature to see what would happen, then choose to Apply Rules when ready.

= Can I create custom condition types? =

Yes, developers can add custom matchers using the `vmfa_rules_engine_matchers` filter. See the developer documentation for details.

= Is there a way to exclude certain files from processing? =

Yes, use the `vmfa_rules_engine_skip_if_assigned` filter to skip files that already have a folder assigned, or create rules with specific conditions to exclude certain file types.

= Does this work with WP-CLI media imports? =

Yes! Rules are automatically applied when you use `wp media import`. For example:

`wp media import ~/photos/* --url=http://example.com/mysite/`

Each imported file will be evaluated against your rules and assigned to the matching folder.

== Screenshots ==

1. Rules management panel with drag-and-drop ordering
2. Rule editor with condition builder
3. Preview mode showing affected files
4. Batch processing results

== Changelog ==

= 1.7.4 =
* Fixed: Prevent a fatal error when the "Virtual Media Folders" parent plugin is missing or older than 2.0.0; show an admin notice instead.
* Changed: Tested up to WordPress 7.1.

= 1.7.3 =
* Security: Resolved the majority of Dependabot alerts by updating build/test dependencies (`@wordpress/scripts` 31 → 32, `@wordpress/components` → 36). Remaining alerts are dev-only transitive dependencies.
* Changed: Added grouped `.github/dependabot.yml` config to consolidate future dependency update PRs.

= 1.7.2 =
* Fixed: Allow both v1 and v2 of composer/installers for broader compatibility

= 1.7.1 =
* Fixed: "Between" operator inputs no longer wrap in the condition builder UI
* Fixed: Playground blueprint now installs from the release ZIP, fixing activation failure due to missing vendor directory

= 1.7.0 =
* Added: EXIF aperture, focal length, ISO, shutter speed, and orientation condition matchers
* Added: IPTC/XMP credit, caption, copyright, and title condition matchers
* Added: Grouped condition type selector (General / EXIF / IPTC sections) in the Rule Editor
* Added: NumericCompareTrait and TextSearchTrait for shared matcher logic
* Changed: Refactored DimensionsMatcher, FileSizeMatcher, ExifCameraMatcher to use shared traits

= 1.6.0 =
* Changed: Refactored Plugin class to extend VMF core `AbstractPlugin` base class
* Changed: Extracted settings tab into new `Admin\SettingsTab` extending `AbstractSettingsTab`
* Changed: Removed duplicated singleton boilerplate, textdomain loading, and WP 7 compat CSS

= 1.5.0 =
* Added: WP 7.0+ design-token style overrides for rule editor, conditions, and preview panels

= 1.4.4 =
* Security: Updated npm and Composer dependencies.

= 1.4.3 =
* Changed: Tested up to WordPress 7.0

= 1.4.2 =
* Changed: Use `vmfo_upload_folder` filter instead of hooking `wp_generate_attachment_metadata` directly
* Changed: New `filter_upload_folder()` method receives folder_id, attachment_id, and metadata
* Deprecated: `evaluate_on_upload()` kept for backward compatibility

= 1.4.1 =
* Fixed: Added Node.js build step to GitHub Actions release workflows
* Fixed: Added `build/` to `.gitignore`

= 1.4.0 =
* Changed: Replaced inline plugin updater with shared `class-github-updater.php`

= 1.3.1 =
* Fixed: Added ABSPATH guards to all PHP source files
* Added: uninstall.php for clean plugin removal

= 1.3.0 =
* Fixed: Changed `vmfa_rules_engine_skip_if_assigned` filter default from `false` to `true`
* Fixed: Rules Engine now respects folder assignments made by Editorial Workflow by default
* Fixed: Prevents race condition where Rules Engine would override Editorial Workflow inbox assignments
* Changed: Improved hook priority documentation in RuleEvaluator

= 1.2.2 =
* Changed: Updated dependencies to latest minor/patch versions

= 1.2.1 =
* Added: Folder deletion protection - folders with active rules cannot be deleted

= 1.2.0 =
* Added: Create folder button (+) in rule editor modal - create folders without leaving the editor
* Added: Automatic selection of newly created folders
* Added: Hierarchical parent folder selection for nested folders
* Improved: Error messages now displayed as proper error elements (red text)
* Improved: Race condition protections for folder operations

= 1.1.2 =
* Fixed: Folder counts not updating in sidebar after rule-based folder assignment on upload
* Fixed: Now explicitly calls vmfRefreshFolders() after folder assignment to ensure accurate counts

= 1.1.1 =
* Changed: Replaced gear icon with settings icon for "Edit rule" button
* Changed: Replaced search icon with funnel icon for "Scan with this rule" button

= 1.1.0 =
* Added: Smart preview scanning - auto-continues until matches are found
* Added: Glob/wildcard filename matching (patterns like `*abc*.*` now work)
* Added: Visible "Load more" button with icon when no scrollable content
* Improved: Server-side scan optimization with target_matches parameter
* Improved: Better UX for large libraries with auto-loading and spinner feedback

= 1.0.0 =
* Added: Single Rule Scan - Scan media using a specific rule with the new search icon button
* Added: WordPress Playground blueprint support for easy testing
* Changed: Blueprint now uses git:directory resource for more reliable plugin loading

= 0.4.1 =
* Removed redundant parent plugin dependency checks (now handled by WordPress Requires Plugins header)

= 0.4.0 =
* Settings now appear as a tab within Virtual Media Folders "Folder Settings" page
* Backwards compatible with older versions of parent plugin

= 0.3.3 =
* Improved condition descriptions for better usability
* Added Norwegian (nb_NO) translations
* Added npm scripts for i18n workflow

= 0.3.2 =
* Fixed WordPress 6.8+ deprecation warnings for form controls

= 0.3.1 =
* Fixed namespace reference for GitHubPluginUpdater

= 0.3.0 =
* Added GitHub auto-updater for plugin updates via GitHub releases

= 0.2.1 =
* Fixed lazy loading pagination stopping at 300 items (offset calculation bug)
* Fixed CheckboxControl deprecation warning for WordPress 6.7+
* Increased preview table height for better scrolling experience

= 0.2.0 =
* Added lazy loading (infinite scroll) for preview results
* Added scan options modal to choose unassigned-only or all media
* Renamed button to "Scan Existing Media" for clarity
* Updated page title to "Virtual Media Folders Rules Engine"
* Fixed button alignment in Rules card header

= 0.1.0 =
* Initial release
* 8 condition matchers: Filename Regex, MIME Type, Dimensions, File Size, EXIF Camera, EXIF Date, Author, IPTC Keywords
* AND logic for combining conditions
* Priority-based rule ordering with drag-and-drop
* Automatic folder assignment on new uploads
* Scan existing media with preview before applying
* REST API for rule management
* React-based admin interface

== Upgrade Notice ==

= 0.3.2 =
Fixes deprecation warnings in WordPress 6.8+.

= 0.3.1 =
Bug fix for auto-updater initialization.

= 0.3.0 =
New: Plugin now supports automatic updates from GitHub releases.

= 0.2.1 =
Bug fix: Lazy loading now correctly loads all preview results beyond 300 items.

= 0.2.0 =
Improved preview with lazy loading - scans your entire library seamlessly.

= 0.1.0 =
Initial release of VMFA Rules Engine.

== Developer Documentation ==

= Hooks =

**Actions:**

`vmfa_rules_engine_folder_assigned` - Fired when a folder is assigned to an attachment.

Parameters:
* `$attachment_id` (int) - The attachment post ID
* `$folder_id` (int) - The assigned folder term ID
* `$rule` (array) - The matched rule data

**Filters:**

`vmfa_rules_engine_matchers` - Modify available condition matchers.

`vmfa_rules_engine_skip_if_assigned` - Whether to skip files that already have a folder.

= REST API =

The plugin provides a REST API under the `vmfa-rules/v1` namespace with endpoints for full rule CRUD operations, batch processing, and statistics.

See the GitHub repository for complete API documentation.
