<?php
/**
 * Dimensions Matcher.
 *
 * @package VmfaRulesEngine
 */

namespace VmfaRulesEngine\Conditions;

/**
 * Matches image dimensions (width/height).
 */
class DimensionsMatcher implements MatcherInterface {

	/**
	 * Check if the attachment dimensions match the condition.
	 *
	 * @param int   $attachment_id Attachment ID.
	 * @param array $metadata      Attachment metadata.
	 * @param array $params        Condition parameters.
	 * @return bool True if dimensions match.
	 */
	public function matches( $attachment_id, $metadata, $params ) {
		if ( ! isset( $params['value'] ) || ! isset( $params['dimension'] ) ) {
			return false;
		}

		$dimension = $params['dimension'] ?? 'width';
		$operator  = $params['operator'] ?? 'gt';
		$value     = absint( $params['value'] );
		$value_end = isset( $params['value_end'] ) ? absint( $params['value_end'] ) : 0;

		// Get the actual dimension value.
		$actual = 0;
		if ( 'width' === $dimension && isset( $metadata['width'] ) ) {
			$actual = absint( $metadata['width'] );
		} elseif ( 'height' === $dimension && isset( $metadata['height'] ) ) {
			$actual = absint( $metadata['height'] );
		} elseif ( 'both' === $dimension ) {
			// For 'both', check if either dimension meets the criteria.
			$width  = isset( $metadata['width'] ) ? absint( $metadata['width'] ) : 0;
			$height = isset( $metadata['height'] ) ? absint( $metadata['height'] ) : 0;
			$actual = min( $width, $height ); // Use the smaller dimension.
		}

		if ( 0 === $actual ) {
			return false;
		}

		return $this->compare( $actual, $operator, $value, $value_end );
	}

	/**
	 * Compare values based on operator.
	 *
	 * @param int    $actual    Actual value.
	 * @param string $operator  Comparison operator.
	 * @param int    $value     First comparison value.
	 * @param int    $value_end Second comparison value (for between).
	 * @return bool Comparison result.
	 */
	private function compare( $actual, $operator, $value, $value_end ) {
		switch ( $operator ) {
			case 'gt':
				return $actual > $value;
			case 'gte':
				return $actual >= $value;
			case 'lt':
				return $actual < $value;
			case 'lte':
				return $actual <= $value;
			case 'eq':
				return $actual === $value;
			case 'between':
				return $actual >= $value && $actual <= $value_end;
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
		return 'dimensions';
	}
}
