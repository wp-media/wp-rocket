<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\CDN\RocketCDN\APIHandler;

use WP_Rocket\Engine\CDN\RocketCDN\APIClient;
use WP_Rocket\Engine\Common\JobManager\APIHandler\AbstractSafeAPIClient;
use WP_Rocket\Engine\License\API\User;

/**
 * Class to Interact with the RocketCDN API - website search.
 */
class WebsiteSearch extends AbstractSafeAPIClient {

	/**
	 * The site URL to search for in the RocketCDN API.
	 *
	 * @var string
	 */
	private $site_url;

	/**
	 * Set site_url.
	 *
	 * @param string $site_url Site url.
	 * @return void
	 */
	public function set_site_url( string $site_url ): void {
		$this->site_url = $site_url;
	}

	/**
	 * Get the transient key for making this API Client calls.
	 *
	 * @return string The transient key.
	 */
	protected function get_transient_key() {
		return 'rocket_cdn_website_search';
	}

	/**
	 * Get the API URL for website search.
	 *
	 * @return string The API URL.
	 */
	protected function get_api_url() {
		if ( empty( $this->site_url ) ) {
			return '';
		}
		return add_query_arg(
			'url',
			untrailingslashit( $this->site_url ),
			APIClient::ROCKETCDN_API . 'website/search/'
		);
	}

	/**
	 * Check RocketCDN free account creation status.
	 *
	 * @return array|false
	 */
	public function find() {
		$cached = get_transient( $this->get_transient_key() );
		if ( false !== $cached ) {
			return $cached;
		}

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

		$response = json_decode( $response, true );
		if ( ! is_array( $response ) ) {
			return false;
		}

		$final = [
			'subscription_status' => $response['subscription_status'] ?? 'cancelled',
			'plan_type'           => $response['subscription_plan_type'] ?? 'free',
			'status_code'         => wp_remote_retrieve_response_code( $response ),
			'website_status'      => $response['status'] ?? '',
		];
		set_transient( $this->get_transient_key(), $final, HOUR_IN_SECONDS );

		return $final;
	}

	/**
	 * Validate response code.
	 *
	 * @param array $response Response object.
	 * @return bool
	 */
	protected function valid_response_code( $response ) {
		return in_array( wp_remote_retrieve_response_code( $response ), [ 200, 404 ], true );
	}

	/**
	 * Validate response body.
	 *
	 * @param array $response Response object.
	 * @return bool
	 */
	protected function valid_response_body( $response ) {
		return ! empty( wp_remote_retrieve_body( $response ) );
	}
}
