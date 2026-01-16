<?php
/**
 * Batch Processor Service.
 *
 * @package VmfaRulesEngine
 */

namespace VmfaRulesEngine\Services;

/**
 * Handles batch rule application to existing media library.
 */
class BatchProcessor {

	/**
	 * Rule evaluator.
	 *
	 * @var RuleEvaluator
	 */
	private $evaluator;

	/**
	 * Parent plugin taxonomy name.
	 *
	 * @var string
	 */
	const TAXONOMY = 'vmfo_folder';

	/**
	 * Constructor.
	 *
	 * @param RuleEvaluator|null $evaluator Optional evaluator instance.
	 */
	public function __construct( $evaluator = null ) {
		$this->evaluator = $evaluator ?? new RuleEvaluator();
	}

	/**
	 * Get attachments for processing.
	 *
	 * @param array $args Query arguments.
	 * @return array Array of attachment IDs.
	 */
	public function get_attachments( $args = array() ) {
		$defaults = array(
			'post_type'      => 'attachment',
			'post_status'    => 'inherit',
			'posts_per_page' => -1,
			'fields'         => 'ids',
			'orderby'        => 'ID',
			'order'          => 'ASC',
		);

		$query_args = wp_parse_args( $args, $defaults );

		// Filter to only unassigned if requested.
		if ( ! empty( $args['unassigned_only'] ) ) {
			$query_args['tax_query'] = array(
				array(
					'taxonomy' => self::TAXONOMY,
					'operator' => 'NOT EXISTS',
				),
			);
		}

		// Filter by mime type if specified.
		if ( ! empty( $args['mime_type'] ) ) {
			$query_args['post_mime_type'] = $args['mime_type'];
		}

		$query = new \WP_Query( $query_args );

		return $query->posts;
	}

	/**
	 * Preview rule application (dry run).
	 *
	 * @param array $args Processing arguments.
	 * @return array Preview results.
	 */
	public function preview( $args = array() ) {
		$attachment_ids = $this->get_attachments( $args );
		$results        = array(
			'total'     => count( $attachment_ids ),
			'matched'   => 0,
			'unmatched' => 0,
			'items'     => array(),
		);

		foreach ( $attachment_ids as $attachment_id ) {
			$metadata = wp_get_attachment_metadata( $attachment_id );
			if ( ! is_array( $metadata ) ) {
				$metadata = array();
			}

			$match = $this->evaluator->evaluate( $attachment_id, $metadata );

			if ( $match ) {
				$folder = get_term( $match['folder_id'], self::TAXONOMY );

				$results['items'][] = array(
					'attachment_id' => $attachment_id,
					'title'         => get_the_title( $attachment_id ),
					'filename'      => basename( get_attached_file( $attachment_id ) ),
					'thumbnail'     => wp_get_attachment_image_url( $attachment_id, 'thumbnail' ),
					'matched_rule'  => array(
						'id'   => $match['rule']['id'],
						'name' => $match['rule']['name'],
					),
					'target_folder' => array(
						'id'   => $match['folder_id'],
						'name' => $folder ? $folder->name : __( 'Unknown', 'vmfa-rules-engine' ),
					),
					'status'        => 'will_assign',
				);
				++$results['matched'];
			} else {
				$results['items'][] = array(
					'attachment_id' => $attachment_id,
					'title'         => get_the_title( $attachment_id ),
					'filename'      => basename( get_attached_file( $attachment_id ) ),
					'thumbnail'     => wp_get_attachment_image_url( $attachment_id, 'thumbnail' ),
					'matched_rule'  => null,
					'target_folder' => null,
					'status'        => 'no_match',
				);
				++$results['unmatched'];
			}
		}

		return $results;
	}

	/**
	 * Apply rules to attachments.
	 *
	 * @param array $args Processing arguments.
	 * @return array Processing results.
	 */
	public function apply( $args = array() ) {
		$attachment_ids = $this->get_attachments( $args );
		$results        = array(
			'total'     => count( $attachment_ids ),
			'assigned'  => 0,
			'skipped'   => 0,
			'errors'    => 0,
			'items'     => array(),
		);

		foreach ( $attachment_ids as $attachment_id ) {
			$metadata = wp_get_attachment_metadata( $attachment_id );
			if ( ! is_array( $metadata ) ) {
				$metadata = array();
			}

			$match = $this->evaluator->evaluate( $attachment_id, $metadata );

			if ( ! $match ) {
				$results['items'][] = array(
					'attachment_id' => $attachment_id,
					'status'        => 'skipped',
					'message'       => __( 'No matching rule', 'vmfa-rules-engine' ),
				);
				++$results['skipped'];
				continue;
			}

			$assigned = $this->evaluator->assign_folder(
				$attachment_id,
				$match['folder_id'],
				$match['rule']
			);

			if ( $assigned ) {
				$folder             = get_term( $match['folder_id'], self::TAXONOMY );
				$results['items'][] = array(
					'attachment_id' => $attachment_id,
					'status'        => 'assigned',
					'folder_id'     => $match['folder_id'],
					'folder_name'   => $folder ? $folder->name : '',
					'rule_id'       => $match['rule']['id'],
					'rule_name'     => $match['rule']['name'],
				);
				++$results['assigned'];
			} else {
				$results['items'][] = array(
					'attachment_id' => $attachment_id,
					'status'        => 'error',
					'message'       => __( 'Failed to assign folder', 'vmfa-rules-engine' ),
				);
				++$results['errors'];
			}
		}

		return $results;
	}

	/**
	 * Get statistics about the media library.
	 *
	 * @return array Statistics.
	 */
	public function get_stats() {
		global $wpdb;

		// Total attachments.
		$total = (int) $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = 'attachment' AND post_status = 'inherit'"
		);

		// Attachments with folders.
		$assigned = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(DISTINCT p.ID) FROM {$wpdb->posts} p
				INNER JOIN {$wpdb->term_relationships} tr ON p.ID = tr.object_id
				INNER JOIN {$wpdb->term_taxonomy} tt ON tr.term_taxonomy_id = tt.term_taxonomy_id
				WHERE p.post_type = 'attachment' AND p.post_status = 'inherit' AND tt.taxonomy = %s",
				self::TAXONOMY
			)
		);

		$unassigned = $total - $assigned;

		// Total folders.
		$folders = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->term_taxonomy} WHERE taxonomy = %s",
				self::TAXONOMY
			)
		);

		return array(
			'total'      => $total,
			'assigned'   => $assigned,
			'unassigned' => $unassigned,
			'folders'    => $folders,
		);
	}
}
