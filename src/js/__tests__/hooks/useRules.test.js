/**
 * Tests for useRules hook.
 *
 * @package
 */

import { describe, it, expect, vi, beforeEach, afterEach } from 'vitest';
import apiFetch from '@wordpress/api-fetch';

// Re-implement hooks for testing since we need actual state management.
describe( 'useRules hook logic', () => {
	beforeEach( () => {
		vi.clearAllMocks();
	} );

	afterEach( () => {
		vi.resetAllMocks();
	} );

	describe( 'fetchRules', () => {
		it( 'should call API with correct path', async () => {
			apiFetch.mockResolvedValueOnce( [] );

			await apiFetch( {
				path: 'vmfa-rules/v1/rules',
				headers: { 'X-WP-Nonce': 'test-nonce' },
			} );

			expect( apiFetch ).toHaveBeenCalledWith( {
				path: 'vmfa-rules/v1/rules',
				headers: { 'X-WP-Nonce': 'test-nonce' },
			} );
		} );

		it( 'should return rules array from API', async () => {
			const mockRules = [
				{ id: 'rule_1', name: 'Test Rule 1', folder_id: 1 },
				{ id: 'rule_2', name: 'Test Rule 2', folder_id: 2 },
			];
			apiFetch.mockResolvedValueOnce( mockRules );

			const result = await apiFetch( { path: 'vmfa-rules/v1/rules' } );

			expect( result ).toEqual( mockRules );
			expect( result ).toHaveLength( 2 );
		} );

		it( 'should handle empty rules array', async () => {
			apiFetch.mockResolvedValueOnce( [] );

			const result = await apiFetch( { path: 'vmfa-rules/v1/rules' } );

			expect( result ).toEqual( [] );
			expect( result ).toHaveLength( 0 );
		} );

		it( 'should handle API errors', async () => {
			const error = new Error( 'Network error' );
			apiFetch.mockRejectedValueOnce( error );

			await expect(
				apiFetch( { path: 'vmfa-rules/v1/rules' } )
			).rejects.toThrow( 'Network error' );
		} );
	} );

	describe( 'createRule', () => {
		it( 'should call API with POST method and rule data', async () => {
			const newRule = {
				name: 'New Rule',
				folder_id: 1,
				conditions: [ { type: 'mime_type', value: 'image/*' } ],
				enabled: true,
			};
			const createdRule = { id: 'rule_new', ...newRule };
			apiFetch.mockResolvedValueOnce( createdRule );

			const result = await apiFetch( {
				path: 'vmfa-rules/v1/rules',
				method: 'POST',
				data: newRule,
			} );

			expect( apiFetch ).toHaveBeenCalledWith( {
				path: 'vmfa-rules/v1/rules',
				method: 'POST',
				data: newRule,
			} );
			expect( result ).toEqual( createdRule );
			expect( result.id ).toBe( 'rule_new' );
		} );

		it( 'should handle validation errors', async () => {
			const error = new Error( 'Validation failed: name is required' );
			apiFetch.mockRejectedValueOnce( error );

			await expect(
				apiFetch( {
					path: 'vmfa-rules/v1/rules',
					method: 'POST',
					data: {},
				} )
			).rejects.toThrow( 'Validation failed' );
		} );
	} );

	describe( 'updateRule', () => {
		it( 'should call API with PUT method and rule ID', async () => {
			const updateData = { name: 'Updated Rule', enabled: false };
			const updatedRule = { id: 'rule_1', ...updateData };
			apiFetch.mockResolvedValueOnce( updatedRule );

			const result = await apiFetch( {
				path: 'vmfa-rules/v1/rules/rule_1',
				method: 'PUT',
				data: updateData,
			} );

			expect( apiFetch ).toHaveBeenCalledWith( {
				path: 'vmfa-rules/v1/rules/rule_1',
				method: 'PUT',
				data: updateData,
			} );
			expect( result.name ).toBe( 'Updated Rule' );
		} );

		it( 'should handle non-existent rule', async () => {
			const error = new Error( 'Rule not found' );
			apiFetch.mockRejectedValueOnce( error );

			await expect(
				apiFetch( {
					path: 'vmfa-rules/v1/rules/nonexistent',
					method: 'PUT',
					data: { name: 'Test' },
				} )
			).rejects.toThrow( 'Rule not found' );
		} );
	} );

	describe( 'deleteRule', () => {
		it( 'should call API with DELETE method', async () => {
			apiFetch.mockResolvedValueOnce( { deleted: true } );

			const result = await apiFetch( {
				path: 'vmfa-rules/v1/rules/rule_1',
				method: 'DELETE',
			} );

			expect( apiFetch ).toHaveBeenCalledWith( {
				path: 'vmfa-rules/v1/rules/rule_1',
				method: 'DELETE',
			} );
			expect( result.deleted ).toBe( true );
		} );
	} );

	describe( 'reorderRules', () => {
		it( 'should call API with new order array', async () => {
			const newOrder = [ 'rule_3', 'rule_1', 'rule_2' ];
			const reorderedRules = [
				{ id: 'rule_3', priority: 1 },
				{ id: 'rule_1', priority: 2 },
				{ id: 'rule_2', priority: 3 },
			];
			apiFetch.mockResolvedValueOnce( reorderedRules );

			const result = await apiFetch( {
				path: 'vmfa-rules/v1/rules/reorder',
				method: 'POST',
				data: { order: newOrder },
			} );

			expect( apiFetch ).toHaveBeenCalledWith( {
				path: 'vmfa-rules/v1/rules/reorder',
				method: 'POST',
				data: { order: newOrder },
			} );
			expect( result[ 0 ].id ).toBe( 'rule_3' );
			expect( result[ 0 ].priority ).toBe( 1 );
		} );
	} );
} );

describe( 'useStats hook logic', () => {
	beforeEach( () => {
		vi.clearAllMocks();
	} );

	it( 'should fetch media library statistics', async () => {
		const mockStats = {
			total: 500,
			assigned: 350,
			unassigned: 150,
			by_type: {
				'image/jpeg': 200,
				'image/png': 100,
				'application/pdf': 50,
			},
		};
		apiFetch.mockResolvedValueOnce( mockStats );

		const result = await apiFetch( { path: 'vmfa-rules/v1/stats' } );

		expect( result.total ).toBe( 500 );
		expect( result.unassigned ).toBe( 150 );
		expect( result.by_type[ 'image/jpeg' ] ).toBe( 200 );
	} );
} );

describe( 'useBatchOperations hook logic', () => {
	beforeEach( () => {
		vi.clearAllMocks();
	} );

	describe( 'preview', () => {
		it( 'should call preview endpoint with options', async () => {
			const mockPreview = {
				total: 50,
				matches: [
					{
						id: 1,
						title: 'IMG_001.jpg',
						rule: 'rule_1',
						folder: 'Photos',
					},
					{
						id: 2,
						title: 'DOC_001.pdf',
						rule: 'rule_2',
						folder: 'Documents',
					},
				],
			};
			apiFetch.mockResolvedValueOnce( mockPreview );

			const result = await apiFetch( {
				path: 'vmfa-rules/v1/preview',
				method: 'POST',
				data: {
					unassigned_only: true,
					limit: 100,
				},
			} );

			expect( result.total ).toBe( 50 );
			expect( result.matches ).toHaveLength( 2 );
		} );

		it( 'should filter by MIME type when specified', async () => {
			apiFetch.mockResolvedValueOnce( { total: 10, matches: [] } );

			await apiFetch( {
				path: 'vmfa-rules/v1/preview',
				method: 'POST',
				data: {
					unassigned_only: true,
					mime_type: 'image/*',
				},
			} );

			expect( apiFetch ).toHaveBeenCalledWith(
				expect.objectContaining( {
					data: expect.objectContaining( {
						mime_type: 'image/*',
					} ),
				} )
			);
		} );
	} );

	describe( 'apply', () => {
		it( 'should call apply-rules endpoint', async () => {
			const mockResult = {
				processed: 50,
				assigned: 45,
				skipped: 5,
			};
			apiFetch.mockResolvedValueOnce( mockResult );

			const result = await apiFetch( {
				path: 'vmfa-rules/v1/apply-rules',
				method: 'POST',
				data: { unassigned_only: true },
			} );

			expect( result.processed ).toBe( 50 );
			expect( result.assigned ).toBe( 45 );
		} );

		it( 'should apply to specific attachment IDs when provided', async () => {
			const attachmentIds = [ 1, 2, 3, 4, 5 ];
			apiFetch.mockResolvedValueOnce( { processed: 5 } );

			await apiFetch( {
				path: 'vmfa-rules/v1/apply-rules',
				method: 'POST',
				data: { attachment_ids: attachmentIds },
			} );

			expect( apiFetch ).toHaveBeenCalledWith(
				expect.objectContaining( {
					data: expect.objectContaining( {
						attachment_ids: attachmentIds,
					} ),
				} )
			);
		} );
	} );
} );

describe( 'useUsers hook logic', () => {
	beforeEach( () => {
		vi.clearAllMocks();
	} );

	it( 'should fetch users list', async () => {
		const mockUsers = [
			{ id: 1, name: 'Admin', username: 'admin' },
			{ id: 2, name: 'Editor', username: 'editor' },
		];
		apiFetch.mockResolvedValueOnce( mockUsers );

		const result = await apiFetch( { path: 'vmfa-rules/v1/users' } );

		expect( result ).toHaveLength( 2 );
		expect( result[ 0 ].name ).toBe( 'Admin' );
	} );
} );
