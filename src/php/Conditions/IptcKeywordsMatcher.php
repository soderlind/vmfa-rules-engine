<?php
/**
 * IPTC Keywords Matcher.
 *
 * @package VmfaRulesEngine
 */

namespace VmfaRulesEngine\Conditions;

/**
 * Matches IPTC keywords embedded in the image.
 */
class IptcKeywordsMatcher implements MatcherInterface {

	/**
	 * Check if any IPTC keywords match.
	 *
	 * @param int   $attachment_id Attachment ID.
	 * @param array $metadata      Attachment metadata.
	 * @param array $params        Condition parameters with 'value' as comma-separated keywords.
	 * @return bool True if any keyword matches.
	 */
	public function matches( $attachment_id, $metadata, $params ) {
		if ( empty( $params['value'] ) ) {
			return false;
		}

		$keywords = $metadata['image_meta']['keywords'] ?? array();

		if ( empty( $keywords ) || ! is_array( $keywords ) ) {
			return false;
		}

		// Parse target keywords (comma-separated).
		$target_keywords = array_map(
			'trim',
			explode( ',', strtolower( $params['value'] ) )
		);

		// Lowercase all actual keywords for comparison.
		$keywords = array_map( 'strtolower', $keywords );

		// Check if any target keyword is found.
		foreach ( $target_keywords as $target ) {
			if ( empty( $target ) ) {
				continue;
			}

			foreach ( $keywords as $keyword ) {
				if ( strpos( $keyword, $target ) !== false ) {
					return true;
				}
			}
		}

		return false;
	}

	/**
	 * Get the condition type identifier.
	 *
	 * @return string
	 */
	public function get_type() {
		return 'iptc_keywords';
	}
}
