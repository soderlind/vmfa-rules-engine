# Parent Plugin Integration Proposal

**For:** virtual-media-folders  
**From:** vmfa-rules-engine add-on  
**Date:** 2025-01-21

## Problem Statement

When the rules engine add-on assigns a folder to an attachment during upload (via `wp_generate_attachment_metadata` filter), the parent plugin's UI doesn't reflect the change until the page is refreshed. This happens because:

1. WordPress adds the attachment to the media library collection in the frontend
2. The rules engine assigns a folder server-side during metadata generation
3. The frontend UI has no mechanism to know the folder assignment changed
4. The item appears in the wrong location until manual refresh

## Proposed Changes

### 1. Expose `refreshMediaLibrary()` globally

**File:** `src/admin/media-library.js`  
**Location:** After the function definition (around line 291)

```javascript
window.vmfRefreshMediaLibrary = refreshMediaLibrary;
```

This allows add-ons to trigger a UI refresh when they've made server-side folder changes.

### 2. Add PHP action hook when folder is assigned

**File:** `src/Admin.php` (or wherever `wp_set_object_terms` is called for folders)

After successful folder assignment:

```php
/**
 * Fires after a media item has been assigned to a folder.
 *
 * @since X.X.X
 *
 * @param int   $attachment_id The attachment ID.
 * @param int   $folder_id     The folder term ID.
 * @param array $result        The result from wp_set_object_terms.
 */
do_action( 'vmfo_folder_assigned', $attachment_id, $folder_id, $result );
```

### 3. (Optional) Expose single-attachment folder update

For more targeted updates without full refresh:

**File:** `src/admin/media-library.js`

```javascript
/**
 * Update a single attachment's folder in the UI without full refresh.
 * Useful for add-ons that assign folders programmatically.
 *
 * @param {number} attachmentId - The attachment post ID.
 * @param {number} folderId     - The target folder term ID.
 */
window.vmfUpdateAttachmentFolder = function(attachmentId, folderId) {
    // Dispatch event so folder tree updates counts
    window.dispatchEvent(new CustomEvent('vmf:folders-updated'));
    
    // If we're viewing a specific folder and this attachment doesn't belong, remove it
    const $selectedFolder = jQuery('.vmf-folder-button.is-selected');
    if ($selectedFolder.length) {
        const currentFolderId = $selectedFolder.data('folder-id');
        if (currentFolderId && currentFolderId !== folderId) {
            // The attachment moved to a different folder - trigger refresh
            refreshMediaLibrary();
        }
    }
};
```

## How Add-ons Would Use These

### Example: Rules Engine triggering refresh after upload

```javascript
// In add-on's JavaScript
jQuery(document).on('vmfa:folder-assigned', function(e, data) {
    if (window.vmfRefreshMediaLibrary) {
        window.vmfRefreshMediaLibrary();
    }
    // Or dispatch the standard event
    window.dispatchEvent(new CustomEvent('vmf:folders-updated'));
});
```

### Example: Listening to folder assignments

```php
// In add-on's PHP
add_action( 'vmfo_folder_assigned', function( $attachment_id, $folder_id, $result ) {
    // React to folder assignments made by parent plugin
    // e.g., log, sync, trigger additional processing
}, 10, 3 );
```

## Backward Compatibility

All proposed changes are additive:
- New global functions won't affect existing code
- New action hooks are optional for add-ons to use
- No changes to existing function signatures or behavior

## Priority

1. **High:** `window.vmfRefreshMediaLibrary` - Solves the immediate upload UX issue
2. **Medium:** `vmfo_folder_assigned` action - Enables better add-on integration
3. **Low:** `window.vmfUpdateAttachmentFolder` - Nice-to-have optimization

## Related

- Rules engine uses `wp_generate_attachment_metadata` filter to assign folders
- Counter updates work correctly (using `wp_update_term_count_now`)
- Only the UI refresh is missing
