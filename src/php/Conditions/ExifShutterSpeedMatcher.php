<?php
/**
 * EXIF Shutter Speed Matcher.
 *
 * @package VmfaRulesEngine
 */

namespace VmfaRulesEngine\Conditions;

defined( 'ABSPATH' ) || exit;

/**
 * Matches EXIF shutter speed (exposure time).
 *
 * Accepts values as fractions ("1/1000") or decimals ("0.001").
 * WordPress stores shutter_speed as a decimal float string.
 */
class ExifShutterSpeedMatcher implements MatcherInterface {

	use NumericCompareTrait;

	/**
	 * Check if the EXIF shutter speed matches the condition.
	 *
	 * @param int   $attachment_id Attachment ID.
	 * @param array $metadata      Attachment metadata.
	 * @param array $params        Condition parameters.
	 * @return bool True if shutter speed matches.
	 */
	public function matches( $attachment_id, $metadata, $params ): bool {
		if ( ! isset( $params['value'] ) || '' === (string) $params['value'] ) {
			return false;
		}

		$shutter_speed = $metadata['image_meta']['shutter_speed'] ?? '';

		if ( '' === (string) $shutter_speed ) {
			return false;
		}

		$operator  = $params['operator'] ?? 'lte';
		$value     = $this->parse_shutter_speed( (string) $params['value'] );
		$value_end = isset( $params['value_end'] ) ? $this->parse_shutter_speed( (string) $params['value_end'] ) : 0.0;

		if ( null === $value ) {
			return false;
		}

		return $this->compare_numeric( (float) $shutter_speed, $operator, $value, $value_end ?? 0.0 );
	}

	/**
	 * Parse a shutter speed string (fraction or decimal) into a float.
	 *
	 * @param string $value e.g. "1/1000", "1/500", "0.001", "2".
	 * @return float|null Parsed value, or null on invalid input.
	 */
	private function parse_shutter_speed( string $value ): ?float {
		$value = trim( $value );

		if ( '' === $value ) {
			return null;
		}

		// Handle fractional notation like "1/1000".
		if ( str_contains( $value, '/' ) ) {
			[ $numerator, $denominator ] = explode( '/', $value, 2 );
			$denominator = (float) $denominator;
			if ( 0.0 === $denominator ) {
				return null;
			}
			return (float) $numerator / $denominator;
		}

		$float = (float) $value;
		return $float >= 0 ? $float : null;
	}

	/**
	 * Get the condition type identifier.
	 *
	 * @return string
	 */
	public function get_type(): string {
		return 'exif_shutter_speed';
	}
}
