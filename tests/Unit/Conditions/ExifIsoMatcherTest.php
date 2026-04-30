<?php
/**
 * Tests for ExifIsoMatcher class.
 *
 * @package VmfaRulesEngine
 */

namespace VmfaRulesEngine\Tests\Unit\Conditions;

use VmfaRulesEngine\Tests\Unit\TestCase;
use VmfaRulesEngine\Conditions\ExifIsoMatcher;

/**
 * ExifIsoMatcher test class.
 */
class ExifIsoMatcherTest extends TestCase {

	private ExifIsoMatcher $matcher;

	protected function setUp(): void {
		parent::setUp();
		$this->matcher = new ExifIsoMatcher();
	}

	public function test_get_type_returns_exif_iso(): void {
		$this->assertEquals( 'exif_iso', $this->matcher->get_type() );
	}

	public function test_matches_returns_false_when_value_empty(): void {
		$this->assertFalse( $this->matcher->matches( 1, [], [ 'value' => '' ] ) );
	}

	public function test_matches_returns_false_when_iso_missing(): void {
		$this->assertFalse(
			$this->matcher->matches( 1, [ 'image_meta' => [] ], [ 'value' => '400' ] )
		);
	}

	public function test_matches_exact_iso(): void {
		$meta   = [ 'image_meta' => [ 'iso' => '400' ] ];
		$params = [ 'value' => '400', 'operator' => 'eq' ];
		$this->assertTrue( $this->matcher->matches( 1, $meta, $params ) );
	}

	public function test_matches_lte_operator_low_iso(): void {
		$meta   = [ 'image_meta' => [ 'iso' => '200' ] ];
		$params = [ 'value' => '400', 'operator' => 'lte' ];
		$this->assertTrue( $this->matcher->matches( 1, $meta, $params ) );
	}

	public function test_matches_gte_operator_high_iso(): void {
		$meta   = [ 'image_meta' => [ 'iso' => '3200' ] ];
		$params = [ 'value' => '1600', 'operator' => 'gte' ];
		$this->assertTrue( $this->matcher->matches( 1, $meta, $params ) );
	}

	public function test_matches_gt_operator_no_match(): void {
		$meta   = [ 'image_meta' => [ 'iso' => '100' ] ];
		$params = [ 'value' => '400', 'operator' => 'gt' ];
		$this->assertFalse( $this->matcher->matches( 1, $meta, $params ) );
	}

	public function test_matches_between_operator(): void {
		$meta   = [ 'image_meta' => [ 'iso' => '800' ] ];
		$params = [ 'value' => '400', 'operator' => 'between', 'value_end' => '3200' ];
		$this->assertTrue( $this->matcher->matches( 1, $meta, $params ) );
	}

	public function test_no_match_between_out_of_range(): void {
		$meta   = [ 'image_meta' => [ 'iso' => '100' ] ];
		$params = [ 'value' => '400', 'operator' => 'between', 'value_end' => '3200' ];
		$this->assertFalse( $this->matcher->matches( 1, $meta, $params ) );
	}
}
