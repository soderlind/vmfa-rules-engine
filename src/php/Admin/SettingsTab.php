<?php
/**
 * Settings Tab for Rules Engine.
 *
 * @package VmfaRulesEngine
 */

declare(strict_types=1);

namespace VmfaRulesEngine\Admin;

defined( 'ABSPATH' ) || exit;

use VirtualMediaFolders\Addon\AbstractSettingsTab;

/**
 * Registers and renders the Rules Engine settings tab.
 */
class SettingsTab extends AbstractSettingsTab {

	/** @inheritDoc */
	protected function get_tab_slug(): string {
		return 'rules-engine';
	}

	/** @inheritDoc */
	protected function get_tab_label(): string {
		return __( 'Rules Engine', 'vmfa-rules-engine' );
	}

	/** @inheritDoc */
	protected function get_text_domain(): string {
		return 'vmfa-rules-engine';
	}

	/** @inheritDoc */
	protected function get_build_path(): string {
		return VMFA_RULES_ENGINE_PATH . 'build/';
	}

	/** @inheritDoc */
	protected function get_build_url(): string {
		return VMFA_RULES_ENGINE_URL . 'build/';
	}

	/** @inheritDoc */
	protected function get_languages_path(): string {
		return VMFA_RULES_ENGINE_PATH . 'languages';
	}

	/** @inheritDoc */
	protected function get_plugin_version(): string {
		return VMFA_RULES_ENGINE_VERSION;
	}

	/** @inheritDoc */
	protected function get_localized_name(): string {
		return 'vmfaRulesEngine';
	}

	/** @inheritDoc */
	protected function get_localized_data(): array {
		return array(
			'restUrl'        => rest_url( 'vmfa-rules/v1/' ),
			'nonce'          => wp_create_nonce( 'wp_rest' ),
			'folders'        => $this->get_folders(),
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
		);
	}

	/** @inheritDoc */
	public function render_tab( string $active_tab, string $active_subtab ): void {
		?>
		<div id="vmfa-rules-engine-app"></div>
		<?php
	}

	/**
	 * Get folders from parent plugin.
	 *
	 * @return array
	 */
	private function get_folders(): array {
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
	private function get_condition_types(): array {
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
					array( 'value' => 'image/*', 'label' => __( 'Any image', 'vmfa-rules-engine' ) ),
					array( 'value' => 'image/jpeg', 'label' => 'JPEG' ),
					array( 'value' => 'image/png', 'label' => 'PNG' ),
					array( 'value' => 'image/gif', 'label' => 'GIF' ),
					array( 'value' => 'image/webp', 'label' => 'WebP' ),
					array( 'value' => 'image/svg+xml', 'label' => 'SVG' ),
					array( 'value' => 'video/*', 'label' => __( 'Any video', 'vmfa-rules-engine' ) ),
					array( 'value' => 'audio/*', 'label' => __( 'Any audio', 'vmfa-rules-engine' ) ),
					array( 'value' => 'application/pdf', 'label' => 'PDF' ),
					array( 'value' => 'application/*', 'label' => __( 'Any document', 'vmfa-rules-engine' ) ),
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
}
