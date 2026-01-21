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
		if ( empty( $params[ 'value' ] ) ) {
			return false;
		}

		$file_path = get_attached_file( $attachment_id );
		if ( ! $file_path ) {
			return false;
		}

		$filename   = basename( $file_path );
		$raw_value  = (string) $params[ 'value' ];
		$raw_value  = trim( $raw_value );
		$regex_body = str_replace( '/', '\/', $raw_value );
		$pattern    = '/' . $regex_body . '/i';

		// Try as user-supplied regex first.
		$result = @preg_match( $pattern, $filename );
		if ( false !== $result ) {
			return 1 === $result;
		}

		// If the regex is invalid, try treating it as a glob pattern.
		// This supports patterns like: *abc*.* or IMG_????.jpg
		if ( false !== strpos( $raw_value, '*' ) || false !== strpos( $raw_value, '?' ) ) {
			$glob_quoted = preg_quote( $raw_value, '/' );
			$glob_body   = str_replace( array( '\\*', '\\?' ), array( '.*', '.' ), $glob_quoted );
			$glob_regex  = '/' . $glob_body . '/i';
			$glob_result = @preg_match( $glob_regex, $filename );
			return 1 === $glob_result;
		}

		return false;
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
