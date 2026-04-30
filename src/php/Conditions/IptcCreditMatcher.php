<?php
/**
 * IPTC Credit Matcher.
 *
 * @package VmfaRulesEngine
 */

namespace VmfaRulesEngine\Conditions;

defined( 'ABSPATH' ) || exit;

/**
 * Matches IPTC credit field.
 */
class IptcCreditMatcher implements MatcherInterface {

	use TextSearchTrait;

	/**
	 * Check if the IPTC credit matches the condition.
	 *
	 * @param int   $attachment_id Attachment ID.
	 * @param array $metadata      Attachment metadata.
	 * @param array $params        Condition parameters. 'value' is the search string.
	 * @return bool True if credit contains the value.
	 */
	public function matches( $attachment_id, $metadata, $params ): bool {
		if ( empty( $params['value'] ) ) {
			return false;
		}

		$credit = $metadata['image_meta']['credit'] ?? '';

		if ( '' === $credit ) {
			return false;
		}

		return $this->text_matches( $credit, $params['value'] );
	}

	/**
	 * Get the condition type identifier.
	 *
	 * @return string
	 */
	public function get_type(): string {
		return 'iptc_credit';
	}
}
