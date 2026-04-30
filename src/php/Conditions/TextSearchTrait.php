<?php
/**
 * Text Search Trait.
 *
 * @package VmfaRulesEngine
 */

namespace VmfaRulesEngine\Conditions;

defined( 'ABSPATH' ) || exit;

/**
 * Shared text partial-match logic for condition matchers.
 */
trait TextSearchTrait {

	/**
	 * Case-insensitive partial match of needle inside haystack.
	 *
	 * @param string $haystack The string to search within.
	 * @param string $needle   The string to search for.
	 * @return bool True if needle is found inside haystack.
	 */
	private function text_matches( string $haystack, string $needle ): bool {
		return stripos( $haystack, $needle ) !== false;
	}
}
