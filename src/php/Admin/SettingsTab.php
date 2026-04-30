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
			// ── General ──────────────────────────────────────────────────────────────
			array(
				'value'       => 'filename_regex',
				'label'       => __( 'Filename matches regex', 'vmfa-rules-engine' ),
				'description' => __( 'Match filename against a regular expression (e.g., ^IMG_)', 'vmfa-rules-engine' ),
				'group'       => 'general',
				'inputType'   => 'text',
				'placeholder' => '^IMG_.*',
			),
			array(
				'value'       => 'mime_type',
				'label'       => __( 'MIME type', 'vmfa-rules-engine' ),
				'description' => __( 'Match by file MIME type', 'vmfa-rules-engine' ),
				'group'       => 'general',
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
				'group'       => 'general',
				'inputType'   => 'dimensions',
			),
			array(
				'value'       => 'file_size',
				'label'       => __( 'File size', 'vmfa-rules-engine' ),
				'description' => __( 'Match files by size (supports >, <, =, >=, <= operators)', 'vmfa-rules-engine' ),
				'group'       => 'general',
				'inputType'   => 'filesize',
			),
			array(
				'value'       => 'author',
				'label'       => __( 'Upload author', 'vmfa-rules-engine' ),
				'description' => __( 'Match by user who uploaded the file', 'vmfa-rules-engine' ),
				'group'       => 'general',
				'inputType'   => 'user',
			),
			// ── EXIF ─────────────────────────────────────────────────────────────────
			array(
				'value'       => 'exif_camera',
				'label'       => __( 'Camera model', 'vmfa-rules-engine' ),
				'description' => __( 'Partial match by camera make/model from EXIF data', 'vmfa-rules-engine' ),
				'group'       => 'exif',
				'inputType'   => 'text',
				'placeholder' => 'iPhone 15 Pro',
			),
			array(
				'value'       => 'exif_date',
				'label'       => __( 'Date taken', 'vmfa-rules-engine' ),
				'description' => __( 'Match photos taken within a date range', 'vmfa-rules-engine' ),
				'group'       => 'exif',
				'inputType'   => 'daterange',
			),
			array(
				'value'       => 'exif_aperture',
				'label'       => __( 'Aperture (f-number)', 'vmfa-rules-engine' ),
				'description' => __( 'Match by lens aperture from EXIF data (e.g. f/2.8)', 'vmfa-rules-engine' ),
				'group'       => 'exif',
				'inputType'   => 'numeric',
				'unit'        => 'f/',
				'step'        => 0.1,
				'placeholder' => '2.8',
			),
			array(
				'value'       => 'exif_focal_length',
				'label'       => __( 'Focal length', 'vmfa-rules-engine' ),
				'description' => __( 'Match by focal length in millimetres from EXIF data', 'vmfa-rules-engine' ),
				'group'       => 'exif',
				'inputType'   => 'numeric',
				'unit'        => 'mm',
				'step'        => 1,
				'placeholder' => '50',
			),
			array(
				'value'       => 'exif_iso',
				'label'       => __( 'ISO sensitivity', 'vmfa-rules-engine' ),
				'description' => __( 'Match by ISO sensitivity value from EXIF data', 'vmfa-rules-engine' ),
				'group'       => 'exif',
				'inputType'   => 'numeric',
				'unit'        => 'ISO',
				'unitPosition' => 'before',
				'step'        => 100,
				'placeholder' => '400',
			),
			array(
				'value'       => 'exif_shutter_speed',
				'label'       => __( 'Shutter speed', 'vmfa-rules-engine' ),
				'description' => __( 'Match by shutter speed (exposure time). Enter as fraction (1/1000) or decimal (0.001).', 'vmfa-rules-engine' ),
				'group'       => 'exif',
				'inputType'   => 'shutter',
				'unit'        => 's',
				'placeholder' => '1/1000',
			),
			array(
				'value'       => 'exif_orientation',
				'label'       => __( 'Orientation', 'vmfa-rules-engine' ),
				'description' => __( 'Match by EXIF orientation tag', 'vmfa-rules-engine' ),
				'group'       => 'exif',
				'inputType'   => 'select',
				'options'     => array(
					array( 'value' => '1', 'label' => __( '1 — Normal (0°)', 'vmfa-rules-engine' ) ),
					array( 'value' => '2', 'label' => __( '2 — Flipped horizontal', 'vmfa-rules-engine' ) ),
					array( 'value' => '3', 'label' => __( '3 — Rotated 180°', 'vmfa-rules-engine' ) ),
					array( 'value' => '4', 'label' => __( '4 — Flipped vertical', 'vmfa-rules-engine' ) ),
					array( 'value' => '5', 'label' => __( '5 — Transposed (90° CW + flip)', 'vmfa-rules-engine' ) ),
					array( 'value' => '6', 'label' => __( '6 — Rotated 90° CW', 'vmfa-rules-engine' ) ),
					array( 'value' => '7', 'label' => __( '7 — Transverse (90° CCW + flip)', 'vmfa-rules-engine' ) ),
					array( 'value' => '8', 'label' => __( '8 — Rotated 90° CCW', 'vmfa-rules-engine' ) ),
				),
			),
			// ── IPTC / XMP ───────────────────────────────────────────────────────────
			array(
				'value'       => 'iptc_keywords',
				'label'       => __( 'Keywords', 'vmfa-rules-engine' ),
				'description' => __( 'Partial match by embedded IPTC keywords (comma-separated)', 'vmfa-rules-engine' ),
				'group'       => 'iptc',
				'inputType'   => 'text',
				'placeholder' => 'product, marketing',
			),
			array(
				'value'       => 'iptc_credit',
				'label'       => __( 'Credit', 'vmfa-rules-engine' ),
				'description' => __( 'Partial match by IPTC credit field', 'vmfa-rules-engine' ),
				'group'       => 'iptc',
				'inputType'   => 'text',
				'placeholder' => 'Reuters',
			),
			array(
				'value'       => 'iptc_caption',
				'label'       => __( 'Caption / Description', 'vmfa-rules-engine' ),
				'description' => __( 'Partial match by IPTC caption or description field', 'vmfa-rules-engine' ),
				'group'       => 'iptc',
				'inputType'   => 'text',
				'placeholder' => 'Partial caption text',
			),
			array(
				'value'       => 'iptc_copyright',
				'label'       => __( 'Copyright', 'vmfa-rules-engine' ),
				'description' => __( 'Partial match by IPTC copyright field', 'vmfa-rules-engine' ),
				'group'       => 'iptc',
				'inputType'   => 'text',
				'placeholder' => '© 2025',
			),
			array(
				'value'       => 'iptc_title',
				'label'       => __( 'Title / Object name', 'vmfa-rules-engine' ),
				'description' => __( 'Partial match by IPTC title or object name field', 'vmfa-rules-engine' ),
				'group'       => 'iptc',
				'inputType'   => 'text',
				'placeholder' => 'Photo title',
			),
		);
	}
}
