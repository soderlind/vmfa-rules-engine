<?php
/**
 * Dimensions Matcher.
 *
 * @package VmfaRulesEngine
 */

namespace VmfaRulesEngine\Conditions;

defined( 'ABSPATH' ) || exit;

/**
 * Matches image dimensions (width/height).
 */
class DimensionsMatcher implements MatcherInterface {

	use NumericCompareTrait;

	/**
	 * Check if the attachment dimensions match the condition.
	 *
	 * @param int   $attachment_id Attachment ID.
	 * @param array $metadata      Attachment metadata.
	 * @param array $params        Condition parameters.
	 * @return bool True if dimensions match.
	 */
	public function matches( $attachment_id, $metadata, $params ) {
		if ( ! isset( $params[ 'value' ] ) || ! isset( $params[ 'dimension' ] ) ) {
			return false;
		}

		$dimension = $params[ 'dimension' ] ?? 'width';
		$operator  = $params[ 'operator' ] ?? 'gt';
		$value     = absint( $params[ 'value' ] );
		$value_end = isset( $params[ 'value_end' ] ) ? absint( $params[ 'value_end' ] ) : 0;

		// Get the actual dimension value.
		$actual = 0;
		if ( 'width' === $dimension && isset( $metadata[ 'width' ] ) ) {
			$actual = absint( $metadata[ 'width' ] );
		} elseif ( 'height' === $dimension && isset( $metadata[ 'height' ] ) ) {
			$actual = absint( $metadata[ 'height' ] );
		} elseif ( 'both' === $dimension ) {
			// For 'both', check if either dimension meets the criteria.
			$width  = isset( $metadata[ 'width' ] ) ? absint( $metadata[ 'width' ] ) : 0;
			$height = isset( $metadata[ 'height' ] ) ? absint( $metadata[ 'height' ] ) : 0;
			$actual = min( $width, $height ); // Use the smaller dimension.
		}

		if ( 0 === $actual ) {
			return false;
		}

		return $this->compare_numeric( (float) $actual, $operator, (float) $value, (float) $value_end );
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
