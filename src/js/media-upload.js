/**
 * Media Upload Handler for Rules Engine.
 *
 * Hooks into WordPress media uploader to refresh the media library
 * after uploads are processed by the rules engine.
 *
 * @package VmfaRulesEngine
 */

( function () {
	'use strict';

	/**
	 * Debounce timer for batching multiple uploads.
	 *
	 * @type {number|null}
	 */
	let refreshTimer = null;

	/**
	 * Refresh the media library and folder counts with debouncing.
	 * Waits for multiple uploads to complete before refreshing.
	 */
	function scheduleRefresh() {
		// Clear any pending refresh.
		if ( refreshTimer ) {
			clearTimeout( refreshTimer );
		}

		// Schedule a refresh after a short delay.
		// This batches multiple rapid uploads into a single refresh.
		refreshTimer = setTimeout( function () {
			// Refresh folder counts in the sidebar.
			// The main plugin (v1.6.2+) has an upload listener, but it may fire
			// before the rules engine assigns the folder. Explicitly calling
			// vmfRefreshFolders() ensures counts are accurate after assignment.
			if ( typeof window.vmfRefreshFolders === 'function' ) {
				window.vmfRefreshFolders();
			}

			// Refresh the media library grid if available.
			if ( typeof window.vmfRefreshMediaLibrary === 'function' ) {
				window.vmfRefreshMediaLibrary();
			} else {
				// Fallback: dispatch the folders-updated event.
				window.dispatchEvent( new CustomEvent( 'vmf:folders-updated' ) );
			}
			refreshTimer = null;
		}, 500 );
	}

	/**
	 * Initialize upload hooks when wp.Uploader is available.
	 */
	function init() {
		// Check if wp.Uploader exists (media library page).
		if ( typeof wp === 'undefined' || ! wp.Uploader ) {
			return;
		}

		// Hook into the uploader's success callback.
		const originalInit = wp.Uploader.prototype.init;
		wp.Uploader.prototype.init = function () {
			originalInit.apply( this, arguments );

			// Listen for file upload success.
			this.uploader.bind( 'FileUploaded', function () {
				// Schedule a refresh after the rules engine processes the upload.
				// The rules engine runs on wp_generate_attachment_metadata filter,
				// which happens after the file is uploaded but before this callback.
				scheduleRefresh();
			} );
		};
	}

	// Initialize when DOM is ready.
	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', init );
	} else {
		init();
	}
} )();
