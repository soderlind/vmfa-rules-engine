<?php
/**
 * EXIF Date Matcher.
 *
 * @package VmfaRulesEngine
 */

namespace VmfaRulesEngine\Conditions;

defined( 'ABSPATH' ) || exit;

/**
 * Matches EXIF date taken.
 */
class ExifDateMatcher implements MatcherInterface {

	/**
	 * Check if the EXIF date matches the condition.
	 *
	 * @param int   $attachment_id Attachment ID.
	 * @param array $metadata      Attachment metadata.
	 * @param array $params        Condition parameters.
	 * @return bool True if date matches.
	 */
	public function matches( $attachment_id, $metadata, $params ) {
		if ( empty( $params[ 'value' ] ) ) {
			return false;
		}

		$created_timestamp = $metadata[ 'image_meta' ][ 'created_timestamp' ] ?? 0;

		if ( empty( $created_timestamp ) ) {
			return false;
		}

		$operator  = $params[ 'operator' ] ?? 'after';
		$value     = strtotime( $params[ 'value' ] );
		$value_end = ! empty( $params[ 'value_end' ] ) ? strtotime( $params[ 'value_end' ] ) : 0;

		if ( false === $value ) {
			return false;
		}

		return $this->compare( $created_timestamp, $operator, $value, $value_end );
	}

	/**
	 * Compare timestamps based on operator.
	 *
	 * @param int    $actual    Actual timestamp.
	 * @param string $operator  Comparison operator.
	 * @param int    $value     First comparison timestamp.
	 * @param int    $value_end Second comparison timestamp (for between).
	 * @return bool Comparison result.
	 */
	private function compare( $actual, $operator, $value, $value_end ) {
		switch ( $operator ) {
			case 'after':
				return $actual > $value;
			case 'before':
				return $actual < $value;
			case 'on':
				// Same day comparison.
				return gmdate( 'Y-m-d', $actual ) === gmdate( 'Y-m-d', $value );
			case 'between':
				return $actual >= $value && $actual <= $value_end;
			case 'year':
				// Match specific year.
				return gmdate( 'Y', $actual ) === gmdate( 'Y', $value );
			case 'month':
				// Match specific month and year.
				return gmdate( 'Y-m', $actual ) === gmdate( 'Y-m', $value );
			default:
				return false;
		}
	}

	/**
	 * Get the condition type identifier.
	 *
	 * @return string
	 */
	public function get_type() {
		return 'exif_date';
	}
}
