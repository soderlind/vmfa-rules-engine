<?php
/**
 * Tests for FilenameRegexMatcher class.
 *
 * @package VmfaRulesEngine
 */

namespace VmfaRulesEngine\Tests\Unit\Conditions;

use VmfaRulesEngine\Tests\Unit\TestCase;
use VmfaRulesEngine\Conditions\FilenameRegexMatcher;
use Brain\Monkey\Functions;

/**
 * FilenameRegexMatcher test class.
 */
class FilenameRegexMatcherTest extends TestCase {

	/**
	 * Matcher instance.
	 *
	 * @var FilenameRegexMatcher
	 */
	private FilenameRegexMatcher $matcher;

	/**
	 * Set up test fixtures.
	 */
	protected function setUp(): void {
		parent::setUp();
		$this->matcher = new FilenameRegexMatcher();
	}

	/**
	 * Test get_type returns correct type.
	 */
	public function test_get_type_returns_filename_regex(): void {
		$this->assertEquals( 'filename_regex', $this->matcher->get_type() );
	}

	/**
	 * Test matches returns false when value is empty.
	 */
	public function test_matches_returns_false_when_value_empty(): void {
		$result = $this->matcher->matches( 123, [], [ 'value' => '' ] );
		$this->assertFalse( $result );
	}

	/**
	 * Test matches returns false when file path not found.
	 */
	public function test_matches_returns_false_when_file_not_found(): void {
		Functions\expect( 'get_attached_file' )
			->once()
			->with( 123 )
			->andReturn( false );

		$result = $this->matcher->matches( 123, [], [ 'value' => '^IMG_' ] );

		$this->assertFalse( $result );
	}

	/**
	 * Test matches returns true for matching filename.
	 */
	public function test_matches_returns_true_for_matching_filename(): void {
		Functions\expect( 'get_attached_file' )
			->once()
			->with( 123 )
			->andReturn( '/var/www/wp-content/uploads/2024/01/IMG_1234.jpg' );

		$result = $this->matcher->matches( 123, [], [ 'value' => '^IMG_' ] );

		$this->assertTrue( $result );
	}

	/**
	 * Test matches returns false for non-matching filename.
	 */
	public function test_matches_returns_false_for_nonmatching_filename(): void {
		Functions\expect( 'get_attached_file' )
			->once()
			->with( 123 )
			->andReturn( '/var/www/wp-content/uploads/2024/01/DSC_5678.jpg' );

		$result = $this->matcher->matches( 123, [], [ 'value' => '^IMG_' ] );

		$this->assertFalse( $result );
	}

	/**
	 * Test matches is case insensitive.
	 */
	public function test_matches_is_case_insensitive(): void {
		Functions\expect( 'get_attached_file' )
			->once()
			->with( 123 )
			->andReturn( '/var/www/wp-content/uploads/img_photo.jpg' );

		$result = $this->matcher->matches( 123, [], [ 'value' => '^IMG_' ] );

		$this->assertTrue( $result );
	}

	/**
	 * Test matches with complex regex pattern.
	 */
	public function test_matches_with_complex_regex(): void {
		Functions\expect( 'get_attached_file' )
			->once()
			->with( 123 )
			->andReturn( '/var/www/wp-content/uploads/screenshot-2024-01-15.png' );

		$result = $this->matcher->matches( 123, [], [ 'value' => 'screenshot-\d{4}-\d{2}-\d{2}' ] );

		$this->assertTrue( $result );
	}

	/**
	 * Test matches handles invalid regex gracefully.
	 */
	public function test_matches_handles_invalid_regex(): void {
		Functions\expect( 'get_attached_file' )
			->once()
			->with( 123 )
			->andReturn( '/var/www/wp-content/uploads/test.jpg' );

		// Invalid regex with unclosed bracket.
		$result = $this->matcher->matches( 123, [], [ 'value' => '[invalid' ] );

		$this->assertFalse( $result );
	}

	/**
	 * Test matches with file extension pattern.
	 */
	public function test_matches_file_extension_pattern(): void {
		Functions\expect( 'get_attached_file' )
			->once()
			->with( 123 )
			->andReturn( '/var/www/wp-content/uploads/document.pdf' );

		$result = $this->matcher->matches( 123, [], [ 'value' => '\.pdf$' ] );

		$this->assertTrue( $result );
	}
}
