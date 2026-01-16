/**
 * Rule Editor modal component.
 *
 * @package VmfaRulesEngine
 */

import { useState, useCallback, useEffect } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import {
	Button,
	Modal,
	TextControl,
	SelectControl,
	ToggleControl,
	Flex,
	FlexItem,
	Spinner,
} from '@wordpress/components';

import { ConditionBuilder } from './ConditionBuilder';

/**
 * Rule Editor component.
 *
 * @param {Object}   props          Component props.
 * @param {Object}   props.rule     Rule to edit (null for new rule).
 * @param {Array}    props.folders  Available folders.
 * @param {Function} props.onSave   Save handler.
 * @param {Function} props.onCancel Cancel handler.
 * @param {boolean}  props.isSaving Whether saving is in progress.
 * @return {JSX.Element} Rule editor modal.
 */
export function RuleEditor( { rule, folders, onSave, onCancel, isSaving } ) {
	const [ name, setName ] = useState( '' );
	const [ folderId, setFolderId ] = useState( '' );
	const [ conditions, setConditions ] = useState( [] );
	const [ stopProcessing, setStopProcessing ] = useState( true );
	const [ enabled, setEnabled ] = useState( true );
	const [ errors, setErrors ] = useState( {} );

	const { strings = {} } = window.vmfaRulesEngine || {};

	// Initialize form with rule data.
	useEffect( () => {
		if ( rule ) {
			setName( rule.name || '' );
			setFolderId( String( rule.folder_id || '' ) );
			setConditions( rule.conditions || [] );
			setStopProcessing( rule.stop_processing ?? true );
			setEnabled( rule.enabled ?? true );
		} else {
			setName( '' );
			setFolderId( folders.length > 0 ? String( folders[ 0 ].id ) : '' );
			setConditions( [] );
			setStopProcessing( true );
			setEnabled( true );
		}
	}, [ rule, folders ] );

	/**
	 * Validate form data.
	 *
	 * @return {boolean} True if valid.
	 */
	const validate = useCallback( () => {
		const newErrors = {};

		if ( ! name.trim() ) {
			newErrors.name = __( 'Rule name is required.', 'vmfa-rules-engine' );
		}

		if ( ! folderId ) {
			newErrors.folder = __( 'Target folder is required.', 'vmfa-rules-engine' );
		}

		if ( conditions.length === 0 ) {
			newErrors.conditions = __( 'At least one condition is required.', 'vmfa-rules-engine' );
		}

		setErrors( newErrors );
		return Object.keys( newErrors ).length === 0;
	}, [ name, folderId, conditions ] );

	/**
	 * Handle form submission.
	 */
	const handleSubmit = useCallback( () => {
		if ( ! validate() ) {
			return;
		}

		onSave( {
			name,
			folder_id: parseInt( folderId, 10 ),
			conditions,
			stop_processing: stopProcessing,
			enabled,
		} );
	}, [ name, folderId, conditions, stopProcessing, enabled, validate, onSave ] );

	/**
	 * Build folder options for select.
	 *
	 * @return {Array} Folder options.
	 */
	const getFolderOptions = useCallback( () => {
		const buildOptions = ( items, parentId = 0, depth = 0 ) => {
			const result = [];
			const children = items.filter( ( f ) => f.parent === parentId );

			for ( const folder of children ) {
				result.push( {
					value: String( folder.id ),
					label: '—'.repeat( depth ) + ( depth > 0 ? ' ' : '' ) + folder.name,
				} );
				result.push( ...buildOptions( items, folder.id, depth + 1 ) );
			}

			return result;
		};

		return [
			{ value: '', label: __( 'Select a folder...', 'vmfa-rules-engine' ) },
			...buildOptions( folders ),
		];
	}, [ folders ] );

	return (
		<Modal
			title={ rule ? strings.editRule || __( 'Edit Rule', 'vmfa-rules-engine' ) : strings.addRule || __( 'Add Rule', 'vmfa-rules-engine' ) }
			onRequestClose={ onCancel }
			className="vmfa-rule-editor-modal"
			shouldCloseOnClickOutside={ false }
		>
			<div className="vmfa-rule-editor">
				<TextControl
					label={ strings.ruleName || __( 'Rule Name', 'vmfa-rules-engine' ) }
					value={ name }
					onChange={ setName }
					placeholder={ __( 'e.g., Mobile Photos', 'vmfa-rules-engine' ) }
					help={ errors.name }
					className={ errors.name ? 'has-error' : '' }
				/>

				<SelectControl
					label={ strings.targetFolder || __( 'Target Folder', 'vmfa-rules-engine' ) }
					value={ folderId }
					options={ getFolderOptions() }
					onChange={ setFolderId }
					help={ errors.folder }
					className={ errors.folder ? 'has-error' : '' }
					__nextHasNoMarginBottom
				/>

				<div className="vmfa-rule-editor__conditions">
					<h3>{ strings.conditions || __( 'Conditions', 'vmfa-rules-engine' ) }</h3>
					<p className="description">
						{ __( 'All conditions must match (AND logic).', 'vmfa-rules-engine' ) }
					</p>
					{ errors.conditions && (
						<p className="vmfa-error-message">{ errors.conditions }</p>
					) }
					<ConditionBuilder
						conditions={ conditions }
						onChange={ setConditions }
					/>
				</div>

				<div className="vmfa-rule-editor__options">
					<ToggleControl
						label={ strings.stopProcessing || __( 'Stop processing after match', 'vmfa-rules-engine' ) }
						help={ __( 'When enabled, no further rules will be evaluated after this rule matches.', 'vmfa-rules-engine' ) }
						checked={ stopProcessing }
						onChange={ setStopProcessing }
					/>

					<ToggleControl
						label={ strings.enabled || __( 'Enabled', 'vmfa-rules-engine' ) }
						help={ __( 'Disabled rules will not be evaluated during uploads.', 'vmfa-rules-engine' ) }
						checked={ enabled }
						onChange={ setEnabled }
					/>
				</div>

				<Flex justify="flex-end" className="vmfa-rule-editor__actions">
					<FlexItem>
						<Button variant="tertiary" onClick={ onCancel } disabled={ isSaving }>
							{ __( 'Cancel', 'vmfa-rules-engine' ) }
						</Button>
					</FlexItem>
					<FlexItem>
						<Button
							variant="primary"
							onClick={ handleSubmit }
							disabled={ isSaving }
						>
							{ isSaving ? (
								<>
									<Spinner />
									{ __( 'Saving...', 'vmfa-rules-engine' ) }
								</>
							) : (
								__( 'Save Rule', 'vmfa-rules-engine' )
							) }
						</Button>
					</FlexItem>
				</Flex>
			</div>
		</Modal>
	);
}
