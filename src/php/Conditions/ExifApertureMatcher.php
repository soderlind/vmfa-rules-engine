<?php
/**
 * EXIF Aperture Matcher.
 *
 * @package VmfaRulesEngine
 */

namespace VmfaRulesEngine\Conditions;

defined( 'ABSPATH' ) || exit;

/**
 * Matches EXIF aperture (f-number) value.
 */
class ExifApertureMatcher implements MatcherInterface {

	use NumericCompareTrait;

	/**
	 * Check if the EXIF aperture matches the condition.
	 *
	 * @param int   $attachment_id Attachment ID.
	 * @param array $metadata      Attachment metadata.
	 * @param array $params        Condition parameters.
	 * @return bool True if aperture matches.
	 */
	public function matches( $attachment_id, $metadata, $params ): bool {
		if ( ! isset( $params['value'] ) || '' === (string) $params['value'] ) {
			return false;
		}

		$aperture = $metadata['image_meta']['aperture'] ?? '';

		if ( '' === (string) $aperture ) {
			return false;
		}

		$operator  = $params['operator'] ?? 'eq';
		$value     = (float) $params['value'];
		$value_end = isset( $params['value_end'] ) ? (float) $params['value_end'] : 0.0;

		return $this->compare_numeric( (float) $aperture, $operator, $value, $value_end );
	}

	/**
	 * Get the condition type identifier.
	 *
	 * @return string
	 */
	public function get_type(): string {
		return 'exif_aperture';
	}
}
