<?php
/**
 * Tests for RuleEvaluator class.
 *
 * @package VmfaRulesEngine
 */

namespace VmfaRulesEngine\Tests\Unit\Services;

use VmfaRulesEngine\Tests\Unit\TestCase;
use VmfaRulesEngine\Services\RuleEvaluator;
use VmfaRulesEngine\Repository\RuleRepository;
use Brain\Monkey\Functions;
use Brain\Monkey\Filters;
use Brain\Monkey\Actions;
use Mockery;

/**
 * RuleEvaluator test class.
 */
class RuleEvaluatorTest extends TestCase {

	/**
	 * Mock repository.
	 *
	 * @var RuleRepository|Mockery\MockInterface
	 */
	private $repository;

	/**
	 * Set up test fixtures.
	 */
	protected function setUp(): void {
		parent::setUp();

		$this->repository = Mockery::mock( RuleRepository::class);

		// Stub apply_filters.
		Filters\expectApplied( 'vmfa_rules_engine_matchers' )
			->andReturnFirstArg();
	}

	/**
	 * Test evaluate_on_upload skips non-create context.
	 */
	public function test_evaluate_on_upload_skips_non_create_context(): void {
		$evaluator = new RuleEvaluator( $this->repository );
		$metadata  = [ 'width' => 1920, 'height' => 1080 ];

		$result = $evaluator->evaluate_on_upload( $metadata, 123, 'update' );

		$this->assertEquals( $metadata, $result );
	}

	/**
	 * Test evaluate_on_upload processes new uploads.
	 */
	public function test_evaluate_on_upload_processes_new_uploads(): void {
		$this->repository
			->shouldReceive( 'get_enabled' )
			->once()
			->andReturn( [] );

		Functions\expect( 'wp_get_object_terms' )
			->once()
			->andReturn( [] );

		Filters\expectApplied( 'vmfa_rules_engine_skip_if_assigned' )
			->once()
			->andReturn( false );

		$evaluator = new RuleEvaluator( $this->repository );
		$metadata  = [ 'width' => 1920, 'height' => 1080 ];

		$result = $evaluator->evaluate_on_upload( $metadata, 123, 'create' );

		$this->assertEquals( $metadata, $result );
	}

	/**
	 * Test evaluate returns null when no rules enabled.
	 */
	public function test_evaluate_returns_null_when_no_rules(): void {
		$this->repository
			->shouldReceive( 'get_enabled' )
			->once()
			->andReturn( [] );

		$evaluator = new RuleEvaluator( $this->repository );

		$result = $evaluator->evaluate( 123, [] );

		$this->assertNull( $result );
	}

	/**
	 * Test evaluate returns matching rule.
	 */
	public function test_evaluate_returns_matching_rule(): void {
		$rule = [
			'id'              => 'rule_1',
			'name'            => 'Test Rule',
			'folder_id'       => 5,
			'conditions'      => [
				[
					'type'  => 'mime_type',
					'value' => 'image/jpeg',
				],
			],
			'stop_processing' => true,
			'enabled'         => true,
		];

		$this->repository
			->shouldReceive( 'get_enabled' )
			->once()
			->andReturn( [ $rule ] );

		Functions\expect( 'get_post_mime_type' )
			->once()
			->with( 123 )
			->andReturn( 'image/jpeg' );

		$evaluator = new RuleEvaluator( $this->repository );

		$result = $evaluator->evaluate( 123, [] );

		$this->assertNotNull( $result );
		$this->assertEquals( 5, $result[ 'folder_id' ] );
		$this->assertEquals( 'rule_1', $result[ 'rule' ][ 'id' ] );
	}

	/**
	 * Test evaluate skips non-matching rules.
	 */
	public function test_evaluate_skips_nonmatching_rules(): void {
		$rules = [
			[
				'id'         => 'rule_1',
				'name'       => 'JPEG Rule',
				'folder_id'  => 5,
				'conditions' => [
					[
						'type'  => 'mime_type',
						'value' => 'image/jpeg',
					],
				],
				'enabled'    => true,
			],
			[
				'id'         => 'rule_2',
				'name'       => 'PNG Rule',
				'folder_id'  => 10,
				'conditions' => [
					[
						'type'  => 'mime_type',
						'value' => 'image/png',
					],
				],
				'enabled'    => true,
			],
		];

		$this->repository
			->shouldReceive( 'get_enabled' )
			->once()
			->andReturn( $rules );

		Functions\expect( 'get_post_mime_type' )
			->times( 2 )
			->with( 123 )
			->andReturn( 'image/png' );

		$evaluator = new RuleEvaluator( $this->repository );

		$result = $evaluator->evaluate( 123, [] );

		$this->assertNotNull( $result );
		$this->assertEquals( 10, $result[ 'folder_id' ] );
		$this->assertEquals( 'rule_2', $result[ 'rule' ][ 'id' ] );
	}

	/**
	 * Test rule_matches requires all conditions (AND logic).
	 */
	public function test_rule_matches_requires_all_conditions(): void {
		$rule = [
			'id'         => 'rule_1',
			'conditions' => [
				[
					'type'  => 'mime_type',
					'value' => 'image/jpeg',
				],
				[
					'type'      => 'dimensions',
					'dimension' => 'width',
					'operator'  => 'gt',
					'value'     => 1920,
				],
			],
		];

		$this->repository
			->shouldReceive( 'get_enabled' )
			->andReturn( [] );

		Functions\expect( 'get_post_mime_type' )
			->once()
			->with( 123 )
			->andReturn( 'image/jpeg' );

		$evaluator = new RuleEvaluator( $this->repository );
		$metadata  = [ 'width' => 1280, 'height' => 720 ]; // Width is NOT > 1920.

		$result = $evaluator->rule_matches( $rule, 123, $metadata );

		$this->assertFalse( $result ); // Should fail because width condition not met.
	}

	/**
	 * Test rule_matches returns false for empty conditions.
	 */
	public function test_rule_matches_returns_false_for_empty_conditions(): void {
		$this->repository
			->shouldReceive( 'get_enabled' )
			->andReturn( [] );

		$evaluator = new RuleEvaluator( $this->repository );

		$result = $evaluator->rule_matches( [ 'conditions' => [] ], 123, [] );

		$this->assertFalse( $result );
	}

	/**
	 * Test assign_folder assigns term to attachment.
	 */
	public function test_assign_folder_assigns_term(): void {
		$term           = new \stdClass();
		$term->term_id  = 5;
		$term->name     = 'Test Folder';
		$term->taxonomy = 'vmfo_folder';

		Functions\expect( 'get_term' )
			->once()
			->with( 5, 'vmfo_folder' )
			->andReturn( $term );

		Functions\expect( 'wp_set_object_terms' )
			->once()
			->with( 123, [ 5 ], 'vmfo_folder' )
			->andReturn( [ 5 ] );

		Functions\expect( 'wp_update_term_count_now' )
			->once()
			->with( [ 5 ], 'vmfo_folder' );

		Functions\expect( 'clean_object_term_cache' )
			->once()
			->with( 123, 'attachment' );

		Actions\expectDone( 'vmfa_rules_engine_folder_assigned' )
			->once()
			->with( 123, 5, Mockery::type( 'array' ) );

		$this->repository
			->shouldReceive( 'get_enabled' )
			->andReturn( [] );

		$evaluator = new RuleEvaluator( $this->repository );

		$result = $evaluator->assign_folder( 123, 5, [ 'id' => 'rule_1' ] );

		$this->assertTrue( $result );
	}

	/**
	 * Test assign_folder returns false for invalid folder.
	 */
	public function test_assign_folder_returns_false_for_invalid_folder(): void {
		Functions\expect( 'get_term' )
			->once()
			->with( 999, 'vmfo_folder' )
			->andReturn( null );

		$this->repository
			->shouldReceive( 'get_enabled' )
			->andReturn( [] );

		$evaluator = new RuleEvaluator( $this->repository );

		$result = $evaluator->assign_folder( 123, 999 );

		$this->assertFalse( $result );
	}

	/**
	 * Test get_matchers returns registered matchers.
	 */
	public function test_get_matchers_returns_registered_matchers(): void {
		$this->repository
			->shouldReceive( 'get_enabled' )
			->andReturn( [] );

		$evaluator = new RuleEvaluator( $this->repository );
		$matchers  = $evaluator->get_matchers();

		$this->assertIsArray( $matchers );
		$this->assertArrayHasKey( 'filename_regex', $matchers );
		$this->assertArrayHasKey( 'mime_type', $matchers );
		$this->assertArrayHasKey( 'dimensions', $matchers );
		$this->assertArrayHasKey( 'file_size', $matchers );
		$this->assertArrayHasKey( 'exif_camera', $matchers );
		$this->assertArrayHasKey( 'exif_date', $matchers );
		$this->assertArrayHasKey( 'author', $matchers );
		$this->assertArrayHasKey( 'iptc_keywords', $matchers );
	}
}
