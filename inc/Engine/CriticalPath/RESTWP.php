<?php

namespace WP_Rocket\Engine\CriticalPath;

use WP_REST_Request;
use WP_REST_Response;
use WP_Error;

/**
 * Class RESTWP
 *
 * @package WP_Rocket\Engine\CriticalPath
 */
abstract class RESTWP {

	/**
	 * Namespace for REST Route.
	 */
	const ROUTE_NAMESPACE = 'wp-rocket/v1';

	/**
	 * Part of route namespace for this inherited class item type.
	 *
	 * @var string $route_namespace to be set with like post, term.
	 */
	protected $route_namespace;

	/**
	 * CPCSS generation and deletion service.
	 *
	 * @var CPCSSService instance for this service.
	 */
	private $cpcss_service;

	/**
	 * RESTWP constructor.
	 *
	 * @since 3.6
	 *
	 * @param CPCSSService $cpcss_service Has the logic for cpcss generation and deletion.
	 */
	public function __construct( CPCSSService $cpcss_service ) {
		$this->cpcss_service = $cpcss_service;
	}

	/**
	 * Registers the generate route in the WP REST API
	 *
	 * @since 3.6
	 *
	 * @return void
	 */
	public function register_generate_route() {
		register_rest_route(
			self::ROUTE_NAMESPACE,
			'cpcss/' . $this->route_namespace . '/(?P<id>[\d]+)',
			[
				'methods'             => 'POST',
				'callback'            => [ $this, 'generate' ],
				'permission_callback' => [ $this, 'check_permissions' ],
			]
		);
	}

	/**
	 * Register Delete CPCSS route in the WP REST API.
	 *
	 * @since  3.6
	 */
	public function register_delete_route() {
		register_rest_route(
			self::ROUTE_NAMESPACE,
			'cpcss/' . $this->route_namespace . '/(?P<id>[\d]+)',
			[
				'methods'             => 'DELETE',
				'callback'            => [ $this, 'delete' ],
				'permission_callback' => [ $this, 'check_permissions' ],
			]
		);
	}

	/**
	 * Checks user's permissions. This is a callback registered to REST route's "permission_callback" parameter.
	 *
	 * @since 3.6
	 *
	 * @return bool true if the user has permission; else false.
	 */
	public function check_permissions() {
		return current_user_can( 'rocket_regenerate_critical_css' );
	}

	/**
	 * Generates the CPCSS for the requested post ID.
	 *
	 * @since 3.6
	 *
	 * @param WP_REST_Request $request WP REST request response.
	 *
	 * @return WP_REST_Response
	 */
	public function generate( WP_REST_Request $request ) {
		$item_id = (int) $request->get_param( 'id' );
		// validate item.
		$validated = $this->validate_item_for_generate( $item_id );
		if ( is_wp_error( $validated ) ) {
			return rest_ensure_response( $this->return_error( $validated ) );
		}

		// get item url.
		$item_url  = $this->get_url( $item_id );
		$item_path = $this->get_path( $item_id );
		$timeout   = ( isset( $request['timeout'] ) && ! empty( $request['timeout'] ) );

		$generated = $this->cpcss_service->process_generate( $item_url, $item_path, $timeout );

		if ( is_wp_error( $generated ) ) {
			return rest_ensure_response(
				$this->return_error( $generated )
			);
		}

		return rest_ensure_response(
			$this->return_success( $generated )
		);

	}

	/**
	 * Validate the item to be sent to generate CPCSS.
	 *
	 * @since 3.6
	 *
	 * @param int $item_id ID for this item to be validated.
	 *
	 * @return true|WP_Error
	 */
	abstract protected function validate_item_for_generate( $item_id );

	/**
	 * Validate the item to be sent to Delete CPCSS.
	 *
	 * @since 3.6
	 *
	 * @param int $item_id ID for this item to be validated.
	 *
	 * @return true|WP_Error
	 */
	abstract protected function validate_item_for_delete( $item_id );

	/**
	 * Get url for this item.
	 *
	 * @since 3.6
	 *
	 * @param int $item_id ID for this item to get Url for.
	 *
	 * @return false|string
	 */
	abstract protected function get_url( $item_id );

	/**
	 * Get CPCSS file path to save CPCSS code into.
	 *
	 * @since 3.6
	 *
	 * @param int $item_id ID for this item to get the path for.
	 *
	 * @return string
	 */
	abstract protected function get_path( $item_id );

	/**
	 * Delete Post ID CPCSS file.
	 *
	 * @since 3.6
	 *
	 * @param WP_REST_Request $request the WP Rest Request object.
	 *
	 * @return WP_REST_Response
	 */
	public function delete( WP_REST_Request $request ) {
		$item_id = (int) $request->get_param( 'id' );

		// validate item.
		$validated = $this->validate_item_for_delete( $item_id );
		if ( is_wp_error( $validated ) ) {
			return rest_ensure_response( $this->return_error( $validated ) );
		}

		$item_path = $this->get_path( $item_id );
		$deleted   = $this->cpcss_service->process_delete( $item_path );
		if ( is_wp_error( $deleted ) ) {
			return rest_ensure_response(
				$this->return_error( $deleted )
			);
		}

		return rest_ensure_response(
			$this->return_success( $deleted )
		);
	}

	/**
	 * Returns the formatted array response
	 *
	 * @since 3.6
	 *
	 * @param bool   $success True for success, false otherwise.
	 * @param string $code    The code to use for the response.
	 * @param string $message The message to send in the response.
	 * @param int    $status  The status code to send for the response.
	 *
	 * @return array
	 */
	protected function return_array_response( $success = false, $code = '', $message = '', $status = 200 ) {
		return [
			'success' => $success,
			'code'    => $code,
			'message' => $message,
			'data'    => [
				'status' => $status,
			],
		];
	}

	/**
	 * Convert WP_Error into array to be used in response.
	 *
	 * @since 3.6
	 *
	 * @param WP_Error $error Error that will be converted to array.
	 *
	 * @return array
	 */
	protected function return_error( $error ) {
		$error_data = $error->get_error_data();

		return $this->return_array_response(
			false,
			$error->get_error_code(),
			$error->get_error_message(),
			isset( $error_data['status'] ) ? $error_data['status'] : 400
		);
	}

	/**
	 * Return success to be used in response.
	 *
	 * @since 3.6
	 *
	 * @param array $data which has success parameters with two keys: code and message.
	 *
	 * @return array
	 */
	protected function return_success( $data ) {
		return $this->return_array_response(
			true,
			$data['code'],
			$data['message'],
			200
		);
	}

}
