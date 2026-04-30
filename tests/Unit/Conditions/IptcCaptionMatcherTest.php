<?php
/**
 * Tests for IptcCaptionMatcher class.
 *
 * @package VmfaRulesEngine
 */

namespace VmfaRulesEngine\Tests\Unit\Conditions;

use VmfaRulesEngine\Tests\Unit\TestCase;
use VmfaRulesEngine\Conditions\IptcCaptionMatcher;

/**
 * IptcCaptionMatcher test class.
 */
class IptcCaptionMatcherTest extends TestCase {

	private IptcCaptionMatcher $matcher;

	protected function setUp(): void {
		parent::setUp();
		$this->matcher = new IptcCaptionMatcher();
	}

	public function test_get_type_returns_iptc_caption(): void {
		$this->assertEquals( 'iptc_caption', $this->matcher->get_type() );
	}

	public function test_matches_returns_false_when_value_empty(): void {
		$this->assertFalse( $this->matcher->matches( 1, [], [ 'value' => '' ] ) );
	}

	public function test_matches_returns_false_when_caption_missing(): void {
		$this->assertFalse(
			$this->matcher->matches( 1, [ 'image_meta' => [] ], [ 'value' => 'sunset' ] )
		);
	}

	public function test_matches_partial_caption(): void {
		$meta = [ 'image_meta' => [ 'caption' => 'Beautiful sunset over the mountains' ] ];
		$this->assertTrue( $this->matcher->matches( 1, $meta, [ 'value' => 'sunset' ] ) );
	}

	public function test_matches_is_case_insensitive(): void {
		$meta = [ 'image_meta' => [ 'caption' => 'Product launch event 2025' ] ];
		$this->assertTrue( $this->matcher->matches( 1, $meta, [ 'value' => 'PRODUCT' ] ) );
	}

	public function test_no_match_different_caption(): void {
		$meta = [ 'image_meta' => [ 'caption' => 'Corporate headshot' ] ];
		$this->assertFalse( $this->matcher->matches( 1, $meta, [ 'value' => 'sunset' ] ) );
	}
}
