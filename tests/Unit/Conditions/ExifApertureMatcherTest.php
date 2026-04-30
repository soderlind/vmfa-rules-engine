<?php
/**
 * Tests for ExifApertureMatcher class.
 *
 * @package VmfaRulesEngine
 */

namespace VmfaRulesEngine\Tests\Unit\Conditions;

use VmfaRulesEngine\Tests\Unit\TestCase;
use VmfaRulesEngine\Conditions\ExifApertureMatcher;

/**
 * ExifApertureMatcher test class.
 */
class ExifApertureMatcherTest extends TestCase {

	private ExifApertureMatcher $matcher;

	protected function setUp(): void {
		parent::setUp();
		$this->matcher = new ExifApertureMatcher();
	}

	public function test_get_type_returns_exif_aperture(): void {
		$this->assertEquals( 'exif_aperture', $this->matcher->get_type() );
	}

	public function test_matches_returns_false_when_value_empty(): void {
		$this->assertFalse( $this->matcher->matches( 1, [], [ 'value' => '' ] ) );
	}

	public function test_matches_returns_false_when_aperture_missing(): void {
		$this->assertFalse(
			$this->matcher->matches( 1, [ 'image_meta' => [] ], [ 'value' => '2.8' ] )
		);
	}

	public function test_matches_exact_eq_operator(): void {
		$meta   = [ 'image_meta' => [ 'aperture' => '2.8' ] ];
		$params = [ 'value' => '2.8', 'operator' => 'eq' ];
		$this->assertTrue( $this->matcher->matches( 1, $meta, $params ) );
	}

	public function test_no_match_eq_operator(): void {
		$meta   = [ 'image_meta' => [ 'aperture' => '4.0' ] ];
		$params = [ 'value' => '2.8', 'operator' => 'eq' ];
		$this->assertFalse( $this->matcher->matches( 1, $meta, $params ) );
	}

	public function test_matches_gt_operator(): void {
		$meta   = [ 'image_meta' => [ 'aperture' => '5.6' ] ];
		$params = [ 'value' => '4.0', 'operator' => 'gt' ];
		$this->assertTrue( $this->matcher->matches( 1, $meta, $params ) );
	}

	public function test_matches_lt_operator(): void {
		$meta   = [ 'image_meta' => [ 'aperture' => '1.8' ] ];
		$params = [ 'value' => '2.8', 'operator' => 'lt' ];
		$this->assertTrue( $this->matcher->matches( 1, $meta, $params ) );
	}

	public function test_matches_between_operator(): void {
		$meta   = [ 'image_meta' => [ 'aperture' => '4.0' ] ];
		$params = [ 'value' => '2.8', 'operator' => 'between', 'value_end' => '8.0' ];
		$this->assertTrue( $this->matcher->matches( 1, $meta, $params ) );
	}

	public function test_no_match_between_operator_out_of_range(): void {
		$meta   = [ 'image_meta' => [ 'aperture' => '1.4' ] ];
		$params = [ 'value' => '2.8', 'operator' => 'between', 'value_end' => '8.0' ];
		$this->assertFalse( $this->matcher->matches( 1, $meta, $params ) );
	}
}
