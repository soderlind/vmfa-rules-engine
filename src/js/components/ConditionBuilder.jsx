/**
 * Condition Builder component.
 *
 * @package VmfaRulesEngine
 */

import { useCallback } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import {
	Button,
	SelectControl,
	TextControl,
	Flex,
	FlexItem,
	FlexBlock,
} from '@wordpress/components';
import { plus, trash } from '@wordpress/icons';

import { useUsers } from '../hooks/useRules';

/**
 * Get default value for a condition type.
 *
 * @param {Object} conditionType Condition type definition.
 * @return {Object} Default condition value.
 */
function getDefaultCondition( conditionType ) {
	const condition = { type: conditionType.value };

	switch ( conditionType.inputType ) {
		case 'select':
			condition.value = conditionType.options?.[ 0 ]?.value || '';
			break;
		case 'dimensions':
			condition.dimension = 'width';
			condition.operator = 'gt';
			condition.value = 1920;
			break;
		case 'filesize':
			condition.operator = 'gt';
			condition.value = 1024; // 1MB in KB.
			break;
		case 'daterange':
			condition.operator = 'after';
			condition.value = '';
			break;
		case 'user':
			condition.value = '';
			break;
		default:
			condition.value = '';
	}

	return condition;
}

/**
 * Single condition row component.
 *
 * @param {Object}   props            Component props.
 * @param {Object}   props.condition  Condition data.
 * @param {number}   props.index      Condition index.
 * @param {Function} props.onChange   Change handler.
 * @param {Function} props.onRemove   Remove handler.
 * @param {Array}    props.users      Available users for author condition.
 * @return {JSX.Element} Condition row.
 */
function ConditionRow( { condition, index, onChange, onRemove, users } ) {
	const { conditionTypes = [] } = window.vmfaRulesEngine || {};
	const conditionType = conditionTypes.find( ( ct ) => ct.value === condition.type );

	/**
	 * Handle type change.
	 *
	 * @param {string} newType New condition type.
	 */
	const handleTypeChange = ( newType ) => {
		const newConditionType = conditionTypes.find( ( ct ) => ct.value === newType );
		if ( newConditionType ) {
			onChange( index, getDefaultCondition( newConditionType ) );
		}
	};

	/**
	 * Handle value change.
	 *
	 * @param {string} key   Field key.
	 * @param {*}      value New value.
	 */
	const handleValueChange = ( key, value ) => {
		onChange( index, { ...condition, [ key ]: value } );
	};

	/**
	 * Render value input based on condition type.
	 *
	 * @return {JSX.Element} Value input.
	 */
	const renderValueInput = () => {
		if ( ! conditionType ) {
			return null;
		}

		switch ( conditionType.inputType ) {
			case 'text':
				return (
					<TextControl
						value={ condition.value || '' }
						onChange={ ( val ) => handleValueChange( 'value', val ) }
						placeholder={ conditionType.placeholder || '' }
						__nextHasNoMarginBottom
					/>
				);

			case 'select':
				return (
					<SelectControl
						value={ condition.value || '' }
						options={ conditionType.options || [] }
						onChange={ ( val ) => handleValueChange( 'value', val ) }
						__nextHasNoMarginBottom
					/>
				);

			case 'dimensions':
				return (
					<Flex gap={ 2 } wrap>
						<FlexItem>
							<SelectControl
								value={ condition.dimension || 'width' }
								options={ [
									{ value: 'width', label: __( 'Width', 'vmfa-rules-engine' ) },
									{ value: 'height', label: __( 'Height', 'vmfa-rules-engine' ) },
									{ value: 'both', label: __( 'Both', 'vmfa-rules-engine' ) },
								] }
								onChange={ ( val ) => handleValueChange( 'dimension', val ) }
								__nextHasNoMarginBottom
							/>
						</FlexItem>
						<FlexItem>
							<SelectControl
								value={ condition.operator || 'gt' }
								options={ [
									{ value: 'gt', label: '>' },
									{ value: 'gte', label: '>=' },
									{ value: 'lt', label: '<' },
									{ value: 'lte', label: '<=' },
									{ value: 'eq', label: '=' },
									{ value: 'between', label: __( 'Between', 'vmfa-rules-engine' ) },
								] }
								onChange={ ( val ) => handleValueChange( 'operator', val ) }
								__nextHasNoMarginBottom
							/>
						</FlexItem>
						<FlexItem>
							<TextControl
								type="number"
								value={ condition.value || '' }
								onChange={ ( val ) => handleValueChange( 'value', parseInt( val, 10 ) || 0 ) }
								placeholder="1920"
								__nextHasNoMarginBottom
							/>
						</FlexItem>
						{ condition.operator === 'between' && (
							<FlexItem>
								<TextControl
									type="number"
									value={ condition.value_end || '' }
									onChange={ ( val ) => handleValueChange( 'value_end', parseInt( val, 10 ) || 0 ) }
									placeholder="3840"
									__nextHasNoMarginBottom
								/>
							</FlexItem>
						) }
						<FlexItem>
							<span className="vmfa-condition-unit">px</span>
						</FlexItem>
					</Flex>
				);

			case 'filesize':
				return (
					<Flex gap={ 2 } wrap>
						<FlexItem>
							<SelectControl
								value={ condition.operator || 'gt' }
								options={ [
									{ value: 'gt', label: '>' },
									{ value: 'gte', label: '>=' },
									{ value: 'lt', label: '<' },
									{ value: 'lte', label: '<=' },
									{ value: 'between', label: __( 'Between', 'vmfa-rules-engine' ) },
								] }
								onChange={ ( val ) => handleValueChange( 'operator', val ) }
								__nextHasNoMarginBottom
							/>
						</FlexItem>
						<FlexItem>
							<TextControl
								type="number"
								value={ condition.value || '' }
								onChange={ ( val ) => handleValueChange( 'value', parseInt( val, 10 ) || 0 ) }
								placeholder="1024"
								__nextHasNoMarginBottom
							/>
						</FlexItem>
						{ condition.operator === 'between' && (
							<FlexItem>
								<TextControl
									type="number"
									value={ condition.value_end || '' }
									onChange={ ( val ) => handleValueChange( 'value_end', parseInt( val, 10 ) || 0 ) }
									placeholder="5120"
									__nextHasNoMarginBottom
								/>
							</FlexItem>
						) }
						<FlexItem>
							<span className="vmfa-condition-unit">KB</span>
						</FlexItem>
					</Flex>
				);

			case 'daterange':
				return (
					<Flex gap={ 2 } wrap>
						<FlexItem>
							<SelectControl
								value={ condition.operator || 'after' }
								options={ [
									{ value: 'after', label: __( 'After', 'vmfa-rules-engine' ) },
									{ value: 'before', label: __( 'Before', 'vmfa-rules-engine' ) },
									{ value: 'on', label: __( 'On', 'vmfa-rules-engine' ) },
									{ value: 'between', label: __( 'Between', 'vmfa-rules-engine' ) },
									{ value: 'year', label: __( 'In year', 'vmfa-rules-engine' ) },
									{ value: 'month', label: __( 'In month', 'vmfa-rules-engine' ) },
								] }
								onChange={ ( val ) => handleValueChange( 'operator', val ) }
								__nextHasNoMarginBottom
							/>
						</FlexItem>
						<FlexItem>
							<TextControl
								type="date"
								value={ condition.value || '' }
								onChange={ ( val ) => handleValueChange( 'value', val ) }
								__nextHasNoMarginBottom
							/>
						</FlexItem>
						{ condition.operator === 'between' && (
							<FlexItem>
								<TextControl
									type="date"
									value={ condition.value_end || '' }
									onChange={ ( val ) => handleValueChange( 'value_end', val ) }
									__nextHasNoMarginBottom
								/>
							</FlexItem>
						) }
					</Flex>
				);

			case 'user':
				return (
					<SelectControl
						value={ String( condition.value || '' ) }
						options={ [
							{ value: '', label: __( 'Select a user...', 'vmfa-rules-engine' ) },
							...users.map( ( u ) => ( {
								value: String( u.id ),
								label: `${ u.display_name } (${ u.user_login })`,
							} ) ),
						] }
						onChange={ ( val ) => handleValueChange( 'value', parseInt( val, 10 ) || 0 ) }
						__nextHasNoMarginBottom
					/>
				);

			default:
				return (
					<TextControl
						value={ condition.value || '' }
						onChange={ ( val ) => handleValueChange( 'value', val ) }
						__nextHasNoMarginBottom
					/>
				);
		}
	};

	return (
		<div className="vmfa-condition-row">
			<Flex gap={ 3 } align="flex-start">
				<FlexItem>
					<SelectControl
						value={ condition.type }
						options={ [
							{ value: '', label: __( 'Select condition...', 'vmfa-rules-engine' ) },
							...conditionTypes.map( ( ct ) => ( {
								value: ct.value,
								label: ct.label,
							} ) ),
						] }
						onChange={ handleTypeChange }
						__nextHasNoMarginBottom
					/>
				</FlexItem>
				<FlexBlock>{ renderValueInput() }</FlexBlock>
				<FlexItem>
					<Button
						icon={ trash }
						label={ __( 'Remove condition', 'vmfa-rules-engine' ) }
						isDestructive
						onClick={ () => onRemove( index ) }
					/>
				</FlexItem>
			</Flex>
			{ conditionType?.description && (
				<p className="vmfa-condition-description">{ conditionType.description }</p>
			) }
		</div>
	);
}

/**
 * Condition Builder component.
 *
 * @param {Object}   props             Component props.
 * @param {Array}    props.conditions  Current conditions.
 * @param {Function} props.onChange    Change handler.
 * @return {JSX.Element} Condition builder.
 */
export function ConditionBuilder( { conditions, onChange } ) {
	const { users } = useUsers();
	const { conditionTypes = [] } = window.vmfaRulesEngine || {};

	/**
	 * Add a new condition.
	 */
	const handleAdd = useCallback( () => {
		const defaultType = conditionTypes[ 0 ];
		if ( defaultType ) {
			onChange( [ ...conditions, getDefaultCondition( defaultType ) ] );
		}
	}, [ conditions, conditionTypes, onChange ] );

	/**
	 * Update a condition.
	 *
	 * @param {number} index        Condition index.
	 * @param {Object} newCondition New condition data.
	 */
	const handleChange = useCallback(
		( index, newCondition ) => {
			const updated = [ ...conditions ];
			updated[ index ] = newCondition;
			onChange( updated );
		},
		[ conditions, onChange ]
	);

	/**
	 * Remove a condition.
	 *
	 * @param {number} index Condition index.
	 */
	const handleRemove = useCallback(
		( index ) => {
			const updated = conditions.filter( ( _, i ) => i !== index );
			onChange( updated );
		},
		[ conditions, onChange ]
	);

	return (
		<div className="vmfa-condition-builder">
			{ conditions.length > 0 ? (
				<div className="vmfa-condition-list">
					{ conditions.map( ( condition, index ) => (
						<ConditionRow
							key={ index }
							condition={ condition }
							index={ index }
							onChange={ handleChange }
							onRemove={ handleRemove }
							users={ users }
						/>
					) ) }
				</div>
			) : (
				<p className="vmfa-condition-empty">
					{ __( 'No conditions added yet. Add at least one condition.', 'vmfa-rules-engine' ) }
				</p>
			) }

			<Button
				variant="secondary"
				icon={ plus }
				onClick={ handleAdd }
				className="vmfa-condition-add"
			>
				{ __( 'Add Condition', 'vmfa-rules-engine' ) }
			</Button>
		</div>
	);
}
