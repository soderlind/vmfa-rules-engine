/**
 * Rule Editor modal component.
 *
 * @package
 */

import { useState, useCallback, useEffect, useRef } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import apiFetch from '@wordpress/api-fetch';
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
import { plus } from '@wordpress/icons';

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
 * @param {Function} props.onFoldersChange Callback when folders list changes.
 * @return {JSX.Element} Rule editor modal.
 */
export function RuleEditor( { rule, folders, onSave, onCancel, isSaving, onFoldersChange } ) {
	const [ name, setName ] = useState( '' );
	const [ folderId, setFolderId ] = useState( '' );
	const [ conditions, setConditions ] = useState( [] );
	const [ stopProcessing, setStopProcessing ] = useState( true );
	const [ enabled, setEnabled ] = useState( true );
	const [ errors, setErrors ] = useState( {} );

	// Create folder modal state
	const [ isCreateFolderOpen, setIsCreateFolderOpen ] = useState( false );
	const [ newFolderName, setNewFolderName ] = useState( '' );
	const [ newFolderParent, setNewFolderParent ] = useState( 0 );
	const [ isCreatingFolder, setIsCreatingFolder ] = useState( false );
	const [ createFolderError, setCreateFolderError ] = useState( '' );

	// Track pending folder selection after creation
	const pendingFolderIdRef = useRef( null );

	// Track if component is mounted to prevent state updates after unmount
	const isMountedRef = useRef( true );

	// Cleanup on unmount
	useEffect( () => {
		isMountedRef.current = true;
		return () => {
			isMountedRef.current = false;
		};
	}, [] );

	const { strings = {} } = window.vmfaRulesEngine || {};

	// Select pending folder when folders list updates
	useEffect( () => {
		if (
			pendingFolderIdRef.current &&
			folders.some(
				( f ) => String( f.id ) === pendingFolderIdRef.current
			)
		) {
			setFolderId( pendingFolderIdRef.current );
			pendingFolderIdRef.current = null;
		}
	}, [ folders ] );

	// Track if form has been initialized
	const isInitializedRef = useRef( false );

	// Initialize form with rule data (only once per rule).
	useEffect( () => {
		// Skip if already initialized for this rule session
		if ( isInitializedRef.current ) {
			return;
		}

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

		isInitializedRef.current = true;
	}, [ rule, folders ] );

	/**
	 * Validate form data.
	 *
	 * @return {boolean} True if valid.
	 */
	const validate = useCallback( () => {
		const newErrors = {};

		if ( ! name.trim() ) {
			newErrors.name = __(
				'Rule name is required.',
				'vmfa-rules-engine'
			);
		}

		if ( ! folderId ) {
			newErrors.folder = __(
				'Target folder is required.',
				'vmfa-rules-engine'
			);
		}

		if ( conditions.length === 0 ) {
			newErrors.conditions = __(
				'At least one condition is required.',
				'vmfa-rules-engine'
			);
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
	}, [
		name,
		folderId,
		conditions,
		stopProcessing,
		enabled,
		validate,
		onSave,
	] );

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
					label:
						'—'.repeat( depth ) +
						( depth > 0 ? ' ' : '' ) +
						folder.name,
				} );
				result.push( ...buildOptions( items, folder.id, depth + 1 ) );
			}

			return result;
		};

		return [
			{
				value: '',
				label: __( 'Select a folder…', 'vmfa-rules-engine' ),
			},
			...buildOptions( folders ),
		];
	}, [ folders ] );

	/**
	 * Build parent folder options for creating a new folder.
	 *
	 * @return {Array} Parent folder options.
	 */
	const getParentFolderOptions = useCallback( () => {
		const buildOptions = ( items, parentId = 0, depth = 0 ) => {
			const result = [];
			const children = items.filter( ( f ) => f.parent === parentId );

			for ( const folder of children ) {
				result.push( {
					value: String( folder.id ),
					label:
						'—'.repeat( depth ) +
						( depth > 0 ? ' ' : '' ) +
						folder.name,
				} );
				result.push( ...buildOptions( items, folder.id, depth + 1 ) );
			}

			return result;
		};

		return [
			{
				value: '0',
				label: __( 'None (top level)', 'vmfa-rules-engine' ),
			},
			...buildOptions( folders ),
		];
	}, [ folders ] );

	/**
	 * Handle create folder.
	 */
	const handleCreateFolder = useCallback( async () => {
		// Prevent double-submit
		if ( isCreatingFolder ) {
			return;
		}

		if ( ! newFolderName.trim() ) {
			setCreateFolderError(
				__( 'Please enter a folder name.', 'vmfa-rules-engine' )
			);
			return;
		}

		setIsCreatingFolder( true );
		setCreateFolderError( '' );

		try {
			const response = await apiFetch( {
				path: '/vmfo/v1/folders',
				method: 'POST',
				data: {
					name: newFolderName.trim(),
					parent: newFolderParent,
				},
			} );

			// Check if component is still mounted
			if ( ! isMountedRef.current ) {
				return;
			}

			// Reset form
			setNewFolderName( '' );
			setNewFolderParent( 0 );
			setIsCreateFolderOpen( false );

			// Get the new folder ID
			const createdFolderId = response?.id
				? String( response.id )
				: null;

			// Notify parent to refresh folders and wait for result
			if ( onFoldersChange ) {
				const updatedFolders = await onFoldersChange();

				// Check if component is still mounted
				if ( ! isMountedRef.current ) {
					return;
				}

				// Verify the folder exists in the returned list before selecting
				if (
					createdFolderId &&
					Array.isArray( updatedFolders ) &&
					updatedFolders.some(
						( f ) => String( f.id ) === createdFolderId
					)
				) {
					setFolderId( createdFolderId );
				} else if ( createdFolderId ) {
					// Fallback: set it anyway and let useEffect handle it
					pendingFolderIdRef.current = createdFolderId;
					setFolderId( createdFolderId );
				}
			} else if ( createdFolderId ) {
				// No callback, just set the folder ID directly
				setFolderId( createdFolderId );
			}
		} catch ( err ) {
			setCreateFolderError(
				err.message ||
					__( 'Failed to create folder.', 'vmfa-rules-engine' )
			);
		} finally {
			if ( isMountedRef.current ) {
				setIsCreatingFolder( false );
			}
		}
	}, [ newFolderName, newFolderParent, onFoldersChange, isCreatingFolder ] );

	return (
		<Modal
			title={
				rule
					? strings.editRule || __( 'Edit Rule', 'vmfa-rules-engine' )
					: strings.addRule || __( 'Add Rule', 'vmfa-rules-engine' )
			}
			onRequestClose={ onCancel }
			className="vmfa-rule-editor-modal"
			shouldCloseOnClickOutside={ false }
		>
			<div className="vmfa-rule-editor">
				<TextControl
					label={
						strings.ruleName ||
						__( 'Rule Name', 'vmfa-rules-engine' )
					}
					value={ name }
					onChange={ setName }
					placeholder={ __(
						'e.g., Mobile Photos',
						'vmfa-rules-engine'
					) }
					className={ errors.name ? 'has-error' : '' }
					__nextHasNoMarginBottom
					__next40pxDefaultSize
				/>
				{ errors.name && (
					<p className="vmfa-error-message">{ errors.name }</p>
				) }

				<div className="vmfa-rule-editor__folder-select">
					<label className="vmfa-rule-editor__folder-label">
						{ strings.targetFolder ||
							__( 'Target Folder', 'vmfa-rules-engine' ) }
					</label>
					<Flex align="flex-end" gap={ 2 }>
						<FlexItem>
							<SelectControl
								value={ folderId }
								options={ getFolderOptions() }
								onChange={ setFolderId }
								className={ errors.folder ? 'has-error' : '' }
								hideLabelFromVision
								__nextHasNoMarginBottom
								__next40pxDefaultSize
							/>
						</FlexItem>
						<FlexItem>
							<Button
								icon={ plus }
								label={ __(
									'Create Folder',
									'vmfa-rules-engine'
								) }
								onClick={ () => {
									setCreateFolderError( '' );
									setNewFolderName( '' );
									setNewFolderParent( 0 );
									setIsCreateFolderOpen( true );
								} }
								className="vmfa-add-folder-button"
								size="default"
							/>
						</FlexItem>
					</Flex>
					{ errors.folder && (
						<p className="vmfa-error-message">{ errors.folder }</p>
					) }
				</div>

				<div className="vmfa-rule-editor__conditions">
					<h3>
						{ strings.conditions ||
							__( 'Conditions', 'vmfa-rules-engine' ) }
					</h3>
					<p className="description">
						{ __(
							'All conditions must match (AND logic).',
							'vmfa-rules-engine'
						) }
					</p>
					{ errors.conditions && (
						<p className="vmfa-error-message">
							{ errors.conditions }
						</p>
					) }
					<ConditionBuilder
						conditions={ conditions }
						onChange={ setConditions }
					/>
				</div>

				<div className="vmfa-rule-editor__options">
					<ToggleControl
						label={
							strings.stopProcessing ||
							__(
								'Stop processing after match',
								'vmfa-rules-engine'
							)
						}
						help={ __(
							'When enabled, no further rules will be evaluated after this rule matches.',
							'vmfa-rules-engine'
						) }
						checked={ stopProcessing }
						onChange={ setStopProcessing }
						__nextHasNoMarginBottom
					/>

					<ToggleControl
						label={
							strings.enabled ||
							__( 'Enabled', 'vmfa-rules-engine' )
						}
						help={ __(
							'Disabled rules will not be evaluated during uploads.',
							'vmfa-rules-engine'
						) }
						checked={ enabled }
						onChange={ setEnabled }
						__nextHasNoMarginBottom
					/>
				</div>

				<Flex justify="flex-end" className="vmfa-rule-editor__actions">
					<FlexItem>
						<Button
							variant="tertiary"
							onClick={ onCancel }
							disabled={ isSaving }
						>
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
									{ __( 'Saving…', 'vmfa-rules-engine' ) }
								</>
							) : (
								__( 'Save Rule', 'vmfa-rules-engine' )
							) }
						</Button>
					</FlexItem>
				</Flex>
			</div>

			{ /* Create Folder Modal */ }
			{ isCreateFolderOpen && (
				<Modal
					title={ __( 'Create Folder', 'vmfa-rules-engine' ) }
					onRequestClose={ () => setIsCreateFolderOpen( false ) }
					className="vmfa-create-folder-modal"
					overlayClassName="vmfa-create-folder-modal-overlay"
				>
					<TextControl
						label={ __( 'Folder Name', 'vmfa-rules-engine' ) }
						value={ newFolderName }
						onChange={ setNewFolderName }
						placeholder={ __(
							'Enter folder name',
							'vmfa-rules-engine'
						) }
						__next40pxDefaultSize
						__nextHasNoMarginBottom
					/>
					<SelectControl
						label={ __( 'Parent Folder', 'vmfa-rules-engine' ) }
						value={ String( newFolderParent ) }
						options={ getParentFolderOptions() }
						onChange={ ( value ) =>
							setNewFolderParent( parseInt( value, 10 ) )
						}
						__next40pxDefaultSize
						__nextHasNoMarginBottom
					/>
					{ createFolderError && (
						<p className="vmfa-create-folder-error">
							{ createFolderError }
						</p>
					) }
					<Flex
						justify="flex-end"
						className="vmfa-create-folder-actions"
					>
						<FlexItem>
							<Button
								variant="secondary"
								onClick={ () => setIsCreateFolderOpen( false ) }
								disabled={ isCreatingFolder }
							>
								{ __( 'Cancel', 'vmfa-rules-engine' ) }
							</Button>
						</FlexItem>
						<FlexItem>
							<Button
								variant="primary"
								onClick={ handleCreateFolder }
								disabled={ isCreatingFolder }
							>
								{ isCreatingFolder
									? __( 'Creating…', 'vmfa-rules-engine' )
									: __( 'Create', 'vmfa-rules-engine' ) }
							</Button>
						</FlexItem>
					</Flex>
				</Modal>
			) }
		</Modal>
	);
}
