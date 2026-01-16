<?php
/**
 * EXIF Camera Matcher.
 *
 * @package VmfaRulesEngine
 */

namespace VmfaRulesEngine\Conditions;

/**
 * Matches EXIF camera model.
 */
class ExifCameraMatcher implements MatcherInterface {

	/**
	 * Check if the EXIF camera model matches.
	 *
	 * @param int   $attachment_id Attachment ID.
	 * @param array $metadata      Attachment metadata.
	 * @param array $params        Condition parameters with 'value' as camera model.
	 * @return bool True if camera model matches.
	 */
	public function matches( $attachment_id, $metadata, $params ) {
		if ( empty( $params['value'] ) ) {
			return false;
		}

		$camera = $metadata['image_meta']['camera'] ?? '';

		if ( empty( $camera ) ) {
			return false;
		}

		// Case-insensitive partial match.
		return stripos( $camera, $params['value'] ) !== false;
	}

	/**
	 * Get the condition type identifier.
	 *
	 * @return string
	 */
	public function get_type() {
		return 'exif_camera';
	}
}
