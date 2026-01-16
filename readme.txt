=== VMFA Rules Engine ===
Contributors: flavflavor
Donate link: https://developer.developer.developer.developer.developer.developer.developer/
Tags: media library, virtual folders, automation, rules engine, media organization
Requires at least: 6.8
Tested up to: 6.8
Requires PHP: 8.3
Stable tag: 0.1.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Rule-based automatic folder assignment for media uploads. Organize your media library automatically based on customizable conditions.

== Description ==

VMFA Rules Engine automatically assigns uploaded media files to virtual folders based on customizable rules. Define conditions based on filename patterns, MIME types, image dimensions, EXIF data, and more—then let the plugin organize your media library automatically.

**Note:** This plugin requires [Virtual Media Folders](https://wordpress.org/plugins/virtual-media-folders/) to be installed and activated.

= Features =

* **8 Condition Types**
  * Filename Regex – Match filenames using regular expressions
  * MIME Type – Match by file type with wildcard support (e.g., `image/*`)
  * Dimensions – Match image width/height with comparison operators
  * File Size – Match by file size with range support
  * EXIF Camera – Match by camera make/model from EXIF metadata
  * EXIF Date – Match by photo capture date ranges
  * Author – Match by WordPress user who uploaded the file
  * IPTC Keywords – Match by embedded IPTC keyword metadata

* **Powerful Rule Management**
  * Combine multiple conditions with AND logic
  * Set rule priority with drag-and-drop ordering
  * Stop processing option (first matching rule wins)
  * Enable/disable individual rules without deleting

* **Batch Processing**
  * Preview mode (dry-run) to see affected files before applying
  * Apply rules to existing unassigned media
  * Filter by MIME type for targeted processing

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

== Screenshots ==

1. Rules management panel with drag-and-drop ordering
2. Rule editor with condition builder
3. Preview mode showing affected files
4. Batch processing results

== Changelog ==

= 0.1.0 =
* Initial release
* 8 condition matchers: Filename Regex, MIME Type, Dimensions, File Size, EXIF Camera, EXIF Date, Author, IPTC Keywords
* AND logic for combining conditions
* Priority-based rule ordering with drag-and-drop
* Batch processing with preview mode
* REST API for rule management
* React-based admin interface

== Upgrade Notice ==

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
