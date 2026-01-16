<?php
/**
 * Author Matcher.
 *
 * @package VmfaRulesEngine
 */

namespace VmfaRulesEngine\Conditions;

/**
 * Matches upload author (user ID).
 */
class AuthorMatcher implements MatcherInterface {

	/**
	 * Check if the attachment author matches.
	 *
	 * @param int   $attachment_id Attachment ID.
	 * @param array $metadata      Attachment metadata.
	 * @param array $params        Condition parameters with 'value' as user ID.
	 * @return bool True if author matches.
	 */
	public function matches( $attachment_id, $metadata, $params ) {
		if ( empty( $params[ 'value' ] ) ) {
			return false;
		}

		$attachment = get_post( $attachment_id );
		if ( ! $attachment ) {
			return false;
		}

		$author_id = absint( $attachment->post_author );
		$target_id = absint( $params[ 'value' ] );

		return $author_id === $target_id;
	}

	/**
	 * Get the condition type identifier.
	 *
	 * @return string
	 */
	public function get_type() {
		return 'author';
	}
}
