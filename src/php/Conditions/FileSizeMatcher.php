<?php
/**
 * File Size Matcher.
 *
 * @package VmfaRulesEngine
 */

namespace VmfaRulesEngine\Conditions;

defined( 'ABSPATH' ) || exit;

/**
 * Matches file size.
 */
class FileSizeMatcher implements MatcherInterface {

	/**
	 * Check if the file size matches the condition.
	 *
	 * @param int   $attachment_id Attachment ID.
	 * @param array $metadata      Attachment metadata.
	 * @param array $params        Condition parameters.
	 * @return bool True if file size matches.
	 */
	public function matches( $attachment_id, $metadata, $params ) {
		if ( ! isset( $params[ 'value' ] ) ) {
			return false;
		}

		$operator  = $params[ 'operator' ] ?? 'gt';
		$value     = absint( $params[ 'value' ] ) * 1024; // Convert KB to bytes.
		$value_end = isset( $params[ 'value_end' ] ) ? absint( $params[ 'value_end' ] ) * 1024 : 0;

		// Get file size from metadata or filesystem.
		$actual = 0;
		if ( isset( $metadata[ 'filesize' ] ) ) {
			$actual = absint( $metadata[ 'filesize' ] );
		} else {
			$file_path = get_attached_file( $attachment_id );
			if ( $file_path && file_exists( $file_path ) ) {
				$actual = filesize( $file_path );
			}
		}

		if ( 0 === $actual ) {
			return false;
		}

		return $this->compare( $actual, $operator, $value, $value_end );
	}

	/**
	 * Compare values based on operator.
	 *
	 * @param int    $actual    Actual value in bytes.
	 * @param string $operator  Comparison operator.
	 * @param int    $value     First comparison value in bytes.
	 * @param int    $value_end Second comparison value in bytes (for between).
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
		return 'file_size';
	}
}
