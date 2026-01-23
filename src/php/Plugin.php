<?php
/**
 * Main Plugin class.
 *
 * @package VmfaRulesEngine
 */

namespace VmfaRulesEngine;

use VmfaRulesEngine\Admin\SettingsPage;
use VmfaRulesEngine\REST\RulesController;
use VmfaRulesEngine\Services\RuleEvaluator;

/**
 * Plugin singleton class.
 */
class Plugin {

	/**
	 * Tab slug for registration with parent plugin.
	 */
	private const TAB_SLUG = 'rules-engine';

	/**
	 * Singleton instance.
	 *
	 * @var Plugin|null
	 */
	private static $instance = null;

	/**
	 * Rule evaluator service.
	 *
	 * @var RuleEvaluator
	 */
	private $rule_evaluator;

	/**
	 * Get singleton instance.
	 *
	 * @return Plugin
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor.
	 */
	private function __construct() {
		$this->init_services();
		$this->init_hooks();
	}

	/**
	 * Initialize services.
	 *
	 * @return void
	 */
	private function init_services() {
		$this->rule_evaluator = new RuleEvaluator();
	}

	/**
	 * Initialize hooks.
	 *
	 * @return void
	 */
	private function init_hooks() {
		// Admin hooks.
		if ( is_admin() ) {
			if ( $this->supports_parent_tabs() ) {
				// Register as a tab in the parent plugin.
				add_filter( 'vmfo_settings_tabs', array( $this, 'register_tab' ) );
				add_action( 'vmfo_settings_enqueue_scripts', array( $this, 'enqueue_tab_scripts' ), 10, 2 );
			} else {
				// Fall back to standalone menu.
				add_action( 'admin_menu', array( $this, 'register_admin_menu' ) );
				add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );
			}

			// Enqueue media upload handler on media library page.
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_media_upload_script' ) );
		}

		// REST API.
		add_action( 'rest_api_init', array( $this, 'register_rest_routes' ) );

		// Rule evaluation on upload.
		add_filter( 'wp_generate_attachment_metadata', array( $this->rule_evaluator, 'evaluate_on_upload' ), 20, 3 );

		// Protect folders with rules from deletion.
		add_filter( 'vmfo_can_delete_folder', array( $this, 'protect_folder_with_rules' ), 10, 3 );
	}

	/**
	 * Check if the parent plugin supports add-on tabs.
	 *
	 * @return bool True if parent supports tabs, false otherwise.
	 */
	private function supports_parent_tabs(): bool {
		return defined( 'VirtualMediaFolders\Settings::SUPPORTS_ADDON_TABS' )
			&& \VirtualMediaFolders\Settings::SUPPORTS_ADDON_TABS;
	}

	/**
	 * Register tab with parent plugin.
	 *
	 * @param array $tabs Existing tabs array.
	 * @return array Modified tabs array.
	 */
	public function register_tab( array $tabs ): array {
		$tabs[ self::TAB_SLUG ] = array(
			'title'    => __( 'Rules Engine', 'vmfa-rules-engine' ),
			'callback' => array( $this, 'render_tab_content' ),
		);
		return $tabs;
	}

	/**
	 * Render tab content within parent plugin's settings page.
	 *
	 * @param string $active_tab    The currently active tab slug.
	 * @param string $active_subtab The currently active subtab slug.
	 * @return void
	 */
	public function render_tab_content( string $active_tab, string $active_subtab ): void {
		?>
		<div id="vmfa-rules-engine-app"></div>
		<?php
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
		// If already blocked, don't override.
		if ( is_wp_error( $can_delete ) ) {
			return $can_delete;
		}

		$folder_id = (int) $folder_id;
		$rules     = get_option( 'vmfa_rules_engine_rules', array() );

		foreach ( $rules as $rule ) {
			if ( isset( $rule[ 'folder_id' ] ) && (int) $rule[ 'folder_id' ] === $folder_id ) {
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
	 * Enqueue scripts when Rules Engine tab is active.
	 *
	 * @param string $active_tab    The currently active tab slug.
	 * @param string $active_subtab The currently active subtab slug.
	 * @return void
	 */
	public function enqueue_tab_scripts( string $active_tab, string $active_subtab ): void {
		if ( self::TAB_SLUG !== $active_tab ) {
			return;
		}

		$this->do_enqueue_assets();
	}

	/**
	 * Register admin menu (fallback when parent doesn't support tabs).
	 *
	 * @return void
	 */
	public function register_admin_menu() {
		add_submenu_page(
			'upload.php',
			__( 'Virtual Media Folders Rules Engine', 'vmfa-rules-engine' ),
			__( 'Rules Engine', 'vmfa-rules-engine' ),
			'manage_options',
			'vmfa-rules-engine',
			array( $this, 'render_admin_page' )
		);
	}

	/**
	 * Render admin page (fallback for standalone page).
	 *
	 * @return void
	 */
	public function render_admin_page() {
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Virtual Media Folders Rules Engine', 'vmfa-rules-engine' ); ?></h1>
			<div id="vmfa-rules-engine-app"></div>
		</div>
		<?php
	}

	/**
	 * Enqueue admin assets (fallback for standalone page).
	 *
	 * @param string $hook_suffix The current admin page hook suffix.
	 * @return void
	 */
	public function enqueue_admin_assets( $hook_suffix ) {
		if ( 'media_page_vmfa-rules-engine' !== $hook_suffix ) {
			return;
		}

		$this->do_enqueue_assets();
	}

	/**
	 * Enqueue media upload script on media library page.
	 *
	 * This script refreshes the media library after uploads
	 * when the rules engine has assigned a folder.
	 *
	 * @param string $hook_suffix The current admin page hook suffix.
	 * @return void
	 */
	public function enqueue_media_upload_script( $hook_suffix ): void {
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
			$asset[ 'dependencies' ],
			$asset[ 'version' ],
			true
		);
	}

	/**
	 * Actually enqueue the scripts and styles.
	 *
	 * @return void
	 */
	private function do_enqueue_assets(): void {
		$asset_file = VMFA_RULES_ENGINE_PATH . 'build/index.asset.php';

		if ( ! file_exists( $asset_file ) ) {
			return;
		}

		$asset = require $asset_file;

		wp_enqueue_script(
			'vmfa-rules-engine-admin',
			VMFA_RULES_ENGINE_URL . 'build/index.js',
			$asset[ 'dependencies' ],
			$asset[ 'version' ],
			true
		);

		wp_set_script_translations(
			'vmfa-rules-engine-admin',
			'vmfa-rules-engine',
			VMFA_RULES_ENGINE_PATH . 'languages'
		);

		wp_enqueue_style(
			'vmfa-rules-engine-admin',
			VMFA_RULES_ENGINE_URL . 'build/index.css',
			array( 'wp-components' ),
			$asset[ 'version' ]
		);

		// Get folders from parent plugin.
		$folders = $this->get_folders();

		wp_localize_script(
			'vmfa-rules-engine-admin',
			'vmfaRulesEngine',
			array(
				'restUrl'        => rest_url( 'vmfa-rules/v1/' ),
				'nonce'          => wp_create_nonce( 'wp_rest' ),
				'folders'        => $folders,
				'conditionTypes' => $this->get_condition_types(),
				'strings'        => array(
					'saveSuccess'    => __( 'Rules saved successfully.', 'vmfa-rules-engine' ),
					'saveError'      => __( 'Failed to save rules.', 'vmfa-rules-engine' ),
					'deleteConfirm'  => __( 'Are you sure you want to delete this rule?', 'vmfa-rules-engine' ),
					'noRules'        => __( 'No rules configured yet.', 'vmfa-rules-engine' ),
					'addRule'        => __( 'Add Rule', 'vmfa-rules-engine' ),
					'editRule'       => __( 'Edit Rule', 'vmfa-rules-engine' ),
					'ruleName'       => __( 'Rule Name', 'vmfa-rules-engine' ),
					'conditions'     => __( 'Conditions', 'vmfa-rules-engine' ),
					'targetFolder'   => __( 'Target Folder', 'vmfa-rules-engine' ),
					'stopProcessing' => __( 'Stop processing after match', 'vmfa-rules-engine' ),
					'enabled'        => __( 'Enabled', 'vmfa-rules-engine' ),
					'applyToLibrary' => __( 'Apply to Library', 'vmfa-rules-engine' ),
					'dryRun'         => __( 'Scan Existing Media', 'vmfa-rules-engine' ),
					'preview'        => __( 'Preview', 'vmfa-rules-engine' ),
					'apply'          => __( 'Apply Changes', 'vmfa-rules-engine' ),
				),
			)
		);
	}

	/**
	 * Get folders from parent plugin.
	 *
	 * @return array
	 */
	private function get_folders() {
		$terms = get_terms(
			array(
				'taxonomy'   => 'vmfo_folder',
				'hide_empty' => false,
				'orderby'    => 'name',
				'order'      => 'ASC',
			)
		);

		if ( is_wp_error( $terms ) ) {
			return array();
		}

		$folders = array();
		foreach ( $terms as $term ) {
			$folders[] = array(
				'id'     => $term->term_id,
				'name'   => $term->name,
				'slug'   => $term->slug,
				'parent' => $term->parent,
			);
		}

		return $folders;
	}

	/**
	 * Get available condition types.
	 *
	 * @return array
	 */
	private function get_condition_types() {
		return array(
			array(
				'value'       => 'filename_regex',
				'label'       => __( 'Filename matches regex', 'vmfa-rules-engine' ),
				'description' => __( 'Match filename against a regular expression (e.g., ^IMG_)', 'vmfa-rules-engine' ),
				'inputType'   => 'text',
				'placeholder' => '^IMG_.*',
			),
			array(
				'value'       => 'mime_type',
				'label'       => __( 'MIME type', 'vmfa-rules-engine' ),
				'description' => __( 'Match by file MIME type', 'vmfa-rules-engine' ),
				'inputType'   => 'select',
				'options'     => array(
					array(
						'value' => 'image/*',
						'label' => __( 'Any image', 'vmfa-rules-engine' ),
					),
					array(
						'value' => 'image/jpeg',
						'label' => 'JPEG',
					),
					array(
						'value' => 'image/png',
						'label' => 'PNG',
					),
					array(
						'value' => 'image/gif',
						'label' => 'GIF',
					),
					array(
						'value' => 'image/webp',
						'label' => 'WebP',
					),
					array(
						'value' => 'image/svg+xml',
						'label' => 'SVG',
					),
					array(
						'value' => 'video/*',
						'label' => __( 'Any video', 'vmfa-rules-engine' ),
					),
					array(
						'value' => 'audio/*',
						'label' => __( 'Any audio', 'vmfa-rules-engine' ),
					),
					array(
						'value' => 'application/pdf',
						'label' => 'PDF',
					),
					array(
						'value' => 'application/*',
						'label' => __( 'Any document', 'vmfa-rules-engine' ),
					),
				),
			),
			array(
				'value'       => 'dimensions',
				'label'       => __( 'Image dimensions', 'vmfa-rules-engine' ),
				'description' => __( 'Match by image width/height', 'vmfa-rules-engine' ),
				'inputType'   => 'dimensions',
			),
			array(
				'value'       => 'file_size',
				'label'       => __( 'File size', 'vmfa-rules-engine' ),
				'description' => __( 'Match files by size (supports >, <, =, >=, <= operators)', 'vmfa-rules-engine' ),
				'inputType'   => 'filesize',
			),
			array(
				'value'       => 'exif_camera',
				'label'       => __( 'EXIF camera model', 'vmfa-rules-engine' ),
				'description' => __( 'Partial match by camera model from EXIF data', 'vmfa-rules-engine' ),
				'inputType'   => 'text',
				'placeholder' => 'iPhone 15 Pro',
			),
			array(
				'value'       => 'exif_date',
				'label'       => __( 'EXIF date taken', 'vmfa-rules-engine' ),
				'description' => __( 'Match photos taken within a date range', 'vmfa-rules-engine' ),
				'inputType'   => 'daterange',
			),
			array(
				'value'       => 'author',
				'label'       => __( 'Upload author', 'vmfa-rules-engine' ),
				'description' => __( 'Match by user who uploaded the file', 'vmfa-rules-engine' ),
				'inputType'   => 'user',
			),
			array(
				'value'       => 'iptc_keywords',
				'label'       => __( 'IPTC keywords', 'vmfa-rules-engine' ),
				'description' => __( 'Partial match by embedded IPTC keywords (comma-separated)', 'vmfa-rules-engine' ),
				'inputType'   => 'text',
				'placeholder' => 'product, marketing',
			),
		);
	}

	/**
	 * Register REST routes.
	 *
	 * @return void
	 */
	public function register_rest_routes() {
		$controller = new RulesController();
		$controller->register_routes();
	}
}
