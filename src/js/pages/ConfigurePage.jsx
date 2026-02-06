/**
 * Configure Page Component for Rules Engine.
 *
 * Placeholder for settings - rules configuration is handled in Dashboard.
 *
 * @package VmfaRulesEngine
 */

import { Card, CardBody, CardHeader } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

/**
 * Configure Page component.
 *
 * @return {JSX.Element} The configure page content.
 */
export function ConfigurePage() {
	return (
		<Card className="vmfo-configure-card">
			<CardHeader>
				<h3>{ __( 'Configuration', 'vmfa-rules-engine' ) }</h3>
			</CardHeader>
			<CardBody>
				<p>
					{ __(
						'Rules Engine configuration is handled directly through the rule editor in the Dashboard tab. Each rule can be configured with:',
						'vmfa-rules-engine'
					) }
				</p>
				<ul>
					<li>
						<strong>
							{ __( 'Rule Name:', 'vmfa-rules-engine' ) }
						</strong>{ ' ' }
						{ __(
							'A descriptive name for the rule',
							'vmfa-rules-engine'
						) }
					</li>
					<li>
						<strong>
							{ __( 'Target Folder:', 'vmfa-rules-engine' ) }
						</strong>{ ' ' }
						{ __(
							'The folder where matched files will be assigned',
							'vmfa-rules-engine'
						) }
					</li>
					<li>
						<strong>
							{ __( 'Conditions:', 'vmfa-rules-engine' ) }
						</strong>{ ' ' }
						{ __(
							'One or more conditions that must match (filename, type, size, date, author)',
							'vmfa-rules-engine'
						) }
					</li>
					<li>
						<strong>
							{ __( 'Stop Processing:', 'vmfa-rules-engine' ) }
						</strong>{ ' ' }
						{ __(
							'Whether to stop evaluating subsequent rules when this rule matches',
							'vmfa-rules-engine'
						) }
					</li>
					<li>
						<strong>
							{ __( 'Enabled:', 'vmfa-rules-engine' ) }
						</strong>{ ' ' }
						{ __(
							'Toggle to enable or disable the rule',
							'vmfa-rules-engine'
						) }
					</li>
				</ul>
				<h4>{ __( 'Rule Priority', 'vmfa-rules-engine' ) }</h4>
				<p>
					{ __(
						'Rules are evaluated in order from top to bottom. Use drag-and-drop in the Dashboard to reorder rules and control which rules take precedence.',
						'vmfa-rules-engine'
					) }
				</p>
			</CardBody>
		</Card>
	);
}

export default ConfigurePage;
