<?php
namespace WP_Rocket\Engine\CDN\RocketCDN;

use WP_Error;

/**
 * Class to Interact with the RocketCDN API
 */
class APIClient {
	const ROCKETCDN_API = 'https://rocketcdn.me/api/';

	/**
	 * Option name used as a short-lived lock to guard against firing duplicate,
	 * near-simultaneous requests to the RocketCDN subscription endpoint (e.g.
	 * two subscribers both reacting to the same current_screen call before the
	 * rocketcdn_status transient has been (re)populated).
	 *
	 * @var string
	 */
	const SUBSCRIPTION_FETCH_LOCK = 'rocketcdn_status_fetch_lock';

	/**
	 * Maximum time, in seconds, a fetch lock is honored before being considered
	 * stale (e.g. left behind by a request that crashed or timed out) and
	 * reclaimable by a later caller.
	 *
	 * @var int
	 */
	const SUBSCRIPTION_FETCH_LOCK_TTL = 30;

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
			'website_attached'              => false,
			'plan_type'                     => 'free',
			'plan_page_limit'               => 0,
			'website_id'                    => 0,
			'status_code'                   => 500,
			'success'                       => false,
		];

		$token = get_option( 'rocketcdn_user_token' );

		if ( empty( $token ) ) {
			return $default;
		}

		if ( ! $this->acquire_subscription_fetch_lock() ) {
			// Another (possibly concurrent) request is already fetching this same
			// data - reuse whatever it leaves behind instead of firing a duplicate
			// request against the RocketCDN API.
			$status = get_transient( 'rocketcdn_status' );

			return false !== $status ? $status : $default;
		}

		try {
			$args = [
				'headers' => [
					'Authorization' => 'Token ' . $token,
				],
			];

			$parsed_home = wp_parse_url( home_url() );
			if ( empty( $parsed_home['host'] ) ) {
				$this->set_status_transient( $default, 3 * MINUTE_IN_SECONDS );

				return $default;
			}

			$response = wp_remote_get(
				sprintf( '%1$ssubscription/%2$s/status', self::ROCKETCDN_API, $parsed_home['host'] ),
				$args
			);

			$status_code            = wp_remote_retrieve_response_code( $response );
			$default['status_code'] = $status_code;

			if ( 200 !== $status_code ) {
				$this->set_status_transient( $default, 404 !== $status_code ? 3 * MINUTE_IN_SECONDS : DAY_IN_SECONDS );

				return $default;
			}

			$data = wp_remote_retrieve_body( $response );

			if ( empty( $data ) ) {
				$this->set_status_transient( $default, 3 * MINUTE_IN_SECONDS );

				return $default;
			}

			$data = json_decode( $data, true );
			if ( empty( $data['success'] ) ) {
				$this->set_status_transient( $default, 3 * MINUTE_IN_SECONDS );
				return $default;
			}

			// Map the data.
			$final_data = [
				'id'                            => $data['subscription_id'] ?? 0,
				'is_active'                     => $data['website_activated'] ?? false,
				'cdn_url'                       => $data['cdn_url'] ?? '',
				'subscription_next_date_update' => $data['next_date_update'] ?? 0,
				'subscription_status'           => $data['status'] ?? 'cancelled',
				'website_attached'              => $data['website_attached'] ?? false,
				'plan_type'                     => $data['plan_type'] ?? 'free',
				'plan_page_limit'               => $data['plan_page_limit'] ?? 0,
				'website_id'                    => $data['website_id'] ?? 0,
				'status_code'                   => $status_code,
				'success'                       => true,
			];

			$this->set_status_transient( $final_data, DAY_IN_SECONDS );

			return $final_data;
		} finally {
			$this->release_subscription_fetch_lock();
		}
	}

	/**
	 * Attempts to acquire the short-lived subscription fetch lock.
	 *
	 * Relies on add_option()'s atomicity (a unique index on option_name in the
	 * options table) to guarantee only one concurrent caller can win the lock,
	 * which a plain get/set transient check-then-act cannot guarantee.
	 *
	 * @return bool True if the lock was acquired, false if another request already holds it.
	 */
	private function acquire_subscription_fetch_lock(): bool {
		if ( add_option( self::SUBSCRIPTION_FETCH_LOCK, time(), '', false ) ) {
			return true;
		}

		$locked_at = get_option( self::SUBSCRIPTION_FETCH_LOCK );

		// Reclaim a stale lock left behind by a request that crashed or timed out
		// before it could release it.
		if ( is_numeric( $locked_at ) && ( time() - (int) $locked_at ) > self::SUBSCRIPTION_FETCH_LOCK_TTL ) {
			delete_option( self::SUBSCRIPTION_FETCH_LOCK );

			return add_option( self::SUBSCRIPTION_FETCH_LOCK, time(), '', false );
		}

		return false;
	}

	/**
	 * Releases the subscription fetch lock.
	 *
	 * @return void
	 */
	private function release_subscription_fetch_lock(): void {
		delete_option( self::SUBSCRIPTION_FETCH_LOCK );
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
	 * @return array|WP_Error
	 */
	public function get_pricing_data() {
		$pricing = get_transient( 'rocketcdn_pricing' );

		if ( false !== $pricing ) {
			return $pricing;
		}

		return $this->get_remote_pricing_data();
	}

	/**
	 * Activates the RocketCDN subscription via API.
	 *
	 * @since 3.20.5
	 *
	 * @param string $token      RocketCDN API token.
	 * @param int    $website_id RocketCDN website ID.
	 * @return true|WP_Error True on success, WP_Error on failure.
	 */
	public function activate_subscription( string $token, int $website_id ) {
		if ( empty( $token ) || empty( $website_id ) ) {
			return $this->get_wp_error( __( 'Missing required parameters for subscription activation.', 'rocket' ) );
		}

		$args = [
			'headers' => [
				'Authorization' => 'Token ' . $token,
				'Content-Type'  => 'application/json',
			],
			'body'    => wp_json_encode(
				[
					'is_active' => true,
				]
			),
			'method'  => 'PATCH',
		];

		$response = wp_remote_request(
			self::ROCKETCDN_API . 'website/' . $website_id . '/',
			$args
		);

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$response_code = wp_remote_retrieve_response_code( $response );

		if ( 200 !== $response_code ) {
			return $this->get_wp_error(
				sprintf(
					/* translators: %d: HTTP response code */
					__( 'Failed to activate RocketCDN subscription. API returned %d.', 'rocket' ),
					$response_code
				)
			);
		}

		return true;
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

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		if ( 200 !== wp_remote_retrieve_response_code( $response ) ) {
			return $this->get_wp_error( __( 'We could not fetch the current price because RocketCDN API returned an unexpected error code.', 'rocket' ) );
		}

		$data = wp_remote_retrieve_body( $response );

		if ( empty( $data ) ) {
			return $this->get_wp_error( __( 'RocketCDN is not available at the moment. Please retry later.', 'rocket' ) );
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
	 * @param string $message Error message.
	 *
	 * @return WP_Error
	 */
	private function get_wp_error( string $message ) {
		return new WP_Error( 'rocketcdn_error', $message );
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

		if ( ! isset( $subscription['website_id'] ) || 0 === $subscription['website_id'] ) {
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
			self::ROCKETCDN_API . 'website/' . $subscription['website_id'] . '/purge/',
			$args
		);

		if ( is_wp_error( $response ) ) {
			return [
				'status'  => $status,
				'message' => $response->get_error_message(),
			];
		}

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
					isset( $data->message ) ? $data->message : ''
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
