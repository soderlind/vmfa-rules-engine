<?php
/**
 * Tests for ExifCameraMatcher class.
 *
 * @package VmfaRulesEngine
 */

namespace VmfaRulesEngine\Tests\Unit\Conditions;

use VmfaRulesEngine\Tests\Unit\TestCase;
use VmfaRulesEngine\Conditions\ExifCameraMatcher;

/**
 * ExifCameraMatcher test class.
 */
class ExifCameraMatcherTest extends TestCase {

	/**
	 * Matcher instance.
	 *
	 * @var ExifCameraMatcher
	 */
	private ExifCameraMatcher $matcher;

	/**
	 * Set up test fixtures.
	 */
	protected function setUp(): void {
		parent::setUp();
		$this->matcher = new ExifCameraMatcher();
	}

	/**
	 * Test get_type returns correct type.
	 */
	public function test_get_type_returns_exif_camera(): void {
		$this->assertEquals( 'exif_camera', $this->matcher->get_type() );
	}

	/**
	 * Test matches returns false when value is empty.
	 */
	public function test_matches_returns_false_when_value_empty(): void {
		$result = $this->matcher->matches( 123, [], [ 'value' => '' ] );
		$this->assertFalse( $result );
	}

	/**
	 * Test matches returns false when camera metadata missing.
	 */
	public function test_matches_returns_false_when_camera_missing(): void {
		$metadata = [ 'image_meta' => [] ];
		$result   = $this->matcher->matches( 123, $metadata, [ 'value' => 'iPhone' ] );
		$this->assertFalse( $result );
	}

	/**
	 * Test matches exact camera model.
	 */
	public function test_matches_exact_camera_model(): void {
		$metadata = [
			'image_meta' => [
				'camera' => 'iPhone 15 Pro Max',
			],
		];

		$result = $this->matcher->matches( 123, $metadata, [ 'value' => 'iPhone 15 Pro Max' ] );

		$this->assertTrue( $result );
	}

	/**
	 * Test matches partial camera model.
	 */
	public function test_matches_partial_camera_model(): void {
		$metadata = [
			'image_meta' => [
				'camera' => 'iPhone 15 Pro Max',
			],
		];

		$result = $this->matcher->matches( 123, $metadata, [ 'value' => 'iPhone' ] );

		$this->assertTrue( $result );
	}

	/**
	 * Test matches is case insensitive.
	 */
	public function test_matches_is_case_insensitive(): void {
		$metadata = [
			'image_meta' => [
				'camera' => 'Canon EOS R5',
			],
		];

		$result = $this->matcher->matches( 123, $metadata, [ 'value' => 'canon' ] );

		$this->assertTrue( $result );
	}

	/**
	 * Test matches returns false for non-matching camera.
	 */
	public function test_matches_returns_false_for_nonmatching_camera(): void {
		$metadata = [
			'image_meta' => [
				'camera' => 'Sony A7 IV',
			],
		];

		$result = $this->matcher->matches( 123, $metadata, [ 'value' => 'Canon' ] );

		$this->assertFalse( $result );
	}

	/**
	 * Test matches with brand prefix.
	 */
	public function test_matches_with_brand_prefix(): void {
		$metadata = [
			'image_meta' => [
				'camera' => 'NIKON D850',
			],
		];

		$result = $this->matcher->matches( 123, $metadata, [ 'value' => 'NIKON' ] );

		$this->assertTrue( $result );
	}
}
