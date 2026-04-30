<?php
/**
 * Tests for ExifShutterSpeedMatcher class.
 *
 * @package VmfaRulesEngine
 */

namespace VmfaRulesEngine\Tests\Unit\Conditions;

use VmfaRulesEngine\Tests\Unit\TestCase;
use VmfaRulesEngine\Conditions\ExifShutterSpeedMatcher;

/**
 * ExifShutterSpeedMatcher test class.
 */
class ExifShutterSpeedMatcherTest extends TestCase {

	private ExifShutterSpeedMatcher $matcher;

	protected function setUp(): void {
		parent::setUp();
		$this->matcher = new ExifShutterSpeedMatcher();
	}

	public function test_get_type_returns_exif_shutter_speed(): void {
		$this->assertEquals( 'exif_shutter_speed', $this->matcher->get_type() );
	}

	public function test_matches_returns_false_when_value_empty(): void {
		$this->assertFalse( $this->matcher->matches( 1, [], [ 'value' => '' ] ) );
	}

	public function test_matches_returns_false_when_shutter_speed_missing(): void {
		$this->assertFalse(
			$this->matcher->matches( 1, [ 'image_meta' => [] ], [ 'value' => '1/1000' ] )
		);
	}

	public function test_matches_fraction_value_lte_fast_shutter(): void {
		// WP stores shutter_speed as decimal: 1/1000 = 0.001
		$meta   = [ 'image_meta' => [ 'shutter_speed' => '0.001' ] ];
		$params = [ 'value' => '1/500', 'operator' => 'lte' ];
		// 0.001 (1/1000) <= 0.002 (1/500) → true
		$this->assertTrue( $this->matcher->matches( 1, $meta, $params ) );
	}

	public function test_no_match_fraction_value_lte(): void {
		$meta   = [ 'image_meta' => [ 'shutter_speed' => '0.1' ] ]; // 1/10
		$params = [ 'value' => '1/500', 'operator' => 'lte' ];
		// 0.1 <= 0.002 → false
		$this->assertFalse( $this->matcher->matches( 1, $meta, $params ) );
	}

	public function test_matches_decimal_value_gte_slow_shutter(): void {
		$meta   = [ 'image_meta' => [ 'shutter_speed' => '0.5' ] ];
		$params = [ 'value' => '0.25', 'operator' => 'gte' ];
		$this->assertTrue( $this->matcher->matches( 1, $meta, $params ) );
	}

	public function test_matches_between_operator(): void {
		$meta   = [ 'image_meta' => [ 'shutter_speed' => '0.01' ] ]; // 1/100
		$params = [ 'value' => '1/1000', 'operator' => 'between', 'value_end' => '1/30' ];
		// 0.001 <= 0.01 <= 0.0333 → true
		$this->assertTrue( $this->matcher->matches( 1, $meta, $params ) );
	}

	public function test_no_match_between_out_of_range(): void {
		$meta   = [ 'image_meta' => [ 'shutter_speed' => '0.001' ] ]; // 1/1000
		$params = [ 'value' => '1/60', 'operator' => 'between', 'value_end' => '1/4' ];
		// 0.001 < 0.0167 → false
		$this->assertFalse( $this->matcher->matches( 1, $meta, $params ) );
	}

	public function test_matches_invalid_fraction_denominator_zero(): void {
		$meta   = [ 'image_meta' => [ 'shutter_speed' => '0.001' ] ];
		$params = [ 'value' => '1/0', 'operator' => 'lte' ];
		$this->assertFalse( $this->matcher->matches( 1, $meta, $params ) );
	}
}
