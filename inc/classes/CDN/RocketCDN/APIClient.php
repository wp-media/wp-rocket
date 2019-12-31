<?php
namespace WP_Rocket\CDN\RocketCDN;

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
	 * @author Remy Perona
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
	 * @author Remy Perona
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
			self::ROCKETCDN_API . 'website/?url=' . home_url(),
			$args
		);

		if ( 200 !== wp_remote_retrieve_response_code( $response ) ) {
			set_transient( 'rocketcdn_status', $default, WEEK_IN_SECONDS );

			return $default;
		}

		$data = wp_remote_retrieve_body( $response );

		if ( empty( $data ) ) {
			set_transient( 'rocketcdn_status', $default, WEEK_IN_SECONDS );

			return $default;
		}

		$data = json_decode( $data );
		$data = array_intersect_key( $data, $default );

		set_transient( 'rocketcdn_status', $data, WEEK_IN_SECONDS );

		return $data;
	}

	/**
	 * Gets pricing & promotion data for RocketCDN from cache if it exists
	 *
	 * Else do a request to the API to get fresh data
	 *
	 * @since 3.5
	 * @author Remy Perona
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
	 * @author Remy Perona
	 *
	 * @return array|WP_Error
	 */
	private function get_remote_pricing_data() {
		$error = new \WP_Error( 'rocketcdn_error', __( 'RocketCDN is not available at the moment. Plese retry later', 'rocket' ) );

		$response = wp_remote_get(
			self::ROCKETCDN_API . 'pricing'
		);

		if ( 200 !== wp_remote_retrieve_response_code( $response ) ) {
			return $error;
		}

		$data = wp_remote_retrieve_body( $response );

		if ( empty( $data ) ) {
			return $error;
		}

		set_transient( 'rocketcdn_pricing', $data, 6 * HOUR_IN_SECONDS );

		return json_decode( $data, true );
	}

	/**
	 * Sends a request to the API to purge the CDN cache
	 *
	 * @since 3.5
	 * @author Remy Perona
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

		$status = 'success';

		return [
			'status'  => $status,
			'message' => __( 'RocketCDN cache purge successful.', 'rocket' ),
		];
	}
}
