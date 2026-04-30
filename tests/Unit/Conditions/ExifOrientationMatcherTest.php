<?php
/**
 * Tests for ExifOrientationMatcher class.
 *
 * @package VmfaRulesEngine
 */

namespace VmfaRulesEngine\Tests\Unit\Conditions;

use VmfaRulesEngine\Tests\Unit\TestCase;
use VmfaRulesEngine\Conditions\ExifOrientationMatcher;

/**
 * ExifOrientationMatcher test class.
 */
class ExifOrientationMatcherTest extends TestCase {

	private ExifOrientationMatcher $matcher;

	protected function setUp(): void {
		parent::setUp();
		$this->matcher = new ExifOrientationMatcher();
	}

	public function test_get_type_returns_exif_orientation(): void {
		$this->assertEquals( 'exif_orientation', $this->matcher->get_type() );
	}

	public function test_matches_returns_false_when_value_empty(): void {
		$this->assertFalse( $this->matcher->matches( 1, [], [ 'value' => '' ] ) );
	}

	public function test_matches_returns_false_when_orientation_missing(): void {
		$this->assertFalse(
			$this->matcher->matches( 1, [ 'image_meta' => [] ], [ 'value' => '1' ] )
		);
	}

	public function test_matches_orientation_1_normal(): void {
		$meta   = [ 'image_meta' => [ 'orientation' => '1' ] ];
		$params = [ 'value' => '1' ];
		$this->assertTrue( $this->matcher->matches( 1, $meta, $params ) );
	}

	public function test_matches_orientation_6_rotated_90_cw(): void {
		$meta   = [ 'image_meta' => [ 'orientation' => '6' ] ];
		$params = [ 'value' => '6' ];
		$this->assertTrue( $this->matcher->matches( 1, $meta, $params ) );
	}

	public function test_no_match_orientation_mismatch(): void {
		$meta   = [ 'image_meta' => [ 'orientation' => '1' ] ];
		$params = [ 'value' => '6' ];
		$this->assertFalse( $this->matcher->matches( 1, $meta, $params ) );
	}

	public function test_matches_with_integer_orientation(): void {
		$meta   = [ 'image_meta' => [ 'orientation' => 3 ] ];
		$params = [ 'value' => '3' ];
		$this->assertTrue( $this->matcher->matches( 1, $meta, $params ) );
	}
}
