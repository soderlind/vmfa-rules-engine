<?php
/**
 * Filename Regex Matcher.
 *
 * @package VmfaRulesEngine
 */

namespace VmfaRulesEngine\Conditions;

/**
 * Matches filename against a regular expression.
 */
class FilenameRegexMatcher implements MatcherInterface {

	/**
	 * Check if the attachment filename matches the regex pattern.
	 *
	 * @param int   $attachment_id Attachment ID.
	 * @param array $metadata      Attachment metadata.
	 * @param array $params        Condition parameters with 'value' as regex pattern.
	 * @return bool True if filename matches the pattern.
	 */
	public function matches( $attachment_id, $metadata, $params ) {
		if ( empty( $params['value'] ) ) {
			return false;
		}

		$file_path = get_attached_file( $attachment_id );
		if ( ! $file_path ) {
			return false;
		}

		$filename = basename( $file_path );
		$pattern  = '/' . str_replace( '/', '\/', $params['value'] ) . '/i';

		// Suppress errors for invalid regex patterns.
		$result = @preg_match( $pattern, $filename );

		return 1 === $result;
	}

	/**
	 * Get the condition type identifier.
	 *
	 * @return string
	 */
	public function get_type() {
		return 'filename_regex';
	}
}
