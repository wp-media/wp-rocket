<?php
namespace WP_Rocket\Engine\CDN\RocketCDN;

use WP_Error;
use WP_Rocket\Engine\CDN\RocketCDN\Database\Queries\RocketCDN as RocketCDNQuery;
use WP_Rocket\Engine\Common\Utils;

/**
 * Class to Interact with the RocketCDN API
 */
class APIClient {
	const ROCKETCDN_API = 'https://rocketcdn.me/api/';

	/**
	 * Option key used to persist last known subscription status.
	 */
	private const LAST_KNOWN_STATUS_OPTION = 'rocketcdn_last_known_subscription_status';

	/**
	 * Option key used to persist last known plan type.
	 */
	private const LAST_KNOWN_PLAN_OPTION = 'rocketcdn_last_known_plan_type';

	/**
	 * RocketCDN pages query.
	 *
	 * @var RocketCDNQuery|null
	 */
	private $query;

	/**
	 * Constructor.
	 *
	 * @param RocketCDNQuery|null $query RocketCDN pages query.
	 */
	public function __construct( ?RocketCDNQuery $query = null ) {
		$this->query = $query;
	}

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

		$parsed_home = wp_parse_url( home_url() );
		if ( empty( $parsed_home['host'] ) ) {
			$this->set_status_transient( $default, 3 * MINUTE_IN_SECONDS );

			return $default;
		}

		$response = wp_remote_get(
			sprintf( '%1$ssubscription/%2$s/status', self::ROCKETCDN_API, $parsed_home['host'] ),
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
		];

		$this->maybe_invalidate_cache_on_status_transition( $final_data );

		$this->set_status_transient( $final_data, DAY_IN_SECONDS );

		return $final_data;
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
	 * Invalidates cache if subscription status changed.
	 *
	 * @param array $fresh_data Fresh subscription data.
	 * @return void
	 */
	private function maybe_invalidate_cache_on_status_transition( array $fresh_data ): void {
		$current_status  = (string) ( $fresh_data['subscription_status'] ?? '' );
		$current_plan    = (string) ( $fresh_data['plan_type'] ?? '' );
		$cached_status   = get_transient( 'rocketcdn_status' );
		$previous_status = '';

		if ( is_array( $cached_status ) && ! empty( $cached_status['subscription_status'] ) ) {
			$previous_status = (string) $cached_status['subscription_status'];
		} else {
			$previous_status = (string) get_option( self::LAST_KNOWN_STATUS_OPTION, '' );
		}

		if ( ! empty( $previous_status ) && $previous_status !== $current_status ) {
			if ( 'paid' === $current_plan ) {
				rocket_clean_domain();
			}

			if ( 'free' === $current_plan ) {
				$this->clear_free_plan_selected_pages_cache();
			}
		}

		update_option( self::LAST_KNOWN_STATUS_OPTION, $current_status );
		update_option( self::LAST_KNOWN_PLAN_OPTION, $current_plan );
	}

	/**
	 * Clears selected pages cache for free RocketCDN plan.
	 *
	 * @return void
	 */
	public function clear_free_plan_pages_cache(): void {
		$this->clear_free_plan_selected_pages_cache();
	}

	/**
	 * Clears page cache for all selected pages in free plan.
	 *
	 * @return void
	 */
	private function clear_free_plan_selected_pages_cache(): void {
		if ( null === $this->query ) {
			return;
		}

		$pages = $this->query->query( [] );

		if ( ! is_array( $pages ) ) {
			return;
		}

		foreach ( $pages as $page ) {
			if ( ! isset( $page->url ) || empty( $page->url ) ) {
				continue;
			}

			if ( Utils::is_home( $page->url ) ) {
				rocket_clean_home();
				continue;
			}

			rocket_clean_files( [ $page->url ] );
		}
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
