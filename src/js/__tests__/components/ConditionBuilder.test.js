/**
 * Tests for ConditionBuilder utility functions.
 *
 * @package VmfaRulesEngine
 */

import { describe, it, expect, beforeEach } from 'vitest';

/**
 * Get default value for a condition type.
 * Extracted from ConditionBuilder for testing.
 *
 * @param {Object} conditionType Condition type definition.
 * @return {Object} Default condition value.
 */
function getDefaultCondition( conditionType ) {
	const condition = { type: conditionType.value };

	switch ( conditionType.inputType ) {
		case 'select':
			condition.value = conditionType.options?.[ 0 ]?.value || '';
			break;
		case 'dimensions':
			condition.dimension = 'width';
			condition.operator = 'gt';
			condition.value = 1920;
			break;
		case 'filesize':
			condition.operator = 'gt';
			condition.value = 1024;
			break;
		case 'daterange':
			condition.operator = 'after';
			condition.value = '';
			break;
		case 'user':
			condition.value = '';
			break;
		default:
			condition.value = '';
	}

	return condition;
}

/**
 * Validate condition data structure.
 *
 * @param {Object} condition Condition to validate.
 * @return {Object} Validation result with isValid and errors.
 */
function validateCondition( condition ) {
	const errors = [];

	if ( ! condition.type ) {
		errors.push( 'Condition type is required' );
	}

	// Type-specific validation.
	switch ( condition.type ) {
		case 'filename_regex':
			if ( ! condition.value ) {
				errors.push( 'Regex pattern is required' );
			} else {
				try {
					new RegExp( condition.value );
				} catch ( e ) {
					errors.push( 'Invalid regex pattern' );
				}
			}
			break;

		case 'mime_type':
			if ( ! condition.value ) {
				errors.push( 'MIME type is required' );
			}
			break;

		case 'dimensions':
			if ( ! condition.dimension ) {
				errors.push( 'Dimension field is required' );
			}
			if ( ! condition.operator ) {
				errors.push( 'Operator is required' );
			}
			if ( typeof condition.value !== 'number' || condition.value < 0 ) {
				errors.push( 'Value must be a positive number' );
			}
			if ( condition.operator === 'between' && ( ! condition.value_end || condition.value_end <= condition.value ) ) {
				errors.push( 'End value must be greater than start value' );
			}
			break;

		case 'file_size':
			if ( ! condition.operator ) {
				errors.push( 'Operator is required' );
			}
			if ( typeof condition.value !== 'number' || condition.value < 0 ) {
				errors.push( 'Value must be a positive number' );
			}
			break;

		case 'exif_camera':
		case 'iptc_keywords':
			if ( ! condition.value ) {
				errors.push( 'Value is required' );
			}
			break;

		case 'exif_date':
			if ( ! condition.operator ) {
				errors.push( 'Operator is required' );
			}
			if ( ! condition.value ) {
				errors.push( 'Date is required' );
			}
			break;

		case 'author':
			if ( ! condition.value ) {
				errors.push( 'Author is required' );
			}
			break;
	}

	return {
		isValid: errors.length === 0,
		errors,
	};
}

describe( 'getDefaultCondition', () => {
	it( 'should return default for text input type', () => {
		const conditionType = {
			value: 'filename_regex',
			inputType: 'text',
		};

		const result = getDefaultCondition( conditionType );

		expect( result ).toEqual( {
			type: 'filename_regex',
			value: '',
		} );
	} );

	it( 'should return default for select input type with options', () => {
		const conditionType = {
			value: 'mime_type',
			inputType: 'select',
			options: [
				{ value: 'image/*', label: 'All Images' },
				{ value: 'image/jpeg', label: 'JPEG' },
			],
		};

		const result = getDefaultCondition( conditionType );

		expect( result ).toEqual( {
			type: 'mime_type',
			value: 'image/*',
		} );
	} );

	it( 'should return default for select input type without options', () => {
		const conditionType = {
			value: 'mime_type',
			inputType: 'select',
		};

		const result = getDefaultCondition( conditionType );

		expect( result ).toEqual( {
			type: 'mime_type',
			value: '',
		} );
	} );

	it( 'should return default for dimensions input type', () => {
		const conditionType = {
			value: 'dimensions',
			inputType: 'dimensions',
		};

		const result = getDefaultCondition( conditionType );

		expect( result ).toEqual( {
			type: 'dimensions',
			dimension: 'width',
			operator: 'gt',
			value: 1920,
		} );
	} );

	it( 'should return default for filesize input type', () => {
		const conditionType = {
			value: 'file_size',
			inputType: 'filesize',
		};

		const result = getDefaultCondition( conditionType );

		expect( result ).toEqual( {
			type: 'file_size',
			operator: 'gt',
			value: 1024,
		} );
	} );

	it( 'should return default for daterange input type', () => {
		const conditionType = {
			value: 'exif_date',
			inputType: 'daterange',
		};

		const result = getDefaultCondition( conditionType );

		expect( result ).toEqual( {
			type: 'exif_date',
			operator: 'after',
			value: '',
		} );
	} );

	it( 'should return default for user input type', () => {
		const conditionType = {
			value: 'author',
			inputType: 'user',
		};

		const result = getDefaultCondition( conditionType );

		expect( result ).toEqual( {
			type: 'author',
			value: '',
		} );
	} );
} );

describe( 'validateCondition', () => {
	describe( 'filename_regex validation', () => {
		it( 'should validate correct regex pattern', () => {
			const condition = {
				type: 'filename_regex',
				value: '^IMG_\\d+',
			};

			const result = validateCondition( condition );

			expect( result.isValid ).toBe( true );
			expect( result.errors ).toHaveLength( 0 );
		} );

		it( 'should reject empty regex pattern', () => {
			const condition = {
				type: 'filename_regex',
				value: '',
			};

			const result = validateCondition( condition );

			expect( result.isValid ).toBe( false );
			expect( result.errors ).toContain( 'Regex pattern is required' );
		} );

		it( 'should reject invalid regex pattern', () => {
			const condition = {
				type: 'filename_regex',
				value: '[invalid',
			};

			const result = validateCondition( condition );

			expect( result.isValid ).toBe( false );
			expect( result.errors ).toContain( 'Invalid regex pattern' );
		} );
	} );

	describe( 'mime_type validation', () => {
		it( 'should validate correct MIME type', () => {
			const condition = {
				type: 'mime_type',
				value: 'image/jpeg',
			};

			const result = validateCondition( condition );

			expect( result.isValid ).toBe( true );
		} );

		it( 'should validate wildcard MIME type', () => {
			const condition = {
				type: 'mime_type',
				value: 'image/*',
			};

			const result = validateCondition( condition );

			expect( result.isValid ).toBe( true );
		} );

		it( 'should reject empty MIME type', () => {
			const condition = {
				type: 'mime_type',
				value: '',
			};

			const result = validateCondition( condition );

			expect( result.isValid ).toBe( false );
			expect( result.errors ).toContain( 'MIME type is required' );
		} );
	} );

	describe( 'dimensions validation', () => {
		it( 'should validate correct dimensions condition', () => {
			const condition = {
				type: 'dimensions',
				dimension: 'width',
				operator: 'gt',
				value: 1920,
			};

			const result = validateCondition( condition );

			expect( result.isValid ).toBe( true );
		} );

		it( 'should validate between operator with valid range', () => {
			const condition = {
				type: 'dimensions',
				dimension: 'width',
				operator: 'between',
				value: 1280,
				value_end: 1920,
			};

			const result = validateCondition( condition );

			expect( result.isValid ).toBe( true );
		} );

		it( 'should reject between operator with invalid range', () => {
			const condition = {
				type: 'dimensions',
				dimension: 'width',
				operator: 'between',
				value: 1920,
				value_end: 1280,
			};

			const result = validateCondition( condition );

			expect( result.isValid ).toBe( false );
			expect( result.errors ).toContain( 'End value must be greater than start value' );
		} );

		it( 'should reject negative dimension value', () => {
			const condition = {
				type: 'dimensions',
				dimension: 'width',
				operator: 'gt',
				value: -100,
			};

			const result = validateCondition( condition );

			expect( result.isValid ).toBe( false );
			expect( result.errors ).toContain( 'Value must be a positive number' );
		} );

		it( 'should reject missing dimension field', () => {
			const condition = {
				type: 'dimensions',
				operator: 'gt',
				value: 1920,
			};

			const result = validateCondition( condition );

			expect( result.isValid ).toBe( false );
			expect( result.errors ).toContain( 'Dimension field is required' );
		} );
	} );

	describe( 'file_size validation', () => {
		it( 'should validate correct file size condition', () => {
			const condition = {
				type: 'file_size',
				operator: 'gt',
				value: 1024,
			};

			const result = validateCondition( condition );

			expect( result.isValid ).toBe( true );
		} );

		it( 'should reject missing operator', () => {
			const condition = {
				type: 'file_size',
				value: 1024,
			};

			const result = validateCondition( condition );

			expect( result.isValid ).toBe( false );
			expect( result.errors ).toContain( 'Operator is required' );
		} );
	} );

	describe( 'exif_camera validation', () => {
		it( 'should validate correct camera condition', () => {
			const condition = {
				type: 'exif_camera',
				value: 'iPhone',
			};

			const result = validateCondition( condition );

			expect( result.isValid ).toBe( true );
		} );

		it( 'should reject empty camera value', () => {
			const condition = {
				type: 'exif_camera',
				value: '',
			};

			const result = validateCondition( condition );

			expect( result.isValid ).toBe( false );
			expect( result.errors ).toContain( 'Value is required' );
		} );
	} );

	describe( 'exif_date validation', () => {
		it( 'should validate correct date condition', () => {
			const condition = {
				type: 'exif_date',
				operator: 'after',
				value: '2024-01-01',
			};

			const result = validateCondition( condition );

			expect( result.isValid ).toBe( true );
		} );

		it( 'should reject missing date', () => {
			const condition = {
				type: 'exif_date',
				operator: 'after',
				value: '',
			};

			const result = validateCondition( condition );

			expect( result.isValid ).toBe( false );
			expect( result.errors ).toContain( 'Date is required' );
		} );
	} );

	describe( 'author validation', () => {
		it( 'should validate correct author condition', () => {
			const condition = {
				type: 'author',
				value: 1,
			};

			const result = validateCondition( condition );

			expect( result.isValid ).toBe( true );
		} );

		it( 'should reject missing author', () => {
			const condition = {
				type: 'author',
				value: '',
			};

			const result = validateCondition( condition );

			expect( result.isValid ).toBe( false );
			expect( result.errors ).toContain( 'Author is required' );
		} );
	} );

	describe( 'iptc_keywords validation', () => {
		it( 'should validate correct keywords condition', () => {
			const condition = {
				type: 'iptc_keywords',
				value: 'landscape, nature',
			};

			const result = validateCondition( condition );

			expect( result.isValid ).toBe( true );
		} );

		it( 'should reject empty keywords', () => {
			const condition = {
				type: 'iptc_keywords',
				value: '',
			};

			const result = validateCondition( condition );

			expect( result.isValid ).toBe( false );
			expect( result.errors ).toContain( 'Value is required' );
		} );
	} );

	describe( 'general validation', () => {
		it( 'should reject condition without type', () => {
			const condition = {
				value: 'test',
			};

			const result = validateCondition( condition );

			expect( result.isValid ).toBe( false );
			expect( result.errors ).toContain( 'Condition type is required' );
		} );
	} );
} );
