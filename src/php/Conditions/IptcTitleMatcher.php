<?php
/**
 * IPTC Title Matcher.
 *
 * @package VmfaRulesEngine
 */

namespace VmfaRulesEngine\Conditions;

defined( 'ABSPATH' ) || exit;

/**
 * Matches IPTC title/object name field.
 */
class IptcTitleMatcher implements MatcherInterface {

	use TextSearchTrait;

	/**
	 * Check if the IPTC title matches the condition.
	 *
	 * @param int   $attachment_id Attachment ID.
	 * @param array $metadata      Attachment metadata.
	 * @param array $params        Condition parameters. 'value' is the search string.
	 * @return bool True if title contains the value.
	 */
	public function matches( $attachment_id, $metadata, $params ): bool {
		if ( empty( $params['value'] ) ) {
			return false;
		}

		$title = $metadata['image_meta']['title'] ?? '';

		if ( '' === $title ) {
			return false;
		}

		return $this->text_matches( $title, $params['value'] );
	}

	/**
	 * Get the condition type identifier.
	 *
	 * @return string
	 */
	public function get_type(): string {
		return 'iptc_title';
	}
}
