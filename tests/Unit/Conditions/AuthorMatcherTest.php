<?php
/**
 * Tests for AuthorMatcher class.
 *
 * @package VmfaRulesEngine
 */

namespace VmfaRulesEngine\Tests\Unit\Conditions;

use VmfaRulesEngine\Tests\Unit\TestCase;
use VmfaRulesEngine\Conditions\AuthorMatcher;
use Brain\Monkey\Functions;

/**
 * AuthorMatcher test class.
 */
class AuthorMatcherTest extends TestCase {

	/**
	 * Matcher instance.
	 *
	 * @var AuthorMatcher
	 */
	private AuthorMatcher $matcher;

	/**
	 * Set up test fixtures.
	 */
	protected function setUp(): void {
		parent::setUp();
		$this->matcher = new AuthorMatcher();
	}

	/**
	 * Test get_type returns correct type.
	 */
	public function test_get_type_returns_author(): void {
		$this->assertEquals( 'author', $this->matcher->get_type() );
	}

	/**
	 * Test matches returns false when value is empty.
	 */
	public function test_matches_returns_false_when_value_empty(): void {
		$result = $this->matcher->matches( 123, [], [ 'value' => '' ] );
		$this->assertFalse( $result );
	}

	/**
	 * Test matches returns false when attachment not found.
	 */
	public function test_matches_returns_false_when_attachment_not_found(): void {
		Functions\expect( 'get_post' )
			->once()
			->with( 123 )
			->andReturn( null );

		$result = $this->matcher->matches( 123, [], [ 'value' => 1 ] );

		$this->assertFalse( $result );
	}

	/**
	 * Test matches author ID.
	 */
	public function test_matches_author_id(): void {
		$attachment              = new \stdClass();
		$attachment->post_author = 5;

		Functions\expect( 'get_post' )
			->once()
			->with( 123 )
			->andReturn( $attachment );

		$result = $this->matcher->matches( 123, [], [ 'value' => 5 ] );

		$this->assertTrue( $result );
	}

	/**
	 * Test matches returns false for different author.
	 */
	public function test_matches_returns_false_for_different_author(): void {
		$attachment              = new \stdClass();
		$attachment->post_author = 5;

		Functions\expect( 'get_post' )
			->once()
			->with( 123 )
			->andReturn( $attachment );

		$result = $this->matcher->matches( 123, [], [ 'value' => 10 ] );

		$this->assertFalse( $result );
	}

	/**
	 * Test matches handles string value.
	 */
	public function test_matches_handles_string_value(): void {
		$attachment              = new \stdClass();
		$attachment->post_author = 5;

		Functions\expect( 'get_post' )
			->once()
			->with( 123 )
			->andReturn( $attachment );

		$result = $this->matcher->matches( 123, [], [ 'value' => '5' ] );

		$this->assertTrue( $result );
	}
}
