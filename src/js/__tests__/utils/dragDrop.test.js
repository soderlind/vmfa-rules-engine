/**
 * Tests for drag-and-drop reordering utilities.
 *
 * @package
 */

import { describe, it, expect } from 'vitest';
import { arrayMove } from '@dnd-kit/sortable';

describe( 'arrayMove', () => {
	it( 'should move item forward in array', () => {
		const items = [ 'a', 'b', 'c', 'd' ];
		const result = arrayMove( items, 0, 2 );

		expect( result ).toEqual( [ 'b', 'c', 'a', 'd' ] );
	} );

	it( 'should move item backward in array', () => {
		const items = [ 'a', 'b', 'c', 'd' ];
		const result = arrayMove( items, 3, 1 );

		expect( result ).toEqual( [ 'a', 'd', 'b', 'c' ] );
	} );

	it( 'should handle moving to same position', () => {
		const items = [ 'a', 'b', 'c' ];
		const result = arrayMove( items, 1, 1 );

		expect( result ).toEqual( [ 'a', 'b', 'c' ] );
	} );

	it( 'should not mutate original array', () => {
		const items = [ 'a', 'b', 'c' ];
		arrayMove( items, 0, 2 );

		expect( items ).toEqual( [ 'a', 'b', 'c' ] );
	} );

	it( 'should work with objects', () => {
		const items = [
			{ id: 1, name: 'First' },
			{ id: 2, name: 'Second' },
			{ id: 3, name: 'Third' },
		];

		const result = arrayMove( items, 2, 0 );

		expect( result[ 0 ].id ).toBe( 3 );
		expect( result[ 1 ].id ).toBe( 1 );
		expect( result[ 2 ].id ).toBe( 2 );
	} );
} );

/**
 * Calculate new priorities after reordering.
 *
 * @param {Array}  rules Rules array after arrayMove.
 * @param {number} step  Priority increment step.
 * @return {Array} Rules with updated priorities.
 */
function recalculatePriorities( rules, step = 10 ) {
	return rules.map( ( rule, index ) => ( {
		...rule,
		priority: ( index + 1 ) * step,
	} ) );
}

/**
 * Get rule IDs in order.
 *
 * @param {Array} rules Rules array.
 * @return {Array<string>} Array of rule IDs.
 */
function getRuleOrder( rules ) {
	return rules.map( ( rule ) => rule.id );
}

describe( 'recalculatePriorities', () => {
	it( 'should assign priorities in increments of 10', () => {
		const rules = [
			{ id: 'a', name: 'A', priority: 50 },
			{ id: 'b', name: 'B', priority: 5 },
			{ id: 'c', name: 'C', priority: 100 },
		];

		const result = recalculatePriorities( rules );

		expect( result[ 0 ].priority ).toBe( 10 );
		expect( result[ 1 ].priority ).toBe( 20 );
		expect( result[ 2 ].priority ).toBe( 30 );
	} );

	it( 'should allow custom step', () => {
		const rules = [
			{ id: 'a', name: 'A' },
			{ id: 'b', name: 'B' },
		];

		const result = recalculatePriorities( rules, 5 );

		expect( result[ 0 ].priority ).toBe( 5 );
		expect( result[ 1 ].priority ).toBe( 10 );
	} );

	it( 'should preserve other rule properties', () => {
		const rules = [ { id: 'a', name: 'A', folder_id: 1, enabled: true } ];

		const result = recalculatePriorities( rules );

		expect( result[ 0 ].id ).toBe( 'a' );
		expect( result[ 0 ].name ).toBe( 'A' );
		expect( result[ 0 ].folder_id ).toBe( 1 );
		expect( result[ 0 ].enabled ).toBe( true );
	} );
} );

describe( 'getRuleOrder', () => {
	it( 'should return array of rule IDs', () => {
		const rules = [
			{ id: 'rule_1', name: 'First' },
			{ id: 'rule_2', name: 'Second' },
			{ id: 'rule_3', name: 'Third' },
		];

		const order = getRuleOrder( rules );

		expect( order ).toEqual( [ 'rule_1', 'rule_2', 'rule_3' ] );
	} );

	it( 'should return empty array for empty rules', () => {
		const order = getRuleOrder( [] );

		expect( order ).toEqual( [] );
	} );
} );

describe( 'full reorder workflow', () => {
	it( 'should reorder rules and update priorities', () => {
		const rules = [
			{ id: 'a', name: 'Rule A', priority: 10 },
			{ id: 'b', name: 'Rule B', priority: 20 },
			{ id: 'c', name: 'Rule C', priority: 30 },
		];

		// Move 'c' to the beginning.
		const reordered = arrayMove( rules, 2, 0 );
		const withPriorities = recalculatePriorities( reordered );
		const order = getRuleOrder( withPriorities );

		expect( order ).toEqual( [ 'c', 'a', 'b' ] );
		expect( withPriorities[ 0 ].priority ).toBe( 10 );
		expect( withPriorities[ 1 ].priority ).toBe( 20 );
		expect( withPriorities[ 2 ].priority ).toBe( 30 );
	} );

	it( 'should handle complex reordering', () => {
		const rules = [
			{ id: '1', priority: 10 },
			{ id: '2', priority: 20 },
			{ id: '3', priority: 30 },
			{ id: '4', priority: 40 },
			{ id: '5', priority: 50 },
		];

		// Multiple moves.
		let result = arrayMove( rules, 4, 1 ); // Move 5 to position 1.
		result = arrayMove( result, 0, 4 ); // Move 1 to position 4.
		result = recalculatePriorities( result );

		const order = getRuleOrder( result );
		expect( order[ 0 ] ).toBe( '5' );
		expect(
			result.every( ( r, i ) => r.priority === ( i + 1 ) * 10 )
		).toBe( true );
	} );
} );
