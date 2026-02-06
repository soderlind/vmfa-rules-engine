/**
 * Dashboard Page Component for Rules Engine.
 *
 * Displays the main rules management interface with batch operations.
 *
 * @package VmfaRulesEngine
 */

import { useState, useCallback } from '@wordpress/element';
import {
	Button,
	Card,
	CardBody,
	CardHeader,
	CheckboxControl,
	Notice,
	Spinner,
} from '@wordpress/components';
import { __ } from '@wordpress/i18n';

import { RulesPanel } from '../components/RulesPanel';
import { useStats, useBatchOperations } from '../hooks/useRules';
import { PreviewModal } from '../components/PreviewModal';

/**
 * Dashboard Page component.
 *
 * @return {JSX.Element} The dashboard page content.
 */
export function DashboardPage() {
	const { stats, refresh: refreshStats } = useStats();
	const {
		preview,
		loadMore,
		apply,
		isProcessing,
		isLoadingMore,
		results,
		clearResults,
	} = useBatchOperations();

	const [ unassignedOnly, setUnassignedOnly ] = useState( true );
	const [ isPreviewOpen, setIsPreviewOpen ] = useState( false );
	const [ notice, setNotice ] = useState( null );

	// Import sprintf for translations.
	const { sprintf } = wp.i18n;

	/**
	 * Handle preview/scan all media.
	 */
	const handlePreview = useCallback( async () => {
		try {
			setNotice( null );
			await preview( { unassignedOnly } );
			setIsPreviewOpen( true );
		} catch ( err ) {
			setNotice( {
				type: 'error',
				message:
					err.message ||
					__( 'Failed to preview rules.', 'vmfa-rules-engine' ),
			} );
		}
	}, [ preview, unassignedOnly ] );

	/**
	 * Handle apply rules to all media.
	 */
	const handleApply = useCallback( async () => {
		if (
			! window.confirm(
				__(
					'Are you sure you want to apply all rules? This will assign folders to matching media files.',
					'vmfa-rules-engine'
				)
			)
		) {
			return;
		}

		try {
			setNotice( null );
			const result = await apply( { unassignedOnly } );
			await refreshStats();
			setNotice( {
				type: 'success',
				message: sprintf(
					/* translators: %d: number of files */
					__( 'Rules applied to %d files.', 'vmfa-rules-engine' ),
					result?.applied || 0
				),
			} );
		} catch ( err ) {
			setNotice( {
				type: 'error',
				message:
					err.message ||
					__( 'Failed to apply rules.', 'vmfa-rules-engine' ),
			} );
		}
	}, [ apply, unassignedOnly, refreshStats ] );

	/**
	 * Handle load more in preview.
	 */
	const handleLoadMore = useCallback( async () => {
		await loadMore( { unassignedOnly } );
	}, [ loadMore, unassignedOnly ] );

	/**
	 * Handle apply from preview modal.
	 *
	 * @param {Array} attachmentIds - Optional array of attachment IDs to apply.
	 */
	const handleApplyFromPreview = useCallback(
		async ( attachmentIds ) => {
			try {
				const result = await apply( {
					unassignedOnly,
					attachmentIds,
				} );
				await refreshStats();
				setIsPreviewOpen( false );
				clearResults();
				setNotice( {
					type: 'success',
					message: sprintf(
						/* translators: %d: number of files */
						__( 'Rules applied to %d files.', 'vmfa-rules-engine' ),
						result?.applied || 0
					),
				} );
			} catch ( err ) {
				setNotice( {
					type: 'error',
					message:
						err.message ||
						__( 'Failed to apply rules.', 'vmfa-rules-engine' ),
				} );
			}
		},
		[ apply, unassignedOnly, refreshStats, clearResults ]
	);

	/**
	 * Close preview modal.
	 */
	const handleClosePreview = useCallback( () => {
		setIsPreviewOpen( false );
		clearResults();
	}, [ clearResults ] );

	return (
		<>
			{ notice && (
				<Notice
					status={ notice.type }
					isDismissible
					onDismiss={ () => setNotice( null ) }
				>
					{ notice.message }
				</Notice>
			) }

			<RulesPanel />

			<Card className="vmfo-actions-card">
				<CardHeader>
					<h3>{ __( 'Batch Operations', 'vmfa-rules-engine' ) }</h3>
				</CardHeader>
				<CardBody>
					<p>
						{ __(
							'Apply your rules to existing media files in the library. Use Preview to see what changes will be made before applying.',
							'vmfa-rules-engine'
						) }
					</p>

					<div className="vmfo-actions-options">
						<CheckboxControl
							label={ __(
								'Only process unassigned media',
								'vmfa-rules-engine'
							) }
							help={ __(
								'When checked, only media files not currently in a folder will be processed.',
								'vmfa-rules-engine'
							) }
							checked={ unassignedOnly }
							onChange={ setUnassignedOnly }
						/>
					</div>

					{ stats && (
						<p className="vmfo-actions-stats">
							{ sprintf(
								/* translators: %1$d: total media, %2$d: unassigned count */
								__(
									'%1$d total media files, %2$d unassigned.',
									'vmfa-rules-engine'
								),
								stats.total || 0,
								stats.unassigned || 0
							) }
						</p>
					) }

					<div className="vmfo-actions-buttons">
						<Button
							variant="secondary"
							onClick={ handlePreview }
							disabled={ isProcessing }
						>
							{ isProcessing ? (
								<>
									<Spinner />
									{ __( 'Scanning…', 'vmfa-rules-engine' ) }
								</>
							) : (
								__( 'Preview Rules', 'vmfa-rules-engine' )
							) }
						</Button>
						<Button
							variant="primary"
							onClick={ handleApply }
							disabled={ isProcessing }
						>
							{ __(
								'Apply Rules to All Media',
								'vmfa-rules-engine'
							) }
						</Button>
					</div>
				</CardBody>
			</Card>

			<Card className="vmfo-actions-card vmfo-info-card">
				<CardHeader>
					<h3>
						{ __( 'How Batch Operations Work', 'vmfa-rules-engine' ) }
					</h3>
				</CardHeader>
				<CardBody>
					<ol>
						<li>
							{ __(
								'Rules are evaluated in priority order (top to bottom)',
								'vmfa-rules-engine'
							) }
						</li>
						<li>
							{ __(
								'Each media file is checked against all enabled rules',
								'vmfa-rules-engine'
							) }
						</li>
						<li>
							{ __(
								'When a rule matches, the file is assigned to the target folder',
								'vmfa-rules-engine'
							) }
						</li>
						<li>
							{ __(
								'If "Stop Processing" is enabled on a rule, no further rules are checked',
								'vmfa-rules-engine'
							) }
						</li>
					</ol>
				</CardBody>
			</Card>

			{ isPreviewOpen && results && (
				<PreviewModal
					results={ results }
					onClose={ handleClosePreview }
					onApply={ handleApplyFromPreview }
					onLoadMore={ handleLoadMore }
					isLoadingMore={ isLoadingMore }
				/>
			) }
		</>
	);
}

export default DashboardPage;
