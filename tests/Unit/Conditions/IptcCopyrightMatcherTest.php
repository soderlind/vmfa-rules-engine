<?php
/**
 * Tests for IptcCopyrightMatcher class.
 *
 * @package VmfaRulesEngine
 */

namespace VmfaRulesEngine\Tests\Unit\Conditions;

use VmfaRulesEngine\Tests\Unit\TestCase;
use VmfaRulesEngine\Conditions\IptcCopyrightMatcher;

/**
 * IptcCopyrightMatcher test class.
 */
class IptcCopyrightMatcherTest extends TestCase {

	private IptcCopyrightMatcher $matcher;

	protected function setUp(): void {
		parent::setUp();
		$this->matcher = new IptcCopyrightMatcher();
	}

	public function test_get_type_returns_iptc_copyright(): void {
		$this->assertEquals( 'iptc_copyright', $this->matcher->get_type() );
	}

	public function test_matches_returns_false_when_value_empty(): void {
		$this->assertFalse( $this->matcher->matches( 1, [], [ 'value' => '' ] ) );
	}

	public function test_matches_returns_false_when_copyright_missing(): void {
		$this->assertFalse(
			$this->matcher->matches( 1, [ 'image_meta' => [] ], [ 'value' => '© 2025' ] )
		);
	}

	public function test_matches_partial_copyright(): void {
		$meta = [ 'image_meta' => [ 'copyright' => '© 2025 Acme Corp' ] ];
		$this->assertTrue( $this->matcher->matches( 1, $meta, [ 'value' => 'Acme' ] ) );
	}

	public function test_matches_year_in_copyright(): void {
		$meta = [ 'image_meta' => [ 'copyright' => '© 2025 Photographer Name' ] ];
		$this->assertTrue( $this->matcher->matches( 1, $meta, [ 'value' => '2025' ] ) );
	}

	public function test_matches_is_case_insensitive(): void {
		$meta = [ 'image_meta' => [ 'copyright' => '© Getty Images' ] ];
		$this->assertTrue( $this->matcher->matches( 1, $meta, [ 'value' => 'getty' ] ) );
	}

	public function test_no_match_different_copyright(): void {
		$meta = [ 'image_meta' => [ 'copyright' => '© 2025 Acme Corp' ] ];
		$this->assertFalse( $this->matcher->matches( 1, $meta, [ 'value' => 'Reuters' ] ) );
	}
}
