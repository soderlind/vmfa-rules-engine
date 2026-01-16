<?php
/**
 * PHPUnit bootstrap file.
 *
 * @package VmfaRulesEngine
 */

// Composer autoloader.
require_once dirname( __DIR__ ) . '/vendor/autoload.php';

// Define constants needed by the plugin.
define( 'ABSPATH', '/tmp/wordpress/' );
define( 'VMFA_RULES_ENGINE_VERSION', '0.1.0' );
define( 'VMFA_RULES_ENGINE_FILE', dirname( __DIR__ ) . '/vmfa-rules-engine.php' );
define( 'VMFA_RULES_ENGINE_PATH', dirname( __DIR__ ) . '/' );
define( 'VMFA_RULES_ENGINE_URL', 'http://example.com/wp-content/plugins/vmfa-rules-engine/' );
define( 'VMFA_RULES_ENGINE_BASENAME', 'vmfa-rules-engine/vmfa-rules-engine.php' );
