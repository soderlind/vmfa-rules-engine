<?php
/**
 * Tests for RuleRepository class.
 *
 * @package VmfaRulesEngine
 */

namespace VmfaRulesEngine\Tests\Unit\Repository;

use VmfaRulesEngine\Tests\Unit\TestCase;
use VmfaRulesEngine\Repository\RuleRepository;
use Brain\Monkey\Functions;

/**
 * RuleRepository test class.
 */
class RuleRepositoryTest extends TestCase {

	/**
	 * Repository instance.
	 *
	 * @var RuleRepository
	 */
	private RuleRepository $repository;

	/**
	 * Set up test fixtures.
	 */
	protected function setUp(): void {
		parent::setUp();
		$this->repository = new RuleRepository();
	}

	/**
	 * Test get_all returns empty array when no rules exist.
	 */
	public function test_get_all_returns_empty_array_when_no_rules(): void {
		Functions\expect( 'get_option' )
			->once()
			->with( 'vmfa_rules_engine_rules', [] )
			->andReturn( [] );

		$result = $this->repository->get_all();

		$this->assertIsArray( $result );
		$this->assertEmpty( $result );
	}

	/**
	 * Test get_all returns rules sorted by priority.
	 */
	public function test_get_all_returns_rules_sorted_by_priority(): void {
		$rules = [
			[
				'id'       => 'rule_abc',
				'name'     => 'Rule B',
				'priority' => 20,
			],
			[
				'id'       => 'rule_xyz',
				'name'     => 'Rule A',
				'priority' => 5,
			],
			[
				'id'       => 'rule_def',
				'name'     => 'Rule C',
				'priority' => 10,
			],
		];

		Functions\expect( 'get_option' )
			->once()
			->with( 'vmfa_rules_engine_rules', [] )
			->andReturn( $rules );

		$result = $this->repository->get_all();

		$this->assertCount( 3, $result );
		$this->assertEquals( 'rule_xyz', $result[ 0 ][ 'id' ] ); // Priority 5.
		$this->assertEquals( 'rule_def', $result[ 1 ][ 'id' ] ); // Priority 10.
		$this->assertEquals( 'rule_abc', $result[ 2 ][ 'id' ] ); // Priority 20.
	}

	/**
	 * Test get_enabled returns only enabled rules.
	 */
	public function test_get_enabled_returns_only_enabled_rules(): void {
		$rules = [
			[
				'id'       => 'rule_1',
				'name'     => 'Enabled Rule',
				'priority' => 1,
				'enabled'  => true,
			],
			[
				'id'       => 'rule_2',
				'name'     => 'Disabled Rule',
				'priority' => 2,
				'enabled'  => false,
			],
			[
				'id'       => 'rule_3',
				'name'     => 'Another Enabled',
				'priority' => 3,
				'enabled'  => true,
			],
		];

		Functions\expect( 'get_option' )
			->once()
			->with( 'vmfa_rules_engine_rules', [] )
			->andReturn( $rules );

		$result = $this->repository->get_enabled();

		$this->assertCount( 2, $result );
		$ids = array_column( $result, 'id' );
		$this->assertContains( 'rule_1', $ids );
		$this->assertContains( 'rule_3', $ids );
		$this->assertNotContains( 'rule_2', $ids );
	}

	/**
	 * Test get returns a single rule by ID.
	 */
	public function test_get_returns_rule_by_id(): void {
		$rules = [
			[
				'id'   => 'rule_1',
				'name' => 'First Rule',
			],
			[
				'id'   => 'rule_2',
				'name' => 'Second Rule',
			],
		];

		Functions\expect( 'get_option' )
			->once()
			->with( 'vmfa_rules_engine_rules', [] )
			->andReturn( $rules );

		$result = $this->repository->get( 'rule_2' );

		$this->assertNotNull( $result );
		$this->assertEquals( 'rule_2', $result[ 'id' ] );
		$this->assertEquals( 'Second Rule', $result[ 'name' ] );
	}

	/**
	 * Test get returns null for non-existent rule.
	 */
	public function test_get_returns_null_for_nonexistent_rule(): void {
		Functions\expect( 'get_option' )
			->once()
			->with( 'vmfa_rules_engine_rules', [] )
			->andReturn( [] );

		$result = $this->repository->get( 'nonexistent' );

		$this->assertNull( $result );
	}

	/**
	 * Test create adds a new rule.
	 */
	public function test_create_adds_new_rule(): void {
		Functions\expect( 'get_option' )
			->once()
			->with( 'vmfa_rules_engine_rules', [] )
			->andReturn( [] );

		Functions\expect( 'wp_generate_password' )
			->once()
			->with( 8, false, false )
			->andReturn( 'abcd1234' );

		Functions\expect( 'update_option' )
			->once()
			->andReturnUsing(
				function ( $option, $value ) {
					$this->assertEquals( 'vmfa_rules_engine_rules', $option );
					$this->assertCount( 1, $value );
					$this->assertEquals( 'rule_abcd1234', $value[ 0 ][ 'id' ] );
					$this->assertEquals( 'Test Rule', $value[ 0 ][ 'name' ] );
					return true;
				}
			);

		$data = [
			'name'            => 'Test Rule',
			'folder_id'       => 5,
			'conditions'      => [],
			'stop_processing' => true,
			'enabled'         => true,
		];

		$result = $this->repository->create( $data );

		$this->assertEquals( 'rule_abcd1234', $result[ 'id' ] );
		$this->assertEquals( 'Test Rule', $result[ 'name' ] );
		$this->assertEquals( 5, $result[ 'folder_id' ] );
	}

	/**
	 * Test update modifies existing rule.
	 */
	public function test_update_modifies_existing_rule(): void {
		$existing_rules = [
			[
				'id'              => 'rule_1',
				'name'            => 'Original Name',
				'folder_id'       => 1,
				'conditions'      => [],
				'priority'        => 1,
				'stop_processing' => false,
				'enabled'         => true,
			],
		];

		Functions\expect( 'get_option' )
			->times( 2 )
			->with( 'vmfa_rules_engine_rules', [] )
			->andReturn(
				$existing_rules,
				[
					[
						'id'              => 'rule_1',
						'name'            => 'Updated Name',
						'folder_id'       => 10,
						'conditions'      => [],
						'priority'        => 1,
						'stop_processing' => true,
						'enabled'         => true,
					],
				]
			);

		Functions\expect( 'update_option' )
			->once()
			->andReturn( true );

		$result = $this->repository->update(
			'rule_1',
			[
				'name'            => 'Updated Name',
				'folder_id'       => 10,
				'conditions'      => [],
				'priority'        => 1,
				'stop_processing' => true,
				'enabled'         => true,
			]
		);

		$this->assertNotNull( $result );
		$this->assertEquals( 'Updated Name', $result[ 'name' ] );
		$this->assertEquals( 10, $result[ 'folder_id' ] );
	}

	/**
	 * Test update returns null for non-existent rule.
	 */
	public function test_update_returns_null_for_nonexistent_rule(): void {
		Functions\expect( 'get_option' )
			->once()
			->with( 'vmfa_rules_engine_rules', [] )
			->andReturn( [] );

		$result = $this->repository->update( 'nonexistent', [ 'name' => 'Test' ] );

		$this->assertNull( $result );
	}

	/**
	 * Test delete removes a rule.
	 */
	public function test_delete_removes_rule(): void {
		$existing_rules = [
			[
				'id'   => 'rule_1',
				'name' => 'Keep This',
			],
			[
				'id'   => 'rule_2',
				'name' => 'Delete This',
			],
		];

		Functions\expect( 'get_option' )
			->once()
			->with( 'vmfa_rules_engine_rules', [] )
			->andReturn( $existing_rules );

		Functions\expect( 'update_option' )
			->once()
			->andReturnUsing(
				function ( $option, $value ) {
					$this->assertCount( 1, $value );
					$this->assertEquals( 'rule_1', $value[ 0 ][ 'id' ] );
					return true;
				}
			);

		$result = $this->repository->delete( 'rule_2' );

		$this->assertTrue( $result );
	}

	/**
	 * Test delete returns false for non-existent rule.
	 */
	public function test_delete_returns_false_for_nonexistent_rule(): void {
		Functions\expect( 'get_option' )
			->once()
			->with( 'vmfa_rules_engine_rules', [] )
			->andReturn( [] );

		$result = $this->repository->delete( 'nonexistent' );

		$this->assertFalse( $result );
	}

	/**
	 * Test reorder updates rule priorities.
	 */
	public function test_reorder_updates_priorities(): void {
		$existing_rules = [
			[
				'id'       => 'rule_a',
				'name'     => 'Rule A',
				'priority' => 1,
			],
			[
				'id'       => 'rule_b',
				'name'     => 'Rule B',
				'priority' => 2,
			],
			[
				'id'       => 'rule_c',
				'name'     => 'Rule C',
				'priority' => 3,
			],
		];

		Functions\expect( 'get_option' )
			->once()
			->with( 'vmfa_rules_engine_rules', [] )
			->andReturn( $existing_rules );

		Functions\expect( 'update_option' )
			->once()
			->andReturnUsing(
				function ( $option, $value ) {
					// Check new order: C, A, B.
					$this->assertEquals( 'rule_c', $value[ 0 ][ 'id' ] );
					$this->assertEquals( 1, $value[ 0 ][ 'priority' ] );
					$this->assertEquals( 'rule_a', $value[ 1 ][ 'id' ] );
					$this->assertEquals( 2, $value[ 1 ][ 'priority' ] );
					$this->assertEquals( 'rule_b', $value[ 2 ][ 'id' ] );
					$this->assertEquals( 3, $value[ 2 ][ 'priority' ] );
					return true;
				}
			);

		$result = $this->repository->reorder( [ 'rule_c', 'rule_a', 'rule_b' ] );

		$this->assertCount( 3, $result );
	}
}
