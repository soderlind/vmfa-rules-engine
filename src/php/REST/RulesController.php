<?php
/**
 * Rules REST API Controller.
 *
 * @package VmfaRulesEngine
 */

namespace VmfaRulesEngine\REST;

use VmfaRulesEngine\Repository\RuleRepository;
use VmfaRulesEngine\Services\BatchProcessor;
use WP_REST_Controller;
use WP_REST_Server;
use WP_REST_Request;
use WP_REST_Response;
use WP_Error;

/**
 * REST API controller for rule management.
 */
class RulesController extends WP_REST_Controller {

	/**
	 * Namespace.
	 *
	 * @var string
	 */
	protected $namespace = 'vmfa-rules/v1';

	/**
	 * Rule repository.
	 *
	 * @var RuleRepository
	 */
	private $repository;

	/**
	 * Batch processor.
	 *
	 * @var BatchProcessor
	 */
	private $batch_processor;

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->repository      = new RuleRepository();
		$this->batch_processor = new BatchProcessor();
	}

	/**
	 * Register routes.
	 *
	 * @return void
	 */
	public function register_routes() {
		// GET/POST /rules.
		register_rest_route(
			$this->namespace,
			'/rules',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_rules' ),
					'permission_callback' => array( $this, 'check_permission' ),
				),
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'create_rule' ),
					'permission_callback' => array( $this, 'check_permission' ),
					'args'                => $this->get_rule_args(),
				),
			)
		);

		// GET/PUT/DELETE /rules/{id}.
		register_rest_route(
			$this->namespace,
			'/rules/(?P<id>[a-zA-Z0-9_]+)',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_rule' ),
					'permission_callback' => array( $this, 'check_permission' ),
					'args'                => array(
						'id' => array(
							'required'          => true,
							'sanitize_callback' => 'sanitize_text_field',
						),
					),
				),
				array(
					'methods'             => WP_REST_Server::EDITABLE,
					'callback'            => array( $this, 'update_rule' ),
					'permission_callback' => array( $this, 'check_permission' ),
					'args'                => $this->get_rule_args(),
				),
				array(
					'methods'             => WP_REST_Server::DELETABLE,
					'callback'            => array( $this, 'delete_rule' ),
					'permission_callback' => array( $this, 'check_permission' ),
					'args'                => array(
						'id' => array(
							'required'          => true,
							'sanitize_callback' => 'sanitize_text_field',
						),
					),
				),
			)
		);

		// POST /rules/reorder.
		register_rest_route(
			$this->namespace,
			'/rules/reorder',
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'reorder_rules' ),
				'permission_callback' => array( $this, 'check_permission' ),
				'args'                => array(
					'order' => array(
						'required'    => true,
						'type'        => 'array',
						'items'       => array( 'type' => 'string' ),
						'description' => __( 'Array of rule IDs in desired order.', 'vmfa-rules-engine' ),
					),
				),
			)
		);

		// POST /preview.
		register_rest_route(
			$this->namespace,
			'/preview',
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'preview_rules' ),
				'permission_callback' => array( $this, 'check_permission' ),
				'args'                => array(
					'unassigned_only' => array(
						'type'    => 'boolean',
						'default' => true,
					),
					'mime_type'       => array(
						'type'              => 'string',
						'sanitize_callback' => 'sanitize_mime_type',
					),
					'limit'           => array(
						'type'    => 'integer',
						'default' => 50,
						'minimum' => 1,
						'maximum' => 200,
					),
					'target_matches'  => array(
						'type'    => 'integer',
						'default' => null,
						'minimum' => 1,
						'maximum' => 200,
					),
					'max_scan'        => array(
						'type'    => 'integer',
						'default' => 500,
						'minimum' => 50,
						'maximum' => 5000,
					),
					'offset'          => array(
						'type'    => 'integer',
						'default' => 0,
						'minimum' => 0,
					),
					'rule_id'         => array(
						'type'              => 'string',
						'sanitize_callback' => 'sanitize_text_field',
						'default'           => null,
					),
				),
			)
		);

		// POST /apply-rules.
		register_rest_route(
			$this->namespace,
			'/apply-rules',
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'apply_rules' ),
				'permission_callback' => array( $this, 'check_permission' ),
				'args'                => array(
					'unassigned_only' => array(
						'type'    => 'boolean',
						'default' => true,
					),
					'mime_type'       => array(
						'type'              => 'string',
						'sanitize_callback' => 'sanitize_mime_type',
					),
					'attachment_ids'  => array(
						'type'  => 'array',
						'items' => array( 'type' => 'integer' ),
					),
				),
			)
		);

		// GET /stats.
		register_rest_route(
			$this->namespace,
			'/stats',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_stats' ),
				'permission_callback' => array( $this, 'check_permission' ),
			)
		);

		// GET /users (for author condition).
		register_rest_route(
			$this->namespace,
			'/users',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_users' ),
				'permission_callback' => array( $this, 'check_permission' ),
			)
		);
	}

	/**
	 * Check if user has permission.
	 *
	 * @return bool|WP_Error True if allowed, WP_Error otherwise.
	 */
	public function check_permission() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return new WP_Error(
				'rest_forbidden',
				__( 'You do not have permission to access this resource.', 'vmfa-rules-engine' ),
				array( 'status' => 403 )
			);
		}
		return true;
	}

	/**
	 * Get all rules.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response
	 */
	public function get_rules( $request ) {
		$rules = $this->repository->get_all();
		return rest_ensure_response( $rules );
	}

	/**
	 * Get a single rule.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response|WP_Error
	 */
	public function get_rule( $request ) {
		$id   = $request->get_param( 'id' );
		$rule = $this->repository->get( $id );

		if ( ! $rule ) {
			return new WP_Error(
				'rest_not_found',
				__( 'Rule not found.', 'vmfa-rules-engine' ),
				array( 'status' => 404 )
			);
		}

		return rest_ensure_response( $rule );
	}

	/**
	 * Create a new rule.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response|WP_Error
	 */
	public function create_rule( $request ) {
		$data = $this->get_rule_data_from_request( $request );
		$rule = $this->repository->create( $data );

		return rest_ensure_response( $rule );
	}

	/**
	 * Update an existing rule.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response|WP_Error
	 */
	public function update_rule( $request ) {
		$id   = $request->get_param( 'id' );
		$data = $this->get_rule_data_from_request( $request );
		$rule = $this->repository->update( $id, $data );

		if ( ! $rule ) {
			return new WP_Error(
				'rest_not_found',
				__( 'Rule not found.', 'vmfa-rules-engine' ),
				array( 'status' => 404 )
			);
		}

		return rest_ensure_response( $rule );
	}

	/**
	 * Delete a rule.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response|WP_Error
	 */
	public function delete_rule( $request ) {
		$id      = $request->get_param( 'id' );
		$deleted = $this->repository->delete( $id );

		if ( ! $deleted ) {
			return new WP_Error(
				'rest_not_found',
				__( 'Rule not found.', 'vmfa-rules-engine' ),
				array( 'status' => 404 )
			);
		}

		return rest_ensure_response(
			array(
				'deleted' => true,
				'id'      => $id,
			)
		);
	}

	/**
	 * Reorder rules.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response
	 */
	public function reorder_rules( $request ) {
		$order = $request->get_param( 'order' );
		$rules = $this->repository->reorder( $order );

		return rest_ensure_response( $rules );
	}

	/**
	 * Preview rule application.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response
	 */
	public function preview_rules( $request ) {
		$args = array(
			'unassigned_only' => $request->get_param( 'unassigned_only' ),
			'posts_per_page'  => $request->get_param( 'limit' ),
			'offset'          => $request->get_param( 'offset' ),
			'target_matches'  => $request->get_param( 'target_matches' ),
			'max_scan'        => $request->get_param( 'max_scan' ),
		);

		if ( $request->get_param( 'mime_type' ) ) {
			$args[ 'mime_type' ] = $request->get_param( 'mime_type' );
		}

		if ( $request->get_param( 'rule_id' ) ) {
			$args[ 'rule_id' ] = $request->get_param( 'rule_id' );
		}

		$results = $this->batch_processor->preview( $args );

		return rest_ensure_response( $results );
	}

	/**
	 * Apply rules to media library.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response
	 */
	public function apply_rules( $request ) {
		$args = array(
			'unassigned_only' => $request->get_param( 'unassigned_only' ),
		);

		if ( $request->get_param( 'mime_type' ) ) {
			$args[ 'mime_type' ] = $request->get_param( 'mime_type' );
		}

		// If specific attachment IDs provided, use them.
		$attachment_ids = $request->get_param( 'attachment_ids' );
		if ( ! empty( $attachment_ids ) ) {
			$args[ 'post__in' ] = array_map( 'absint', $attachment_ids );
			unset( $args[ 'unassigned_only' ] );
		}

		$results = $this->batch_processor->apply( $args );

		return rest_ensure_response( $results );
	}

	/**
	 * Get media library statistics.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response
	 */
	public function get_stats( $request ) {
		$stats = $this->batch_processor->get_stats();

		// Add rules count.
		$rules            = $this->repository->get_all();
		$stats[ 'rules' ] = count( $rules );

		$enabled_rules            = $this->repository->get_enabled();
		$stats[ 'rules_enabled' ] = count( $enabled_rules );

		return rest_ensure_response( $stats );
	}

	/**
	 * Get users for author condition.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response
	 */
	public function get_users( $request ) {
		$users = get_users(
			array(
				'capability' => 'upload_files',
				'orderby'    => 'display_name',
				'order'      => 'ASC',
			)
		);

		$result = array();
		foreach ( $users as $user ) {
			$result[] = array(
				'id'           => $user->ID,
				'display_name' => $user->display_name,
				'user_login'   => $user->user_login,
			);
		}

		return rest_ensure_response( $result );
	}

	/**
	 * Get rule arguments for validation.
	 *
	 * @return array
	 */
	private function get_rule_args() {
		return array(
			'name'            => array(
				'type'              => 'string',
				'required'          => true,
				'sanitize_callback' => 'sanitize_text_field',
			),
			'conditions'      => array(
				'type'    => 'array',
				'default' => array(),
			),
			'folder_id'       => array(
				'type'              => 'integer',
				'required'          => true,
				'sanitize_callback' => 'absint',
			),
			'priority'        => array(
				'type'              => 'integer',
				'default'           => 10,
				'sanitize_callback' => 'absint',
			),
			'stop_processing' => array(
				'type'    => 'boolean',
				'default' => true,
			),
			'enabled'         => array(
				'type'    => 'boolean',
				'default' => true,
			),
		);
	}

	/**
	 * Extract rule data from request.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return array
	 */
	private function get_rule_data_from_request( $request ) {
		return array(
			'name'            => $request->get_param( 'name' ),
			'conditions'      => $request->get_param( 'conditions' ) ?? array(),
			'folder_id'       => $request->get_param( 'folder_id' ),
			'priority'        => $request->get_param( 'priority' ) ?? 10,
			'stop_processing' => $request->get_param( 'stop_processing' ) ?? true,
			'enabled'         => $request->get_param( 'enabled' ) ?? true,
		);
	}
}
