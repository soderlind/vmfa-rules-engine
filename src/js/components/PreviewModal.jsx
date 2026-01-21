/**
 * Preview Modal component.
 *
 * @package
 */

import {
	useState,
	useCallback,
	useMemo,
	useRef,
	useEffect,
} from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import {
	Button,
	Modal,
	Spinner,
	CheckboxControl,
	Flex,
	FlexItem,
} from '@wordpress/components';

/**
 * Preview Modal component.
 *
 * @param {Object}   props               Component props.
 * @param {Object}   props.results       Preview results.
 * @param {Function} props.onApply       Apply handler.
 * @param {Function} props.onClose       Close handler.
 * @param {Function} props.onLoadMore    Load more handler.
 * @param {boolean}  props.isProcessing  Whether processing is in progress.
 * @param {boolean}  props.isLoadingMore Whether loading more items.
 * @return {JSX.Element} Preview modal.
 */
export function PreviewModal( {
	results,
	onApply,
	onClose,
	onLoadMore,
	isProcessing,
	isLoadingMore,
} ) {
	const [ selectedIds, setSelectedIds ] = useState( new Set() );
	const [ selectAll, setSelectAll ] = useState( false );
	const [ applied, setApplied ] = useState( false );
	const tableContainerRef = useRef( null );

	const { strings = {} } = window.vmfaRulesEngine || {};

	// Items that will be assigned.
	const assignableItems = useMemo( () => {
		if ( ! results?.items ) {
			return [];
		}
		return results.items.filter(
			( item ) => item.status === 'will_assign'
		);
	}, [ results ] );

	// Items with no match.
	const unmatchedItems = useMemo( () => {
		if ( ! results?.items ) {
			return [];
		}
		return results.items.filter( ( item ) => item.status === 'no_match' );
	}, [ results ] );

	// Infinite scroll: detect when user scrolls near bottom.
	useEffect( () => {
		const container = tableContainerRef.current;
		if ( ! container || ! results?.has_more || isLoadingMore ) {
			return;
		}

		const handleScroll = () => {
			const { scrollTop, scrollHeight, clientHeight } = container;
			// Load more when within 100px of bottom.
			if ( scrollHeight - scrollTop - clientHeight < 100 ) {
				onLoadMore?.();
			}
		};

		container.addEventListener( 'scroll', handleScroll );
		return () => container.removeEventListener( 'scroll', handleScroll );
	}, [ results?.has_more, isLoadingMore, onLoadMore ] );

	/**
	 * Toggle selection of an item.
	 *
	 * @param {number} id Attachment ID.
	 */
	const toggleSelection = useCallback( ( id ) => {
		setSelectedIds( ( prev ) => {
			const next = new Set( prev );
			if ( next.has( id ) ) {
				next.delete( id );
			} else {
				next.add( id );
			}
			return next;
		} );
	}, [] );

	/**
	 * Toggle select all.
	 *
	 * @param {boolean} checked Whether to select all.
	 */
	const handleSelectAll = useCallback(
		( checked ) => {
			setSelectAll( checked );
			if ( checked ) {
				setSelectedIds(
					new Set(
						assignableItems.map( ( item ) => item.attachment_id )
					)
				);
			} else {
				setSelectedIds( new Set() );
			}
		},
		[ assignableItems ]
	);

	/**
	 * Handle apply button click.
	 */
	const handleApply = useCallback( async () => {
		if ( selectedIds.size === 0 ) {
			return;
		}

		await onApply( Array.from( selectedIds ) );
		setApplied( true );
	}, [ selectedIds, onApply ] );

	if ( ! results ) {
		return (
			<Modal
				title={
					strings.preview || __( 'Preview', 'vmfa-rules-engine' )
				}
				onRequestClose={ onClose }
				className="vmfa-preview-modal"
			>
				<div className="vmfa-preview-loading">
					<Spinner />
					<p>{ __( 'Loading preview…', 'vmfa-rules-engine' ) }</p>
				</div>
			</Modal>
		);
	}

	if ( applied && results.assigned !== undefined ) {
		return (
			<Modal
				title={ __( 'Rules Applied', 'vmfa-rules-engine' ) }
				onRequestClose={ onClose }
				className="vmfa-preview-modal"
			>
				<div className="vmfa-preview-applied">
					<div className="vmfa-preview-stats">
						<div className="vmfa-preview-stat vmfa-preview-stat--success">
							<span className="vmfa-preview-stat-value">
								{ results.assigned }
							</span>
							<span className="vmfa-preview-stat-label">
								{ __( 'Assigned', 'vmfa-rules-engine' ) }
							</span>
						</div>
						<div className="vmfa-preview-stat">
							<span className="vmfa-preview-stat-value">
								{ results.skipped }
							</span>
							<span className="vmfa-preview-stat-label">
								{ __( 'Skipped', 'vmfa-rules-engine' ) }
							</span>
						</div>
						{ results.errors > 0 && (
							<div className="vmfa-preview-stat vmfa-preview-stat--error">
								<span className="vmfa-preview-stat-value">
									{ results.errors }
								</span>
								<span className="vmfa-preview-stat-label">
									{ __( 'Errors', 'vmfa-rules-engine' ) }
								</span>
							</div>
						) }
					</div>
					<Button variant="primary" onClick={ onClose }>
						{ __( 'Close', 'vmfa-rules-engine' ) }
					</Button>
				</div>
			</Modal>
		);
	}

	return (
		<Modal
			title={ strings.preview || __( 'Preview', 'vmfa-rules-engine' ) }
			onRequestClose={ onClose }
			className="vmfa-preview-modal"
			shouldCloseOnClickOutside={ false }
		>
			<div className="vmfa-preview-content">
				<div className="vmfa-preview-summary">
					<p>
						{ __( 'Scanned', 'vmfa-rules-engine' ) }{ ' ' }
						<strong>{ results.total }</strong>
						{ results.total_count > results.total && (
							<>
								{ ' ' }
								{ __( 'of', 'vmfa-rules-engine' ) }{ ' ' }
								<strong>{ results.total_count }</strong>
							</>
						) }{ ' ' }
						{ __( 'media items.', 'vmfa-rules-engine' ) }{ ' ' }
						<strong>{ results.matched }</strong>{ ' ' }
						{ __(
							'will be assigned to folders.',
							'vmfa-rules-engine'
						) }
						{ results.has_more && (
							<span className="vmfa-preview-loading-hint">
								{ ' ' }
								{ __(
									'Scroll down to load more.',
									'vmfa-rules-engine'
								) }
							</span>
						) }
					</p>
				</div>

				{ assignableItems.length > 0 && (
					<div className="vmfa-preview-section">
						<div className="vmfa-preview-section-header">
							<h3>
								{ __(
									'Will be assigned',
									'vmfa-rules-engine'
								) }
							</h3>
							<CheckboxControl
								label={ __(
									'Select all loaded',
									'vmfa-rules-engine'
								) }
								checked={ selectAll }
								onChange={ handleSelectAll }
								__nextHasNoMarginBottom
							/>
						</div>
						<div
							className="vmfa-preview-table"
							ref={ tableContainerRef }
						>
							<table>
								<thead>
									<tr>
										<th className="vmfa-preview-col-check"></th>
										<th className="vmfa-preview-col-thumb"></th>
										<th>
											{ __(
												'File',
												'vmfa-rules-engine'
											) }
										</th>
										<th>
											{ __(
												'Rule',
												'vmfa-rules-engine'
											) }
										</th>
										<th>
											{ __(
												'Folder',
												'vmfa-rules-engine'
											) }
										</th>
									</tr>
								</thead>
								<tbody>
									{ assignableItems.map( ( item ) => (
										<tr key={ item.attachment_id }>
											<td className="vmfa-preview-col-check">
												<CheckboxControl
													checked={ selectedIds.has(
														item.attachment_id
													) }
													onChange={ () =>
														toggleSelection(
															item.attachment_id
														)
													}
													__nextHasNoMarginBottom
												/>
											</td>
											<td className="vmfa-preview-col-thumb">
												{ item.thumbnail && (
													<img
														src={ item.thumbnail }
														alt=""
														width="40"
														height="40"
													/>
												) }
											</td>
											<td>
												<div className="vmfa-preview-file">
													<span className="vmfa-preview-filename">
														{ item.filename }
													</span>
													<span className="vmfa-preview-title">
														{ item.title }
													</span>
												</div>
											</td>
											<td>{ item.matched_rule?.name }</td>
											<td>
												<span className="vmfa-preview-folder">
													{ item.target_folder?.name }
												</span>
											</td>
										</tr>
									) ) }
								</tbody>
							</table>
							{ isLoadingMore && (
								<div className="vmfa-preview-loading-more">
									<Spinner />
									<span>
										{ __(
											'Loading more…',
											'vmfa-rules-engine'
										) }
									</span>
								</div>
							) }
							{ results.has_more && ! isLoadingMore && (
								<div className="vmfa-preview-load-more">
									<Button
										variant="secondary"
										onClick={ onLoadMore }
									>
										{ __(
											'Load more',
											'vmfa-rules-engine'
										) }
									</Button>
								</div>
							) }
						</div>
					</div>
				) }

				{ unmatchedItems.length > 0 && (
					<details className="vmfa-preview-section vmfa-preview-section--collapsed">
						<summary>
							{ __( 'No matching rules', 'vmfa-rules-engine' ) } (
							{ unmatchedItems.length })
						</summary>
						<div className="vmfa-preview-unmatched">
							{ unmatchedItems.slice( 0, 20 ).map( ( item ) => (
								<div
									key={ item.attachment_id }
									className="vmfa-preview-unmatched-item"
								>
									{ item.thumbnail && (
										<img
											src={ item.thumbnail }
											alt=""
											width="30"
											height="30"
										/>
									) }
									<span>{ item.filename }</span>
								</div>
							) ) }
							{ unmatchedItems.length > 20 && (
								<p className="vmfa-preview-more">
									{ __( 'And', 'vmfa-rules-engine' ) }{ ' ' }
									{ unmatchedItems.length - 20 }{ ' ' }
									{ __( 'more…', 'vmfa-rules-engine' ) }
								</p>
							) }
						</div>
					</details>
				) }

				<Flex justify="flex-end" className="vmfa-preview-actions">
					<FlexItem>
						<Button
							variant="tertiary"
							onClick={ onClose }
							disabled={ isProcessing }
						>
							{ __( 'Cancel', 'vmfa-rules-engine' ) }
						</Button>
					</FlexItem>
					<FlexItem>
						<Button
							variant="primary"
							onClick={ handleApply }
							disabled={ selectedIds.size === 0 || isProcessing }
						>
							{ isProcessing ? (
								<>
									<Spinner />
									{ __( 'Applying…', 'vmfa-rules-engine' ) }
								</>
							) : (
								<>
									{ strings.apply ||
										__(
											'Apply Changes',
											'vmfa-rules-engine'
										) }
									{ selectedIds.size > 0 &&
										` (${ selectedIds.size })` }
								</>
							) }
						</Button>
					</FlexItem>
				</Flex>
			</div>
		</Modal>
	);
}
