<?php
/**
 * Tests for FileSizeMatcher class.
 *
 * @package VmfaRulesEngine
 */

namespace VmfaRulesEngine\Tests\Unit\Conditions;

use VmfaRulesEngine\Tests\Unit\TestCase;
use VmfaRulesEngine\Conditions\FileSizeMatcher;
use Brain\Monkey\Functions;

/**
 * FileSizeMatcher test class.
 */
class FileSizeMatcherTest extends TestCase {

	/**
	 * Matcher instance.
	 *
	 * @var FileSizeMatcher
	 */
	private FileSizeMatcher $matcher;

	/**
	 * Set up test fixtures.
	 */
	protected function setUp(): void {
		parent::setUp();
		$this->matcher = new FileSizeMatcher();
	}

	/**
	 * Test get_type returns correct type.
	 */
	public function test_get_type_returns_file_size(): void {
		$this->assertEquals( 'file_size', $this->matcher->get_type() );
	}

	/**
	 * Test matches returns false when value missing.
	 */
	public function test_matches_returns_false_when_value_missing(): void {
		$result = $this->matcher->matches( 123, [], [] );
		$this->assertFalse( $result );
	}

	/**
	 * Test matches file size from metadata.
	 */
	public function test_matches_file_size_from_metadata(): void {
		// 2MB in bytes.
		$metadata = [ 'filesize' => 2097152 ];
		$params   = [
			'operator' => 'gt',
			'value'    => 1024, // 1MB in KB.
		];

		$result = $this->matcher->matches( 123, $metadata, $params );

		$this->assertTrue( $result );
	}

	/**
	 * Test matches file size greater than.
	 */
	public function test_matches_file_size_greater_than(): void {
		// 5MB in bytes.
		$metadata = [ 'filesize' => 5242880 ];
		$params   = [
			'operator' => 'gt',
			'value'    => 1024, // 1MB in KB.
		];

		$result = $this->matcher->matches( 123, $metadata, $params );

		$this->assertTrue( $result );
	}

	/**
	 * Test matches file size less than.
	 */
	public function test_matches_file_size_less_than(): void {
		// 500KB in bytes.
		$metadata = [ 'filesize' => 512000 ];
		$params   = [
			'operator' => 'lt',
			'value'    => 1024, // 1MB in KB.
		];

		$result = $this->matcher->matches( 123, $metadata, $params );

		$this->assertTrue( $result );
	}

	/**
	 * Test matches file size between.
	 */
	public function test_matches_file_size_between(): void {
		// 3MB in bytes.
		$metadata = [ 'filesize' => 3145728 ];
		$params   = [
			'operator'  => 'between',
			'value'     => 2048, // 2MB in KB.
			'value_end' => 5120, // 5MB in KB.
		];

		$result = $this->matcher->matches( 123, $metadata, $params );

		$this->assertTrue( $result );
	}

	/**
	 * Test matches file size not between.
	 */
	public function test_matches_file_size_not_between(): void {
		// 10MB in bytes.
		$metadata = [ 'filesize' => 10485760 ];
		$params   = [
			'operator'  => 'between',
			'value'     => 2048, // 2MB in KB.
			'value_end' => 5120, // 5MB in KB.
		];

		$result = $this->matcher->matches( 123, $metadata, $params );

		$this->assertFalse( $result );
	}

	/**
	 * Test matches falls back to filesystem when metadata missing.
	 */
	public function test_matches_falls_back_to_filesystem(): void {
		$file_path = '/tmp/test-file.jpg';

		Functions\expect( 'get_attached_file' )
			->once()
			->with( 123 )
			->andReturn( $file_path );

		// Create a temporary file for testing.
		file_put_contents( $file_path, str_repeat( 'x', 2097152 ) ); // 2MB.

		$params = [
			'operator' => 'gt',
			'value'    => 1024, // 1MB in KB.
		];

		$result = $this->matcher->matches( 123, [], $params );

		// Clean up.
		unlink( $file_path );

		$this->assertTrue( $result );
	}

	/**
	 * Test matches returns false when file not found.
	 */
	public function test_matches_returns_false_when_file_not_found(): void {
		Functions\expect( 'get_attached_file' )
			->once()
			->with( 123 )
			->andReturn( '/nonexistent/path/file.jpg' );

		$params = [
			'operator' => 'gt',
			'value'    => 1024,
		];

		$result = $this->matcher->matches( 123, [], $params );

		$this->assertFalse( $result );
	}
}
