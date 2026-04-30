<?php
/**
 * EXIF Focal Length Matcher.
 *
 * @package VmfaRulesEngine
 */

namespace VmfaRulesEngine\Conditions;

defined( 'ABSPATH' ) || exit;

/**
 * Matches EXIF focal length in millimetres.
 */
class ExifFocalLengthMatcher implements MatcherInterface {

	use NumericCompareTrait;

	/**
	 * Check if the EXIF focal length matches the condition.
	 *
	 * @param int   $attachment_id Attachment ID.
	 * @param array $metadata      Attachment metadata.
	 * @param array $params        Condition parameters.
	 * @return bool True if focal length matches.
	 */
	public function matches( $attachment_id, $metadata, $params ): bool {
		if ( ! isset( $params['value'] ) || '' === (string) $params['value'] ) {
			return false;
		}

		$focal_length = $metadata['image_meta']['focal_length'] ?? '';

		if ( '' === (string) $focal_length ) {
			return false;
		}

		$operator  = $params['operator'] ?? 'eq';
		$value     = (float) $params['value'];
		$value_end = isset( $params['value_end'] ) ? (float) $params['value_end'] : 0.0;

		return $this->compare_numeric( (float) $focal_length, $operator, $value, $value_end );
	}

	/**
	 * Get the condition type identifier.
	 *
	 * @return string
	 */
	public function get_type(): string {
		return 'exif_focal_length';
	}
}
