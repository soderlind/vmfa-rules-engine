/**
 * Webpack configuration for vmfa-rules-engine.
 *
 * @package VmfaRulesEngine
 */

const defaultConfig = require( '@wordpress/scripts/config/webpack.config' );

module.exports = {
	...defaultConfig,
	externals: {
		...defaultConfig.externals,
		'@vmfo/shared': 'vmfo.shared',
	},
};
