<?php
/**
 * EXIF Orientation Matcher.
 *
 * @package VmfaRulesEngine
 */

namespace VmfaRulesEngine\Conditions;

defined( 'ABSPATH' ) || exit;

/**
 * Matches EXIF orientation value (1–8 per EXIF spec).
 */
class ExifOrientationMatcher implements MatcherInterface {

	/**
	 * Check if the EXIF orientation matches the condition.
	 *
	 * @param int   $attachment_id Attachment ID.
	 * @param array $metadata      Attachment metadata.
	 * @param array $params        Condition parameters. 'value' is the target orientation int.
	 * @return bool True if orientation matches.
	 */
	public function matches( $attachment_id, $metadata, $params ): bool {
		if ( ! isset( $params['value'] ) || '' === (string) $params['value'] ) {
			return false;
		}

		$orientation = $metadata['image_meta']['orientation'] ?? '';

		if ( '' === (string) $orientation ) {
			return false;
		}

		return (int) $orientation === (int) $params['value'];
	}

	/**
	 * Get the condition type identifier.
	 *
	 * @return string
	 */
	public function get_type(): string {
		return 'exif_orientation';
	}
}
