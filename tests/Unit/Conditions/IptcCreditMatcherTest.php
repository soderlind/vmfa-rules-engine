<?php
/**
 * Tests for IptcCreditMatcher class.
 *
 * @package VmfaRulesEngine
 */

namespace VmfaRulesEngine\Tests\Unit\Conditions;

use VmfaRulesEngine\Tests\Unit\TestCase;
use VmfaRulesEngine\Conditions\IptcCreditMatcher;

/**
 * IptcCreditMatcher test class.
 */
class IptcCreditMatcherTest extends TestCase {

	private IptcCreditMatcher $matcher;

	protected function setUp(): void {
		parent::setUp();
		$this->matcher = new IptcCreditMatcher();
	}

	public function test_get_type_returns_iptc_credit(): void {
		$this->assertEquals( 'iptc_credit', $this->matcher->get_type() );
	}

	public function test_matches_returns_false_when_value_empty(): void {
		$this->assertFalse( $this->matcher->matches( 1, [], [ 'value' => '' ] ) );
	}

	public function test_matches_returns_false_when_credit_missing(): void {
		$this->assertFalse(
			$this->matcher->matches( 1, [ 'image_meta' => [] ], [ 'value' => 'Reuters' ] )
		);
	}

	public function test_matches_exact_credit(): void {
		$meta = [ 'image_meta' => [ 'credit' => 'Reuters' ] ];
		$this->assertTrue( $this->matcher->matches( 1, $meta, [ 'value' => 'Reuters' ] ) );
	}

	public function test_matches_partial_credit(): void {
		$meta = [ 'image_meta' => [ 'credit' => 'Reuters Photography' ] ];
		$this->assertTrue( $this->matcher->matches( 1, $meta, [ 'value' => 'Reuters' ] ) );
	}

	public function test_matches_is_case_insensitive(): void {
		$meta = [ 'image_meta' => [ 'credit' => 'AFP' ] ];
		$this->assertTrue( $this->matcher->matches( 1, $meta, [ 'value' => 'afp' ] ) );
	}

	public function test_no_match_different_credit(): void {
		$meta = [ 'image_meta' => [ 'credit' => 'Getty Images' ] ];
		$this->assertFalse( $this->matcher->matches( 1, $meta, [ 'value' => 'Reuters' ] ) );
	}
}
