<?php
/**
 * Rule Evaluator Service.
 *
 * @package VmfaRulesEngine
 */

namespace VmfaRulesEngine\Services;

use VmfaRulesEngine\Repository\RuleRepository;
use VmfaRulesEngine\Conditions\MatcherInterface;
use VmfaRulesEngine\Conditions\FilenameRegexMatcher;
use VmfaRulesEngine\Conditions\MimeTypeMatcher;
use VmfaRulesEngine\Conditions\DimensionsMatcher;
use VmfaRulesEngine\Conditions\FileSizeMatcher;
use VmfaRulesEngine\Conditions\ExifCameraMatcher;
use VmfaRulesEngine\Conditions\ExifDateMatcher;
use VmfaRulesEngine\Conditions\AuthorMatcher;
use VmfaRulesEngine\Conditions\IptcKeywordsMatcher;

/**
 * Evaluates rules against attachments and assigns folders.
 */
class RuleEvaluator {

	/**
	 * Rule repository.
	 *
	 * @var RuleRepository
	 */
	private $repository;

	/**
	 * Condition matchers indexed by type.
	 *
	 * @var array<string, MatcherInterface>
	 */
	private $matchers = array();

	/**
	 * Parent plugin taxonomy name.
	 *
	 * @var string
	 */
	const TAXONOMY = 'vmfo_folder';

	/**
	 * Constructor.
	 *
	 * @param RuleRepository|null $repository Optional repository instance.
	 */
	public function __construct( $repository = null ) {
		$this->repository = $repository ?? new RuleRepository();
		$this->register_matchers();
	}

	/**
	 * Register all condition matchers.
	 *
	 * @return void
	 */
	private function register_matchers() {
		$matcher_classes = array(
			FilenameRegexMatcher::class,
			MimeTypeMatcher::class,
			DimensionsMatcher::class,
			FileSizeMatcher::class,
			ExifCameraMatcher::class,
			ExifDateMatcher::class,
			AuthorMatcher::class,
			IptcKeywordsMatcher::class,
		);

		foreach ( $matcher_classes as $class ) {
			$matcher                              = new $class();
			$this->matchers[ $matcher->get_type() ] = $matcher;
		}

		/**
		 * Filter to allow adding custom matchers.
		 *
		 * @param array<string, MatcherInterface> $matchers Registered matchers.
		 */
		$this->matchers = apply_filters( 'vmfa_rules_engine_matchers', $this->matchers );
	}

	/**
	 * Evaluate rules on upload.
	 *
	 * Hooked to 'wp_generate_attachment_metadata' filter.
	 *
	 * @param array  $metadata      Attachment metadata.
	 * @param int    $attachment_id Attachment ID.
	 * @param string $context       Context: 'create' for new uploads.
	 * @return array Unmodified metadata.
	 */
	public function evaluate_on_upload( $metadata, $attachment_id, $context ) {
		// Only process new uploads.
		if ( 'create' !== $context ) {
			return $metadata;
		}

		// Check if attachment already has a folder assigned (e.g., by parent plugin's default folder).
		$existing_terms = wp_get_object_terms( $attachment_id, self::TAXONOMY, array( 'fields' => 'ids' ) );

		/**
		 * Filter whether to skip rule evaluation if folder already assigned.
		 *
		 * @param bool $skip          Whether to skip evaluation.
		 * @param int  $attachment_id Attachment ID.
		 * @param array $existing_terms Currently assigned folder IDs.
		 */
		$skip_if_assigned = apply_filters( 'vmfa_rules_engine_skip_if_assigned', false, $attachment_id, $existing_terms );

		if ( $skip_if_assigned && ! empty( $existing_terms ) && ! is_wp_error( $existing_terms ) ) {
			return $metadata;
		}

		// Evaluate rules.
		$result = $this->evaluate( $attachment_id, $metadata );

		if ( $result && ! empty( $result['folder_id'] ) ) {
			$this->assign_folder( $attachment_id, $result['folder_id'], $result['rule'] );
		}

		return $metadata;
	}

	/**
	 * Evaluate all enabled rules against an attachment.
	 *
	 * @param int   $attachment_id Attachment ID.
	 * @param array $metadata      Attachment metadata (optional, will be fetched if not provided).
	 * @return array|null Result with 'folder_id' and 'rule', or null if no match.
	 */
	public function evaluate( $attachment_id, $metadata = null ) {
		if ( null === $metadata ) {
			$metadata = wp_get_attachment_metadata( $attachment_id );
			if ( ! is_array( $metadata ) ) {
				$metadata = array();
			}
		}

		$rules = $this->repository->get_enabled();

		foreach ( $rules as $rule ) {
			if ( $this->rule_matches( $rule, $attachment_id, $metadata ) ) {
				return array(
					'folder_id' => $rule['folder_id'],
					'rule'      => $rule,
				);
			}

			// If rule has stop_processing and didn't match, continue to next rule.
		}

		return null;
	}

	/**
	 * Check if a single rule matches an attachment.
	 *
	 * @param array $rule          Rule data.
	 * @param int   $attachment_id Attachment ID.
	 * @param array $metadata      Attachment metadata.
	 * @return bool True if all conditions match (AND logic).
	 */
	public function rule_matches( $rule, $attachment_id, $metadata ) {
		$conditions = $rule['conditions'] ?? array();

		if ( empty( $conditions ) ) {
			return false;
		}

		// AND logic: all conditions must match.
		foreach ( $conditions as $condition ) {
			$type = $condition['type'] ?? '';

			if ( ! isset( $this->matchers[ $type ] ) ) {
				// Unknown condition type, skip (or fail depending on strictness).
				continue;
			}

			$matcher = $this->matchers[ $type ];

			if ( ! $matcher->matches( $attachment_id, $metadata, $condition ) ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Assign a folder to an attachment.
	 *
	 * @param int   $attachment_id Attachment ID.
	 * @param int   $folder_id     Folder term ID.
	 * @param array $rule          The matching rule (for action hook).
	 * @return bool True on success.
	 */
	public function assign_folder( $attachment_id, $folder_id, $rule = array() ) {
		// Verify folder exists.
		$term = get_term( $folder_id, self::TAXONOMY );

		if ( ! $term || is_wp_error( $term ) ) {
			return false;
		}

		$result = wp_set_object_terms( $attachment_id, array( $folder_id ), self::TAXONOMY );

		if ( is_wp_error( $result ) ) {
			return false;
		}

		/**
		 * Action fired after rule-based folder assignment.
		 *
		 * @param int   $attachment_id Attachment ID.
		 * @param int   $folder_id     Assigned folder ID.
		 * @param array $rule          The matching rule.
		 */
		do_action( 'vmfa_rules_engine_folder_assigned', $attachment_id, $folder_id, $rule );

		return true;
	}

	/**
	 * Get available matchers.
	 *
	 * @return array<string, MatcherInterface>
	 */
	public function get_matchers() {
		return $this->matchers;
	}
}
