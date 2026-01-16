/**
 * Main Rules Panel component.
 *
 * @package VmfaRulesEngine
 */

import { useState, useCallback } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import {
	Button,
	Card,
	CardBody,
	CardHeader,
	CheckboxControl,
	Flex,
	FlexItem,
	Modal,
	Notice,
	Spinner,
	ToggleControl,
} from '@wordpress/components';
import { plus, cog, trash } from '@wordpress/icons';
import {
	DndContext,
	closestCenter,
	KeyboardSensor,
	PointerSensor,
	useSensor,
	useSensors,
} from '@dnd-kit/core';
import {
	SortableContext,
	sortableKeyboardCoordinates,
	verticalListSortingStrategy,
	useSortable,
} from '@dnd-kit/sortable';
import { CSS } from '@dnd-kit/utilities';

import { useRules, useStats, useBatchOperations } from '../hooks/useRules';
import { RuleEditor } from './RuleEditor';
import { PreviewModal } from './PreviewModal';

/**
 * Sortable rule item component.
 *
 * @param {Object}   props            Component props.
 * @param {Object}   props.rule       Rule data.
 * @param {Function} props.onEdit     Edit handler.
 * @param {Function} props.onDelete   Delete handler.
 * @param {Function} props.onToggle   Toggle handler.
 * @param {Array}    props.folders    Available folders.
 * @return {JSX.Element} Sortable rule item.
 */
function SortableRuleItem( { rule, onEdit, onDelete, onToggle, folders } ) {
	const {
		attributes,
		listeners,
		setNodeRef,
		transform,
		transition,
		isDragging,
	} = useSortable( { id: rule.id } );

	const style = {
		transform: CSS.Transform.toString( transform ),
		transition,
		opacity: isDragging ? 0.5 : 1,
	};

	const folder = folders.find( ( f ) => f.id === rule.folder_id );
	const folderName = folder ? folder.name : __( 'Unknown folder', 'vmfa-rules-engine' );

	return (
		<div
			ref={ setNodeRef }
			style={ style }
			className={ `vmfa-rule-item ${ ! rule.enabled ? 'vmfa-rule-item--disabled' : '' }` }
		>
			<div className="vmfa-rule-item__drag" { ...attributes } { ...listeners }>
				<span className="dashicons dashicons-menu"></span>
			</div>
			<div className="vmfa-rule-item__content">
				<div className="vmfa-rule-item__header">
					<strong className="vmfa-rule-item__name">{ rule.name }</strong>
					<span className="vmfa-rule-item__folder">→ { folderName }</span>
				</div>
				<div className="vmfa-rule-item__conditions">
					{ rule.conditions.length > 0 ? (
						<span>
							{ rule.conditions.length }{ ' ' }
							{ rule.conditions.length === 1
								? __( 'condition', 'vmfa-rules-engine' )
								: __( 'conditions', 'vmfa-rules-engine' ) }
						</span>
					) : (
						<span className="vmfa-rule-item__no-conditions">
							{ __( 'No conditions', 'vmfa-rules-engine' ) }
						</span>
					) }
					{ rule.stop_processing && (
						<span className="vmfa-rule-item__stop-badge">
							{ __( 'Stop', 'vmfa-rules-engine' ) }
						</span>
					) }
				</div>
			</div>
			<div className="vmfa-rule-item__actions">
				<ToggleControl
					checked={ rule.enabled }
					onChange={ ( enabled ) => onToggle( rule.id, enabled ) }
					__nextHasNoMarginBottom
				/>
				<Button
					icon={ cog }
					label={ __( 'Edit rule', 'vmfa-rules-engine' ) }
					onClick={ () => onEdit( rule ) }
				/>
				<Button
					icon={ trash }
					label={ __( 'Delete rule', 'vmfa-rules-engine' ) }
					isDestructive
					onClick={ () => onDelete( rule.id ) }
				/>
			</div>
		</div>
	);
}

/**
 * Stats card component.
 *
 * @param {Object} props       Component props.
 * @param {Object} props.stats Stats data.
 * @return {JSX.Element} Stats card.
 */
function StatsCard( { stats } ) {
	if ( ! stats ) {
		return null;
	}

	return (
		<Card className="vmfa-stats-card">
			<CardBody>
				<div className="vmfa-stats-grid">
					<div className="vmfa-stats-item">
						<span className="vmfa-stats-value">{ stats.total }</span>
						<span className="vmfa-stats-label">
							{ __( 'Total Media', 'vmfa-rules-engine' ) }
						</span>
					</div>
					<div className="vmfa-stats-item">
						<span className="vmfa-stats-value">{ stats.assigned }</span>
						<span className="vmfa-stats-label">
							{ __( 'Assigned', 'vmfa-rules-engine' ) }
						</span>
					</div>
					<div className="vmfa-stats-item">
						<span className="vmfa-stats-value vmfa-stats-value--highlight">
							{ stats.unassigned }
						</span>
						<span className="vmfa-stats-label">
							{ __( 'Unassigned', 'vmfa-rules-engine' ) }
						</span>
					</div>
					<div className="vmfa-stats-item">
						<span className="vmfa-stats-value">{ stats.rules_enabled }</span>
						<span className="vmfa-stats-label">
							{ __( 'Active Rules', 'vmfa-rules-engine' ) }
						</span>
					</div>
				</div>
			</CardBody>
		</Card>
	);
}

/**
 * Main Rules Panel component.
 *
 * @return {JSX.Element} Rules panel.
 */
export function RulesPanel() {
	const {
		rules,
		isLoading,
		error,
		isSaving,
		createRule,
		updateRule,
		deleteRule,
		reorderRules,
		toggleRule,
	} = useRules();

	const { stats, refresh: refreshStats } = useStats();
	const { preview, loadMore, apply, isProcessing, isLoadingMore, results, clearResults } = useBatchOperations();

	const [ editingRule, setEditingRule ] = useState( null );
	const [ isEditorOpen, setIsEditorOpen ] = useState( false );
	const [ isPreviewOpen, setIsPreviewOpen ] = useState( false );
	const [ successMessage, setSuccessMessage ] = useState( '' );

	const { folders = [], strings = {} } = window.vmfaRulesEngine || {};

	// DnD sensors.
	const sensors = useSensors(
		useSensor( PointerSensor ),
		useSensor( KeyboardSensor, {
			coordinateGetter: sortableKeyboardCoordinates,
		} )
	);

	/**
	 * Handle drag end for reordering.
	 *
	 * @param {Object} event DnD event.
	 */
	const handleDragEnd = useCallback(
		( event ) => {
			const { active, over } = event;

			if ( active.id !== over?.id ) {
				const oldIndex = rules.findIndex( ( r ) => r.id === active.id );
				const newIndex = rules.findIndex( ( r ) => r.id === over.id );

				const newOrder = [ ...rules ];
				const [ removed ] = newOrder.splice( oldIndex, 1 );
				newOrder.splice( newIndex, 0, removed );

				reorderRules( newOrder.map( ( r ) => r.id ) );
			}
		},
		[ rules, reorderRules ]
	);

	/**
	 * Handle creating a new rule.
	 */
	const handleAddRule = useCallback( () => {
		setEditingRule( null );
		setIsEditorOpen( true );
	}, [] );

	/**
	 * Handle editing a rule.
	 *
	 * @param {Object} rule Rule to edit.
	 */
	const handleEditRule = useCallback( ( rule ) => {
		setEditingRule( rule );
		setIsEditorOpen( true );
	}, [] );

	/**
	 * Handle deleting a rule.
	 *
	 * @param {string} id Rule ID.
	 */
	const handleDeleteRule = useCallback(
		async ( id ) => {
			if ( ! window.confirm( strings.deleteConfirm || 'Are you sure?' ) ) {
				return;
			}
			await deleteRule( id );
		},
		[ deleteRule, strings.deleteConfirm ]
	);

	/**
	 * Handle saving a rule from the editor.
	 *
	 * @param {Object} data Rule data.
	 */
	const handleSaveRule = useCallback(
		async ( data ) => {
			try {
				if ( editingRule ) {
					await updateRule( editingRule.id, data );
				} else {
					await createRule( data );
				}
				setIsEditorOpen( false );
				setEditingRule( null );
				setSuccessMessage( strings.saveSuccess || 'Rule saved successfully.' );
				setTimeout( () => setSuccessMessage( '' ), 3000 );
			} catch ( err ) {
				// Error handled in hook.
			}
		},
		[ editingRule, createRule, updateRule, strings.saveSuccess ]
	);

	const [ isPreviewOptionsOpen, setIsPreviewOptionsOpen ] = useState( false );
	const [ previewUnassignedOnly, setPreviewUnassignedOnly ] = useState( true );

	/**
	 * Handle preview button click - show options first.
	 */
	const handlePreviewClick = useCallback( () => {
		setIsPreviewOptionsOpen( true );
	}, [] );

	/**
	 * Run the preview with selected options.
	 */
	const handleRunPreview = useCallback( async () => {
		setIsPreviewOptionsOpen( false );
		await preview( { unassignedOnly: previewUnassignedOnly } );
		setIsPreviewOpen( true );
	}, [ preview, previewUnassignedOnly ] );

	/**
	 * Handle loading more preview items.
	 */
	const handleLoadMore = useCallback( async () => {
		await loadMore( { unassignedOnly: previewUnassignedOnly } );
	}, [ loadMore, previewUnassignedOnly ] );

	/**
	 * Handle apply from preview modal.
	 *
	 * @param {Array} attachmentIds Attachment IDs to apply to.
	 */
	const handleApply = useCallback(
		async ( attachmentIds ) => {
			await apply( { attachmentIds } );
			refreshStats();
		},
		[ apply, refreshStats ]
	);

	/**
	 * Handle closing preview modal.
	 */
	const handleClosePreview = useCallback( () => {
		setIsPreviewOpen( false );
		clearResults();
	}, [ clearResults ] );

	if ( isLoading ) {
		return (
			<div className="vmfa-rules-loading">
				<Spinner />
				<p>{ __( 'Loading rules...', 'vmfa-rules-engine' ) }</p>
			</div>
		);
	}

	return (
		<div className="vmfa-rules-panel">
			{ error && (
				<Notice status="error" isDismissible={ false }>
					{ error }
				</Notice>
			) }

			{ successMessage && (
				<Notice status="success" isDismissible onDismiss={ () => setSuccessMessage( '' ) }>
					{ successMessage }
				</Notice>
			) }

			<StatsCard stats={ stats } />

			<Card className="vmfa-rules-card">
				<CardHeader>
					<Flex align="center" justify="space-between">
						<FlexItem>
							<h2>{ __( 'Rules', 'vmfa-rules-engine' ) }</h2>
						</FlexItem>
						<Flex gap={ 2 }>
							<FlexItem>
								<Button
									variant="secondary"
									onClick={ handlePreviewClick }
									disabled={ rules.length === 0 || isProcessing }
								>
									{ isProcessing ? (
										<Spinner />
									) : (
										strings.dryRun || __( 'Scan Existing Media', 'vmfa-rules-engine' )
									) }
								</Button>
							</FlexItem>
							<FlexItem>
								<Button variant="primary" icon={ plus } onClick={ handleAddRule }>
									{ strings.addRule || __( 'Add Rule', 'vmfa-rules-engine' ) }
								</Button>
							</FlexItem>
						</Flex>
					</Flex>
				</CardHeader>
				<CardBody>
					{ rules.length === 0 ? (
						<div className="vmfa-rules-empty">
							<p>{ strings.noRules || __( 'No rules configured yet.', 'vmfa-rules-engine' ) }</p>
							<Button variant="primary" icon={ plus } onClick={ handleAddRule }>
								{ __( 'Create your first rule', 'vmfa-rules-engine' ) }
							</Button>
						</div>
					) : (
						<DndContext
							sensors={ sensors }
							collisionDetection={ closestCenter }
							onDragEnd={ handleDragEnd }
						>
							<SortableContext
								items={ rules.map( ( r ) => r.id ) }
								strategy={ verticalListSortingStrategy }
							>
								<div className="vmfa-rules-list">
									{ rules.map( ( rule ) => (
										<SortableRuleItem
											key={ rule.id }
											rule={ rule }
											folders={ folders }
											onEdit={ handleEditRule }
											onDelete={ handleDeleteRule }
											onToggle={ toggleRule }
										/>
									) ) }
								</div>
							</SortableContext>
						</DndContext>
					) }
				</CardBody>
			</Card>

			{ isEditorOpen && (
				<RuleEditor
					rule={ editingRule }
					folders={ folders }
					onSave={ handleSaveRule }
					onCancel={ () => {
						setIsEditorOpen( false );
						setEditingRule( null );
					} }
					isSaving={ isSaving }
				/>
			) }

			{ isPreviewOptionsOpen && (
				<Modal
					title={ __( 'Scan Options', 'vmfa-rules-engine' ) }
					onRequestClose={ () => setIsPreviewOptionsOpen( false ) }
					size="small"
				>
					<p>{ __( 'Choose which media files to scan.', 'vmfa-rules-engine' ) }</p>
					<CheckboxControl
						label={ __( 'Unassigned media only', 'vmfa-rules-engine' ) }
						help={ __( 'When checked, only media not yet in any folder will be scanned. Uncheck to include all media (may reassign existing).', 'vmfa-rules-engine' ) }
						checked={ previewUnassignedOnly }
						onChange={ setPreviewUnassignedOnly }
						__nextHasNoMarginBottom
					/>
					<Flex justify="flex-end" style={ { marginTop: '16px' } }>
						<FlexItem>
							<Button
								variant="secondary"
								onClick={ () => setIsPreviewOptionsOpen( false ) }
							>
								{ __( 'Cancel', 'vmfa-rules-engine' ) }
							</Button>
						</FlexItem>
						<FlexItem>
							<Button
								variant="primary"
								onClick={ handleRunPreview }
							>
								{ __( 'Run Preview', 'vmfa-rules-engine' ) }
							</Button>
						</FlexItem>
					</Flex>
				</Modal>
			) }

			{ isPreviewOpen && (
				<PreviewModal
					results={ results }
					onApply={ handleApply }
					onClose={ handleClosePreview }
					onLoadMore={ handleLoadMore }
					isProcessing={ isProcessing }
					isLoadingMore={ isLoadingMore }
				/>
			) }
		</div>
	);
}
