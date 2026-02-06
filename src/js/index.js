/**
 * Main entry point for the Rules Engine admin UI.
 *
 * @package VmfaRulesEngine
 */

import { useState, useEffect, useCallback } from '@wordpress/element';
import { createRoot } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import apiFetch from '@wordpress/api-fetch';

import { AddonShell, StatsCard } from '@vmfo/shared';
import { useStats } from './hooks/useRules';
import {
	OverviewPage,
	DashboardPage,
	ConfigurePage,
	ActionsPage,
	LogsPage,
} from './pages';

import './styles/admin.scss';

/**
 * Main Rules Engine App using AddonShell.
 *
 * @return {JSX.Element} The app component.
 */
function RulesEngineApp() {
	const { stats, isLoading } = useStats();
	const [ enabled, setEnabled ] = useState( true );

	// Build KPI stats for AddonShell.
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
					label: __( 'Assigned', 'vmfa-rules-engine' ),
					value: stats.assigned?.toLocaleString() ?? '—',
				},
				{
					label: __( 'Unassigned', 'vmfa-rules-engine' ),
					value: stats.unassigned?.toLocaleString() ?? '—',
				},
		  ]
		: [];

	return (
		<AddonShell
			addonKey="rules-engine"
			addonLabel={ __( 'Rules Engine', 'vmfa-rules-engine' ) }
			enabled={ enabled }
			stats={ kpiStats }
			overviewContent={ <OverviewPage /> }
			dashboardContent={ <DashboardPage /> }
			configureContent={ <ConfigurePage /> }
			actionsContent={ <ActionsPage /> }
			logsContent={ <LogsPage /> }
		/>
	);
}

/**
 * Initialize the Rules Engine admin app.
 */
function initRulesEngine() {
	const container = document.getElementById( 'vmfa-rules-engine-app' );
	if ( container ) {
		const root = createRoot( container );
		root.render( <RulesEngineApp /> );
	}
}

// Initialize when DOM is ready.
if ( document.readyState === 'loading' ) {
	document.addEventListener( 'DOMContentLoaded', initRulesEngine );
} else {
	initRulesEngine();
}
