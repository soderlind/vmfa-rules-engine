<?php
/**
 * EXIF ISO Matcher.
 *
 * @package VmfaRulesEngine
 */

namespace VmfaRulesEngine\Conditions;

defined( 'ABSPATH' ) || exit;

/**
 * Matches EXIF ISO sensitivity value.
 */
class ExifIsoMatcher implements MatcherInterface {

	use NumericCompareTrait;

	/**
	 * Check if the EXIF ISO value matches the condition.
	 *
	 * @param int   $attachment_id Attachment ID.
	 * @param array $metadata      Attachment metadata.
	 * @param array $params        Condition parameters.
	 * @return bool True if ISO matches.
	 */
	public function matches( $attachment_id, $metadata, $params ): bool {
		if ( ! isset( $params['value'] ) || '' === (string) $params['value'] ) {
			return false;
		}

		$iso = $metadata['image_meta']['iso'] ?? '';

		if ( '' === (string) $iso ) {
			return false;
		}

		$operator  = $params['operator'] ?? 'lte';
		$value     = (float) $params['value'];
		$value_end = isset( $params['value_end'] ) ? (float) $params['value_end'] : 0.0;

		return $this->compare_numeric( (float) $iso, $operator, $value, $value_end );
	}

	/**
	 * Get the condition type identifier.
	 *
	 * @return string
	 */
	public function get_type(): string {
		return 'exif_iso';
	}
}
