/**
 * Tests for rule data structures and validation.
 *
 * @package
 */

import { describe, it, expect } from 'vitest';

/**
 * Validate a rule object.
 *
 * @param {Object} rule Rule to validate.
 * @return {Object} Validation result with isValid and errors.
 */
function validateRule( rule ) {
	const errors = [];

	if ( ! rule.name || rule.name.trim() === '' ) {
		errors.push( 'Rule name is required' );
	}

	if ( ! rule.folder_id ) {
		errors.push( 'Target folder is required' );
	}

	if ( ! Array.isArray( rule.conditions ) || rule.conditions.length === 0 ) {
		errors.push( 'At least one condition is required' );
	}

	return {
		isValid: errors.length === 0,
		errors,
	};
}

/**
 * Create a default rule object.
 *
 * @param {Object} overrides Optional property overrides.
 * @return {Object} Default rule object.
 */
function createDefaultRule( overrides = {} ) {
	return {
		id: '',
		name: '',
		conditions: [],
		folder_id: null,
		priority: 10,
		stop_processing: true,
		enabled: true,
		...overrides,
	};
}

/**
 * Sort rules by priority.
 *
 * @param {Array} rules Rules array.
 * @return {Array} Sorted rules.
 */
function sortRulesByPriority( rules ) {
	return [ ...rules ].sort( ( a, b ) => a.priority - b.priority );
}

/**
 * Find first matching rule for conditions.
 *
 * @param {Array}  rules      Rules array.
 * @param {Object} attachment Mock attachment data.
 * @return {Object|null} First matching rule or null.
 */
function findMatchingRule( rules, attachment ) {
	const sortedRules = sortRulesByPriority( rules ).filter(
		( r ) => r.enabled
	);

	for ( const rule of sortedRules ) {
		const allConditionsMatch = rule.conditions.every( ( condition ) => {
			switch ( condition.type ) {
				case 'mime_type':
					if ( condition.value.endsWith( '/*' ) ) {
						const prefix = condition.value.replace( '/*', '' );
						return attachment.mime_type.startsWith( prefix );
					}
					return attachment.mime_type === condition.value;

				case 'filename_regex':
					try {
						const regex = new RegExp( condition.value, 'i' );
						return regex.test( attachment.filename );
					} catch ( e ) {
						return false;
					}

				case 'dimensions':
					const dim =
						condition.dimension === 'both'
							? Math.min( attachment.width, attachment.height )
							: attachment[ condition.dimension ];
					return compareValue(
						dim,
						condition.operator,
						condition.value,
						condition.value_end
					);

				case 'file_size':
					return compareValue(
						attachment.file_size,
						condition.operator,
						condition.value,
						condition.value_end
					);

				case 'author':
					return (
						attachment.author_id === parseInt( condition.value, 10 )
					);

				default:
					return false;
			}
		} );

		if ( allConditionsMatch ) {
			return rule;
		}
	}

	return null;
}

/**
 * Compare value with operator.
 *
 * @param {number} value     Value to compare.
 * @param {string} operator  Comparison operator.
 * @param {number} target    Target value.
 * @param {number} targetEnd End value for between operator.
 * @return {boolean} Comparison result.
 */
function compareValue( value, operator, target, targetEnd ) {
	switch ( operator ) {
		case 'gt':
			return value > target;
		case 'gte':
			return value >= target;
		case 'lt':
			return value < target;
		case 'lte':
			return value <= target;
		case 'eq':
			return value === target;
		case 'between':
			return value >= target && value <= targetEnd;
		default:
			return false;
	}
}

describe( 'validateRule', () => {
	it( 'should validate a complete rule', () => {
		const rule = {
			name: 'Photo Rule',
			folder_id: 1,
			conditions: [ { type: 'mime_type', value: 'image/*' } ],
		};

		const result = validateRule( rule );

		expect( result.isValid ).toBe( true );
		expect( result.errors ).toHaveLength( 0 );
	} );

	it( 'should reject rule without name', () => {
		const rule = {
			name: '',
			folder_id: 1,
			conditions: [ { type: 'mime_type', value: 'image/*' } ],
		};

		const result = validateRule( rule );

		expect( result.isValid ).toBe( false );
		expect( result.errors ).toContain( 'Rule name is required' );
	} );

	it( 'should reject rule with whitespace-only name', () => {
		const rule = {
			name: '   ',
			folder_id: 1,
			conditions: [ { type: 'mime_type', value: 'image/*' } ],
		};

		const result = validateRule( rule );

		expect( result.isValid ).toBe( false );
		expect( result.errors ).toContain( 'Rule name is required' );
	} );

	it( 'should reject rule without folder', () => {
		const rule = {
			name: 'Test Rule',
			folder_id: null,
			conditions: [ { type: 'mime_type', value: 'image/*' } ],
		};

		const result = validateRule( rule );

		expect( result.isValid ).toBe( false );
		expect( result.errors ).toContain( 'Target folder is required' );
	} );

	it( 'should reject rule without conditions', () => {
		const rule = {
			name: 'Test Rule',
			folder_id: 1,
			conditions: [],
		};

		const result = validateRule( rule );

		expect( result.isValid ).toBe( false );
		expect( result.errors ).toContain(
			'At least one condition is required'
		);
	} );

	it( 'should collect multiple errors', () => {
		const rule = {
			name: '',
			folder_id: null,
			conditions: [],
		};

		const result = validateRule( rule );

		expect( result.isValid ).toBe( false );
		expect( result.errors ).toHaveLength( 3 );
	} );
} );

describe( 'createDefaultRule', () => {
	it( 'should create rule with default values', () => {
		const rule = createDefaultRule();

		expect( rule ).toEqual( {
			id: '',
			name: '',
			conditions: [],
			folder_id: null,
			priority: 10,
			stop_processing: true,
			enabled: true,
		} );
	} );

	it( 'should allow overriding default values', () => {
		const rule = createDefaultRule( {
			name: 'My Rule',
			priority: 5,
			enabled: false,
		} );

		expect( rule.name ).toBe( 'My Rule' );
		expect( rule.priority ).toBe( 5 );
		expect( rule.enabled ).toBe( false );
		expect( rule.stop_processing ).toBe( true ); // Not overridden.
	} );
} );

describe( 'sortRulesByPriority', () => {
	it( 'should sort rules by priority ascending', () => {
		const rules = [
			{ id: 'c', name: 'Rule C', priority: 30 },
			{ id: 'a', name: 'Rule A', priority: 10 },
			{ id: 'b', name: 'Rule B', priority: 20 },
		];

		const sorted = sortRulesByPriority( rules );

		expect( sorted[ 0 ].id ).toBe( 'a' );
		expect( sorted[ 1 ].id ).toBe( 'b' );
		expect( sorted[ 2 ].id ).toBe( 'c' );
	} );

	it( 'should not mutate original array', () => {
		const rules = [
			{ id: 'b', priority: 20 },
			{ id: 'a', priority: 10 },
		];

		sortRulesByPriority( rules );

		expect( rules[ 0 ].id ).toBe( 'b' );
	} );
} );

describe( 'findMatchingRule', () => {
	const rules = [
		{
			id: 'rule_photos',
			name: 'Photos',
			folder_id: 1,
			conditions: [ { type: 'mime_type', value: 'image/*' } ],
			priority: 10,
			enabled: true,
		},
		{
			id: 'rule_docs',
			name: 'Documents',
			folder_id: 2,
			conditions: [ { type: 'mime_type', value: 'application/pdf' } ],
			priority: 20,
			enabled: true,
		},
		{
			id: 'rule_iphone',
			name: 'iPhone Photos',
			folder_id: 3,
			conditions: [
				{ type: 'mime_type', value: 'image/*' },
				{ type: 'filename_regex', value: '^IMG_' },
			],
			priority: 5,
			enabled: true,
		},
		{
			id: 'rule_disabled',
			name: 'Disabled Rule',
			folder_id: 4,
			conditions: [ { type: 'mime_type', value: 'video/*' } ],
			priority: 1,
			enabled: false,
		},
	];

	it( 'should match MIME type wildcard', () => {
		const attachment = {
			mime_type: 'image/jpeg',
			filename: 'photo.jpg',
		};

		const match = findMatchingRule( rules, attachment );

		expect( match ).not.toBeNull();
		// rule_iphone has higher priority (5) but requires both conditions.
		// Since filename doesn't match ^IMG_, it falls through to rule_photos (priority 10).
		expect( match.id ).toBe( 'rule_photos' );
	} );

	it( 'should match exact MIME type', () => {
		const attachment = {
			mime_type: 'application/pdf',
			filename: 'document.pdf',
		};

		const match = findMatchingRule( rules, attachment );

		expect( match ).not.toBeNull();
		expect( match.id ).toBe( 'rule_docs' );
	} );

	it( 'should match multiple conditions (AND logic)', () => {
		const attachment = {
			mime_type: 'image/jpeg',
			filename: 'IMG_1234.jpg',
		};

		const match = findMatchingRule( rules, attachment );

		expect( match ).not.toBeNull();
		expect( match.id ).toBe( 'rule_iphone' ); // Has highest priority and matches both conditions.
	} );

	it( 'should skip disabled rules', () => {
		const attachment = {
			mime_type: 'video/mp4',
			filename: 'movie.mp4',
		};

		const match = findMatchingRule( rules, attachment );

		expect( match ).toBeNull(); // Disabled rule should not match.
	} );

	it( 'should return null when no rules match', () => {
		const attachment = {
			mime_type: 'audio/mp3',
			filename: 'song.mp3',
		};

		const match = findMatchingRule( rules, attachment );

		expect( match ).toBeNull();
	} );

	it( 'should match filename regex case-insensitively', () => {
		const attachment = {
			mime_type: 'image/jpeg',
			filename: 'img_5678.jpg',
		};

		const match = findMatchingRule( rules, attachment );

		expect( match ).not.toBeNull();
		expect( match.id ).toBe( 'rule_iphone' );
	} );
} );

describe( 'compareValue', () => {
	it( 'should compare greater than', () => {
		expect( compareValue( 100, 'gt', 50 ) ).toBe( true );
		expect( compareValue( 50, 'gt', 50 ) ).toBe( false );
		expect( compareValue( 25, 'gt', 50 ) ).toBe( false );
	} );

	it( 'should compare greater than or equal', () => {
		expect( compareValue( 100, 'gte', 50 ) ).toBe( true );
		expect( compareValue( 50, 'gte', 50 ) ).toBe( true );
		expect( compareValue( 25, 'gte', 50 ) ).toBe( false );
	} );

	it( 'should compare less than', () => {
		expect( compareValue( 25, 'lt', 50 ) ).toBe( true );
		expect( compareValue( 50, 'lt', 50 ) ).toBe( false );
		expect( compareValue( 100, 'lt', 50 ) ).toBe( false );
	} );

	it( 'should compare less than or equal', () => {
		expect( compareValue( 25, 'lte', 50 ) ).toBe( true );
		expect( compareValue( 50, 'lte', 50 ) ).toBe( true );
		expect( compareValue( 100, 'lte', 50 ) ).toBe( false );
	} );

	it( 'should compare equal', () => {
		expect( compareValue( 50, 'eq', 50 ) ).toBe( true );
		expect( compareValue( 51, 'eq', 50 ) ).toBe( false );
	} );

	it( 'should compare between', () => {
		expect( compareValue( 75, 'between', 50, 100 ) ).toBe( true );
		expect( compareValue( 50, 'between', 50, 100 ) ).toBe( true );
		expect( compareValue( 100, 'between', 50, 100 ) ).toBe( true );
		expect( compareValue( 25, 'between', 50, 100 ) ).toBe( false );
		expect( compareValue( 125, 'between', 50, 100 ) ).toBe( false );
	} );

	it( 'should return false for unknown operator', () => {
		expect( compareValue( 50, 'unknown', 50 ) ).toBe( false );
	} );
} );

describe( 'dimension matching', () => {
	const dimensionRules = [
		{
			id: 'rule_4k',
			name: '4K Images',
			folder_id: 1,
			conditions: [
				{
					type: 'dimensions',
					dimension: 'width',
					operator: 'gte',
					value: 3840,
				},
			],
			priority: 10,
			enabled: true,
		},
		{
			id: 'rule_hd',
			name: 'HD Images',
			folder_id: 2,
			conditions: [
				{
					type: 'dimensions',
					dimension: 'width',
					operator: 'between',
					value: 1920,
					value_end: 3839,
				},
			],
			priority: 20,
			enabled: true,
		},
	];

	it( 'should match 4K width', () => {
		const attachment = {
			mime_type: 'image/jpeg',
			filename: 'photo.jpg',
			width: 4096,
			height: 2160,
		};

		const match = findMatchingRule( dimensionRules, attachment );

		expect( match ).not.toBeNull();
		expect( match.id ).toBe( 'rule_4k' );
	} );

	it( 'should match HD width range', () => {
		const attachment = {
			mime_type: 'image/jpeg',
			filename: 'photo.jpg',
			width: 2560,
			height: 1440,
		};

		const match = findMatchingRule( dimensionRules, attachment );

		expect( match ).not.toBeNull();
		expect( match.id ).toBe( 'rule_hd' );
	} );
} );

describe( 'author matching', () => {
	const authorRules = [
		{
			id: 'rule_admin',
			name: 'Admin Uploads',
			folder_id: 1,
			conditions: [ { type: 'author', value: '1' } ],
			priority: 10,
			enabled: true,
		},
	];

	it( 'should match author ID', () => {
		const attachment = {
			mime_type: 'image/jpeg',
			filename: 'photo.jpg',
			author_id: 1,
		};

		const match = findMatchingRule( authorRules, attachment );

		expect( match ).not.toBeNull();
		expect( match.id ).toBe( 'rule_admin' );
	} );

	it( 'should not match different author', () => {
		const attachment = {
			mime_type: 'image/jpeg',
			filename: 'photo.jpg',
			author_id: 2,
		};

		const match = findMatchingRule( authorRules, attachment );

		expect( match ).toBeNull();
	} );
} );
