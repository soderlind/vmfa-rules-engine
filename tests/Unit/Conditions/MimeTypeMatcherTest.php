<?php
/**
 * Tests for MimeTypeMatcher class.
 *
 * @package VmfaRulesEngine
 */

namespace VmfaRulesEngine\Tests\Unit\Conditions;

use VmfaRulesEngine\Tests\Unit\TestCase;
use VmfaRulesEngine\Conditions\MimeTypeMatcher;
use Brain\Monkey\Functions;

/**
 * MimeTypeMatcher test class.
 */
class MimeTypeMatcherTest extends TestCase {

	/**
	 * Matcher instance.
	 *
	 * @var MimeTypeMatcher
	 */
	private MimeTypeMatcher $matcher;

	/**
	 * Set up test fixtures.
	 */
	protected function setUp(): void {
		parent::setUp();
		$this->matcher = new MimeTypeMatcher();
	}

	/**
	 * Test get_type returns correct type.
	 */
	public function test_get_type_returns_mime_type(): void {
		$this->assertEquals( 'mime_type', $this->matcher->get_type() );
	}

	/**
	 * Test matches returns false when value is empty.
	 */
	public function test_matches_returns_false_when_value_empty(): void {
		$result = $this->matcher->matches( 123, [], [ 'value' => '' ] );
		$this->assertFalse( $result );
	}

	/**
	 * Test matches returns false when mime type not found.
	 */
	public function test_matches_returns_false_when_mime_not_found(): void {
		Functions\expect( 'get_post_mime_type' )
			->once()
			->with( 123 )
			->andReturn( false );

		$result = $this->matcher->matches( 123, [], [ 'value' => 'image/jpeg' ] );

		$this->assertFalse( $result );
	}

	/**
	 * Test matches exact MIME type.
	 */
	public function test_matches_exact_mime_type(): void {
		Functions\expect( 'get_post_mime_type' )
			->once()
			->with( 123 )
			->andReturn( 'image/jpeg' );

		$result = $this->matcher->matches( 123, [], [ 'value' => 'image/jpeg' ] );

		$this->assertTrue( $result );
	}

	/**
	 * Test matches returns false for different MIME type.
	 */
	public function test_matches_returns_false_for_different_mime(): void {
		Functions\expect( 'get_post_mime_type' )
			->once()
			->with( 123 )
			->andReturn( 'image/png' );

		$result = $this->matcher->matches( 123, [], [ 'value' => 'image/jpeg' ] );

		$this->assertFalse( $result );
	}

	/**
	 * Test matches wildcard pattern for images.
	 */
	public function test_matches_image_wildcard(): void {
		Functions\expect( 'get_post_mime_type' )
			->once()
			->with( 123 )
			->andReturn( 'image/webp' );

		$result = $this->matcher->matches( 123, [], [ 'value' => 'image/*' ] );

		$this->assertTrue( $result );
	}

	/**
	 * Test matches wildcard pattern for videos.
	 */
	public function test_matches_video_wildcard(): void {
		Functions\expect( 'get_post_mime_type' )
			->once()
			->with( 123 )
			->andReturn( 'video/mp4' );

		$result = $this->matcher->matches( 123, [], [ 'value' => 'video/*' ] );

		$this->assertTrue( $result );
	}

	/**
	 * Test wildcard does not match different type.
	 */
	public function test_wildcard_does_not_match_different_type(): void {
		Functions\expect( 'get_post_mime_type' )
			->once()
			->with( 123 )
			->andReturn( 'application/pdf' );

		$result = $this->matcher->matches( 123, [], [ 'value' => 'image/*' ] );

		$this->assertFalse( $result );
	}

	/**
	 * Test matches application wildcard.
	 */
	public function test_matches_application_wildcard(): void {
		Functions\expect( 'get_post_mime_type' )
			->once()
			->with( 123 )
			->andReturn( 'application/vnd.openxmlformats-officedocument.wordprocessingml.document' );

		$result = $this->matcher->matches( 123, [], [ 'value' => 'application/*' ] );

		$this->assertTrue( $result );
	}
}
