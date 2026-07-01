<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\CDN\RocketCDN\APIHandler;

use WP_Rocket\Engine\CDN\RocketCDN\APIClient;
use WP_Rocket\Engine\Common\JobManager\APIHandler\AbstractSafeAPIClient;
use WP_Rocket\Engine\License\API\User;

/**
 * Class to Interact with the RocketCDN API - check creation status.
 */
class CheckStatusAPIClient extends AbstractSafeAPIClient {

	/**
	 * Task ID to check the status.
	 *
	 * @var string
	 */
	private $task_id;

	/**
	 * Set task ID.
	 *
	 * @param string $task_id Task ID.
	 * @return void
	 */
	public function set_task_id( string $task_id ): void {
		$this->task_id = $task_id;
	}

	/**
	 * Get the transient key for making this API Client calls.
	 *
	 * @return string The transient key for plugin updates.
	 */
	protected function get_transient_key() {
		return 'rocket_cdn_check_status_request';
	}

	/**
	 * Get the API URL for creating rocketcdn free account silently.
	 *
	 * @return string The API URL.
	 */
	protected function get_api_url() {
		if ( empty( $this->task_id ) ) {
			return '';
		}
		return sprintf( '%1$swebsite/task/%2$s/', APIClient::ROCKETCDN_API, $this->task_id );
	}

	/**
	 * Check RocketCDN free account creation status.
	 *
	 * @return array|false
	 */
	public function check() {
		$token = get_option( 'rocketcdn_user_token' );

		if ( empty( $token ) ) {
			return false;
		}

		$args = [
			'headers' => [
				'Authorization' => 'Token ' . $token,
			],
		];

		$response = $this->send_get_request( $args, true );

		if ( is_wp_error( $response ) ) {
			return false;
		}

		$response = wp_remote_retrieve_body( $response );
		if ( empty( $response ) ) {
			return false;
		}

		return json_decode( $response, true );
	}
}
