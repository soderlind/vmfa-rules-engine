/**
 * Main entry point for the Rules Engine admin UI.
 *
 * @package VmfaRulesEngine
 */

import { createRoot } from '@wordpress/element';
import { RulesPanel } from './components/RulesPanel';
import './styles/admin.scss';

/**
 * Initialize the Rules Engine admin app.
 */
function initRulesEngine() {
	const container = document.getElementById( 'vmfa-rules-engine-app' );
	if ( container ) {
		const root = createRoot( container );
		root.render( <RulesPanel /> );
	}
}

// Initialize when DOM is ready.
if ( document.readyState === 'loading' ) {
	document.addEventListener( 'DOMContentLoaded', initRulesEngine );
} else {
	initRulesEngine();
}
