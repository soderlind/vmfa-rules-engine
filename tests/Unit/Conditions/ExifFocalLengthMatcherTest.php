<?php
/**
 * Tests for ExifFocalLengthMatcher class.
 *
 * @package VmfaRulesEngine
 */

namespace VmfaRulesEngine\Tests\Unit\Conditions;

use VmfaRulesEngine\Tests\Unit\TestCase;
use VmfaRulesEngine\Conditions\ExifFocalLengthMatcher;

/**
 * ExifFocalLengthMatcher test class.
 */
class ExifFocalLengthMatcherTest extends TestCase {

	private ExifFocalLengthMatcher $matcher;

	protected function setUp(): void {
		parent::setUp();
		$this->matcher = new ExifFocalLengthMatcher();
	}

	public function test_get_type_returns_exif_focal_length(): void {
		$this->assertEquals( 'exif_focal_length', $this->matcher->get_type() );
	}

	public function test_matches_returns_false_when_value_empty(): void {
		$this->assertFalse( $this->matcher->matches( 1, [], [ 'value' => '' ] ) );
	}

	public function test_matches_returns_false_when_focal_length_missing(): void {
		$this->assertFalse(
			$this->matcher->matches( 1, [ 'image_meta' => [] ], [ 'value' => '50' ] )
		);
	}

	public function test_matches_exact_focal_length(): void {
		$meta   = [ 'image_meta' => [ 'focal_length' => '50' ] ];
		$params = [ 'value' => '50', 'operator' => 'eq' ];
		$this->assertTrue( $this->matcher->matches( 1, $meta, $params ) );
	}

	public function test_matches_gt_operator(): void {
		$meta   = [ 'image_meta' => [ 'focal_length' => '200' ] ];
		$params = [ 'value' => '100', 'operator' => 'gt' ];
		$this->assertTrue( $this->matcher->matches( 1, $meta, $params ) );
	}

	public function test_matches_lte_operator(): void {
		$meta   = [ 'image_meta' => [ 'focal_length' => '35' ] ];
		$params = [ 'value' => '50', 'operator' => 'lte' ];
		$this->assertTrue( $this->matcher->matches( 1, $meta, $params ) );
	}

	public function test_matches_between_operator(): void {
		$meta   = [ 'image_meta' => [ 'focal_length' => '85' ] ];
		$params = [ 'value' => '50', 'operator' => 'between', 'value_end' => '200' ];
		$this->assertTrue( $this->matcher->matches( 1, $meta, $params ) );
	}

	public function test_no_match_between_out_of_range(): void {
		$meta   = [ 'image_meta' => [ 'focal_length' => '24' ] ];
		$params = [ 'value' => '50', 'operator' => 'between', 'value_end' => '200' ];
		$this->assertFalse( $this->matcher->matches( 1, $meta, $params ) );
	}
}
