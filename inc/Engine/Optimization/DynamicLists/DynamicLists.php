<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Optimization\DynamicLists;

use WP_Rocket\Admin\Options_Data;

class DynamicLists {
	/**
	 * Options_Data instance
	 *
	 * @var Options_Data
	 */
	private $options;

	/**
	 * DataManager instance
	 *
	 * @var DataManager
	 */
	private $data_manager;

	/**
	 * Instantiate the class
	 *
	 * @param Options_Data $options Options_Data instance.
	 * @param DataManager  $data_manager DataManager instance.
	 */
	public function __construct( Options_Data $options, DataManager $data_manager ) {
		$this->options      = $options;
		$this->data_manager = $data_manager;
	}

	/**
	 * Registers the dynamic lists update route
	 *
	 * @return void
	 */
	public function register_rest_route() {
		register_rest_route(
			'wp-rocket/v1',
			'dynamic_lists/update',
			[
				'methods'             => 'PUT',
				'callback'            => [ $this, 'rest_update_response' ],
				'permission_callback' => current_user_can( 'rocket_manage_options' ),
			]
		);
	}

	/**
	 * Returns the update response
	 *
	 * @return WP_REST_Response
	 */
	public function rest_update_response() {
		return rest_ensure_request( $this->update_lists_from_remote() );
	}

	/**
	 * Updates the lists from remote
	 *
	 * @return bool
	 */
	private function update_lists_from_remote() {
		$response = wp_remote_post(
			'https://wp-rocket.me/dynamiclists',
			[
				'body' => [
					'email' => $this->options->get( 'consumer_email', '' ),
					'key'   => $this->options->get( 'consumer_key', '' ),
					'hash'  => $this->data_manager->get_lists_hash(),
				],
			]
		);

		if ( 200 !== wp_remote_retrieve_response_code( $response ) ) {
			return false;
		}

		$body = wp_remote_retrieve_body( $response );

		if ( empty( $body ) ) {
			return false;
		}

		return $this->data_manager->put_lists_to_file( $body );
	}
}
