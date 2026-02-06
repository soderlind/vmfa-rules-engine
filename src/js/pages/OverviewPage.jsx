/**
 * Overview Page Component for Rules Engine.
 *
 * Displays add-on description, KPI stats, and quick info.
 *
 * @package VmfaRulesEngine
 */

import { useState, useEffect, useCallback } from '@wordpress/element';
import { Card, CardBody, CardHeader } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import apiFetch from '@wordpress/api-fetch';

import { StatsCard } from '@vmfo/shared';

/**
 * Overview Page component.
 *
 * @return {JSX.Element} The overview page content.
 */
export function OverviewPage() {
	const [ stats, setStats ] = useState( null );
	const [ loading, setLoading ] = useState( true );

	const { nonce } = window.vmfaRulesEngine || {};

	/**
	 * Fetch rules statistics.
	 */
	const fetchStats = useCallback( async () => {
		try {
			const response = await apiFetch( {
				path: 'vmfa-rules/v1/stats',
				headers: { 'X-WP-Nonce': nonce },
			} );
			setStats( response );
		} catch ( err ) {
			// Ignore fetch errors; stats may still show loading.
		} finally {
			setLoading( false );
		}
	}, [ nonce ] );

	useEffect( () => {
		fetchStats();
	}, [ fetchStats ] );

	const kpiStats = stats
		? [
				{
					label: __( 'Total Rules', 'vmfa-rules-engine' ),
					value: stats.rules_total?.toLocaleString() ?? '—',
				},
				{
					label: __( 'Active Rules', 'vmfa-rules-engine' ),
					value: stats.rules_enabled?.toLocaleString() ?? '—',
				},
				{
					label: __( 'Total Matched', 'vmfa-rules-engine' ),
					value: stats.assigned?.toLocaleString() ?? '—',
				},
				{
					label: __( 'Unassigned Media', 'vmfa-rules-engine' ),
					value: stats.unassigned?.toLocaleString() ?? '—',
				},
		  ]
		: [
				{
					label: __( 'Total Rules', 'vmfa-rules-engine' ),
					isLoading: loading,
				},
				{
					label: __( 'Active Rules', 'vmfa-rules-engine' ),
					isLoading: loading,
				},
				{
					label: __( 'Total Matched', 'vmfa-rules-engine' ),
					isLoading: loading,
				},
				{
					label: __( 'Unassigned Media', 'vmfa-rules-engine' ),
					isLoading: loading,
				},
		  ];

	return (
		<>
			<StatsCard stats={ kpiStats } />

			<Card className="vmfo-overview-card">
				<CardHeader>
					<h3>{ __( 'About Rules Engine', 'vmfa-rules-engine' ) }</h3>
				</CardHeader>
				<CardBody>
					<p>
						{ __(
							'Rules Engine automatically organizes your media library by applying customizable rules to files as they are uploaded. Define conditions based on filename patterns, file types, upload dates, and more to ensure consistent folder organization.',
							'vmfa-rules-engine'
						) }
					</p>
					<h4>{ __( 'Features', 'vmfa-rules-engine' ) }</h4>
					<ul>
						<li>
							{ __(
								'Automatic folder assignment on upload',
								'vmfa-rules-engine'
							) }
						</li>
						<li>
							{ __(
								'Multiple condition types: filename, MIME type, size, date, author',
								'vmfa-rules-engine'
							) }
						</li>
						<li>
							{ __(
								'Rule priority with drag-and-drop ordering',
								'vmfa-rules-engine'
							) }
						</li>
						<li>
							{ __(
								'Preview rules before applying to existing media',
								'vmfa-rules-engine'
							) }
						</li>
						<li>
							{ __(
								'Batch operations for organizing existing files',
								'vmfa-rules-engine'
							) }
						</li>
						<li>
							{ __(
								'Stop processing option for rule chains',
								'vmfa-rules-engine'
							) }
						</li>
					</ul>
					<h4>{ __( 'Getting Started', 'vmfa-rules-engine' ) }</h4>
					<ol>
						<li>
							{ __(
								'Create rules in the Dashboard tab',
								'vmfa-rules-engine'
							) }
						</li>
						<li>
							{ __(
								'Set conditions to match specific files',
								'vmfa-rules-engine'
							) }
						</li>
						<li>
							{ __(
								'Choose a target folder for matched files',
								'vmfa-rules-engine'
							) }
						</li>
						<li>
							{ __(
								'Use Actions to apply rules to existing media',
								'vmfa-rules-engine'
							) }
						</li>
					</ol>
				</CardBody>
			</Card>
		</>
	);
}

export default OverviewPage;
