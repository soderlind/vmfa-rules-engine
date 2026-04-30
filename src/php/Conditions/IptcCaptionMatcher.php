<?php
/**
 * IPTC Caption Matcher.
 *
 * @package VmfaRulesEngine
 */

namespace VmfaRulesEngine\Conditions;

defined( 'ABSPATH' ) || exit;

/**
 * Matches IPTC caption/description field.
 */
class IptcCaptionMatcher implements MatcherInterface {

	use TextSearchTrait;

	/**
	 * Check if the IPTC caption matches the condition.
	 *
	 * @param int   $attachment_id Attachment ID.
	 * @param array $metadata      Attachment metadata.
	 * @param array $params        Condition parameters. 'value' is the search string.
	 * @return bool True if caption contains the value.
	 */
	public function matches( $attachment_id, $metadata, $params ): bool {
		if ( empty( $params['value'] ) ) {
			return false;
		}

		$caption = $metadata['image_meta']['caption'] ?? '';

		if ( '' === $caption ) {
			return false;
		}

		return $this->text_matches( $caption, $params['value'] );
	}

	/**
	 * Get the condition type identifier.
	 *
	 * @return string
	 */
	public function get_type(): string {
		return 'iptc_caption';
	}
}
