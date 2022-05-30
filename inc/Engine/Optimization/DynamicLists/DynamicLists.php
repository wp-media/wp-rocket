<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Optimization\DynamicLists;

use WP_Rocket\Abstract_Render;

class DynamicLists {
	/**
	 * APIClient instance
	 *
	 * @var APIClient
	 */
	private $api;

	/**
	 * DataManager instance
	 *
	 * @var DataManager
	 */
	private $data_manager;

	const ROUTE_NAMESPACE = 'wp-rocket/v1';

	/**
	 * Instantiate the class.
	 *
	 * @param APIClient   $api APIClient instance.
	 * @param DataManager $data_manager DataManager instance.
	 */
	public function __construct( APIClient $api, DataManager $data_manager ) {
		$this->api          = $api;
		$this->data_manager = $data_manager;
	}

	/**
	 * Registers the dynamic lists update route
	 *
	 * @return void
	 */
	public function register_rest_route() {
		register_rest_route(
			self::ROUTE_NAMESPACE,
			'dynamic_lists/update',
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
	 * @return bool true if the user has permission; else false.
	 */
	public function check_permissions() {
		return current_user_can( 'rocket_manage_options' );
	}

	/**
	 * Returns the update response
	 *
	 * @return WP_REST_Response|WP_Error
	 */
	public function rest_update_response() {
		return rest_ensure_request( $this->update_lists_from_remote() );
	}

	/**
	 * Updates the lists from remote
	 *
	 * @return array
	 */
	public function update_lists_from_remote() {
		$result = $this->api->get_exclusions_list( $this->data_manager->get_lists_hash() );

		if (
			( 200 !== $result['code'] && 201 !== $result['code'] )
			|| empty( $result['body'] )
		) {
			return [
				'success' => false,
				'data'    => '',
				'message' => __( 'Could not get updated lists from server.', 'rocket' ),
			];
		}

		if ( 201 === $result['code'] ) {
			return [
				'success' => true,
				'data'    => '',
				'message' => __( 'Lists are up to date.', 'rocket' ),
			];
		}

		if ( ! $this->data_manager->save_dynamic_lists( $result['body'] ) ) {
			return [
				'success' => false,
				'data'    => '',
				'message' => __( 'Could not update lists.', 'rocket' ),
			];
		}

		return [
			'success' => true,
			'data'    => $result['body'],
			'message' => __( 'Lists are successfully updated.', 'rocket' ),
		];
	}

	/**
	 * Update dynamic list from API after plugin update.
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
	 * Clear dynamic lists update event.
	 */
	public function clear_schedule_lists_update() {
		wp_clear_scheduled_hook( 'rocket_update_dynamic_lists' );
	}
}
