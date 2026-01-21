/**
 * Custom hook for managing rules via REST API.
 *
 * @package
 */

import { useState, useEffect, useCallback } from '@wordpress/element';
import apiFetch from '@wordpress/api-fetch';

/**
 * Hook for fetching and managing rules.
 *
 * @return {Object} Rules state and actions.
 */
export function useRules() {
	const [ rules, setRules ] = useState( [] );
	const [ isLoading, setIsLoading ] = useState( true );
	const [ error, setError ] = useState( null );
	const [ isSaving, setIsSaving ] = useState( false );

	const { restUrl, nonce } = window.vmfaRulesEngine || {};

	/**
	 * Fetch all rules.
	 */
	const fetchRules = useCallback( async () => {
		setIsLoading( true );
		setError( null );

		try {
			const response = await apiFetch( {
				path: 'vmfa-rules/v1/rules',
				headers: { 'X-WP-Nonce': nonce },
			} );
			setRules( response );
		} catch ( err ) {
			setError( err.message || 'Failed to fetch rules' );
		} finally {
			setIsLoading( false );
		}
	}, [ nonce ] );

	/**
	 * Create a new rule.
	 *
	 * @param {Object} data Rule data.
	 * @return {Promise<Object>} Created rule.
	 */
	const createRule = useCallback(
		async ( data ) => {
			setIsSaving( true );
			setError( null );

			try {
				const response = await apiFetch( {
					path: 'vmfa-rules/v1/rules',
					method: 'POST',
					data,
					headers: { 'X-WP-Nonce': nonce },
				} );
				setRules( ( prev ) => [ ...prev, response ] );
				return response;
			} catch ( err ) {
				setError( err.message || 'Failed to create rule' );
				throw err;
			} finally {
				setIsSaving( false );
			}
		},
		[ nonce ]
	);

	/**
	 * Update an existing rule.
	 *
	 * @param {string} id   Rule ID.
	 * @param {Object} data Updated rule data.
	 * @return {Promise<Object>} Updated rule.
	 */
	const updateRule = useCallback(
		async ( id, data ) => {
			setIsSaving( true );
			setError( null );

			try {
				const response = await apiFetch( {
					path: `vmfa-rules/v1/rules/${ id }`,
					method: 'PUT',
					data,
					headers: { 'X-WP-Nonce': nonce },
				} );
				setRules( ( prev ) =>
					prev.map( ( rule ) => ( rule.id === id ? response : rule ) )
				);
				return response;
			} catch ( err ) {
				setError( err.message || 'Failed to update rule' );
				throw err;
			} finally {
				setIsSaving( false );
			}
		},
		[ nonce ]
	);

	/**
	 * Delete a rule.
	 *
	 * @param {string} id Rule ID.
	 * @return {Promise<void>}
	 */
	const deleteRule = useCallback(
		async ( id ) => {
			setIsSaving( true );
			setError( null );

			try {
				await apiFetch( {
					path: `vmfa-rules/v1/rules/${ id }`,
					method: 'DELETE',
					headers: { 'X-WP-Nonce': nonce },
				} );
				setRules( ( prev ) =>
					prev.filter( ( rule ) => rule.id !== id )
				);
			} catch ( err ) {
				setError( err.message || 'Failed to delete rule' );
				throw err;
			} finally {
				setIsSaving( false );
			}
		},
		[ nonce ]
	);

	/**
	 * Reorder rules.
	 *
	 * @param {Array<string>} order Array of rule IDs in new order.
	 * @return {Promise<Array>} Reordered rules.
	 */
	const reorderRules = useCallback(
		async ( order ) => {
			setError( null );

			try {
				const response = await apiFetch( {
					path: 'vmfa-rules/v1/rules/reorder',
					method: 'POST',
					data: { order },
					headers: { 'X-WP-Nonce': nonce },
				} );
				setRules( response );
				return response;
			} catch ( err ) {
				setError( err.message || 'Failed to reorder rules' );
				throw err;
			}
		},
		[ nonce ]
	);

	/**
	 * Toggle rule enabled state.
	 *
	 * @param {string}  id      Rule ID.
	 * @param {boolean} enabled New enabled state.
	 * @return {Promise<Object>} Updated rule.
	 */
	const toggleRule = useCallback(
		async ( id, enabled ) => {
			const rule = rules.find( ( r ) => r.id === id );
			if ( ! rule ) {
				return;
			}

			return updateRule( id, { ...rule, enabled } );
		},
		[ rules, updateRule ]
	);

	// Fetch rules on mount.
	useEffect( () => {
		fetchRules();
	}, [ fetchRules ] );

	return {
		rules,
		isLoading,
		error,
		isSaving,
		fetchRules,
		createRule,
		updateRule,
		deleteRule,
		reorderRules,
		toggleRule,
	};
}

/**
 * Hook for fetching media library statistics.
 *
 * @return {Object} Stats state.
 */
export function useStats() {
	const [ stats, setStats ] = useState( null );
	const [ isLoading, setIsLoading ] = useState( true );

	const { nonce } = window.vmfaRulesEngine || {};

	const fetchStats = useCallback( async () => {
		setIsLoading( true );

		try {
			const response = await apiFetch( {
				path: 'vmfa-rules/v1/stats',
				headers: { 'X-WP-Nonce': nonce },
			} );
			setStats( response );
		} catch ( err ) {
			// Silently fail for stats.
		} finally {
			setIsLoading( false );
		}
	}, [ nonce ] );

	useEffect( () => {
		fetchStats();
	}, [ fetchStats ] );

	return { stats, isLoading, refresh: fetchStats };
}

/**
 * Hook for batch operations.
 *
 * @return {Object} Batch operation state and actions.
 */
export function useBatchOperations() {
	const [ isProcessing, setIsProcessing ] = useState( false );
	const [ isLoadingMore, setIsLoadingMore ] = useState( false );
	const [ results, setResults ] = useState( null );
	const [ error, setError ] = useState( null );

	const { nonce } = window.vmfaRulesEngine || {};

	/**
	 * Preview rule application (initial load).
	 *
	 * @param {Object} options Preview options.
	 * @return {Promise<Object>} Preview results.
	 */
	const preview = useCallback(
		async ( options = {} ) => {
			setIsProcessing( true );
			setError( null );
			setResults( null );

			try {
				const data = {
					unassigned_only: options.unassignedOnly ?? true,
					limit: options.limit ?? 50,
					offset: 0,
					mime_type: options.mimeType,
					target_matches: options.targetMatches ?? 50,
					max_scan: options.maxScan ?? 500,
				};

				// Add rule_id for single-rule scanning.
				if ( options.ruleId ) {
					data.rule_id = options.ruleId;
				}

				const response = await apiFetch( {
					path: 'vmfa-rules/v1/preview',
					method: 'POST',
					data,
					headers: { 'X-WP-Nonce': nonce },
				} );
				setResults( response );
				return response;
			} catch ( err ) {
				setError( err.message || 'Failed to preview rules' );
				throw err;
			} finally {
				setIsProcessing( false );
			}
		},
		[ nonce ]
	);

	/**
	 * Load more preview items.
	 *
	 * @param {Object} options Load more options.
	 * @return {Promise<Object>} Additional results.
	 */
	const loadMore = useCallback(
		async ( options = {} ) => {
			if ( ! results || ! results.has_more || isLoadingMore ) {
				return null;
			}

			setIsLoadingMore( true );
			setError( null );

			try {
				// Calculate offset based on currently loaded items count.
				const newOffset = results.items.length;
				const data = {
					unassigned_only: options.unassignedOnly ?? true,
					limit: results.limit,
					offset: newOffset,
					mime_type: options.mimeType,
					target_matches: options.targetMatches ?? 50,
					max_scan: options.maxScan ?? 500,
				};

				// Preserve rule_id from initial results for single-rule scanning.
				if ( results.rule_id ) {
					data.rule_id = results.rule_id;
				}

				const response = await apiFetch( {
					path: 'vmfa-rules/v1/preview',
					method: 'POST',
					data,
					headers: { 'X-WP-Nonce': nonce },
				} );

				// Merge new items with existing results.
				setResults( ( prev ) => {
					const mergedItems = [ ...prev.items, ...response.items ];
					return {
						...prev,
						items: mergedItems,
						total: mergedItems.length,
						matched: prev.matched + response.matched,
						unmatched: prev.unmatched + response.unmatched,
						has_more: response.has_more,
					};
				} );

				return response;
			} catch ( err ) {
				setError( err.message || 'Failed to load more' );
				throw err;
			} finally {
				setIsLoadingMore( false );
			}
		},
		[ nonce, results, isLoadingMore ]
	);

	/**
	 * Apply rules to media library.
	 *
	 * @param {Object} options Apply options.
	 * @return {Promise<Object>} Apply results.
	 */
	const apply = useCallback(
		async ( options = {} ) => {
			setIsProcessing( true );
			setError( null );
			setResults( null );

			try {
				const response = await apiFetch( {
					path: 'vmfa-rules/v1/apply-rules',
					method: 'POST',
					data: {
						unassigned_only: options.unassignedOnly ?? true,
						attachment_ids: options.attachmentIds,
						mime_type: options.mimeType,
					},
					headers: { 'X-WP-Nonce': nonce },
				} );
				setResults( response );
				return response;
			} catch ( err ) {
				setError( err.message || 'Failed to apply rules' );
				throw err;
			} finally {
				setIsProcessing( false );
			}
		},
		[ nonce ]
	);

	/**
	 * Clear results.
	 */
	const clearResults = useCallback( () => {
		setResults( null );
		setError( null );
	}, [] );

	return {
		isProcessing,
		isLoadingMore,
		results,
		error,
		preview,
		loadMore,
		apply,
		clearResults,
	};
}

/**
 * Hook for fetching users (for author condition).
 *
 * @return {Object} Users state.
 */
export function useUsers() {
	const [ users, setUsers ] = useState( [] );
	const [ isLoading, setIsLoading ] = useState( true );

	const { nonce } = window.vmfaRulesEngine || {};

	useEffect( () => {
		const fetchUsers = async () => {
			try {
				const response = await apiFetch( {
					path: 'vmfa-rules/v1/users',
					headers: { 'X-WP-Nonce': nonce },
				} );
				setUsers( response );
			} catch ( err ) {
				// Silently fail.
			} finally {
				setIsLoading( false );
			}
		};

		fetchUsers();
	}, [ nonce ] );

	return { users, isLoading };
}
