<?php
/**
 * Plugin Name: Virtual Media Folders Rules Engine
 * Plugin URI: https://github.com/soderlind/vmfa-rules-engine
 * Description: Rule-based automatic folder assignment for media uploads. Add-on for Virtual Media Folders.
 * Version: 0.4.1
 * Author: Per Soderlind
 * Author URI: https://soderlind.no
 * License: GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: vmfa-rules-engine
 * Domain Path: /languages
 * Requires at least: 6.8
 * Requires PHP: 8.3
 * Requires Plugins: virtual-media-folders
 *
 * @package VmfaRulesEngine
 */

defined( 'ABSPATH' ) || exit;

// Plugin constants.
define( 'VMFA_RULES_ENGINE_VERSION', '0.4.1' );
define( 'VMFA_RULES_ENGINE_FILE', __FILE__ );
define( 'VMFA_RULES_ENGINE_PATH', plugin_dir_path( __FILE__ ) );
define( 'VMFA_RULES_ENGINE_URL', plugin_dir_url( __FILE__ ) );
define( 'VMFA_RULES_ENGINE_BASENAME', plugin_basename( __FILE__ ) );

// Composer autoload.
if ( file_exists( VMFA_RULES_ENGINE_PATH . 'vendor/autoload.php' ) ) {
	require_once VMFA_RULES_ENGINE_PATH . 'vendor/autoload.php';
}

/**
 * Initialize the plugin.
 *
 * @return void
 */
function vmfa_rules_engine_init() {
	// Update checker via GitHub releases.
	VmfaRulesEngine\Update\GitHubPluginUpdater::create_with_assets(
		'https://github.com/soderlind/vmfa-rules-engine',
		__FILE__,
		'vmfa-rules-engine',
		'/vmfa-rules-engine\.zip/',
		'main'
	);

	// Load text domain.
	load_plugin_textdomain( 'vmfa-rules-engine', false, dirname( VMFA_RULES_ENGINE_BASENAME ) . '/languages' );

	// Initialize plugin components.
	VmfaRulesEngine\Plugin::get_instance();
}
add_action( 'init', 'vmfa_rules_engine_init', 20 );

/**
 * Activation hook.
 *
 * @return void
 */
function vmfa_rules_engine_activate() {
	// Set default options if not exists.
	if ( false === get_option( 'vmfa_rules_engine_rules' ) ) {
		update_option( 'vmfa_rules_engine_rules', array() );
	}
}
register_activation_hook( __FILE__, 'vmfa_rules_engine_activate' );

/**
 * Deactivation hook.
 *
 * @return void
 */
function vmfa_rules_engine_deactivate() {
	// Clean up transients if any.
	delete_transient( 'vmfa_rules_engine_preview_cache' );
}
register_deactivation_hook( __FILE__, 'vmfa_rules_engine_deactivate' );
