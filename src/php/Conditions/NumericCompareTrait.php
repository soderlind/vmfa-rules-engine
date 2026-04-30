<?php
/**
 * Numeric Compare Trait.
 *
 * @package VmfaRulesEngine
 */

namespace VmfaRulesEngine\Conditions;

defined( 'ABSPATH' ) || exit;

/**
 * Shared numeric comparison logic for condition matchers.
 */
trait NumericCompareTrait {

	/**
	 * Compare a numeric value against a target using the given operator.
	 *
	 * @param float  $actual    The actual value to test.
	 * @param string $operator  One of: gt, gte, lt, lte, eq, between.
	 * @param float  $value     Primary comparison value.
	 * @param float  $value_end Upper bound for 'between' operator.
	 * @return bool Comparison result.
	 */
	private function compare_numeric( float $actual, string $operator, float $value, float $value_end = 0.0 ): bool {
		switch ( $operator ) {
			case 'gt':
				return $actual > $value;
			case 'gte':
				return $actual >= $value;
			case 'lt':
				return $actual < $value;
			case 'lte':
				return $actual <= $value;
			case 'eq':
				return abs( $actual - $value ) < 0.0001;
			case 'between':
				return $actual >= $value && $actual <= $value_end;
			default:
				return false;
		}
	}
}
