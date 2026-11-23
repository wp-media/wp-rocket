<?php

namespace WP_Rocket\Engine\License\API;

use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Common\JobManager\APIHandler\AbstractSafeAPIClient;

class UserClient extends AbstractSafeAPIClient {
	/**
	 * Use the CustomerDataTrait
	 */
	use CustomerDataTrait;

	const USER_ENDPOINT = 'https://api.wp-rocket.me/stat/1.0/wp-rocket/user.php';

	/**
	 * Transient set while the licence server rejects the licence, such as an unknown or nulled one.
	 */
	const LICENCE_REJECTED_TRANSIENT = 'wp_rocket_licence_rejected';

	/**
	 * WP Rocket options instance
	 *
	 * @var Options_Data
	 */
	private $options;

	/**
	 * Get the transient key for plugin updates.
	 *
	 * This method returns the transient key used for caching plugin updates.
	 *
	 * @return string The transient key for plugin updates.
	 */
	protected function get_transient_key() {
		return 'wpr_user_information';
	}

	/**
	 * Get the API URL for plugin updates.
	 *
	 * This method returns the API URL used for fetching plugin updates.
	 *
	 * @return string The API URL for plugin updates.
	 */
	protected function get_api_url() {
		return self::USER_ENDPOINT;
	}

	/**
	 * Instantiate the class
	 *
	 * @param Options_Data $options WP Rocket options instance.
	 */
	public function __construct( Options_Data $options ) {
		$this->options = $options;
	}

	/**
	 * Gets user data from cache if it exists, else gets it from the user endpoint
	 *
	 * Cache the user data for 24 hours in a transient
	 *
	 * @since 3.7.3
	 *
	 * @return bool|object
	 */
	public function get_user_data() {
		$cached_data = get_transient( 'wp_rocket_customer_data' );

		if ( false !== $cached_data ) {
			return $cached_data;
		}

		$data = $this->get_raw_user_data();

		if ( false === $data ) {
			return false;
		}

		set_transient( 'wp_rocket_customer_data', $data, DAY_IN_SECONDS );

		return $data;
	}

	/**
	 * Flushes the user data cache.
	 *
	 * @return void
	 */
	public function flush_cache() {
		delete_transient( 'wp_rocket_customer_data' );
	}

	/**
	 * Gets the user data from the user endpoint
	 *
	 * @since 3.7.3
	 *
	 * @return bool|object
	 */
	private function get_raw_user_data() {
		$customer_data = $this->get_customer_data();

		$response = $this->send_post_request(
			[
				'body' => 'user_id=' . rawurlencode( $customer_data['email'] ) . '&consumer_key=' . $customer_data['key'],
			],
			true
		);

		if ( is_wp_error( $response ) ) {
			return false;
		}

		$body = wp_remote_retrieve_body( $response );

		if ( empty( $body ) ) {
			return false;
		}

		return json_decode( $body );
	}

	/**
	 * Checks whether the licence server rejected the licence on its last answer.
	 *
	 * @return bool
	 */
	public function is_licence_rejected(): bool {
		return (bool) get_transient( self::LICENCE_REJECTED_TRANSIENT );
	}

	/**
	 * Validate response code, and record whether the licence server rejected the licence.
	 *
	 * The result is the parent's: a rejection stays an invalid answer, so the request backoff applies as before.
	 * The rejection flag lasts as long as the longest backoff, and the next valid answer clears it.
	 *
	 * @param array $response Response object.
	 * @return bool
	 */
	protected function valid_response_code( $response ) {
		if ( parent::valid_response_code( $response ) ) {
			delete_transient( self::LICENCE_REJECTED_TRANSIENT );

			return true;
		}

		if ( $this->is_licence_rejection( $response ) ) {
			set_transient( self::LICENCE_REJECTED_TRANSIENT, 1, DAY_IN_SECONDS );
		}

		return false;
	}

	/**
	 * Checks whether a response is the licence server rejecting the licence.
	 *
	 * The endpoint answers 403 or 404 with a JSON body whose `success` is false. For a licence it does not know,
	 * the edge in front of it answers 403 with an HTML page instead, so a 403 is a rejection whatever its body.
	 *
	 * @param array $response Response object.
	 * @return bool
	 */
	private function is_licence_rejection( $response ): bool {
		$code = (int) wp_remote_retrieve_response_code( $response );

		if ( 403 === $code ) {
			return true;
		}

		return 404 === $code && false === ( json_decode( wp_remote_retrieve_body( $response ) )->success ?? null );
	}
}
