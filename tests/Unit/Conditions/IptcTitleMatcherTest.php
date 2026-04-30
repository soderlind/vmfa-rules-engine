<?php
/**
 * Tests for IptcTitleMatcher class.
 *
 * @package VmfaRulesEngine
 */

namespace VmfaRulesEngine\Tests\Unit\Conditions;

use VmfaRulesEngine\Tests\Unit\TestCase;
use VmfaRulesEngine\Conditions\IptcTitleMatcher;

/**
 * IptcTitleMatcher test class.
 */
class IptcTitleMatcherTest extends TestCase {

	private IptcTitleMatcher $matcher;

	protected function setUp(): void {
		parent::setUp();
		$this->matcher = new IptcTitleMatcher();
	}

	public function test_get_type_returns_iptc_title(): void {
		$this->assertEquals( 'iptc_title', $this->matcher->get_type() );
	}

	public function test_matches_returns_false_when_value_empty(): void {
		$this->assertFalse( $this->matcher->matches( 1, [], [ 'value' => '' ] ) );
	}

	public function test_matches_returns_false_when_title_missing(): void {
		$this->assertFalse(
			$this->matcher->matches( 1, [ 'image_meta' => [] ], [ 'value' => 'hero' ] )
		);
	}

	public function test_matches_partial_title(): void {
		$meta = [ 'image_meta' => [ 'title' => 'Hero banner image 2025' ] ];
		$this->assertTrue( $this->matcher->matches( 1, $meta, [ 'value' => 'hero' ] ) );
	}

	public function test_matches_is_case_insensitive(): void {
		$meta = [ 'image_meta' => [ 'title' => 'Product Shot A' ] ];
		$this->assertTrue( $this->matcher->matches( 1, $meta, [ 'value' => 'PRODUCT' ] ) );
	}

	public function test_no_match_different_title(): void {
		$meta = [ 'image_meta' => [ 'title' => 'Event Photography' ] ];
		$this->assertFalse( $this->matcher->matches( 1, $meta, [ 'value' => 'hero' ] ) );
	}
}
