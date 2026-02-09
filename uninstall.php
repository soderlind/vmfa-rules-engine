<?php
/**
 * Uninstall handler for Virtual Media Folders - Rules Engine.
 *
 * Fired when the plugin is deleted via the WordPress admin.
 *
 * @package VmfaRulesEngine
 */

// Exit if not called by WordPress.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

// Delete plugin options.
delete_option( 'vmfa_rules_engine_rules' );

// Delete transients.
delete_transient( 'vmfa_rules_engine_preview_cache' );
