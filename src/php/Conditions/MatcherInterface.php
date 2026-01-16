<?php
/**
 * Condition Matcher Interface.
 *
 * @package VmfaRulesEngine
 */

namespace VmfaRulesEngine\Conditions;

/**
 * Interface for condition matchers.
 */
interface MatcherInterface {

	/**
	 * Check if the attachment matches the condition.
	 *
	 * @param int   $attachment_id Attachment ID.
	 * @param array $metadata      Attachment metadata.
	 * @param array $params        Condition parameters.
	 * @return bool True if condition matches.
	 */
	public function matches( $attachment_id, $metadata, $params );

	/**
	 * Get the condition type identifier.
	 *
	 * @return string
	 */
	public function get_type();
}
