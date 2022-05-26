<?php
declare( strict_types=1 );

namespace WP_Rocket\Engine\Optimization\DynamicLists;

use WP_Rocket\Abstract_Render;

class DynamicLists {
	use DataManagerTrait;

	/**
	 * APIClient instance
	 *
	 * @var APIClient
	 */
	private $api;

	const ROUTE_NAMESPACE = 'wp-rocket/v1';

	/**
	 * Instantiate the class.
	 *
	 * @param APIClient $api APIClient instance.
	 */
	public function __construct( APIClient $api ) {
		$this->api = $api;
	}
	/**
	 * Register wp rest route.
	 *
	 * @return void
	 */
	public function register_rest_route() {
		register_rest_route(
			self::ROUTE_NAMESPACE,
			'wpr-dynamic-lists',
			[
				'methods'             => 'PUT',
				'callback'            => [ $this, 'rest_update_response' ],
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
		return current_user_can( 'rocket_manage_options' );
	}

	/**
	 * Enable CDN and add RocketCDN URL to WP Rocket options
	 *
	 * @param \WP_REST_Request $request the WP REST Request object.
	 *
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function rest_update_response( \WP_REST_Request $request ) {
		$response = $this->update_lists_from_remote();

		return rest_ensure_response( $response );
	}

	/**
	 * Update dynamic_lists from Api.
	 *
	 * @return array
	 */
	public function update_lists_from_remote() {
		$dynamic_lists = $this->get_lists_from_file();
		$hash          = $dynamic_lists ? md5( $dynamic_lists ) : '';
		$result        = $this->api->get_exclusions_list( $hash );
		if ( ( 200 !== $result['code'] && 201 !== $result['code'] ) || empty( $result['body'] ) ) {
			return [
				'success' => false,
				'data'    => '',
				'message' => __( 'Couldn\'t get updated lists from server.', 'rocket' ),
			];
		}
		if ( 201 === $result['code'] ) {
			return [
				'success' => true,
				'data'    => '',
				'message' => __( 'Lists are up to date.', 'rocket' ),
			];
		}
		if ( ! $this->save_dynamic_lists( $result['body'] ) ) {
			return [
				'success' => false,
				'data'    => '',
				'message' => __( 'Couldn\'t update lists.', 'rocket' ),
			];
		}

		return [
			'success' => true,
			'data'    => $result['body'],
			'message' => __( 'Lists are successfully updated.', 'rocket' ),
		];
	}

	/**
	 * Update dynamic_lists from Api after update.
	 *
	 * @return void
	 */
	public function update_lists_after_upgrade() {
		$this->update_lists_from_remote();
	}

	/**
	 * Schedule cron to update dynamic lists weekly.
	 *
	 * @return void
	 */
	public function schedule_lists_update() {
		if ( ! wp_next_scheduled( 'rocket_update_dynamic_lists' ) ) {
			wp_schedule_event( time(), 'weekly', 'rocket_update_dynamic_lists' );
		}
	}

	/**
	 * Clear lists_update schedule.
	 */
	public function clear_schedule_lists_update() {
		wp_clear_scheduled_hook( 'rocket_update_dynamic_lists' );
	}
}
