<?php
/**
 * MIME Type Matcher.
 *
 * @package VmfaRulesEngine
 */

namespace VmfaRulesEngine\Conditions;

/**
 * Matches file MIME type.
 */
class MimeTypeMatcher implements MatcherInterface {

	/**
	 * Check if the attachment MIME type matches.
	 *
	 * @param int   $attachment_id Attachment ID.
	 * @param array $metadata      Attachment metadata.
	 * @param array $params        Condition parameters with 'value' as MIME type.
	 * @return bool True if MIME type matches.
	 */
	public function matches( $attachment_id, $metadata, $params ) {
		if ( empty( $params['value'] ) ) {
			return false;
		}

		$mime_type = get_post_mime_type( $attachment_id );
		if ( ! $mime_type ) {
			return false;
		}

		$pattern = $params['value'];

		// Handle wildcard patterns like 'image/*'.
		if ( strpos( $pattern, '/*' ) !== false ) {
			$type_prefix = str_replace( '/*', '/', $pattern );
			return strpos( $mime_type, $type_prefix ) === 0;
		}

		return $mime_type === $pattern;
	}

	/**
	 * Get the condition type identifier.
	 *
	 * @return string
	 */
	public function get_type() {
		return 'mime_type';
	}
}
