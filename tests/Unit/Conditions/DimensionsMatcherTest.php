<?php
/**
 * Tests for DimensionsMatcher class.
 *
 * @package VmfaRulesEngine
 */

namespace VmfaRulesEngine\Tests\Unit\Conditions;

use VmfaRulesEngine\Tests\Unit\TestCase;
use VmfaRulesEngine\Conditions\DimensionsMatcher;

/**
 * DimensionsMatcher test class.
 */
class DimensionsMatcherTest extends TestCase {

	/**
	 * Matcher instance.
	 *
	 * @var DimensionsMatcher
	 */
	private DimensionsMatcher $matcher;

	/**
	 * Set up test fixtures.
	 */
	protected function setUp(): void {
		parent::setUp();
		$this->matcher = new DimensionsMatcher();
	}

	/**
	 * Test get_type returns correct type.
	 */
	public function test_get_type_returns_dimensions(): void {
		$this->assertEquals( 'dimensions', $this->matcher->get_type() );
	}

	/**
	 * Test matches returns false when value missing.
	 */
	public function test_matches_returns_false_when_value_missing(): void {
		$result = $this->matcher->matches( 123, [], [ 'dimension' => 'width' ] );
		$this->assertFalse( $result );
	}

	/**
	 * Test matches returns false when dimension missing.
	 */
	public function test_matches_returns_false_when_dimension_missing(): void {
		$result = $this->matcher->matches( 123, [], [ 'value' => 1920 ] );
		$this->assertFalse( $result );
	}

	/**
	 * Test matches width greater than.
	 */
	public function test_matches_width_greater_than(): void {
		$metadata = [ 'width' => 2000, 'height' => 1000 ];
		$params   = [
			'dimension' => 'width',
			'operator'  => 'gt',
			'value'     => 1920,
		];

		$result = $this->matcher->matches( 123, $metadata, $params );

		$this->assertTrue( $result );
	}

	/**
	 * Test matches width not greater than.
	 */
	public function test_matches_width_not_greater_than(): void {
		$metadata = [ 'width' => 1920, 'height' => 1080 ];
		$params   = [
			'dimension' => 'width',
			'operator'  => 'gt',
			'value'     => 1920,
		];

		$result = $this->matcher->matches( 123, $metadata, $params );

		$this->assertFalse( $result );
	}

	/**
	 * Test matches width greater than or equal.
	 */
	public function test_matches_width_greater_than_or_equal(): void {
		$metadata = [ 'width' => 1920, 'height' => 1080 ];
		$params   = [
			'dimension' => 'width',
			'operator'  => 'gte',
			'value'     => 1920,
		];

		$result = $this->matcher->matches( 123, $metadata, $params );

		$this->assertTrue( $result );
	}

	/**
	 * Test matches height less than.
	 */
	public function test_matches_height_less_than(): void {
		$metadata = [ 'width' => 800, 'height' => 600 ];
		$params   = [
			'dimension' => 'height',
			'operator'  => 'lt',
			'value'     => 1080,
		];

		$result = $this->matcher->matches( 123, $metadata, $params );

		$this->assertTrue( $result );
	}

	/**
	 * Test matches exact dimension.
	 */
	public function test_matches_exact_dimension(): void {
		$metadata = [ 'width' => 1920, 'height' => 1080 ];
		$params   = [
			'dimension' => 'width',
			'operator'  => 'eq',
			'value'     => 1920,
		];

		$result = $this->matcher->matches( 123, $metadata, $params );

		$this->assertTrue( $result );
	}

	/**
	 * Test matches dimension between.
	 */
	public function test_matches_dimension_between(): void {
		$metadata = [ 'width' => 1920, 'height' => 1080 ];
		$params   = [
			'dimension' => 'width',
			'operator'  => 'between',
			'value'     => 1280,
			'value_end' => 2560,
		];

		$result = $this->matcher->matches( 123, $metadata, $params );

		$this->assertTrue( $result );
	}

	/**
	 * Test matches dimension not between.
	 */
	public function test_matches_dimension_not_between(): void {
		$metadata = [ 'width' => 800, 'height' => 600 ];
		$params   = [
			'dimension' => 'width',
			'operator'  => 'between',
			'value'     => 1280,
			'value_end' => 2560,
		];

		$result = $this->matcher->matches( 123, $metadata, $params );

		$this->assertFalse( $result );
	}

	/**
	 * Test matches both dimensions uses minimum.
	 */
	public function test_matches_both_uses_minimum(): void {
		$metadata = [ 'width' => 1920, 'height' => 1080 ];
		$params   = [
			'dimension' => 'both',
			'operator'  => 'gt',
			'value'     => 1000,
		];

		$result = $this->matcher->matches( 123, $metadata, $params );

		$this->assertTrue( $result );
	}

	/**
	 * Test matches returns false when metadata missing.
	 */
	public function test_matches_returns_false_when_metadata_missing(): void {
		$params = [
			'dimension' => 'width',
			'operator'  => 'gt',
			'value'     => 1920,
		];

		$result = $this->matcher->matches( 123, [], $params );

		$this->assertFalse( $result );
	}
}
