<?php
/**
 * Base test case with Brain Monkey setup.
 *
 * @package VmfaRulesEngine
 */

namespace VmfaRulesEngine\Tests\Unit;

use PHPUnit\Framework\TestCase as PHPUnitTestCase;
use Brain\Monkey;
use Brain\Monkey\Functions;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

/**
 * Base test case class.
 */
abstract class TestCase extends PHPUnitTestCase {

	use MockeryPHPUnitIntegration;

	/**
	 * Set up Brain Monkey before each test.
	 */
	protected function setUp(): void {
		parent::setUp();
		Monkey\setUp();

		// Common WordPress function stubs.
		$this->stub_common_wp_functions();
	}

	/**
	 * Tear down Brain Monkey after each test.
	 */
	protected function tearDown(): void {
		Monkey\tearDown();
		parent::tearDown();
	}

	/**
	 * Stub common WordPress functions used throughout tests.
	 */
	protected function stub_common_wp_functions(): void {
		// Sanitization functions.
		Functions\stubs(
			[
				'sanitize_text_field' => function ( $str ) {
					return trim( strip_tags( $str ) );
				},
				'sanitize_key'        => function ( $key ) {
					return preg_replace( '/[^a-z0-9_\-]/', '', strtolower( $key ) );
				},
				'sanitize_mime_type'  => function ( $mime ) {
					return preg_replace( '/[^a-zA-Z0-9\/\+\-\.]/', '', $mime );
				},
				'absint'              => function ( $val ) {
					return abs( (int) $val );
				},
				'wp_parse_args'       => function ( $args, $defaults = [] ) {
					if ( is_object( $args ) ) {
						$args = get_object_vars( $args );
					}
					return array_merge( $defaults, $args );
				},
				'__'                  => function ( $text, $domain = 'default' ) {
					return $text;
				},
				'esc_html__'          => function ( $text, $domain = 'default' ) {
					return $text;
				},
				'esc_attr__'          => function ( $text, $domain = 'default' ) {
					return $text;
				},
			]
		);
	}
}
