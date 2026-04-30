<?php
/**
 * IPTC Copyright Matcher.
 *
 * @package VmfaRulesEngine
 */

namespace VmfaRulesEngine\Conditions;

defined( 'ABSPATH' ) || exit;

/**
 * Matches IPTC copyright field.
 */
class IptcCopyrightMatcher implements MatcherInterface {

	use TextSearchTrait;

	/**
	 * Check if the IPTC copyright matches the condition.
	 *
	 * @param int   $attachment_id Attachment ID.
	 * @param array $metadata      Attachment metadata.
	 * @param array $params        Condition parameters. 'value' is the search string.
	 * @return bool True if copyright contains the value.
	 */
	public function matches( $attachment_id, $metadata, $params ): bool {
		if ( empty( $params['value'] ) ) {
			return false;
		}

		$copyright = $metadata['image_meta']['copyright'] ?? '';

		if ( '' === $copyright ) {
			return false;
		}

		return $this->text_matches( $copyright, $params['value'] );
	}

	/**
	 * Get the condition type identifier.
	 *
	 * @return string
	 */
	public function get_type(): string {
		return 'iptc_copyright';
	}
}
