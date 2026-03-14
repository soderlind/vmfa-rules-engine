<?php
/**
 * Main Plugin class.
 *
 * @package VmfaRulesEngine
 */

declare(strict_types=1);

namespace VmfaRulesEngine;

defined( 'ABSPATH' ) || exit;

use VirtualMediaFolders\Addon\AbstractPlugin;
use VmfaRulesEngine\Admin\SettingsTab;
use VmfaRulesEngine\REST\RulesController;
use VmfaRulesEngine\Services\RuleEvaluator;

/**
 * Plugin bootstrap class.
 */
final class Plugin extends AbstractPlugin {

	/**
	 * Rule evaluator service.
	 *
	 * @var RuleEvaluator|null
	 */
	private ?RuleEvaluator $rule_evaluator = null;

	/**
	 * Settings tab instance.
	 *
	 * @var SettingsTab|null
	 */
	private ?SettingsTab $settings_tab = null;

	/** @inheritDoc */
	protected function get_text_domain(): string {
		return 'vmfa-rules-engine';
	}

	/** @inheritDoc */
	protected function get_plugin_file(): string {
		return VMFA_RULES_ENGINE_FILE;
	}

	/**
	 * Initialize services.
	 *
	 * @return void
	 */
	protected function init_services(): void {
		$this->rule_evaluator = new RuleEvaluator();
		$this->settings_tab   = new SettingsTab();
	}

	/**
	 * Initialize hooks.
	 *
	 * @return void
	 */
	protected function init_hooks(): void {
		// Admin hooks.
		if ( is_admin() ) {
			if ( $this->supports_parent_tabs() ) {
				add_filter( 'vmfo_settings_tabs', array( $this->settings_tab, 'register_tab' ) );
				add_action( 'vmfo_settings_enqueue_scripts', array( $this->settings_tab, 'enqueue_tab_scripts' ), 10, 2 );
			} else {
				add_action( 'admin_menu', array( $this->settings_tab, 'register_admin_menu' ) );
				add_action( 'admin_enqueue_scripts', array( $this->settings_tab, 'enqueue_admin_assets' ) );
			}

			// Enqueue media upload handler on media library page.
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_media_upload_script' ) );
		}

		// REST API.
		add_action( 'rest_api_init', array( $this, 'register_rest_routes' ) );

		// Rule evaluation on upload.
		add_filter( 'vmfo_upload_folder', array( $this->rule_evaluator, 'filter_upload_folder' ), 10, 3 );

		// Protect folders with rules from deletion.
		add_filter( 'vmfo_can_delete_folder', array( $this, 'protect_folder_with_rules' ), 10, 3 );
	}

	/**
	 * Protect folders that have rules from being deleted.
	 *
	 * @param bool|\WP_Error $can_delete Whether the folder can be deleted.
	 * @param int            $folder_id  The folder ID.
	 * @param \WP_Term       $term       The folder term object.
	 * @return bool|\WP_Error True if can delete, WP_Error if protected.
	 */
	public function protect_folder_with_rules( $can_delete, $folder_id, $term ) {
		if ( is_wp_error( $can_delete ) ) {
			return $can_delete;
		}

		$folder_id = (int) $folder_id;
		$rules     = get_option( 'vmfa_rules_engine_rules', array() );

		foreach ( $rules as $rule ) {
			if ( isset( $rule['folder_id'] ) && (int) $rule['folder_id'] === $folder_id ) {
				return new \WP_Error(
					'folder_has_rules',
					sprintf(
						/* translators: %s: folder name */
						__( 'Cannot delete folder "%s": it has active rules. Remove the rules first.', 'vmfa-rules-engine' ),
						$term->name
					),
					array( 'status' => 400 )
				);
			}
		}

		return $can_delete;
	}

	/**
	 * Enqueue media upload script on media library page.
	 *
	 * @param string $hook_suffix The current admin page hook suffix.
	 * @return void
	 */
	public function enqueue_media_upload_script( string $hook_suffix ): void {
		if ( 'upload.php' !== $hook_suffix ) {
			return;
		}

		$asset_file = VMFA_RULES_ENGINE_PATH . 'build/media-upload.asset.php';

		if ( ! file_exists( $asset_file ) ) {
			return;
		}

		$asset = require $asset_file;

		wp_enqueue_script(
			'vmfa-rules-engine-media-upload',
			VMFA_RULES_ENGINE_URL . 'build/media-upload.js',
			$asset['dependencies'],
			$asset['version'],
			true
		);
	}

	/**
	 * Register REST routes.
	 *
	 * @return void
	 */
	public function register_rest_routes(): void {
		$controller = new RulesController();
		$controller->register_routes();
	}
}
