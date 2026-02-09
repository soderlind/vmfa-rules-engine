<?php
/**
 * Rule Repository class.
 *
 * @package VmfaRulesEngine
 */

namespace VmfaRulesEngine\Repository;

defined( 'ABSPATH' ) || exit;

/**
 * Handles CRUD operations for rules stored in WordPress options.
 */
class RuleRepository {

	/**
	 * Option name for storing rules.
	 *
	 * @var string
	 */
	const OPTION_NAME = 'vmfa_rules_engine_rules';

	/**
	 * Get all rules.
	 *
	 * @return array Array of rules sorted by priority.
	 */
	public function get_all() {
		$rules = get_option( self::OPTION_NAME, array() );

		if ( ! is_array( $rules ) ) {
			return array();
		}

		// Sort by priority (lower number = higher priority).
		usort(
			$rules,
			function ( $a, $b ) {
				return ( $a[ 'priority' ] ?? 10 ) - ( $b[ 'priority' ] ?? 10 );
			}
		);

		return $rules;
	}

	/**
	 * Get only enabled rules.
	 *
	 * @return array Array of enabled rules sorted by priority.
	 */
	public function get_enabled() {
		$rules = $this->get_all();

		return array_filter(
			$rules,
			function ( $rule ) {
				return ! empty( $rule[ 'enabled' ] );
			}
		);
	}

	/**
	 * Get a single rule by ID.
	 *
	 * @param string $id Rule ID.
	 * @return array|null Rule data or null if not found.
	 */
	public function get( $id ) {
		$rules = $this->get_all();

		foreach ( $rules as $rule ) {
			if ( $rule[ 'id' ] === $id ) {
				return $rule;
			}
		}

		return null;
	}

	/**
	 * Create a new rule.
	 *
	 * @param array $data Rule data.
	 * @return array Created rule with generated ID.
	 */
	public function create( $data ) {
		$rules = $this->get_all();

		// Generate unique ID.
		$id = $this->generate_id();

		$rule = $this->prepare_rule_data( $data, $id );

		// Set default priority if not provided.
		if ( ! isset( $rule[ 'priority' ] ) ) {
			$rule[ 'priority' ] = count( $rules ) + 1;
		}

		$rules[] = $rule;

		update_option( self::OPTION_NAME, $rules );

		return $rule;
	}

	/**
	 * Update an existing rule.
	 *
	 * @param string $id   Rule ID.
	 * @param array  $data Updated rule data.
	 * @return array|null Updated rule or null if not found.
	 */
	public function update( $id, $data ) {
		$rules = $this->get_all();
		$found = false;

		foreach ( $rules as $index => $rule ) {
			if ( $rule[ 'id' ] === $id ) {
				$rules[ $index ] = $this->prepare_rule_data( $data, $id );
				$found           = true;
				break;
			}
		}

		if ( ! $found ) {
			return null;
		}

		update_option( self::OPTION_NAME, $rules );

		return $this->get( $id );
	}

	/**
	 * Delete a rule.
	 *
	 * @param string $id Rule ID.
	 * @return bool True if deleted, false if not found.
	 */
	public function delete( $id ) {
		$rules = $this->get_all();
		$found = false;

		$rules = array_filter(
			$rules,
			function ( $rule ) use ( $id, &$found ) {
				if ( $rule[ 'id' ] === $id ) {
					$found = true;
					return false;
				}
				return true;
			}
		);

		if ( ! $found ) {
			return false;
		}

		// Re-index array.
		$rules = array_values( $rules );

		update_option( self::OPTION_NAME, $rules );

		return true;
	}

	/**
	 * Reorder rules.
	 *
	 * @param array $order Array of rule IDs in desired order.
	 * @return array Reordered rules.
	 */
	public function reorder( $order ) {
		$rules       = $this->get_all();
		$rules_by_id = array();

		// Index rules by ID.
		foreach ( $rules as $rule ) {
			$rules_by_id[ $rule[ 'id' ] ] = $rule;
		}

		// Rebuild array in new order with updated priorities.
		$reordered = array();
		$priority  = 1;

		foreach ( $order as $id ) {
			if ( isset( $rules_by_id[ $id ] ) ) {
				$rule             = $rules_by_id[ $id ];
				$rule[ 'priority' ] = $priority;
				$reordered[]      = $rule;
				++$priority;
				unset( $rules_by_id[ $id ] );
			}
		}

		// Append any rules not in the order array.
		foreach ( $rules_by_id as $rule ) {
			$rule[ 'priority' ] = $priority;
			$reordered[]      = $rule;
			++$priority;
		}

		update_option( self::OPTION_NAME, $reordered );

		return $reordered;
	}

	/**
	 * Prepare rule data with defaults and sanitization.
	 *
	 * @param array  $data Rule data.
	 * @param string $id   Rule ID.
	 * @return array Prepared rule data.
	 */
	private function prepare_rule_data( $data, $id ) {
		return array(
			'id'              => $id,
			'name'            => sanitize_text_field( $data[ 'name' ] ?? '' ),
			'conditions'      => $this->sanitize_conditions( $data[ 'conditions' ] ?? array() ),
			'folder_id'       => absint( $data[ 'folder_id' ] ?? 0 ),
			'priority'        => absint( $data[ 'priority' ] ?? 10 ),
			'stop_processing' => ! empty( $data[ 'stop_processing' ] ),
			'enabled'         => ! empty( $data[ 'enabled' ] ),
		);
	}

	/**
	 * Sanitize conditions array.
	 *
	 * @param array $conditions Raw conditions.
	 * @return array Sanitized conditions.
	 */
	private function sanitize_conditions( $conditions ) {
		if ( ! is_array( $conditions ) ) {
			return array();
		}

		$sanitized = array();

		foreach ( $conditions as $condition ) {
			if ( ! isset( $condition[ 'type' ] ) ) {
				continue;
			}

			$sanitized_condition = array(
				'type' => sanitize_key( $condition[ 'type' ] ),
			);

			// Sanitize based on condition type.
			switch ( $condition[ 'type' ] ) {
				case 'filename_regex':
				case 'exif_camera':
				case 'iptc_keywords':
					$sanitized_condition[ 'value' ] = sanitize_text_field( $condition[ 'value' ] ?? '' );
					break;

				case 'mime_type':
					$sanitized_condition[ 'value' ] = sanitize_mime_type( $condition[ 'value' ] ?? '' );
					break;

				case 'dimensions':
					$sanitized_condition[ 'operator' ] = sanitize_key( $condition[ 'operator' ] ?? 'gt' );
					$sanitized_condition[ 'dimension' ] = sanitize_key( $condition[ 'dimension' ] ?? 'width' );
					$sanitized_condition[ 'value' ] = absint( $condition[ 'value' ] ?? 0 );
					$sanitized_condition[ 'value_end' ] = absint( $condition[ 'value_end' ] ?? 0 );
					break;

				case 'file_size':
					$sanitized_condition[ 'operator' ] = sanitize_key( $condition[ 'operator' ] ?? 'gt' );
					$sanitized_condition[ 'value' ] = absint( $condition[ 'value' ] ?? 0 );
					$sanitized_condition[ 'value_end' ] = absint( $condition[ 'value_end' ] ?? 0 );
					break;

				case 'exif_date':
					$sanitized_condition[ 'operator' ] = sanitize_key( $condition[ 'operator' ] ?? 'after' );
					$sanitized_condition[ 'value' ] = sanitize_text_field( $condition[ 'value' ] ?? '' );
					$sanitized_condition[ 'value_end' ] = sanitize_text_field( $condition[ 'value_end' ] ?? '' );
					break;

				case 'author':
					$sanitized_condition[ 'value' ] = absint( $condition[ 'value' ] ?? 0 );
					break;

				default:
					$sanitized_condition[ 'value' ] = sanitize_text_field( $condition[ 'value' ] ?? '' );
					break;
			}

			$sanitized[] = $sanitized_condition;
		}

		return $sanitized;
	}

	/**
	 * Generate a unique rule ID.
	 *
	 * @return string Unique ID.
	 */
	private function generate_id() {
		return 'rule_' . wp_generate_password( 8, false, false );
	}
}
