<?php
namespace WP_Rocket\Engine\CDN\RocketCDN;

use WP_Error;

/**
 * Class to Interact with the RocketCDN API
 */
class APIClient {
	const ROCKETCDN_API = 'https://rocketcdn.me/api/';

	/**
	 * Gets current RocketCDN subscription data from cache if it exists
	 *
	 * Else do a request to the API to get fresh data
	 *
	 * @since 3.5
	 *
	 * @return array
	 */
	public function get_subscription_data() {
		$status = get_transient( 'rocketcdn_status' );

		if ( false !== $status ) {
			return $status;
		}

		return $this->get_remote_subscription_data();
	}

	/**
	 * Gets fresh RocketCDN subscription data from the API
	 *
	 * @since 3.5
	 *
	 * @return array
	 */
	private function get_remote_subscription_data() {
		$default = [
			'id'                            => 0,
			'is_active'                     => false,
			'cdn_url'                       => '',
			'subscription_next_date_update' => 0,
			'subscription_status'           => 'cancelled',
		];

		$token = get_option( 'rocketcdn_user_token' );

		if ( empty( $token ) ) {
			return $default;
		}

		$args = [
			'headers' => [
				'Authorization' => 'Token ' . $token,
			],
		];

		$response = wp_remote_get(
			self::ROCKETCDN_API . 'website/search/?url=' . home_url(),
			$args
		);

		if ( 200 !== wp_remote_retrieve_response_code( $response ) ) {
			$this->set_status_transient( $default, 3 * MINUTE_IN_SECONDS );

			return $default;
		}

		$data = wp_remote_retrieve_body( $response );

		if ( empty( $data ) ) {
			$this->set_status_transient( $default, 3 * MINUTE_IN_SECONDS );

			return $default;
		}

		$data = json_decode( $data, true );
		$data = array_intersect_key( (array) $data, $default );
		$data = array_merge( $default, $data );

		$this->set_status_transient( $data, WEEK_IN_SECONDS );

		return $data;
	}

	/**
	 * Sets the RocketCDN status transient with the provided value
	 *
	 * @since 3.5
	 *
	 * @param array $value Transient value.
	 * @param int   $duration Transient duration.
	 * @return void
	 */
	private function set_status_transient( $value, $duration ) {
		set_transient( 'rocketcdn_status', $value, $duration );
	}

	/**
	 * Gets pricing & promotion data for RocketCDN from cache if it exists
	 *
	 * Else do a request to the API to get fresh data
	 *
	 * @since 3.5
	 *
	 * @return array
	 */
	public function get_pricing_data() {
		$pricing = get_transient( 'rocketcdn_pricing' );

		if ( false !== $pricing ) {
			return $pricing;
		}

		return $this->get_remote_pricing_data();
	}

	/**
	 * Gets fresh pricing & promotion data for RocketCDN
	 *
	 * @since 3.5
	 *
	 * @return array|WP_Error
	 */
	private function get_remote_pricing_data() {
		$response = wp_remote_get( self::ROCKETCDN_API . 'pricing' );

		if ( 200 !== wp_remote_retrieve_response_code( $response ) ) {
			return $this->get_wp_error();
		}

		$data = wp_remote_retrieve_body( $response );

		if ( empty( $data ) ) {
			return $this->get_wp_error();
		}

		$data = json_decode( $data, true );

		set_transient( 'rocketcdn_pricing', $data, 6 * HOUR_IN_SECONDS );

		return $data;
	}

	/**
	 * Gets a new WP_Error instance
	 *
	 * @since 3.5
	 *
	 * @return WP_Error
	 */
	private function get_wp_error() {
		return new WP_Error( 'rocketcdn_error', __( 'RocketCDN is not available at the moment. Please retry later', 'rocket' ) );
	}

	/**
	 * Sends a request to the API to purge the CDN cache
	 *
	 * @since 3.5
	 *
	 * @return array
	 */
	public function purge_cache_request() {
		$subscription = $this->get_subscription_data();
		$status       = 'error';

		if ( ! isset( $subscription['id'] ) || 0 === $subscription['id'] ) {
			return [
				'status'  => $status,
				'message' => __( 'RocketCDN cache purge failed: Missing identifier parameter.', 'rocket' ),
			];
		}

		$token = get_option( 'rocketcdn_user_token' );

		if ( empty( $token ) ) {
			return [
				'status'  => $status,
				'message' => __( 'RocketCDN cache purge failed: Missing user token.', 'rocket' ),
			];
		}

		$args = [
			'method'  => 'DELETE',
			'headers' => [
				'Authorization' => 'Token ' . $token,
			],
		];

		$response = wp_remote_request(
			self::ROCKETCDN_API . 'website/' . $subscription['id'] . '/purge/',
			$args
		);

		if ( 200 !== wp_remote_retrieve_response_code( $response ) ) {
			return [
				'status'  => $status,
				'message' => __( 'RocketCDN cache purge failed: The API returned an unexpected response code.', 'rocket' ),
			];
		}

		$data = wp_remote_retrieve_body( $response );

		if ( empty( $data ) ) {
			return [
				'status'  => $status,
				'message' => __( 'RocketCDN cache purge failed: The API returned an empty response.', 'rocket' ),
			];
		}

		$data = json_decode( $data );

		if ( ! isset( $data->success ) ) {
			return [
				'status'  => $status,
				'message' => __( 'RocketCDN cache purge failed: The API returned an unexpected response.', 'rocket' ),
			];
		}

		if ( ! $data->success ) {
			return [
				'status'  => $status,
				'message' => sprintf(
					// translators: %s = message returned by the API.
					__( 'RocketCDN cache purge failed: %s.', 'rocket' ),
					$data->message
				),
			];
		}

		return [
			'status'  => 'success',
			'message' => __( 'RocketCDN cache purge successful.', 'rocket' ),
		];
	}

	/**
	 * Filter the arguments used in an HTTP request, to make sure our user token has not been overwritten
	 * by some other plugin.
	 *
	 * @since  3.5
	 *
	 * @param  array  $args An array of HTTP request arguments.
	 * @param  string $url  The request URL.
	 * @return array
	 */
	public function preserve_authorization_token( $args, $url ) {
		if ( strpos( $url, self::ROCKETCDN_API ) === false ) {
			return $args;
		}

		if ( empty( $args['headers']['Authorization'] ) && self::ROCKETCDN_API . 'pricing' === $url ) {
			return $args;
		}

		$token = get_option( 'rocketcdn_user_token' );

		if ( empty( $token ) ) {
			return $args;
		}

		$value = 'token ' . $token;

		if ( isset( $args['headers']['Authorization'] ) && $value === $args['headers']['Authorization'] ) {
			return $args;
		}

		$args['headers']['Authorization'] = $value;

		return $args;
	}
}
