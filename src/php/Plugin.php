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
			add_action( 'admin_menu', array( $this, 'register_admin_menu' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );
		}

		// REST API.
		add_action( 'rest_api_init', array( $this, 'register_rest_routes' ) );

		// Rule evaluation on upload.
		add_filter( 'wp_generate_attachment_metadata', array( $this->rule_evaluator, 'evaluate_on_upload' ), 20, 3 );
	}

	/**
	 * Register admin menu.
	 *
	 * @return void
	 */
	public function register_admin_menu() {
		add_submenu_page(
			'upload.php',
			__( 'Rules Engine', 'vmfa-rules-engine' ),
			__( 'Rules Engine', 'vmfa-rules-engine' ),
			'manage_options',
			'vmfa-rules-engine',
			array( $this, 'render_admin_page' )
		);
	}

	/**
	 * Render admin page.
	 *
	 * @return void
	 */
	public function render_admin_page() {
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'VMFA Rules Engine', 'vmfa-rules-engine' ); ?></h1>
			<div id="vmfa-rules-engine-app"></div>
		</div>
		<?php
	}

	/**
	 * Enqueue admin assets.
	 *
	 * @param string $hook_suffix The current admin page hook suffix.
	 * @return void
	 */
	public function enqueue_admin_assets( $hook_suffix ) {
		if ( 'media_page_vmfa-rules-engine' !== $hook_suffix ) {
			return;
		}

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
					'dryRun'         => __( 'Dry Run (Preview)', 'vmfa-rules-engine' ),
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
				'description' => __( 'Match by file size in KB', 'vmfa-rules-engine' ),
				'inputType'   => 'filesize',
			),
			array(
				'value'       => 'exif_camera',
				'label'       => __( 'EXIF camera model', 'vmfa-rules-engine' ),
				'description' => __( 'Match by camera model from EXIF data', 'vmfa-rules-engine' ),
				'inputType'   => 'text',
				'placeholder' => 'iPhone 15 Pro',
			),
			array(
				'value'       => 'exif_date',
				'label'       => __( 'EXIF date taken', 'vmfa-rules-engine' ),
				'description' => __( 'Match by photo capture date', 'vmfa-rules-engine' ),
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
				'description' => __( 'Match by embedded IPTC keywords', 'vmfa-rules-engine' ),
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
